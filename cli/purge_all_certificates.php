<?php
// This file is part of Moodle - http://moodle.org/
//
// CLI script to purge ALL certificates from the platform.
// WARNING: This is a destructive and irreversible operation!
//
// Usage: php blocks/download_certificates/cli/purge_all_certificates.php
//
// @package   block_download_certificates

define('CLI_SCRIPT', true);

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');

list($options, $unrecognized) = cli_get_params(
    [
        'confirm' => false,
        'help' => false,
    ],
    ['y' => 'confirm', 'h' => 'help']
);

if ($options['help']) {
    echo "Purge ALL certificates from the platform.

WARNING: This is a DESTRUCTIVE and IRREVERSIBLE operation!
It will delete ALL certificate issues from ALL certificate plugins.

Affected tables:
  - tool_certificate_issues (tool_certificate / mod_coursecertificate)
  - customcert_issues (mod_customcert)
  - certificate_issues (mod_certificate)
  - simplecertificate_issues (mod_simplecertificate)
  - certificatebeautiful_issue (mod_certificatebeautiful)

Options:
  -y, --confirm    Skip interactive confirmation (use with caution!)
  -h, --help       Print this help

Example:
  php blocks/download_certificates/cli/purge_all_certificates.php
  php blocks/download_certificates/cli/purge_all_certificates.php --confirm
";
    exit(0);
}

global $DB;

$dbman = $DB->get_manager();

// =========================================================================
// Step 1: Count existing certificates per type.
// =========================================================================
cli_writeln("=== Analyse des certificats présents sur la plateforme ===\n");

$tables = [
    'tool_certificate_issues' => 'tool_certificate (certificats site)',
    'customcert_issues' => 'mod_customcert (Custom Certificate)',
    'certificate_issues' => 'mod_certificate (Certificate)',
    'simplecertificate_issues' => 'mod_simplecertificate (Simple Certificate)',
    'certificatebeautiful_issue' => 'mod_certificatebeautiful (Certificate Beautiful)',
];

$counts = [];
$total = 0;

foreach ($tables as $table => $label) {
    if ($dbman->table_exists($table)) {
        $count = $DB->count_records($table);
        $counts[$table] = $count;
        $total += $count;
        $status = $count > 0 ? "\033[1;33m{$count}\033[0m" : "\033[0;32m0\033[0m";
        cli_writeln("  {$label}: {$status} certificat(s)");
    } else {
        $counts[$table] = -1;
        cli_writeln("  {$label}: \033[0;90mtable absente\033[0m");
    }
}

cli_writeln("\n  \033[1mTotal: {$total} certificat(s)\033[0m\n");

if ($total === 0) {
    cli_writeln("Aucun certificat à supprimer. La plateforme est déjà vide.");
    exit(0);
}

// =========================================================================
// Step 2: Ask for confirmation.
// =========================================================================
if (!$options['confirm']) {
    cli_writeln("\033[1;31m⚠  ATTENTION: Cette opération va supprimer DÉFINITIVEMENT {$total} certificat(s).\033[0m");
    cli_writeln("   Cette action est IRRÉVERSIBLE.\n");
    $input = cli_input("Tapez 'OUI' pour confirmer la suppression");

    if (strtoupper(trim($input)) !== 'OUI') {
        cli_writeln("\nOpération annulée.");
        exit(0);
    }
    cli_writeln("");
}

// =========================================================================
// Step 3: Delete all certificates.
// =========================================================================
cli_writeln("=== Suppression en cours ===\n");

$deleted = 0;

// --- tool_certificate_issues ---
if (isset($counts['tool_certificate_issues']) && $counts['tool_certificate_issues'] > 0) {
    // Delete associated files first.
    $issues = $DB->get_records('tool_certificate_issues', [], '', 'id');
    $fs = get_file_storage();
    $syscontextid = context_system::instance()->id;
    foreach ($issues as $issue) {
        $fs->delete_area_files($syscontextid, 'tool_certificate', 'issues', $issue->id);
    }
    $count = $DB->count_records('tool_certificate_issues');
    $DB->delete_records('tool_certificate_issues');
    $deleted += $count;
    cli_writeln("  ✓ tool_certificate_issues: {$count} supprimé(s)");
}

// --- customcert_issues ---
if (isset($counts['customcert_issues']) && $counts['customcert_issues'] > 0) {
    $count = $DB->count_records('customcert_issues');
    $DB->delete_records('customcert_issues');
    $deleted += $count;
    cli_writeln("  ✓ customcert_issues: {$count} supprimé(s)");
}

// --- certificate_issues ---
if (isset($counts['certificate_issues']) && $counts['certificate_issues'] > 0) {
    $count = $DB->count_records('certificate_issues');
    $DB->delete_records('certificate_issues');
    $deleted += $count;
    cli_writeln("  ✓ certificate_issues: {$count} supprimé(s)");
}

// --- simplecertificate_issues ---
if (isset($counts['simplecertificate_issues']) && $counts['simplecertificate_issues'] > 0) {
    // Delete associated files.
    $issues = $DB->get_records('simplecertificate_issues', [], '', 'id, pathnamehash');
    $fs = get_file_storage();
    foreach ($issues as $issue) {
        if (!empty($issue->pathnamehash)) {
            $file = $fs->get_file_by_hash($issue->pathnamehash);
            if ($file) {
                $file->delete();
            }
        }
    }
    $count = $DB->count_records('simplecertificate_issues');
    $DB->delete_records('simplecertificate_issues');
    $deleted += $count;
    cli_writeln("  ✓ simplecertificate_issues: {$count} supprimé(s)");
}

// --- certificatebeautiful_issue ---
if (isset($counts['certificatebeautiful_issue']) && $counts['certificatebeautiful_issue'] > 0) {
    $count = $DB->count_records('certificatebeautiful_issue');
    $DB->delete_records('certificatebeautiful_issue');
    $deleted += $count;
    cli_writeln("  ✓ certificatebeautiful_issue: {$count} supprimé(s)");
}

// =========================================================================
// Step 4: Purge caches.
// =========================================================================
purge_all_caches();
cli_writeln("\n  ✓ Caches purgés");

// =========================================================================
// Summary.
// =========================================================================
cli_writeln("\n=== Terminé ! ===");
cli_writeln("  \033[1;32m{$deleted} certificat(s) supprimé(s) avec succès.\033[0m");
cli_writeln("  La plateforme est maintenant vierge de tout certificat.");
