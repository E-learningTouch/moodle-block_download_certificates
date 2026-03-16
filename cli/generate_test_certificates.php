<?php
// This file is part of Moodle - http://moodle.org/
//
// CLI script to generate REAL test certificates for load testing.
// Uses the official tool_certificate API to create valid certificates with PDF files.
//
// Usage: php blocks/download_certificates/cli/generate_test_certificates.php --count=1000
//
// @package   block_download_certificates

define('CLI_SCRIPT', true);

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->libdir . '/enrollib.php');
require_once($CFG->dirroot . '/course/lib.php');

list($options, $unrecognized) = cli_get_params(
    [
        'count' => 1000,
        'users' => 50,
        'courses' => 10,
        'help' => false,
        'cleanup' => false,
    ],
    ['c' => 'count', 'u' => 'users', 'n' => 'courses', 'h' => 'help']
);

if ($options['help']) {
    echo "Generate REAL test certificates for load testing.

This script will:
  1. Create test users (if needed)
  2. Create test courses (if needed)
  3. Create/reuse a certificate template
  4. Enrol users in courses
  5. Issue real certificates with valid PDF files via tool_certificate API

Options:
  -c, --count=N     Number of certificates to generate (default: 1000)
  -u, --users=N     Number of test users to create (default: 50)
  -n, --courses=N   Number of test courses to create (default: 10)
  --cleanup         Remove all previously generated test data before generating
  -h, --help        Print this help

Example:
  php blocks/download_certificates/cli/generate_test_certificates.php --count=1000
  php blocks/download_certificates/cli/generate_test_certificates.php --cleanup
";
    exit(0);
}

global $DB, $CFG;

// =========================================================================
// Step 0: Cleanup if requested.
// =========================================================================
if ($options['cleanup']) {
    cli_writeln("=== Cleaning up previous test data ===");

    // Find test users.
    $testusers = $DB->get_records_select('user', "username LIKE 'testcert_user_%'", [], '', 'id');
    if ($testusers) {
        $userids = array_keys($testusers);
        // Revoke their certificates.
        $issues = $DB->get_records_list('tool_certificate_issues', 'userid', $userids);
        foreach ($issues as $issue) {
            // Delete associated files.
            $fs = get_file_storage();
            $fs->delete_area_files(context_system::instance()->id, 'tool_certificate', 'issues', $issue->id);
            $DB->delete_records('tool_certificate_issues', ['id' => $issue->id]);
        }
        cli_writeln("  Deleted " . count($issues) . " certificate issues");

        // Delete user enrolments and users.
        foreach ($userids as $uid) {
            $enrolments = $DB->get_records('user_enrolments', ['userid' => $uid]);
            foreach ($enrolments as $ue) {
                $DB->delete_records('user_enrolments', ['id' => $ue->id]);
            }
            delete_user($DB->get_record('user', ['id' => $uid]));
        }
        cli_writeln("  Deleted " . count($userids) . " test users");
    }

    // Delete test courses.
    $testcourses = $DB->get_records_select('course', "shortname LIKE 'TESTCERT_%'", [], '', 'id');
    if ($testcourses) {
        foreach ($testcourses as $course) {
            delete_course($course->id, false);
        }
        cli_writeln("  Deleted " . count($testcourses) . " test courses");
    }

    // Delete test template.
    if ($DB->get_manager()->table_exists('tool_certificate_templates')) {
        $template = $DB->get_record('tool_certificate_templates', ['name' => 'Test Load Certificate']);
        if ($template) {
            $tpl = \tool_certificate\template::instance(0, $template);
            $tpl->delete();
            cli_writeln("  Deleted test template");
        }
    }

    cli_writeln("Cleanup complete!\n");

    if ($options['count'] <= 0) {
        exit(0);
    }
}

$count = (int) $options['count'];
$numusers = (int) $options['users'];
$numcourses = (int) $options['courses'];

// =========================================================================
// Step 1: Check prerequisites.
// =========================================================================
cli_writeln("=== Checking prerequisites ===");

$dbman = $DB->get_manager();
if (!$dbman->table_exists('tool_certificate_issues') || !$dbman->table_exists('tool_certificate_templates')) {
    cli_error('tool_certificate plugin is not installed! Please install it first.');
}

cli_writeln("  tool_certificate plugin: OK");

// =========================================================================
// Step 2: Create test users.
// =========================================================================
cli_writeln("\n=== Creating test users ===");

$existingusers = $DB->get_records_select('user', "username LIKE 'testcert_user_%' AND deleted = 0", [], '', 'id, username');
$userids = array_keys($existingusers);

if (count($userids) >= $numusers) {
    cli_writeln("  Reusing " . count($userids) . " existing test users");
} else {
    $needed = $numusers - count($userids);
    cli_writeln("  Creating {$needed} new test users...");

    for ($i = count($userids) + 1; $i <= $numusers; $i++) {
        $username = 'testcert_user_' . str_pad($i, 4, '0', STR_PAD_LEFT);
        try {
            // Check if user exists first.
            $existing = $DB->get_record('user', ['username' => $username, 'deleted' => 0]);
            if ($existing) {
                $userids[] = $existing->id;
                continue;
            }
            $user = new stdClass();
            $user->username = $username;
            $user->firstname = 'Test';
            $user->lastname = 'CertUser' . $i;
            $user->email = 'testcert_user_' . $i . '@example.com';
            $user->password = hash_internal_user_password('TestCert1!');
            $user->confirmed = 1;
            $user->mnethostid = $CFG->mnet_localhost_id;
            $user->timecreated = time();
            $user->timemodified = time();
            $user->auth = 'manual';
            $user->id = $DB->insert_record('user', $user);
            $userids[] = $user->id;
        } catch (Exception $e) {
            cli_writeln("  Warning: Could not create user {$username}: " . $e->getMessage());
        }
    }
    cli_writeln("  Total test users: " . count($userids));
}

// =========================================================================
// Step 3: Create test courses.
// =========================================================================
cli_writeln("\n=== Creating test courses ===");

$existingcourses = $DB->get_records_select('course', "shortname LIKE 'TESTCERT_%'", [], '', 'id, shortname');
$courseids = array_keys($existingcourses);

if (count($courseids) >= $numcourses) {
    cli_writeln("  Reusing " . count($courseids) . " existing test courses");
} else {
    $needed = $numcourses - count($courseids);
    cli_writeln("  Creating {$needed} new test courses...");

    for ($i = count($courseids) + 1; $i <= $numcourses; $i++) {
        $shortname = 'TESTCERT_' . str_pad($i, 3, '0', STR_PAD_LEFT);
        try {
            $existing = $DB->get_record('course', ['shortname' => $shortname]);
            if ($existing) {
                $courseids[] = $existing->id;
                continue;
            }
            $coursedata = new stdClass();
            $coursedata->shortname = $shortname;
            $coursedata->fullname = 'Test Certificate Course ' . $i;
            $coursedata->category = 1;
            $coursedata->format = 'topics';
            $coursedata->numsections = 1;
            $course = create_course($coursedata);
            $courseids[] = $course->id;
        } catch (Exception $e) {
            cli_writeln("  Warning: Could not create course {$shortname}: " . $e->getMessage());
        }
    }
    cli_writeln("  Total test courses: " . count($courseids));
}

// =========================================================================
// Step 4: Enrol users in courses.
// =========================================================================
cli_writeln("\n=== Enrolling users in courses ===");

$enrolplugin = enrol_get_plugin('manual');
if (!$enrolplugin) {
    cli_error('Manual enrolment plugin not available!');
}

$enrolcount = 0;
foreach ($courseids as $courseid) {
    // Get or create manual enrol instance for this course.
    $enrolinstances = $DB->get_records('enrol', ['courseid' => $courseid, 'enrol' => 'manual']);
    $enrolinstance = reset($enrolinstances);

    if (!$enrolinstance) {
        // Create manual enrol instance.
        $enrolplugin->add_instance($DB->get_record('course', ['id' => $courseid]));
        $enrolinstance = $DB->get_record('enrol', ['courseid' => $courseid, 'enrol' => 'manual']);
    }

    foreach ($userids as $userid) {
        if (!$DB->record_exists('user_enrolments', ['enrolid' => $enrolinstance->id, 'userid' => $userid])) {
            $enrolplugin->enrol_user($enrolinstance, $userid, 5); // 5 = student role.
            $enrolcount++;
        }
    }
}
cli_writeln("  Created {$enrolcount} new enrolments");

// =========================================================================
// Step 5: Create or reuse certificate template.
// =========================================================================
cli_writeln("\n=== Setting up certificate template ===");

$template = $DB->get_record('tool_certificate_templates', ['name' => 'Test Load Certificate']);
if ($template) {
    $templateobj = \tool_certificate\template::instance(0, $template);
    cli_writeln("  Reusing existing template (ID: {$template->id})");
} else {
    // Create a new template via the API.
    $templateobj = \tool_certificate\template::create((object) [
        'name' => 'Test Load Certificate',
        'contextid' => context_system::instance()->id,
    ]);
    cli_writeln("  Created new template (ID: {$templateobj->get_id()})");

    // Add a page with A4 landscape dimensions.
    $page = $templateobj->new_page();
    $page->save((object) [
        'width' => 297,
        'height' => 210,
        'leftmargin' => 10,
        'rightmargin' => 10,
    ]);
    $pageid = $page->get_id();
    cli_writeln("  Added page to template");

    // Add visible elements to the template.
    $sequence = 1;

    // 1. Border element.
    $el = \tool_certificate\element::instance(0, (object) [
        'element' => 'border',
        'pageid' => $pageid,
    ]);
    $el->save_form_data((object) [
        'name' => 'Border',
        'colour' => '#003366',
        'width' => 2,
        'sequence' => $sequence++,
    ]);
    cli_writeln("  Added element: border");

    // 2. Title text "CERTIFICAT DE RÉUSSITE".
    $el = \tool_certificate\element::instance(0, (object) [
        'element' => 'text',
        'pageid' => $pageid,
    ]);
    $el->save_form_data((object) [
        'name' => 'Title',
        'text' => 'CERTIFICAT DE RÉUSSITE',
        'font' => 'freesans',
        'fontsize' => 30,
        'colour' => '#003366',
        'posx' => 0,
        'posy' => 30,
        'width' => 277,
        'refpoint' => 1,
        'sequence' => $sequence++,
    ]);
    cli_writeln("  Added element: title text");

    // 3. Subtitle text.
    $el = \tool_certificate\element::instance(0, (object) [
        'element' => 'text',
        'pageid' => $pageid,
    ]);
    $el->save_form_data((object) [
        'name' => 'Subtitle',
        'text' => 'Ce certificat atteste que',
        'font' => 'freesans',
        'fontsize' => 14,
        'colour' => '#666666',
        'posx' => 0,
        'posy' => 65,
        'width' => 277,
        'refpoint' => 1,
        'sequence' => $sequence++,
    ]);
    cli_writeln("  Added element: subtitle text");

    // 4. User fullname field.
    $el = \tool_certificate\element::instance(0, (object) [
        'element' => 'userfield',
        'pageid' => $pageid,
    ]);
    $el->save_form_data((object) [
        'name' => 'Student Name',
        'userfield' => 'fullname',
        'font' => 'freesans',
        'fontsize' => 24,
        'colour' => '#000000',
        'posx' => 0,
        'posy' => 80,
        'width' => 277,
        'refpoint' => 1,
        'sequence' => $sequence++,
    ]);
    cli_writeln("  Added element: user fullname");

    // 5. Text "a suivi avec succès la formation".
    $el = \tool_certificate\element::instance(0, (object) [
        'element' => 'text',
        'pageid' => $pageid,
    ]);
    $el->save_form_data((object) [
        'name' => 'Completion text',
        'text' => 'a suivi avec succès la formation',
        'font' => 'freesans',
        'fontsize' => 14,
        'colour' => '#666666',
        'posx' => 0,
        'posy' => 105,
        'width' => 277,
        'refpoint' => 1,
        'sequence' => $sequence++,
    ]);
    cli_writeln("  Added element: completion text");

    // 6. Date element.
    $el = \tool_certificate\element::instance(0, (object) [
        'element' => 'date',
        'pageid' => $pageid,
    ]);
    $el->save_form_data((object) [
        'name' => 'Date',
        'dateitem' => -1,
        'dateformat' => 'strftimedatefull',
        'font' => 'freesans',
        'fontsize' => 12,
        'colour' => '#333333',
        'posx' => 0,
        'posy' => 150,
        'width' => 277,
        'refpoint' => 1,
        'sequence' => $sequence++,
    ]);
    cli_writeln("  Added element: date");

    // 7. Verification code.
    $el = \tool_certificate\element::instance(0, (object) [
        'element' => 'code',
        'pageid' => $pageid,
    ]);
    $el->save_form_data((object) [
        'name' => 'Verification Code',
        'display' => 1,
        'font' => 'freesans',
        'fontsize' => 9,
        'colour' => '#999999',
        'posx' => 0,
        'posy' => 190,
        'width' => 277,
        'refpoint' => 1,
        'sequence' => $sequence++,
    ]);
    cli_writeln("  Added element: verification code");

    cli_writeln("  Template created with " . ($sequence - 1) . " elements");
}

// =========================================================================
// Step 6: Issue certificates!
// =========================================================================
// Reload the template from DB to clear the stale pages cache.
// (new_page() caches $this->pages as [] before the page is saved,
//  and generate_pdf() would then find no pages and return null → 0-byte PDF)
$templateobj = \tool_certificate\template::instance($templateobj->get_id());

cli_writeln("\n=== Issuing {$count} certificates ===");
cli_writeln("  (This may take a while due to PDF generation...)\n");

$issued = 0;
$errors = 0;
$starttime = microtime(true);

// Disable notifications to avoid spamming during mass generation.
$CFG->noemailever = true;

for ($i = 0; $i < $count; $i++) {
    $userid = $userids[$i % count($userids)];
    $courseid = $courseids[$i % count($courseids)];

    try {
        $templateobj->issue_certificate(
            $userid,
            null,  // No expiry.
            [],    // No extra data.
            'tool_certificate',
            $courseid
        );
        $issued++;
    } catch (Exception $e) {
        $errors++;
        if ($errors <= 5) {
            cli_writeln("  Error #{$errors}: " . $e->getMessage());
        } else if ($errors == 6) {
            cli_writeln("  (Suppressing further error messages...)");
        }
    }

    // Progress feedback every 50 records (PDF generation is slow).
    if (($i + 1) % 50 === 0) {
        $elapsed = round(microtime(true) - $starttime, 1);
        $rate = round(($i + 1) / $elapsed, 1);
        $eta = round(($count - $i - 1) / $rate, 0);
        cli_writeln("  Progress: " . ($i + 1) . "/{$count} ({$rate}/s, ETA: {$eta}s)");
    }
}

$totaltime = round(microtime(true) - $starttime, 2);
cli_writeln("\n=== Done! ===");
cli_writeln("  Issued: {$issued} certificates");
cli_writeln("  Errors: {$errors}");
cli_writeln("  Time: {$totaltime}s");
cli_writeln("  Rate: " . round($issued / max($totaltime, 0.1), 1) . " certificates/s");

// Final count.
$totalissues = $DB->count_records('tool_certificate_issues');
cli_writeln("\n  Total tool_certificate_issues in DB: {$totalissues}");
