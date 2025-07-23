<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Controller class for block_download_certificateses plugin.
 *
 * @package   block_download_certificates
 * @copyright 2015 Manieer Chhettri  (Original Idea for Moodle 2.x and 3.x)
 * @copyright 2025 E-learning Touch' contact@elearningtouch.com (Maintainer)
 * @author    Thomas Clément 222384061+ClementThomasELT@users.noreply.github.com (Coder)
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot . '/lib/filelib.php');
require_once($CFG->dirroot . '/lib/accesslib.php');

/**
 * Main controller for certificate downloads.
 */
class block_download_certificates_controller {

    /**
     * Get certificates data for display.
     *
     * @return array Data for mustache template
     */
    public function get_certificates_data() {
        global $DB, $CFG;

        $certificatesdata = [];

        // Get tool_certificate certificates.
        $toolcertificates = $this->get_tool_certificate_issues();
        foreach ($toolcertificates as $cert) {
            $certificatesdata[] = [
                'id' => $cert->id,
                'username' => fullname($cert),
                'email' => $cert->email,
                'coursename' => $cert->coursename ?: get_string('unknown', 'block_download_certificates'),
                'templatename' => $cert->templatename ?: get_string('unknown', 'block_download_certificates'),
                'code' => $cert->code,
                'timecreated' => userdate($cert->timecreated),
                'timecreated_raw' => $cert->timecreated,
                'type' => 'tool_certificate',
                'download_single_url' => new moodle_url('/blocks/download_certificates/download_precise.php', [
                    'timecreated' => $cert->timecreated,
                    'code' => $cert->code,
                    'type' => 'tool_certificate',
                    'sesskey' => sesskey(),
                ]),
                'certificate_url' => $this->get_certificate_download_url($cert->timecreated, $cert->code),
            ];
        }

        // Get customcert certificates.
        $customcertificates = $this->get_customcert_issues();

        foreach ($customcertificates as $cert) {
            $certificatesdata[] = [
                'id' => $cert->id,
                'username' => fullname($cert),
                'email' => $cert->email,
                'coursename' => $cert->coursename ?: get_string('unknown', 'block_download_certificates'),
                'templatename' => $cert->certificatename ?: get_string('unknown', 'block_download_certificates'),
                'code' => $cert->id, // Customcert uses ID instead of code.
                'timecreated' => userdate($cert->timecreated),
                'timecreated_raw' => $cert->timecreated,
                'type' => 'customcert',
                'download_single_url' => new moodle_url('/blocks/download_certificates/download_precise.php', [
                    'userid' => $cert->userid,
                    'certificateid' => $cert->customcertid,
                    'type' => 'customcert',
                    'sesskey' => sesskey(),
                ]),
            ];
        }

        // Get mod_certificate certificates.
        $modcertificates = $this->get_mod_certificate_issues();

        foreach ($modcertificates as $cert) {
            $certificatesdata[] = [
                'id' => $cert->id,
                'username' => fullname($cert),
                'email' => $cert->email,
                'coursename' => $cert->coursename ?: get_string('unknown', 'block_download_certificates'),
                'templatename' => $cert->certificatename ?: get_string('unknown', 'block_download_certificates'),
                'code' => $cert->id, // Mod_certificate uses ID instead of code.
                'timecreated' => userdate($cert->timecreated),
                'timecreated_raw' => $cert->timecreated,
                'type' => 'mod_certificate',
                'download_single_url' => new moodle_url('/blocks/download_certificates/download_precise.php', [
                    'userid' => $cert->userid,
                    'certificateid' => $cert->certificateid,
                    'type' => 'mod_certificate',
                    'sesskey' => sesskey(),
                ]),
                'certificate_url' => $this->get_mod_certificate_download_url($cert->certificateid,
                                                                            $cert->coursename,
                                                                            $cert->certificatename),
            ];
        }

        // Get mod_simplecertificate certificates.
        $simplecertificates = $this->get_simplecertificate_issues();

        foreach ($simplecertificates as $cert) {
            $certificatesdata[] = [
                'id' => $cert->id,
                'username' => fullname($cert),
                'email' => $cert->email,
                'coursename' => $cert->coursename_full ?: ($cert->coursename ?:
                                                            get_string('unknown', 'block_download_certificates')),
                'templatename' => $cert->certificatename ?: get_string('unknown', 'block_download_certificates'),
                'code' => $cert->code,
                'timecreated' => userdate($cert->timecreated),
                'timecreated_raw' => $cert->timecreated,
                'type' => 'mod_simplecertificate',
                'download_single_url' => new moodle_url('/blocks/download_certificates/download_precise.php', [
                    'userid' => $cert->userid,
                    'certificateid' => $cert->certificateid,
                    'code' => $cert->code,
                    'type' => 'mod_simplecertificate',
                    'sesskey' => sesskey(),
                ]),
                'certificate_url' => $this->get_simplecertificate_download_url($cert->code),
            ];
        }

        // Get mod_certificatebeautiful certificates.
        $certificatebeautifulcertificates = $this->get_certificatebeautiful_issues();

        foreach ($certificatebeautifulcertificates as $cert) {
            $certificatesdata[] = [
                'id' => $cert->id,
                'username' => fullname($cert),
                'email' => $cert->email,
                'coursename' => $cert->coursename ?: get_string('unknown', 'block_download_certificates'),
                'templatename' => $cert->certificatename ?: get_string('unknown', 'block_download_certificates'),
                'code' => $cert->code,
                'timecreated' => userdate($cert->timecreated),
                'timecreated_raw' => $cert->timecreated,
                'type' => 'mod_certificatebeautiful',
                'download_single_url' => new moodle_url('/blocks/download_certificates/download_precise.php', [
                    'userid' => $cert->userid,
                    'certificateid' => $cert->certificatebeautifulid,
                    'code' => $cert->code,
                    'type' => 'mod_certificatebeautiful',
                    'sesskey' => sesskey(),
                ]),
                'certificate_url' => $this->get_certificatebeautiful_download_url($cert->code),
            ];
        }

        // Sort all certificates by date (newest first).
        usort($certificatesdata, function($a, $b) {
            return $b['timecreated_raw'] - $a['timecreated_raw'];
        });

        return [
            'certificates' => $certificatesdata,
            'total_count' => count($certificatesdata),
            'download_all_url' => new moodle_url('/blocks/download_certificates/download.php', [
                'action' => 'download_all',
                'sesskey' => sesskey(),
            ]),
            'has_certificates' => !empty($certificatesdata),
            'cohorts' => $this->get_cohorts_with_certificates(),
            'has_cohorts' => !empty($this->get_cohorts_with_certificates()),
            'sesskey' => sesskey(),
        ];
    }

    /**
     * Generate download URL for a certificate.
     *
     * @param int $timecreated Time created timestamp
     * @param string $code Certificate code
     * @return string Download URL
     */
    private function get_certificate_download_url($timecreated, $code) {
        global $CFG;
        return $CFG->wwwroot . '/pluginfile.php/1/tool_certificate/issues/' . $timecreated . '/' . $code . '.pdf';
    }

    /**
     * Generate download URL for a mod_simplecertificate certificate.
     *
     * @param string $code Certificate code
     * @return string Download URL
     */
    private function get_simplecertificate_download_url($code) {
        global $CFG;
        // Use the external API URL for mod_simplecertificate.
        return $CFG->wwwroot . '/mod/simplecertificate/wmsendfile.php?code=' . urlencode($code);
    }

    /**
     * Generate download URL for a mod_certificatebeautiful certificate.
     *
     * @param string $code Certificate code
     * @return string Download URL
     */
    private function get_certificatebeautiful_download_url($code) {
        global $CFG;
        // Use the external API URL for mod_certificatebeautiful.
        return $CFG->wwwroot . '/mod/certificatebeautiful/view-pdf.php?code=' . urlencode($code);
    }

    /**
     * Generate download URL for mod_certificatebeautiful with action=download for individual downloads.
     *
     * @param string $code Certificate code
     * @return string Download URL
     */
    private function get_certificatebeautiful_individual_download_url($code) {
        global $CFG;
        // Use the external API URL for mod_certificatebeautiful with action=download for individual downloads.
        return $CFG->wwwroot . '/mod/certificatebeautiful/view-pdf.php?code=' . urlencode($code) . '&action=download';
    }

    /**
     * Generate download URL for a mod_certificate certificate.
     *
     * @param int $certificateid Certificate ID (from certificate table)
     * @param string $coursename Course name
     * @param string $certificatename Certificate name
     * @param int $userid User ID (optional, for more specific URL)
     * @return string Download URL
     */
    private function get_mod_certificate_download_url($certificateid, $coursename, $certificatename, $userid = null) {
        global $CFG, $DB;

        // Get the certificate record to find the course.
        $certificate = $DB->get_record('certificate', ['id' => $certificateid], 'course');
        if (!$certificate) {
            return '';
        }

        // Get course shortname instead of fullname.
        $course = $DB->get_record('course', ['id' => $certificate->course], 'shortname');
        $courseshortname = $course ? $course->shortname : ($coursename ?: 'Course');

        // Get the course module for this certificate to get the correct context.
        $cm = get_coursemodule_from_instance('certificate', $certificateid, $certificate->course);
        if (!$cm) {
            return '';
        }

        // Get module context (not course context).
        $modulecontext = context_module::instance($cm->id);

        // If we have a userid, try to get the specific certificate issue ID.
        $issueitemid = $certificateid; // Default to certificate ID.
        if ($userid) {
            $issue = $DB->get_record('certificate_issues',
                ['userid' => $userid, 'certificateid' => $certificateid],
                'id'
            );
            if ($issue) {
                $issueitemid = $issue->id; // Use the issue ID instead.
            }
        }

        $cleancertname = preg_replace('/[^a-zA-Z0-9_-]/', '_', $certificatename ?: 'Certificate');

        $filename = $courseshortname . '_' . $cleancertname . '.pdf';

        return $CFG->wwwroot . '/pluginfile.php/' . $modulecontext->id . '/mod_certificate/issue/' .
                $issueitemid . '/' . rawurlencode($filename);
    }

    /**
     * Get mod_certificate file content from Moodle file storage.
     *
     * @param int $certificateid Certificate ID
     * @param string $coursename Course name
     * @param string $certificatename Certificate name
     * @param int $userid User ID (optional, for more specific file lookup)
     * @return string|false File content or false on failure
     */
    private function get_mod_certificate_file_content($certificateid, $coursename, $certificatename, $userid = null) {
        global $DB;

        try {
            // Get the certificate record to find the course.
            $certificate = $DB->get_record('certificate', ['id' => $certificateid]);
            if (!$certificate) {
                return false;
            }

            // Get course shortname instead of fullname.
            $course = $DB->get_record('course', ['id' => $certificate->course], 'shortname');
            $courseshortname = $course ? $course->shortname : ($coursename ?: 'Course');

            // Get the course module for this certificate to get the correct context.
            $cm = get_coursemodule_from_instance('certificate', $certificateid, $certificate->course);
            if (!$cm) {
                return false;
            }

            // Get module context (not course context).
            $modulecontext = context_module::instance($cm->id);

            // Get file storage instance.
            $fs = get_file_storage();

            // Determine the correct itemid to use.
            $issueitemid = $certificateid; // Default to certificate ID.
            if ($userid) {
                $issue = $DB->get_record('certificate_issues',
                    ['userid' => $userid, 'certificateid' => $certificateid],
                    'id'
                );
                if ($issue) {
                    $issueitemid = $issue->id; // Use the issue ID instead.
                }
            }

            // Clean names for filename - ensure they are valid for URLs.
            $cleancoursename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $courseshortname);
            $cleancertname = preg_replace('/[^a-zA-Z0-9_-]/', '_', $certificatename ?: 'Certificate');

            // Generate filename: [Nom_abrégé_du_cours]_[nom_du_certificat].pdf.
            $filename = $cleancoursename . '_' . $cleancertname . '.pdf';

            // Try to find the file using the issue itemid with module context.
            $file = $fs->get_file($modulecontext->id, 'mod_certificate', 'issue', $issueitemid, '/', $filename);

            if ($file && !$file->is_directory()) {
                return $file->get_content();
            }

            // Try with different filename patterns if the exact match fails.
            $possiblenames = [
                $filename,
                $issueitemid . '.pdf',
                $certificateid . '.pdf',
                'certificate.pdf',
                'certificate_' . $issueitemid . '.pdf',
                'certificate_' . $certificateid . '.pdf',
            ];

            foreach ($possiblenames as $testname) {
                $file = $fs->get_file($modulecontext->id, 'mod_certificate', 'issue', $issueitemid, '/', $testname);
                if ($file && !$file->is_directory()) {
                    return $file->get_content();
                }
            }

            // Search all files in the area with issue itemid using module context.
            $files = $fs->get_area_files($modulecontext->id, 'mod_certificate', 'issue', $issueitemid, 'timemodified DESC', false);

            foreach ($files as $file) {
                if (strpos($file->get_filename(), '.pdf') !== false) {
                    return $file->get_content();
                }
            }

            // Fallback: try with certificate ID as itemid.
            if ($issueitemid !== $certificateid) {
                $files = $fs->get_area_files($modulecontext->id, 'mod_certificate',
                                            'issue', $certificateid,
                                            'timemodified DESC', false);

                foreach ($files as $file) {
                    if (strpos($file->get_filename(), '.pdf') !== false) {
                        return $file->get_content();
                    }
                }
            }

            return false;

        } catch (Exception $e) {
            debugging('Error getting mod_certificate file: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get mod_certificate file content by downloading from URL (for ZIP downloads).
     *
     * @param int $certificateid Certificate ID
     * @param string $coursename Course name
     * @param string $certificatename Certificate name
     * @param int $userid User ID (required for mod_certificate)
     * @return string|false File content or false on failure
     */
    private function get_mod_certificate_content_via_url($certificateid, $coursename, $certificatename, $userid) {
        try {
            // First try to get from file storage.
            $content = $this->get_mod_certificate_file_content($certificateid, $coursename, $certificatename, $userid);
            if ($content !== false) {
                return $content;
            }

            // If file storage fails, generate URL and download content.
            $fileurl = $this->get_mod_certificate_download_url($certificateid, $coursename, $certificatename, $userid);
            if (empty($fileurl)) {
                return false;
            }

            // Download content via HTTP.
            $content = $this->download_file_via_http($fileurl);
            if ($content !== false && strlen($content) > 100) {
                return $content;
            }

            return false;

        } catch (Exception $e) {
            debugging('Error getting mod_certificate content via URL: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get simplecertificate file content via URL method (similar to mod_certificate).
     *
     * @param string $code Certificate code
     * @return string|false File content or false on failure
     */
    private function get_simplecertificate_content_via_url($code) {
        try {
            // First try to get content from storage method.
            $content = $this->get_simplecertificate_file_content($code);
            if ($content !== false) {
                return $content;
            }

            // If file storage fails, generate URL and download content.
            $fileurl = $this->get_simplecertificate_download_url($code);
            if (empty($fileurl)) {
                return false;
            }

            // Download content via HTTP.
            $content = $this->download_file_via_http($fileurl);
            if ($content !== false && strlen($content) > 100) {
                return $content;
            }

            return false;

        } catch (Exception $e) {
            debugging('Error getting simplecertificate content via URL: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get certificatebeautiful file content via URL method (similar to mod_simplecertificate).
     *
     * @param string $code Certificate code
     * @return string|false File content or false on failure
     */
    private function get_certificatebeautiful_content_via_url($code) {
        try {
            // First try to get content from storage method.
            $content = $this->get_certificatebeautiful_file_content($code);
            if ($content !== false) {
                return $content;
            }

            // If file storage fails, generate URL and download content.
            $fileurl = $this->get_certificatebeautiful_download_url($code);
            if (empty($fileurl)) {
                return false;
            }

            // Download content via HTTP.
            $content = $this->download_file_via_http($fileurl);
            if ($content !== false && strlen($content) > 100) {
                return $content;
            }

            return false;

        } catch (Exception $e) {
            debugging('Error getting certificatebeautiful content via URL: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get certificatebeautiful file content from storage (similar to mod_simplecertificate).
     *
     * @param string $code Certificate code
     * @return string|false File content or false on failure
     */
    private function get_certificatebeautiful_file_content($code) {
        global $DB;

        try {
            // Get the certificatebeautiful issue with all fields.
            $issue = $DB->get_record('certificatebeautiful_issue', ['code' => $code]);
            if (!$issue) {
                return false;
            }

            $fs = get_file_storage();

            // Method 1: Try pathnamehash if available (like simplecertificate).
            if (!empty($issue->pathnamehash)) {
                $file = $fs->get_file_by_hash($issue->pathnamehash);
                if ($file && !$file->is_directory()) {
                    return $file->get_content();
                }
            }

            // Method 2: Try via course module context (most common for certificate plugins).
            if ($issue->cmid) {
                $cm = get_coursemodule_from_id('certificatebeautiful', $issue->cmid);
                if ($cm) {
                    $context = context_module::instance($cm->id);

                    // Common file areas for certificate plugins.
                    $fileareas = ['issued', 'certificate', 'certificates', 'pdf', 'issue'];
                    $filenames = [
                        $code . '.pdf',
                        $issue->id . '.pdf',
                        'certificate_' . $code . '.pdf',
                        'cert_' . $issue->userid . '_' . $issue->id . '.pdf',
                    ];

                    foreach ($fileareas as $filearea) {
                        foreach ($filenames as $filename) {
                            // Try with issue id as itemid.
                            $file = $fs->get_file($context->id, 'mod_certificatebeautiful', $filearea, $issue->id, '/', $filename);
                            if ($file && !$file->is_directory()) {
                                return $file->get_content();
                            }

                            // Try with 0 as itemid.
                            $file = $fs->get_file($context->id, 'mod_certificatebeautiful', $filearea, 0, '/', $filename);
                            if ($file && !$file->is_directory()) {
                                return $file->get_content();
                            }

                            // Try with userid as itemid.
                            $file = $fs->get_file($context->id,
                                                'mod_certificatebeautiful',
                                                $filearea,
                                                $issue->userid,
                                                '/',
                                                $filename);
                            if ($file && !$file->is_directory()) {
                                return $file->get_content();
                            }
                        }
                    }
                }
            }

            // Method 3: Try via course context if module context fails.
            if (!empty($issue->courseid)) {
                $coursecontext = context_course::instance($issue->courseid);
                $fileareas = ['certificates', 'certificate', 'issued'];

                foreach ($fileareas as $filearea) {
                    $file = $fs->get_file($coursecontext->id,
                                        'mod_certificatebeautiful',
                                        $filearea,
                                        $issue->id,
                                        '/',
                                        $code .
                                        '.pdf');
                    if ($file && !$file->is_directory()) {
                        return $file->get_content();
                    }
                }
            }

            // Method 4: Search all files with matching names (slower but comprehensive).
            $files = $fs->get_area_files_select(
                0, 'mod_certificatebeautiful', false, 'filename', false,
                "filename LIKE '%{$code}%' OR filename LIKE '%{$issue->id}%'"
            );

            foreach ($files as $file) {
                if (!$file->is_directory() && strpos($file->get_filename(), '.pdf') !== false) {
                    return $file->get_content();
                }
            }

            // If all methods fail, return false to fall back to URL method.
            return false;

        } catch (Exception $e) {
            debugging('Error getting certificatebeautiful file: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get simplecertificate file content directly from Moodle file storage (preferred method).
     *
     * @param string $code Certificate code
     * @return string|false File content or false on failure
     */
    private function get_simplecertificate_file_content($code) {
        global $DB;

        try {
            // First, try to get the file directly from Moodle file storage using pathnamehash.
            // This avoids the "COPIE" watermark completely.
            $issue = $DB->get_record('simplecertificate_issues', ['code' => $code]);
            if ($issue && !empty($issue->pathnamehash)) {
                $fs = get_file_storage();
                $file = $fs->get_file_by_hash($issue->pathnamehash);

                if ($file && !$file->is_directory()) {
                    return $file->get_content(); // Direct file content without watermark.
                }
            }

            // If direct file access fails, try the external API with authentication simulation.
            return $this->get_simplecertificate_via_authenticated_api($code);

        } catch (Exception $e) {
            debugging('Error getting simplecertificate file: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Download a single mod_certificate certificate file using internal API.
     *
     * @param int $userid User ID
     * @param int $certificateid Certificate ID
     */
    public function download_single_mod_certificate($userid, $certificateid) {
        global $DB, $CFG;

        // Check permissions.
        $context = context_system::instance();
        require_capability('block/download_certificates:manage', $context);

        // Verify the certificate issue exists.
        $issue = $DB->get_record('certificate_issues',
            ['userid' => $userid, 'certificateid' => $certificateid],
            '*', IGNORE_MISSING);

        if (!$issue) {
            throw new moodle_exception('certificatenotfound', 'block_download_certificates');
        }

        // Get certificate and course info.
        $sql = "SELECT ci.*, u.firstname, u.lastname, cert.name as certificatename, c.fullname as coursename
                FROM {certificate_issues} ci
                JOIN {user} u ON u.id = ci.userid
                JOIN {certificate} cert ON cert.id = ci.certificateid
                LEFT JOIN {course} c ON c.id = cert.course
                WHERE ci.userid = :userid AND ci.certificateid = :certificateid";

                $certificate = $DB->get_record_sql($sql, ['userid' => $userid, 'certificateid' => $certificateid]);

        if (!$certificate) {
            throw new moodle_exception('certificatenotfound', 'block_download_certificates');
        }

        try {
            // Generate the direct URL for the mod_certificate with the specific user ID.
            $fileurl = $this->get_mod_certificate_download_url($certificateid,
                                                            $certificate->coursename,
                                                            $certificate->certificatename,
                                                            $userid);

            if (empty($fileurl)) {
                throw new moodle_exception('cannotdownloadcertificate', 'block_download_certificates', '',
                    'Unable to generate download URL for mod_certificate.');
            }

            // For mod_certificate, redirect directly to the pluginfile URL instead of downloading via HTTP.
            // This avoids timeout issues and uses Moodle's native file serving.
            redirect($fileurl);

        } catch (Exception $e) {
            debugging('Error accessing mod_certificate: ' . $e->getMessage());
            throw new moodle_exception('cannotdownloadcertificate', 'block_download_certificates');
        }
    }

    /**
     * Download a single mod_simplecertificate certificate file using API.
     *
     * @param int $userid User ID
     * @param int $certificateid Certificate ID
     * @param string $code Certificate code
     */
    public function download_single_simplecertificate($userid, $certificateid, $code) {
        global $DB, $CFG;

        // Check permissions.
        $context = context_system::instance();
        require_capability('block/download_certificates:manage', $context);

        // Verify the certificate issue exists.
        $issue = $DB->get_record('simplecertificate_issues',
            ['userid' => $userid, 'certificateid' => $certificateid, 'code' => $code],
            '*', IGNORE_MISSING);

        if (!$issue) {
            throw new moodle_exception('certificatenotfound', 'block_download_certificates');
        }

        // Check if certificate is not deleted.
        if (!empty($issue->timedeleted) && $issue->timedeleted != 0) {
            throw new moodle_exception('certificatenotfound', 'block_download_certificates');
        }

        try {
            // Generate the API URL for mod_simplecertificate.
            $fileurl = $this->get_simplecertificate_download_url($code);

            if (empty($fileurl)) {
                throw new moodle_exception('cannotdownloadcertificate', 'block_download_certificates', '',
                    'Unable to generate download URL for mod_simplecertificate.');
            }

            // For mod_simplecertificate, redirect directly to the API URL.
            // This uses the plugin's API to serve the file.
            redirect($fileurl);

        } catch (Exception $e) {
            debugging('Error accessing mod_simplecertificate: ' . $e->getMessage());
            throw new moodle_exception('cannotdownloadcertificate', 'block_download_certificates');
        }
    }

    /**
     * Download a single mod_certificatebeautiful certificate file using API.
     *
     * @param int $userid User ID
     * @param int $certificateid Certificate ID
     * @param string $code Certificate code
     */
    public function download_single_certificatebeautiful($userid, $certificateid, $code) {
        global $DB, $CFG;

        // Check permissions.
        $context = context_system::instance();
        require_capability('block/download_certificates:manage', $context);

        // Verify the certificate issue exists.
        $issue = $DB->get_record('certificatebeautiful_issue',
            ['userid' => $userid, 'certificatebeautifulid' => $certificateid, 'code' => $code],
            '*', IGNORE_MISSING);

        if (!$issue) {
            throw new moodle_exception('certificatenotfound', 'block_download_certificates');
        }

        try {
            // Generate the API URL for mod_certificatebeautiful with action=download for individual downloads.
            $fileurl = $this->get_certificatebeautiful_individual_download_url($code);

            if (empty($fileurl)) {
                throw new moodle_exception('cannotdownloadcertificate', 'block_download_certificates', '',
                    'Unable to generate download URL for mod_certificatebeautiful.');
            }

            // For mod_certificatebeautiful, redirect directly to the API URL.
            // This uses the plugin's API to serve the file.
            redirect($fileurl);

        } catch (Exception $e) {
            debugging('Error accessing mod_certificatebeautiful: ' . $e->getMessage());
            throw new moodle_exception('cannotdownloadcertificate', 'block_download_certificates');
        }
    }

    /**
     * Download a single certificate file.
     *
     * @param int $timecreated Time created timestamp
     * @param string $code Certificate code
     */
    public function download_single_certificate($timecreated, $code) {
        global $DB;

        // Check capabilities.
        $context = context_system::instance();
        require_capability('block/download_certificates:manage', $context);

        // Verify the certificate exists and get user info.
        $sql = "SELECT tci.*, u.firstname, u.lastname, u.email,
                       tc.name as templatename, c.fullname as coursename
                FROM {tool_certificate_issues} tci
                JOIN {user} u ON u.id = tci.userid
                LEFT JOIN {tool_certificate_templates} tc ON tc.id = tci.templateid
                LEFT JOIN {course} c ON c.id = tci.courseid
                WHERE tci.timecreated = :timecreated AND tci.code = :code";

        $certificate = $DB->get_record_sql($sql, [
            'timecreated' => $timecreated,
            'code' => $code,
        ]);

        if (!$certificate) {
            throw new moodle_exception('certificatenotfound', 'block_download_certificates');
        }

        // Get the certificate file URL.
        $fileurl = $this->get_certificate_download_url($timecreated, $code);

        // Try to get file content directly from Moodle file storage.
        $filecontent = $this->get_certificate_file_content($timecreated, $code);

        if ($filecontent === false) {
            // Fallback: try to download via HTTP.
            $filecontent = $this->download_file_via_http($fileurl);
        }

        if ($filecontent === false) {
            throw new moodle_exception('cannotdownloadcertificate', 'block_download_certificates');
        }

        // Generate filename.
        $filename = $this->generate_certificate_filename($certificate);

        // Send the file to user.
        $this->send_file_to_user($filecontent, $filename);
    }

    /**
     * Get certificate file content from Moodle file storage.
     *
     * @param int $timecreated Time created timestamp
     * @param string $code Certificate code
     * @return string|false File content or false on failure
     */
    private function get_certificate_file_content($timecreated, $code) {
        try {
            // Get file storage instance.
            $fs = get_file_storage();

            // Try system context first.
            $context = context_system::instance();

            // Try to find the file in different ways.
            $filename = $code . '.pdf';

            // Method 1: Direct file lookup with timecreated as itemid.
            $file = $fs->get_file($context->id, 'tool_certificate', 'issues', $timecreated, '/', $filename);

            if ($file && !$file->is_directory()) {
                return $file->get_content();
            }

            // Method 2: Try with different itemid variations.
            $itemids = [$timecreated, 0, crc32($code), abs(crc32($code))];

            foreach ($itemids as $itemid) {
                $file = $fs->get_file($context->id, 'tool_certificate', 'issues', $itemid, '/', $filename);
                if ($file && !$file->is_directory()) {
                    return $file->get_content();
                }
            }

            // Method 3: Search in all files in the area with timecreated.
            $files = $fs->get_area_files($context->id, 'tool_certificate', 'issues', $timecreated,
                'timemodified DESC', false);

            foreach ($files as $file) {
                if ($file->get_filename() === $filename ||
                    strpos($file->get_filename(), $code) !== false) {
                    return $file->get_content();
                }
            }

            // Method 4: Search in all files with itemid 0 (default).
            $files = $fs->get_area_files($context->id, 'tool_certificate', 'issues', 0, 'timemodified DESC', false);

            foreach ($files as $file) {
                if ($file->get_filename() === $filename ||
                    strpos($file->get_filename(), $code) !== false) {
                    return $file->get_content();
                }
            }

            // Method 5: Try to find the certificate record and use its ID.
            global $DB;
            $certificate = $DB->get_record('tool_certificate_issues', [
                'timecreated' => $timecreated,
                'code' => $code,
            ]);

            if ($certificate) {
                $file = $fs->get_file($context->id, 'tool_certificate', 'issues', $certificate->id, '/', $filename);
                if ($file && !$file->is_directory()) {
                    return $file->get_content();
                }
            }

            return false;

        } catch (Exception $e) {
            debugging('Error getting certificate file: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Download file via HTTP request.
     *
     * @param string $url File URL
     * @return string|false File content or false on failure
     */
    private function download_file_via_http($url) {
        global $CFG, $USER, $SESSION;

        try {
            // Use Moodle's curl class.
            $curl = new curl();

            // Set options.
            $curl->setopt([
                'CURLOPT_TIMEOUT' => 60,
                'CURLOPT_CONNECTTIMEOUT' => 10,
                'CURLOPT_FOLLOWLOCATION' => true,
                'CURLOPT_MAXREDIRS' => 5,
                'CURLOPT_SSL_VERIFYPEER' => false,
                'CURLOPT_SSL_VERIFYHOST' => false,
                'CURLOPT_USERAGENT' => 'Moodle Certificate Downloader/1.0',
                'CURLOPT_RETURNTRANSFER' => true,
                'CURLOPT_FAILONERROR' => false,
                'CURLOPT_COOKIEJAR' => '',
                'CURLOPT_COOKIEFILE' => '',
            ]);

            // Add authentication for internal URLs.
            if (strpos($url, $CFG->wwwroot) === 0) {
                // Get current session info.
                $sessioncookie = $this->get_session_cookie();
                $headers = [
                    'Accept: application/pdf,*/*',
                    'Cache-Control: no-cache',
                    'Pragma: no-cache',
                ];

                // Add session cookie if available.
                if ($sessioncookie) {
                    $curl->setopt(['CURLOPT_COOKIE' => $sessioncookie]);
                }

                // Add user agent and headers.
                $curl->setopt(['CURLOPT_HTTPHEADER' => $headers]);

                // Add referrer for better authentication.
                if (isset($_SERVER['HTTP_HOST'])) {
                    $curl->setopt(['CURLOPT_REFERER' => $CFG->wwwroot]);
                }
            }

            $content = $curl->get($url);
            $info = $curl->get_info();
            $errno = $curl->get_errno();

            // Check for cURL errors.
            if ($errno !== 0) {
                debugging('cURL error: ' . $curl->error . ' (code: ' . $errno . ')');
                return false;
            }

            // Check HTTP status.
            $httpcode = intval($info['http_code']);
            if ($httpcode < 200 || $httpcode >= 300) {
                debugging('HTTP error ' . $httpcode . ' for URL: ' . $url);

                // If it's a 403/401, try with different authentication.
                if ($httpcode === 403 || $httpcode === 401) {
                    return $this->try_alternative_download($url);
                }

                return false;
            }

            // Check content length.
            if (strlen($content) < 100) {
                debugging('Downloaded content too small: ' . strlen($content) . ' bytes');
                return false;
            }

            return $content;

        } catch (Exception $e) {
            debugging('HTTP download error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Try alternative download methods when authentication fails.
     *
     * @param string $url File URL
     * @return string|false File content or false on failure
     */
    private function try_alternative_download($url) {
        global $CFG;

        // Parse URL to extract certificate parameters.
        if (preg_match('#/pluginfile\.php/(\d+)/([^/]+)/([^/]+)/(\d+)/([^/]+)\.pdf#', $url, $matches)) {
            $contextid = $matches[1];
            $component = $matches[2];
            $filearea = $matches[3];
            $timecreated = $matches[4];
            $code = str_replace('.pdf', '', $matches[5]);

            // Try direct file access.
            $content = $this->get_certificate_file_content($timecreated, $code);
            if ($content !== false) {
                return $content;
            }
        }

        // Try with file_get_contents as last resort for block files.
        if (strpos($url, 'http') !== 0 || strpos($url, $CFG->wwwroot) === 0) {
            // Convert to block file path if possible.
            $blockpath = str_replace($CFG->wwwroot, $CFG->dirroot, $url);
            if (file_exists($blockpath)) {
                return file_get_contents($blockpath);
            }
        }

        return false;
    }

    /**
     * Get session cookie for authentication.
     *
     * @return string|false Session cookie or false
     */
    private function get_session_cookie() {
        global $CFG;

        $sessionname = 'MoodleSession' . $CFG->sessioncookie;
        $sessionid = session_id();

        if (!empty($sessionid)) {
            return $sessionname . '=' . $sessionid;
        }

        return false;
    }

    /**
     * Generate filename for certificate.
     *
     * @param stdClass $cert Certificate object
     * @return string Filename
     */
    private function generate_certificate_filename($cert) {
        global $DB;

        // Try to get user info if not already present.
        if (empty($cert->firstname) || empty($cert->lastname)) {
            $userid = !empty($cert->userid) ? $cert->userid : null;
            if ($userid) {
                $user = $DB->get_record('user', ['id' => $userid], 'firstname, lastname');
                if ($user) {
                    $cert->firstname = $user->firstname;
                    $cert->lastname = $user->lastname;
                }
            }
        }

        // Build username safely from available fields - use consistent format.
        $firstname = !empty($cert->firstname) ? clean_filename($cert->firstname) : 'Unknown';
        $lastname = !empty($cert->lastname) ? clean_filename($cert->lastname) : 'User';
        $username = $firstname . '_' . $lastname;

        $certname = !empty($cert->templatename) ? clean_filename($cert->templatename) : 'certificate';
        $code = !empty($cert->code) ? $cert->code : $cert->id;

        // Format: [nomapprenant]_[nomcertificat]_[codeducertificat].pdf.
        return $username . '_' . $certname . '_' . $code . '.pdf';
    }

    /**
     * Send file to user for download.
     *
     * @param string $filecontent File content
     * @param string $filename Filename
     */
    private function send_file_to_user($filecontent, $filename) {
        // Clean any previous output.
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Set headers.
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($filecontent));
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: 0');

        // Output content.
        echo $filecontent;
        exit;
    }

    /**
     * Download all certificates as ZIP.
     */
    public function download_all_certificates() {
        global $DB;

        // Check capabilities.
        $context = context_system::instance();
        require_capability('block/download_certificates:manage', $context);

        // Get all tool_certificate certificates.
        $toolcertificates = $this->get_tool_certificate_issues();

        // Get all customcert certificates.
        $customcertificates = $this->get_customcert_issues();

        // Get all mod_certificate certificates.
        $modcertificates = $this->get_mod_certificate_issues();

        // Get all mod_simplecertificate certificates.
        $simplecertificates = $this->get_simplecertificate_issues();

        // Get all mod_certificatebeautiful certificates.
        $certificatebeautifulcertificates = $this->get_certificatebeautiful_issues();

        // Check if we have any certificates at all.
        if (empty($toolcertificates) &&
            empty($customcertificates) &&
            empty($modcertificates) &&
            empty($simplecertificates) &&
            empty($certificatebeautifulcertificates)) {
            throw new moodle_exception('nocertificates', 'block_download_certificates');
        }

        // Create ZIP.
        $tempdir = make_request_directory();
        $zipfilename = 'all_certificates_' . date('Y-m-d_H-i-s') . '.zip';
        $zippath = $tempdir . '/' . $zipfilename;

        $zip = new ZipArchive();
        if ($zip->open($zippath, ZipArchive::CREATE) !== true) {
            throw new moodle_exception('cannotcreatezipfile', 'block_download_certificates');
        }

        $filesadded = 0;
        $errors = [];

        // Process tool_certificate certificates.
        foreach ($toolcertificates as $cert) {
            try {
                $filecontent = $this->get_certificate_file_content($cert->timecreated, $cert->code);

                if ($filecontent === false) {
                    // Try HTTP download.
                    $fileurl = $this->get_certificate_download_url($cert->timecreated, $cert->code);
                    $filecontent = $this->download_file_via_http($fileurl);
                }

                if ($filecontent !== false) {
                    $filename = 'tool_certificate/' . $this->generate_certificate_filename($cert);
                    $zip->addFromString($filename, $filecontent);
                    $filesadded++;
                } else {
                    $errors[] = 'Failed to download tool_certificate: ' . $cert->code;
                }
            } catch (Exception $e) {
                $errors[] = 'Error downloading tool_certificate ' . $cert->code . ': ' . $e->getMessage();
            }
        }

        // Process customcert certificates.
        foreach ($customcertificates as $cert) {
            try {
                // Get template info for this certificate.
                $sql = "SELECT ct.id as templateid, ct.name as templatename, ct.contextid
                        FROM {customcert} cc
                        JOIN {customcert_templates} ct ON ct.id = cc.templateid
                        WHERE cc.id = :certificateid";

                $templateinfo = $DB->get_record_sql($sql, ['certificateid' => $cert->customcertid]);

                if ($templateinfo) {
                    // Create template instance and generate PDF.
                    $templatedata = new \stdClass();
                    $templatedata->id = $templateinfo->templateid;
                    $templatedata->name = $templateinfo->templatename;
                    $templatedata->contextid = $templateinfo->contextid;

                    $template = new \mod_customcert\template($templatedata);
                    $filecontent = $template->generate_pdf(false, $cert->userid, true);

                    if ($filecontent !== false && !empty($filecontent)) {
                        $filename = 'customcert/' . $this->generate_customcert_filename($cert);
                        $zip->addFromString($filename, $filecontent);
                        $filesadded++;
                    } else {
                        $errors[] = 'Failed to generate customcert PDF for user: ' . $cert->userid;
                    }
                } else {
                    $errors[] = 'Template not found for customcert: ' . $cert->customcertid;
                }
            } catch (Exception $e) {
                $errors[] = 'Error downloading customcert ' . $cert->id . ': ' . $e->getMessage();
            }
        }

        // Process mod_certificate certificates.
        foreach ($modcertificates as $cert) {
            try {
                // For mod_certificate, use the URL download method to get content.
                $filecontent = $this->get_mod_certificate_content_via_url($cert->certificateid,
                    $cert->coursename, $cert->certificatename, $cert->userid);

                if ($filecontent !== false && !empty($filecontent)) {
                    $filename = 'mod_certificate/' . $this->generate_mod_certificate_filename($cert);
                    $zip->addFromString($filename, $filecontent);
                    $filesadded++;
                } else {
                    $errors[] = 'Failed to download mod_certificate PDF for user: ' . $cert->userid;
                }
            } catch (Exception $e) {
                $errors[] = 'Error downloading mod_certificate ' . $cert->id . ': ' . $e->getMessage();
            }
        }

        // Process mod_simplecertificate certificates.
        foreach ($simplecertificates as $cert) {
            try {
                // For mod_simplecertificate, use the content via URL method like mod_certificate.
                $filecontent = $this->get_simplecertificate_content_via_url($cert->code);

                if ($filecontent !== false && !empty($filecontent)) {
                    $filename = 'mod_simplecertificate/' . $this->generate_simplecertificate_filename($cert);
                    $zip->addFromString($filename, $filecontent);
                    $filesadded++;
                } else {
                    $errors[] = 'Failed to download mod_simplecertificate PDF for user: ' . $cert->userid .
                        ' (code: ' . $cert->code . ')';
                }
            } catch (Exception $e) {
                $errors[] = 'Error downloading mod_simplecertificate ' . $cert->id . ': ' . $e->getMessage();
            }
        }

        // Process mod_certificatebeautiful certificates.
        foreach ($certificatebeautifulcertificates as $cert) {
            try {
                // For mod_certificatebeautiful, use the content via URL method like mod_simplecertificate.
                $filecontent = $this->get_certificatebeautiful_content_via_url($cert->code);

                if ($filecontent !== false && !empty($filecontent)) {
                    $filename = 'mod_certificatebeautiful/' . $this->generate_certificatebeautiful_filename($cert);
                    $zip->addFromString($filename, $filecontent);
                    $filesadded++;
                } else {
                    $errors[] = 'Failed to download mod_certificatebeautiful PDF for user: ' . $cert->userid .
                        ' (code: ' . $cert->code . ')';
                }
            } catch (Exception $e) {
                $errors[] = 'Error downloading mod_certificatebeautiful ' . $cert->id . ': ' . $e->getMessage();
            }
        }

        // Add error log if needed.
        if (!empty($errors)) {
            $errorlog = "Download errors:\n" . implode("\n", $errors);
            $zip->addFromString('download_errors.txt', $errorlog);
        }

        $zip->close();

        if ($filesadded === 0) {
            unlink($zippath);
            throw new moodle_exception('novalidcertificates', 'block_download_certificates');
        }

        // Send ZIP file.
        $this->send_zip_file($zippath, $zipfilename);
        unlink($zippath);
    }

    /**
     * Send ZIP file to user.
     *
     * @param string $filepath ZIP file path
     * @param string $filename ZIP filename
     */
    private function send_zip_file($filepath, $filename) {

        if (!file_exists($filepath)) {
            throw new moodle_exception('filenotfound', 'block_download_certificates');
        }

        $filesize = filesize($filepath);

        // Vérification du contenu du ZIP avant envoi.
        $zip = new ZipArchive();
        if ($zip->open($filepath) === true) {
            $numfiles = $zip->numFiles;

            for ($i = 0; $i < $numfiles; $i++) {
                $fileinfo = $zip->statIndex($i);
            }
            $zip->close();
        }

        // Clean any previous output.
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Set headers.
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: 0');

        // Output file.
        readfile($filepath);
        exit;
    }

    /**
     * Download certificates by date range as ZIP.
     *
     * @param int $startdate Start date timestamp
     * @param int $enddate End date timestamp
     */
    public function download_certificates_by_date_range($startdate, $enddate) {
        global $DB;

        // Check capabilities.
        $context = context_system::instance();
        require_capability('block/download_certificates:manage', $context);

        // Validate dates.
        if ($startdate > $enddate) {
            throw new moodle_exception('invalidaterange', 'block_download_certificates');
        }

        // Get tool_certificate certificates in date range.
        $sql = "SELECT tci.id, tci.userid, tci.templateid, tci.code, tci.emailed,
                       tci.timecreated, tci.expires, tci.data, tci.component, tci.courseid,
                       u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                       u.middlename, u.alternatename, u.email,
                       c.fullname as coursename,
                       tc.name as templatename
                FROM {tool_certificate_issues} tci
                JOIN {user} u ON u.id = tci.userid
                LEFT JOIN {course} c ON c.id = tci.courseid
                LEFT JOIN {tool_certificate_templates} tc ON tc.id = tci.templateid
                WHERE tci.timecreated >= ? AND tci.timecreated <= ?
                ORDER BY tci.timecreated DESC";
        $toolcertificates = $DB->get_records_sql($sql, [$startdate, $enddate]);

        // Get customcert certificates in date range.
        $sql = "SELECT ci.id, ci.userid, ci.customcertid, ci.code, ci.emailed, ci.timecreated,
                       u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                       u.middlename, u.alternatename, u.email,
                       c.fullname as coursename,
                       cc.name as certificatename
                FROM {customcert_issues} ci
                JOIN {user} u ON u.id = ci.userid
                JOIN {customcert} cc ON cc.id = ci.customcertid
                LEFT JOIN {course} c ON c.id = cc.course
                WHERE ci.timecreated >= ? AND ci.timecreated <= ?
                ORDER BY ci.timecreated DESC";

        $customcertificates = [];
        $dbman = $DB->get_manager();
        if ($dbman->table_exists('customcert_issues')) {
            $customcertificates = $DB->get_records_sql($sql, [$startdate, $enddate]);
        }

        // Get mod_certificate certificates in date range.
        $sql = "SELECT ci.id, ci.userid, ci.certificateid, ci.code, ci.timecreated,
                       u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                       u.middlename, u.alternatename, u.email,
                       c.fullname as coursename,
                       cert.name as certificatename
                FROM {certificate_issues} ci
                JOIN {user} u ON u.id = ci.userid
                JOIN {certificate} cert ON cert.id = ci.certificateid
                LEFT JOIN {course} c ON c.id = cert.course
                WHERE ci.timecreated >= ? AND ci.timecreated <= ?
                ORDER BY ci.timecreated DESC";        $modcertificates = [];
        if ($dbman->table_exists('certificate_issues')) {
            $modcertificates = $DB->get_records_sql($sql, [$startdate, $enddate]);
        }

        // Get mod_simplecertificate certificates in date range.
        $sql = "SELECT si.id, si.certificateid, si.userid, si.certificatename, si.code,
                       si.timecreated, si.timedeleted, si.haschange, si.pathnamehash, si.coursename,
                       u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                       u.middlename, u.alternatename, u.email,
                       c.fullname as coursename_full
                FROM {simplecertificate_issues} si
                JOIN {user} u ON u.id = si.userid
                LEFT JOIN {course} c ON c.shortname = si.coursename OR c.fullname = si.coursename
                WHERE si.timecreated >= ? AND si.timecreated <= ?
                AND (si.timedeleted IS NULL OR si.timedeleted = 0)
                ORDER BY si.timecreated DESC";

        $simplecertificates = [];
        if ($dbman->table_exists('simplecertificate_issues')) {
            $simplecertificates = $DB->get_records_sql($sql, [$startdate, $enddate]);
        }

        // Get mod_certificatebeautiful certificates in date range.
        $sql = "SELECT cbi.id, cbi.userid, cbi.cmid, cbi.certificatebeautifulid, cbi.code,
                       cbi.version, cbi.timecreated,
                       u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                       u.middlename, u.alternatename, u.email,
                       c.fullname as coursename,
                       cb.name as certificatename
                FROM {certificatebeautiful_issue} cbi
                JOIN {user} u ON u.id = cbi.userid
                LEFT JOIN {certificatebeautiful} cb ON cb.id = cbi.certificatebeautifulid
                LEFT JOIN {course_modules} cm ON cm.id = cbi.cmid
                LEFT JOIN {course} c ON c.id = cm.course
                WHERE cbi.timecreated >= ? AND cbi.timecreated <= ?
                ORDER BY cbi.timecreated DESC";

        $certificatebeautifulcertificates = [];
        if ($dbman->table_exists('certificatebeautiful_issue')) {
            $certificatebeautifulcertificates = $DB->get_records_sql($sql, [$startdate, $enddate]);
        }

        if (empty($toolcertificates) && empty($customcertificates) && empty($modcertificates) &&
            empty($simplecertificates) && empty($certificatebeautifulcertificates)) {
            throw new moodle_exception('nocertificatesinrange', 'block_download_certificates');
        }

        // Create ZIP.
        $tempdir = make_request_directory();
        $startdatestr = date('Y-m-d', $startdate);
        $enddatestr = date('Y-m-d', $enddate);
        $zipfilename = "certificates_{$startdatestr}_to_{$enddatestr}.zip";
        $zippath = $tempdir . '/' . $zipfilename;

        $zip = new ZipArchive();
        if ($zip->open($zippath, ZipArchive::CREATE) !== true) {
            throw new moodle_exception('cannotcreatezipfile', 'block_download_certificates');
        }

        $filesadded = 0;
        $errors = [];

        // Process tool_certificate certificates.
        foreach ($toolcertificates as $cert) {
            try {
                $filecontent = $this->get_certificate_file_content($cert->timecreated, $cert->code);

                if ($filecontent === false) {
                    // Try HTTP download.
                    $fileurl = $this->get_certificate_download_url($cert->timecreated, $cert->code);
                    $filecontent = $this->download_file_via_http($fileurl);
                }

                if ($filecontent !== false) {
                    $filename = 'tool_certificate/' . $this->generate_certificate_filename($cert);
                    $zip->addFromString($filename, $filecontent);
                    $filesadded++;
                } else {
                    $errors[] = 'Failed to download tool_certificate: ' . $cert->code;
                }
            } catch (Exception $e) {
                $errors[] = 'Error downloading tool_certificate ' . $cert->code . ': ' . $e->getMessage();
            }
        }

        // Process customcert certificates.
        foreach ($customcertificates as $cert) {
            try {
                // Get template info for this certificate.
                $sql = "SELECT ct.id as templateid, ct.name as templatename, ct.contextid
                        FROM {customcert} cc
                        JOIN {customcert_templates} ct ON ct.id = cc.templateid
                        WHERE cc.id = :certificateid";

                $templateinfo = $DB->get_record_sql($sql, ['certificateid' => $cert->customcertid]);

                if ($templateinfo) {
                    // Create template instance and generate PDF.
                    $templatedata = new \stdClass();
                    $templatedata->id = $templateinfo->templateid;
                    $templatedata->name = $templateinfo->templatename;
                    $templatedata->contextid = $templateinfo->contextid;

                    $template = new \mod_customcert\template($templatedata);
                    $filecontent = $template->generate_pdf(false, $cert->userid, true);

                    if ($filecontent !== false && !empty($filecontent)) {
                        $filename = 'customcert/' . $this->generate_customcert_filename($cert);
                        $zip->addFromString($filename, $filecontent);
                        $filesadded++;
                    } else {
                        $errors[] = 'Failed to generate customcert PDF for user: ' . $cert->userid;
                    }
                } else {
                    $errors[] = 'Template not found for customcert: ' . $cert->customcertid;
                }
            } catch (Exception $e) {
                $errors[] = 'Error downloading customcert ' . $cert->id . ': ' . $e->getMessage();
            }
        }

        // Process mod_certificate certificates.
        foreach ($modcertificates as $cert) {
            try {
                // For mod_certificate, use the URL download method to get content.
                $filecontent = $this->get_mod_certificate_content_via_url($cert->certificateid,
                    $cert->coursename, $cert->certificatename, $cert->userid);

                if ($filecontent !== false && !empty($filecontent)) {
                    $filename = 'mod_certificate/' . $this->generate_mod_certificate_filename($cert);
                    $zip->addFromString($filename, $filecontent);
                    $filesadded++;
                } else {
                    $errors[] = 'Failed to download mod_certificate PDF for user: ' . $cert->userid;
                }
            } catch (Exception $e) {
                $errors[] = 'Error downloading mod_certificate ' . $cert->id . ': ' . $e->getMessage();
            }
        }

        // Process mod_simplecertificate certificates.
        foreach ($simplecertificates as $cert) {
            try {
                // For mod_simplecertificate, use the content via URL method like mod_certificate.
                $filecontent = $this->get_simplecertificate_content_via_url($cert->code);

                if ($filecontent !== false && !empty($filecontent)) {
                    $filename = 'mod_simplecertificate/' . $this->generate_simplecertificate_filename($cert);
                    $zip->addFromString($filename, $filecontent);
                    $filesadded++;
                } else {
                    $errors[] = 'Failed to download mod_simplecertificate PDF for user: ' . $cert->userid .
                        ' (code: ' . $cert->code . ')';
                }
            } catch (Exception $e) {
                $errors[] = 'Error downloading mod_simplecertificate ' . $cert->id . ': ' . $e->getMessage();
            }
        }

        // Process mod_certificatebeautiful certificates.
        foreach ($certificatebeautifulcertificates as $cert) {
            try {
                // For mod_certificatebeautiful, use the content via URL method like mod_simplecertificate.
                $filecontent = $this->get_certificatebeautiful_content_via_url($cert->code);

                if ($filecontent !== false && !empty($filecontent)) {
                    $filename = 'mod_certificatebeautiful/' . $this->generate_certificatebeautiful_filename($cert);
                    $zip->addFromString($filename, $filecontent);
                    $filesadded++;
                } else {
                    $errors[] = 'Failed to download mod_certificatebeautiful PDF for user: ' . $cert->userid .
                        ' (code: ' . $cert->code . ')';
                }
            } catch (Exception $e) {
                $errors[] = 'Error downloading mod_certificatebeautiful ' . $cert->id . ': ' . $e->getMessage();
            }
        }

        // Add error log if needed.
        if (!empty($errors)) {
            $errorlog = "Download errors for date range {$startdatestr} to {$enddatestr}:\n" . implode("\n", $errors);
            $zip->addFromString('download_errors.txt', $errorlog);
        }

        $zip->close();

        if ($filesadded === 0) {
            unlink($zippath);
            throw new moodle_exception('novalidcertificatesinrange', 'block_download_certificates');
        }

        // Send ZIP file.
        $this->send_zip_file($zippath, $zipfilename);
        unlink($zippath);
    }

    /**
     * Get list of courses that have certificates.
     *
     * @return array Array of courses with certificate counts
     */
    public function get_courses_with_certificates() {
        global $DB;

        // Check capabilities.
        $context = context_system::instance();
        require_capability('block/download_certificates:manage', $context);

        // Get courses with tool_certificate certificates.
        $sql = "SELECT c.id, c.fullname, c.shortname, COUNT(tci.id) as tool_certificate_count
                FROM {course} c
                INNER JOIN {tool_certificate_issues} tci ON c.id = tci.courseid
                WHERE c.id > 1
                GROUP BY c.id, c.fullname, c.shortname";

        $toolcertcourses = $DB->get_records_sql($sql);

        // Get courses with customcert certificates.
        $customcertcourses = [];
        $dbman = $DB->get_manager();
        if ($dbman->table_exists('customcert_issues')) {
            $sql = "SELECT c.id, c.fullname, c.shortname, COUNT(ci.id) as customcert_count
                    FROM {course} c
                    INNER JOIN {customcert} cc ON c.id = cc.course
                    INNER JOIN {customcert_issues} ci ON cc.id = ci.customcertid
                    WHERE c.id > 1
                    GROUP BY c.id, c.fullname, c.shortname";

            $customcertcourses = $DB->get_records_sql($sql);
        }

        // Get courses with mod_certificate certificates.
        $modcertcourses = [];
        if ($dbman->table_exists('certificate_issues')) {
            $sql = "SELECT c.id, c.fullname, c.shortname, COUNT(ci.id) as mod_certificate_count
                    FROM {course} c
                    INNER JOIN {certificate} cert ON c.id = cert.course
                    INNER JOIN {certificate_issues} ci ON cert.id = ci.certificateid
                    WHERE c.id > 1
                    GROUP BY c.id, c.fullname, c.shortname";

            $modcertcourses = $DB->get_records_sql($sql);
        }

        // Get courses with mod_simplecertificate certificates.
        $simplecertcourses = [];
        if ($dbman->table_exists('simplecertificate_issues')) {
            $sql = "SELECT c.id, c.fullname, c.shortname, COUNT(si.id) as simplecertificate_count
                    FROM {course} c
                    INNER JOIN {simplecertificate_issues} si ON (c.shortname = si.coursename OR c.fullname = si.coursename)
                    WHERE c.id > 1 AND (si.timedeleted IS NULL OR si.timedeleted = 0)
                    GROUP BY c.id, c.fullname, c.shortname";

            $simplecertcourses = $DB->get_records_sql($sql);
        }

        // Get courses with mod_certificatebeautiful certificates.
        $certificatebeautifulcourses = [];
        if ($dbman->table_exists('certificatebeautiful_issue')) {
            $sql = "SELECT c.id, c.fullname, c.shortname, COUNT(cbi.id) as certificatebeautiful_count
                    FROM {course} c
                    INNER JOIN {course_modules} cm ON c.id = cm.course
                    INNER JOIN {certificatebeautiful_issue} cbi ON cm.id = cbi.cmid
                    WHERE c.id > 1
                    GROUP BY c.id, c.fullname, c.shortname";

            $certificatebeautifulcourses = $DB->get_records_sql($sql);
        }

        // Merge and combine counts.
        $allcourses = [];
        foreach ($toolcertcourses as $course) {
            $allcourses[$course->id] = [
                'id' => $course->id,
                'fullname' => $course->fullname,
                'shortname' => $course->shortname,
                'tool_certificate_count' => $course->tool_certificate_count,
                'customcert_count' => 0,
                'mod_certificate_count' => 0,
                'simplecertificate_count' => 0,
                'certificatebeautiful_count' => 0,
            ];
        }

        foreach ($customcertcourses as $course) {
            if (isset($allcourses[$course->id])) {
                $allcourses[$course->id]['customcert_count'] = $course->customcert_count;
            } else {
                $allcourses[$course->id] = [
                    'id' => $course->id,
                    'fullname' => $course->fullname,
                    'shortname' => $course->shortname,
                    'tool_certificate_count' => 0,
                    'customcert_count' => $course->customcert_count,
                    'mod_certificate_count' => 0,
                    'simplecertificate_count' => 0,
                ];
            }
        }

        foreach ($modcertcourses as $course) {
            if (isset($allcourses[$course->id])) {
                $allcourses[$course->id]['mod_certificate_count'] = $course->mod_certificate_count;
            } else {
                $allcourses[$course->id] = [
                    'id' => $course->id,
                    'fullname' => $course->fullname,
                    'shortname' => $course->shortname,
                    'tool_certificate_count' => 0,
                    'customcert_count' => 0,
                    'mod_certificate_count' => $course->mod_certificate_count,
                    'simplecertificate_count' => 0,
                    'certificatebeautiful_count' => 0,
                ];
            }
        }

        foreach ($simplecertcourses as $course) {
            if (isset($allcourses[$course->id])) {
                $allcourses[$course->id]['simplecertificate_count'] = $course->simplecertificate_count;
            } else {
                $allcourses[$course->id] = [
                    'id' => $course->id,
                    'fullname' => $course->fullname,
                    'shortname' => $course->shortname,
                    'tool_certificate_count' => 0,
                    'customcert_count' => 0,
                    'mod_certificate_count' => 0,
                    'simplecertificate_count' => $course->simplecertificate_count,
                    'certificatebeautiful_count' => 0,
                ];
            }
        }

        foreach ($certificatebeautifulcourses as $course) {
            if (isset($allcourses[$course->id])) {
                $allcourses[$course->id]['certificatebeautiful_count'] = $course->certificatebeautiful_count;
            } else {
                $allcourses[$course->id] = [
                    'id' => $course->id,
                    'fullname' => $course->fullname,
                    'shortname' => $course->shortname,
                    'tool_certificate_count' => 0,
                    'customcert_count' => 0,
                    'mod_certificate_count' => 0,
                    'simplecertificate_count' => 0,
                    'certificatebeautiful_count' => $course->certificatebeautiful_count,
                ];
            }
        }

        // Calculate total count and format for return.
        $courselist = [];
        foreach ($allcourses as $course) {
            $totalcount = $course['tool_certificate_count'] + $course['customcert_count'] +
                $course['mod_certificate_count'] + $course['simplecertificate_count'] +
                $course['certificatebeautiful_count'];
            $courselist[] = [
                'id' => $course['id'],
                'fullname' => $course['fullname'],
                'shortname' => $course['shortname'],
                'certificate_count' => $totalcount,
                'tool_certificate_count' => $course['tool_certificate_count'],
                'customcert_count' => $course['customcert_count'],
                'mod_certificate_count' => $course['mod_certificate_count'],
                'simplecertificate_count' => $course['simplecertificate_count'],
                'certificatebeautiful_count' => $course['certificatebeautiful_count'],
            ];
        }

        // Sort by course name.
        usort($courselist, function($a, $b) {
            return strcmp($a['fullname'], $b['fullname']);
        });

        return $courselist;
    }

    /**
     * Download all certificates for a specific course as ZIP.
     *
     * @param int $courseid Course ID
     */
    public function download_certificates_by_course($courseid) {
        global $DB;

        // Check capabilities.
        $context = context_system::instance();
        require_capability('block/download_certificates:manage', $context);

        // Verify the course exists.
        $course = $DB->get_record('course', ['id' => $courseid]);
        if (!$course) {
            throw new moodle_exception('coursenotfound', 'block_download_certificates');
        }

        // Get all tool_certificate certificates for this course.
        $sql = "SELECT tci.id, tci.userid, tci.templateid, tci.code, tci.emailed,
                       tci.timecreated, tci.expires, tci.data, tci.component, tci.courseid,
                       u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                       u.middlename, u.alternatename, u.email,
                       c.fullname as coursename,
                       tc.name as templatename
                FROM {tool_certificate_issues} tci
                JOIN {user} u ON u.id = tci.userid
                LEFT JOIN {course} c ON c.id = tci.courseid
                LEFT JOIN {tool_certificate_templates} tc ON tc.id = tci.templateid
                WHERE tci.courseid = :courseid
                ORDER BY u.lastname, u.firstname, tci.timecreated";

        $toolcertificates = $DB->get_records_sql($sql, ['courseid' => $courseid]);

        // Get all customcert certificates for this course.
        $customcertificates = [];
        $dbman = $DB->get_manager();
        if ($dbman->table_exists('customcert_issues')) {
            $sql = "SELECT ci.id, ci.userid, ci.customcertid, ci.code, ci.emailed, ci.timecreated,
                           u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                           u.middlename, u.alternatename, u.email,
                           c.fullname as coursename,
                           cc.name as certificatename
                    FROM {customcert_issues} ci
                    JOIN {user} u ON u.id = ci.userid
                    JOIN {customcert} cc ON cc.id = ci.customcertid
                    LEFT JOIN {course} c ON c.id = cc.course
                    WHERE cc.course = :courseid
                    ORDER BY u.lastname, u.firstname, ci.timecreated";

            $customcertificates = $DB->get_records_sql($sql, ['courseid' => $courseid]);
        }

        // Get all mod_certificate certificates for this course.
        $modcertificates = [];
        if ($dbman->table_exists('certificate_issues')) {
            $sql = "SELECT ci.id, ci.userid, ci.certificateid, ci.code, ci.timecreated,
                           u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                           u.middlename, u.alternatename, u.email,
                           c.fullname as coursename,
                           cert.name as certificatename
                    FROM {certificate_issues} ci
                    JOIN {user} u ON u.id = ci.userid
                    JOIN {certificate} cert ON cert.id = ci.certificateid
                    LEFT JOIN {course} c ON c.id = cert.course
                    WHERE cert.course = :courseid
                    ORDER BY u.lastname, u.firstname, ci.timecreated";

            $modcertificates = $DB->get_records_sql($sql, ['courseid' => $courseid]);
        }

        // Get all mod_simplecertificate certificates for this course.
        $simplecertificates = [];
        if ($dbman->table_exists('simplecertificate_issues')) {
            $sql = "SELECT si.id, si.certificateid, si.userid, si.certificatename, si.code,
                           si.timecreated, si.timedeleted, si.haschange, si.pathnamehash, si.coursename,
                           u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                           u.middlename, u.alternatename, u.email,
                           c.fullname as coursename_full
                    FROM {simplecertificate_issues} si
                    JOIN {user} u ON u.id = si.userid
                    LEFT JOIN {course} c ON c.shortname = si.coursename OR c.fullname = si.coursename
                    WHERE c.id = :courseid AND (si.timedeleted IS NULL OR si.timedeleted = 0)
                    ORDER BY u.lastname, u.firstname, si.timecreated";

            $simplecertificates = $DB->get_records_sql($sql, ['courseid' => $courseid]);
        }

        // Get all mod_certificatebeautiful certificates for this course.
        $certificatebeautifulcertificates = [];
        if ($dbman->table_exists('certificatebeautiful_issue')) {
            $sql = "SELECT cbi.id, cbi.userid, cbi.cmid, cbi.certificatebeautifulid, cbi.code,
                           cbi.version, cbi.timecreated,
                           u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                           u.middlename, u.alternatename, u.email,
                           c.fullname as coursename,
                           cb.name as certificatename
                    FROM {certificatebeautiful_issue} cbi
                    JOIN {user} u ON u.id = cbi.userid
                    LEFT JOIN {certificatebeautiful} cb ON cb.id = cbi.certificatebeautifulid
                    LEFT JOIN {course_modules} cm ON cm.id = cbi.cmid
                    LEFT JOIN {course} c ON c.id = cm.course
                    WHERE c.id = :courseid
                    ORDER BY u.lastname, u.firstname, cbi.timecreated";

            $certificatebeautifulcertificates = $DB->get_records_sql($sql, ['courseid' => $courseid]);
        }

        if (empty($toolcertificates) && empty($customcertificates) && empty($modcertificates) &&
            empty($simplecertificates) && empty($certificatebeautifulcertificates)) {
            throw new moodle_exception('nocertificatesforcourse', 'block_download_certificates');
        }

        // Create ZIP file.
        $tempdir = make_temp_directory('block_download_certificates');
        $cleancoursename = clean_filename($course->fullname);
        $zipfilename = "Certificats_{$cleancoursename}.zip";
        $zippath = $tempdir . '/' . $zipfilename;

        // IMPORTANT: Supprimer le fichier ZIP existant s'il existe pour éviter les mélanges.
        if (file_exists($zippath)) {
            unlink($zippath);
        }

        $zip = new ZipArchive();
        if ($zip->open($zippath, ZipArchive::CREATE) !== true) {
            throw new moodle_exception('cannotcreatezipfile', 'block_download_certificates');
        }

        $filesadded = 0;
        $errors = [];
        $addedfiles = []; // Track added files to prevent duplicates.

        // Process tool_certificate certificates.
        foreach ($toolcertificates as $cert) {

            try {
                // Get certificate file content.
                $filecontent = $this->get_certificate_file_content($cert->timecreated, $cert->code);

                if ($filecontent === false) {
                    // Try HTTP download.
                    $fileurl = $this->get_certificate_download_url($cert->timecreated, $cert->code);
                    $filecontent = $this->download_file_via_http($fileurl);
                }

                if ($filecontent !== false) {
                    $filename = 'tool_certificate/' . $this->generate_certificate_filename($cert);

                    // Prevent duplicate files.
                    if (!in_array($filename, $addedfiles)) {
                        $zip->addFromString($filename, $filecontent);
                        $addedfiles[] = $filename;
                        $filesadded++;
                    }
                } else {
                    $errors[] = 'Failed to download tool_certificate: ' . $cert->code;
                }
            } catch (Exception $e) {
                // Continue with other certificates if one fails.
                $errors[] = 'Error with tool_certificate ' . $cert->code . ': ' . $e->getMessage();
                continue;
            }
        }

        // Process customcert certificates.
        foreach ($customcertificates as $cert) {
            try {
                global $CFG;
                require_once($CFG->dirroot . '/mod/customcert/classes/template.php');

                // Get template info for this certificate.
                $sql = "SELECT ct.id as templateid, ct.name as templatename, ct.contextid
                        FROM {customcert} cc
                        JOIN {customcert_templates} ct ON ct.id = cc.templateid
                        WHERE cc.id = :certificateid";

                $templateinfo = $DB->get_record_sql($sql, ['certificateid' => $cert->customcertid]);

                if ($templateinfo) {
                    // Create template instance and generate PDF.
                    $templatedata = new \stdClass();
                    $templatedata->id = $templateinfo->templateid;
                    $templatedata->name = $templateinfo->templatename;
                    $templatedata->contextid = $templateinfo->contextid;

                    $template = new \mod_customcert\template($templatedata);
                    $filecontent = $template->generate_pdf(false, $cert->userid, true);

                    if ($filecontent !== false && !empty($filecontent)) {
                        $filename = 'customcert/' . $this->generate_customcert_filename($cert);

                        // Prevent duplicate files.
                        if (!in_array($filename, $addedfiles)) {
                            $zip->addFromString($filename, $filecontent);
                            $addedfiles[] = $filename;
                            $filesadded++;
                        }
                    } else {
                        $errors[] = 'Failed to generate customcert PDF for user: ' . $cert->userid;
                    }
                } else {
                    $errors[] = 'Template not found for customcert: ' . $cert->customcertid;
                }
            } catch (Exception $e) {
                // Continue with other certificates if one fails.
                $errors[] = 'Error with customcert ' . $cert->id . ': ' . $e->getMessage();
                continue;
            }
        }

        // Process mod_certificate certificates.
        foreach ($modcertificates as $modcert) {
            try {
                // For mod_certificate, use the URL download method to get content.
                $filecontent = $this->get_mod_certificate_content_via_url($modcert->certificateid,
                    $modcert->coursename, $modcert->certificatename, $modcert->userid);

                if ($filecontent !== false && !empty($filecontent)) {
                    $filename = 'mod_certificate/' . $this->generate_mod_certificate_filename($modcert);
                    $zip->addFromString($filename, $filecontent);
                    $filesadded++;
                } else {
                    $errors[] = 'Failed to download mod_certificate PDF for user: ' . $modcert->userid;
                }
            } catch (Exception $e) {
                $errors[] = 'Error with mod_certificate ' . $modcert->id . ': ' . $e->getMessage();
                continue;
            }
        }

        // Process mod_simplecertificate certificates.
        foreach ($simplecertificates as $simplecert) {
            try {
                // For mod_simplecertificate, use the content via URL method like mod_certificate.
                $filecontent = $this->get_simplecertificate_content_via_url($simplecert->code);

                if ($filecontent !== false && !empty($filecontent)) {
                    $filename = 'mod_simplecertificate/' . $this->generate_simplecertificate_filename($simplecert);
                    $zip->addFromString($filename, $filecontent);
                    $filesadded++;
                } else {
                    $errors[] = 'Failed to download mod_simplecertificate PDF for user: ' .
                        $simplecert->userid . ' (code: ' . $simplecert->code . ')';
                }
            } catch (Exception $e) {
                $errors[] = 'Error with mod_simplecertificate ' . $simplecert->id . ': ' . $e->getMessage();
                continue;
            }
        }

        // Process mod_certificatebeautiful certificates.
        foreach ($certificatebeautifulcertificates as $cbcert) {
            try {
                // For mod_certificatebeautiful, try file storage first then URL fallback.
                $filecontent = $this->get_certificatebeautiful_content_via_url($cbcert->code);

                if ($filecontent !== false && !empty($filecontent)) {
                    $filename = 'mod_certificatebeautiful/' . $this->generate_certificatebeautiful_filename($cbcert);
                    $zip->addFromString($filename, $filecontent);
                    $filesadded++;
                } else {
                    $errors[] = 'Failed to download mod_certificatebeautiful PDF for user: ' .
                                $cbcert->userid . ' (code: ' . $cbcert->code . ')';
                }
            } catch (Exception $e) {
                $errors[] = 'Error with mod_certificatebeautiful ' . $cbcert->id . ': ' . $e->getMessage();
                continue;
            }
        }

        // Add error log only if there are critical errors.
        if (!empty($errors)) {
            $errorlog = "Download errors for course '{$course->fullname}':\n" . implode("\n", $errors);
            $zip->addFromString('download_errors.txt', $errorlog);
        }

        $zip->close();

        if ($filesadded === 0) {
            unlink($zippath);
            throw new moodle_exception('novalidcertificatesforcourse', 'block_download_certificates');
        }

        // Send ZIP file.
        $this->send_zip_file($zippath, $zipfilename);
        unlink($zippath);
    }

    /**
     * Download all certificates for a specific user as ZIP.
     *
     * @param int $userid User ID
     */
    public function download_user_certificates($userid) {
        global $DB, $USER;

        // Users can only download their own certificates.
        if ($userid !== $USER->id) {
            $context = context_system::instance();
            require_capability('block/download_certificates:manage', $context);
        }

        // Get user's tool_certificate certificates.
        $sql = "SELECT tci.*, u.firstname, u.lastname, c.fullname as coursename, tc.name as templatename
                FROM {tool_certificate_issues} tci
                JOIN {user} u ON u.id = tci.userid
                LEFT JOIN {course} c ON c.id = tci.courseid
                LEFT JOIN {tool_certificate_templates} tc ON tc.id = tci.templateid
                WHERE tci.userid = ?
                ORDER BY tci.timecreated DESC";

        $toolcertificates = $DB->get_records_sql($sql, [$userid]);

        // Get user's customcert certificates.
        $customcertificates = [];
        $dbman = $DB->get_manager();
        if ($dbman->table_exists('customcert_issues')) {
            $sql = "SELECT ci.id, ci.userid, ci.customcertid, ci.code, ci.emailed, ci.timecreated,
                           u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                           u.middlename, u.alternatename, u.email,
                           c.fullname as coursename,
                           cc.name as certificatename
                    FROM {customcert_issues} ci
                    JOIN {user} u ON u.id = ci.userid
                    JOIN {customcert} cc ON cc.id = ci.customcertid
                    LEFT JOIN {course} c ON c.id = cc.course
                    WHERE ci.userid = ?
                    ORDER BY ci.timecreated DESC";

            $customcertificates = $DB->get_records_sql($sql, [$userid]);
        }

        // Get all mod_certificate certificates for this user.
        $modcertificates = [];
        if ($dbman->table_exists('certificate_issues')) {
            $sql = "SELECT ci.id, ci.userid, ci.certificateid, ci.code, ci.timecreated,
                           u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                           u.middlename, u.alternatename, u.email,
                           c.fullname as coursename,
                           cert.name as certificatename
                    FROM {certificate_issues} ci
                    JOIN {user} u ON u.id = ci.userid
                    JOIN {certificate} cert ON cert.id = ci.certificateid
                    LEFT JOIN {course} c ON c.id = cert.course
                    WHERE ci.userid = ?
                    ORDER BY ci.timecreated DESC";

            $modcertificates = $DB->get_records_sql($sql, [$userid]);
        }

        // Get all mod_simplecertificate certificates for this user.
        $simplecertificates = [];
        if ($dbman->table_exists('simplecertificate_issues')) {
            $sql = "SELECT si.id, si.certificateid, si.userid, si.certificatename, si.code,
                           si.timecreated, si.timedeleted, si.haschange, si.pathnamehash, si.coursename,
                           u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                           u.middlename, u.alternatename, u.email,
                           c.fullname as coursename_full
                    FROM {simplecertificate_issues} si
                    JOIN {user} u ON u.id = si.userid
                    LEFT JOIN {course} c ON c.shortname = si.coursename OR c.fullname = si.coursename
                    WHERE si.userid = ? AND (si.timedeleted IS NULL OR si.timedeleted = 0)
                    ORDER BY si.timecreated DESC";

            $simplecertificates = $DB->get_records_sql($sql, [$userid]);
        }

        // Get all mod_certificatebeautiful certificates for this user.
        $certificatebeautifulcertificates = [];
        if ($dbman->table_exists('certificatebeautiful_issue')) {
            $sql = "SELECT cbi.id, cbi.userid, cbi.cmid, cbi.certificatebeautifulid, cbi.code,
                           cbi.version, cbi.timecreated,
                           u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                           u.middlename, u.alternatename, u.email,
                           c.fullname as coursename,
                           cb.name as certificatename
                    FROM {certificatebeautiful_issue} cbi
                    JOIN {user} u ON u.id = cbi.userid
                    LEFT JOIN {certificatebeautiful} cb ON cb.id = cbi.certificatebeautifulid
                    LEFT JOIN {course_modules} cm ON cm.id = cbi.cmid
                    LEFT JOIN {course} c ON c.id = cm.course
                    WHERE cbi.userid = ?
                    ORDER BY cbi.timecreated DESC";

            $certificatebeautifulcertificates = $DB->get_records_sql($sql, [$userid]);
        }

        if (empty($toolcertificates) &&
            empty($customcertificates) &&
            empty($modcertificates) &&
            empty($simplecertificates) &&
            empty($certificatebeautifulcertificates)) {
            throw new moodle_exception('nocertificatesuser', 'block_download_certificates');
        }

        // Create ZIP.
        $tempdir = make_request_directory();
        $user = $DB->get_record('user', ['id' => $userid],
            'firstname, lastname, firstnamephonetic, lastnamephonetic, middlename, alternatename');
        $username = fullname($user);
        $zipfilename = 'certificates_' . clean_filename($username) . '.zip';
        $zippath = $tempdir . '/' . $zipfilename;

        $zip = new ZipArchive();
        if ($zip->open($zippath, ZipArchive::CREATE) !== true) {
            throw new moodle_exception('cannotcreatezipfile', 'block_download_certificates');
        }

        $filesadded = 0;
        $errors = [];

        // Process tool_certificate certificates.
        foreach ($toolcertificates as $cert) {
            try {
                $filecontent = $this->get_certificate_file_content($cert->timecreated, $cert->code);
                if ($filecontent !== false) {
                    $filename = 'tool_certificate/' . $this->generate_certificate_filename($cert);
                    $zip->addFromString($filename, $filecontent);
                    $filesadded++;
                } else {
                    $errors[] = sprintf('Error downloading tool_certificate %s (code: %s)',
                                      $cert->timecreated, $cert->code);
                }
            } catch (Exception $e) {
                $errors[] = sprintf('Error downloading tool_certificate %s: %s',
                                  $cert->code, $e->getMessage());
            }
        }

        // Process customcert certificates.
        foreach ($customcertificates as $cert) {
            try {
                global $CFG;
                require_once($CFG->dirroot . '/mod/customcert/classes/template.php');

                // Get template info for this certificate.
                $sql = "SELECT ct.id as templateid, ct.name as templatename, ct.contextid
                        FROM {customcert} cc
                        JOIN {customcert_templates} ct ON ct.id = cc.templateid
                        WHERE cc.id = :certificateid";

                $templateinfo = $DB->get_record_sql($sql, ['certificateid' => $cert->customcertid]);

                if ($templateinfo) {
                    // Create template instance and generate PDF.
                    $templatedata = new \stdClass();
                    $templatedata->id = $templateinfo->templateid;
                    $templatedata->name = $templateinfo->templatename;
                    $templatedata->contextid = $templateinfo->contextid;

                    $template = new \mod_customcert\template($templatedata);
                    $filecontent = $template->generate_pdf(false, $cert->userid, true);

                    if ($filecontent !== false && !empty($filecontent)) {
                        $filename = 'customcert/' . $this->generate_customcert_filename($cert);
                        $zip->addFromString($filename, $filecontent);
                        $filesadded++;
                    } else {
                        $errors[] = 'Failed to generate customcert PDF for user: ' . $cert->userid;
                    }
                } else {
                    $errors[] = 'Template not found for customcert: ' . $cert->customcertid;
                }
            } catch (Exception $e) {
                $errors[] = sprintf('Error downloading customcert %s: %s',
                                  $cert->id, $e->getMessage());
            }
        }

        // Process mod_certificate certificates.
        foreach ($modcertificates as $modcert) {
            try {
                // For mod_certificate, use the URL download method to get content.
                $filecontent = $this->get_mod_certificate_content_via_url($modcert->certificateid,
                                                                            $modcert->coursename,
                                                                            $modcert->certificatename,
                                                                            $modcert->userid);

                if ($filecontent !== false && !empty($filecontent)) {
                    $filename = 'mod_certificate/' . $this->generate_mod_certificate_filename($modcert);
                    $zip->addFromString($filename, $filecontent);
                    $filesadded++;
                } else {
                    $errors[] = 'Failed to download mod_certificate PDF for user: ' . $modcert->userid;
                }
            } catch (Exception $e) {
                $errors[] = sprintf('Error downloading mod_certificate %s: %s',
                                  $modcert->id, $e->getMessage());
            }
        }

        // Process mod_simplecertificate certificates.
        foreach ($simplecertificates as $simplecert) {
            try {
                // For mod_simplecertificate, use the content via URL method like mod_certificate.
                $filecontent = $this->get_simplecertificate_content_via_url($simplecert->code);

                if ($filecontent !== false && !empty($filecontent)) {
                    $filename = 'mod_simplecertificate/' . $this->generate_simplecertificate_filename($simplecert);
                    $zip->addFromString($filename, $filecontent);
                    $filesadded++;
                } else {
                    $errors[] = 'Failed to download mod_simplecertificate PDF for user: ' .
                                $simplecert->userid . ' (code: ' . $simplecert->code . ')';
                }
            } catch (Exception $e) {
                $errors[] = sprintf('Error downloading mod_simplecertificate %s: %s',
                                  $simplecert->id, $e->getMessage());
            }
        }

        // Process mod_certificatebeautiful certificates.
        foreach ($certificatebeautifulcertificates as $beautifulcert) {
            try {
                // For mod_certificatebeautiful, use the content via URL method.
                $filecontent = $this->get_certificatebeautiful_content_via_url($beautifulcert->code);

                if ($filecontent !== false && !empty($filecontent)) {
                    $filename = 'mod_certificatebeautiful/' . $this->generate_certificatebeautiful_filename($beautifulcert);
                    $zip->addFromString($filename, $filecontent);
                    $filesadded++;
                } else {
                    $errors[] = 'Failed to download mod_certificatebeautiful PDF for user: ' .
                                $beautifulcert->userid . ' (code: ' . $beautifulcert->code . ')';
                }
            } catch (Exception $e) {
                $errors[] = sprintf('Error downloading mod_certificatebeautiful %s: %s',
                                  $beautifulcert->id, $e->getMessage());
            }
        }

        // Add error log if needed.
        if (!empty($errors)) {
            $errorlog = "Download errors for user '{$username}':\n" . implode("\n", $errors);
            $zip->addFromString('download_errors.txt', $errorlog);
        }

        $zip->close();

        if ($filesadded === 0) {
            unlink($zippath);
            throw new moodle_exception('novalidcertificatesuser', 'block_download_certificates');
        }

        // Send ZIP file.
        $this->send_zip_file($zippath, $zipfilename);
        unlink($zippath);
    }

    /**
     * Download certificates for all members of a cohort.
     *
     * @param int $cohortid Cohort ID
     * @throws moodle_exception
     */
    public function download_cohort_certificates($cohortid) {
        global $DB;

        // Verify cohort exists.
        $cohort = $DB->get_record('cohort', ['id' => $cohortid], '*', MUST_EXIST);

        // Get all members of the cohort.
        $sql = "SELECT DISTINCT u.id, u.firstname, u.lastname, u.email
                FROM {cohort_members} cm
                JOIN {user} u ON u.id = cm.userid
                WHERE cm.cohortid = :cohortid
                AND u.deleted = 0
                ORDER BY u.lastname, u.firstname";

        $cohortmembers = $DB->get_records_sql($sql, ['cohortid' => $cohortid]);

        if (empty($cohortmembers)) {
            throw new moodle_exception('nocohortmembers', 'block_download_certificates');
        }

        // Get user IDs.
        $userids = array_keys($cohortmembers);

        // Get tool_certificate certificates for all cohort members.
        list($insql, $params) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        $sql = "SELECT tci.id, tci.userid, tci.templateid, tci.code, tci.emailed,
                       tci.timecreated, tci.expires, tci.data, tci.component, tci.courseid,
                       u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                       u.middlename, u.alternatename, u.email,
                       c.fullname as coursename,
                       tc.name as templatename
                FROM {tool_certificate_issues} tci
                JOIN {user} u ON u.id = tci.userid
                LEFT JOIN {course} c ON c.id = tci.courseid
                LEFT JOIN {tool_certificate_templates} tc ON tc.id = tci.templateid
                WHERE tci.userid $insql
                ORDER BY u.lastname, u.firstname, tci.timecreated DESC";

        $toolcertificates = $DB->get_records_sql($sql, $params);

        // Get customcert certificates for all cohort members.
        $customcertificates = [];
        $dbman = $DB->get_manager();
        if ($dbman->table_exists('customcert_issues')) {
            $sql = "SELECT ci.id, ci.userid, ci.customcertid, ci.code, ci.emailed, ci.timecreated,
                           u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                           u.middlename, u.alternatename, u.email,
                           c.fullname as coursename,
                           cc.name as certificatename
                    FROM {customcert_issues} ci
                    JOIN {user} u ON u.id = ci.userid
                    JOIN {customcert} cc ON cc.id = ci.customcertid
                    LEFT JOIN {course} c ON c.id = cc.course
                    WHERE ci.userid $insql
                    ORDER BY u.lastname, u.firstname, ci.timecreated DESC";

            $customcertificates = $DB->get_records_sql($sql, $params);
        }

        // Get mod_certificate certificates for all cohort members.
        $modcertificates = [];
        if ($dbman->table_exists('certificate_issues')) {
            $sql = "SELECT ci.id, ci.userid, ci.certificateid, ci.code, ci.timecreated,
                           u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                           u.middlename, u.alternatename, u.email,
                           c.fullname as coursename,
                           cert.name as certificatename
                    FROM {certificate_issues} ci
                    JOIN {user} u ON u.id = ci.userid
                    JOIN {certificate} cert ON cert.id = ci.certificateid
                    LEFT JOIN {course} c ON c.id = cert.course
                    WHERE ci.userid $insql
                    ORDER BY u.lastname, u.firstname, ci.timecreated DESC";

            $modcertificates = $DB->get_records_sql($sql, $params);
        }

        // Get all mod_simplecertificate certificates for these users.
        $simplecertificates = [];
        if ($dbman->table_exists('simplecertificate_issues')) {
            $sql = "SELECT si.id, si.certificateid, si.userid, si.certificatename, si.code,
                           si.timecreated, si.timedeleted, si.haschange, si.pathnamehash, si.coursename,
                           u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                           u.middlename, u.alternatename, u.email,
                           c.fullname as coursename_full
                    FROM {simplecertificate_issues} si
                    JOIN {user} u ON u.id = si.userid
                    LEFT JOIN {course} c ON c.shortname = si.coursename OR c.fullname = si.coursename
                    WHERE si.userid $insql AND (si.timedeleted IS NULL OR si.timedeleted = 0)
                    ORDER BY u.lastname, u.firstname, si.timecreated";

            $simplecertificates = $DB->get_records_sql($sql, $params);
        }

        // Get all mod_certificatebeautiful certificates for these users.
        $certificatebeautifulcertificates = [];
        if ($dbman->table_exists('certificatebeautiful_issue')) {
            $sql = "SELECT cbi.id, cbi.userid, cbi.cmid, cbi.certificatebeautifulid, cbi.code,
                           cbi.version, cbi.timecreated,
                           u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                           u.middlename, u.alternatename, u.email,
                           c.fullname as coursename,
                           cb.name as certificatename
                    FROM {certificatebeautiful_issue} cbi
                    JOIN {user} u ON u.id = cbi.userid
                    LEFT JOIN {certificatebeautiful} cb ON cb.id = cbi.certificatebeautifulid
                    LEFT JOIN {course_modules} cm ON cm.id = cbi.cmid
                    LEFT JOIN {course} c ON c.id = cm.course
                    WHERE cbi.userid $insql
                    ORDER BY u.lastname, u.firstname, cbi.timecreated";

            $certificatebeautifulcertificates = $DB->get_records_sql($sql, $params);
        }

        if (empty($toolcertificates) &&
            empty($customcertificates) &&
            empty($modcertificates) &&
            empty($simplecertificates) &&
            empty($certificatebeautifulcertificates)) {
            throw new moodle_exception('nocertificatescohort', 'block_download_certificates');
        }

        // Create ZIP file.
        $tempdir = make_temp_directory('block_download_certificates');
        $zipfilename = 'cohort_' . clean_filename($cohort->name) . '_certificates_' . date('Y-m-d_H-i-s') . '.zip';
        $zippath = $tempdir . '/' . $zipfilename;

        // IMPORTANT: Supprimer le fichier ZIP existant s'il existe pour éviter les mélanges.
        if (file_exists($zippath)) {
            unlink($zippath);
        }

        $zip = new ZipArchive();
        if ($zip->open($zippath, ZipArchive::CREATE) !== true) {
            throw new moodle_exception('cannotcreatezipfile', 'block_download_certificates');
        }

        $filesadded = 0;
        $errors = [];

        // Process tool_certificate certificates.
        foreach ($toolcertificates as $cert) {
            try {
                // Get certificate file content.
                $filecontent = $this->get_certificate_file_content($cert->timecreated, $cert->code);

                if ($filecontent === false) {
                    // Try HTTP download.
                    $fileurl = $this->get_certificate_download_url($cert->timecreated, $cert->code);
                    $filecontent = $this->download_file_via_http($fileurl);
                }

                if ($filecontent !== false) {
                    $filename = 'tool_certificate/' . $this->generate_certificate_filename($cert);
                    $zip->addFromString($filename, $filecontent);
                    $filesadded++;
                } else {
                    $errors[] = 'Failed to download tool_certificate: ' . $cert->code;
                }
            } catch (Exception $e) {
                // Continue with other certificates if one fails.
                $errors[] = 'Error with tool_certificate ' . $cert->code . ': ' . $e->getMessage();
                continue;
            }
        }

        // Process customcert certificates.
        foreach ($customcertificates as $cert) {
            try {
                // Get template info for this certificate.
                $sql = "SELECT ct.id as templateid, ct.name as templatename, ct.contextid
                        FROM {customcert} cc
                        JOIN {customcert_templates} ct ON ct.id = cc.templateid
                        WHERE cc.id = :certificateid";

                $templateinfo = $DB->get_record_sql($sql, ['certificateid' => $cert->customcertid]);

                if ($templateinfo) {
                    // Create template instance and generate PDF.
                    $templatedata = new \stdClass();
                    $templatedata->id = $templateinfo->templateid;
                    $templatedata->name = $templateinfo->templatename;
                    $templatedata->contextid = $templateinfo->contextid;

                    $template = new \mod_customcert\template($templatedata);
                    $filecontent = $template->generate_pdf(false, $cert->userid, true);

                    if ($filecontent !== false && !empty($filecontent)) {
                        $filename = 'customcert/' . $this->generate_customcert_filename($cert);
                        $zip->addFromString($filename, $filecontent);
                        $filesadded++;
                    } else {
                        $errors[] = 'Failed to generate customcert PDF for user: ' . $cert->userid;
                    }
                } else {
                    $errors[] = 'Template not found for customcert: ' . $cert->customcertid;
                }
            } catch (Exception $e) {
                // Continue with other certificates if one fails.
                $errors[] = 'Error with customcert ' . $cert->id . ': ' . $e->getMessage();
                continue;
            }
        }

        // Process mod_certificate certificates.
        foreach ($modcertificates as $modcert) {
            try {
                // For mod_certificate, use the URL download method to get content.
                $filecontent = $this->get_mod_certificate_content_via_url($modcert->certificateid,
                                                                            $modcert->coursename,
                                                                            $modcert->certificatename,
                                                                            $modcert->userid);

                if ($filecontent !== false && !empty($filecontent)) {
                    $filename = 'mod_certificate/' . $this->generate_mod_certificate_filename($modcert);
                    $zip->addFromString($filename, $filecontent);
                    $filesadded++;
                } else {
                    $errors[] = 'Failed to download mod_certificate PDF for user: ' . $modcert->userid;
                }
            } catch (Exception $e) {
                $errors[] = 'Error with mod_certificate ' . $modcert->id . ': ' . $e->getMessage();
                continue;
            }
        }

        // Process mod_simplecertificate certificates.
        foreach ($simplecertificates as $simplecert) {
            try {
                // For mod_simplecertificate, use the content via URL method like mod_certificate.
                $filecontent = $this->get_simplecertificate_content_via_url($simplecert->code);

                if ($filecontent !== false && !empty($filecontent)) {
                    $filename = 'mod_simplecertificate/' . $this->generate_simplecertificate_filename($simplecert);
                    $zip->addFromString($filename, $filecontent);
                    $filesadded++;
                } else {
                    $errors[] = 'Failed to download mod_simplecertificate PDF for user: ' .
                                $simplecert->userid . ' (code: ' . $simplecert->code . ')';
                }
            } catch (Exception $e) {
                $errors[] = 'Error with mod_simplecertificate ' . $simplecert->id . ': ' . $e->getMessage();
                continue;
            }
        }

        // Process mod_certificatebeautiful certificates.
        foreach ($certificatebeautifulcertificates as $beautifulcert) {
            try {
                // For mod_certificatebeautiful, use the content via URL method.
                $filecontent = $this->get_certificatebeautiful_content_via_url($beautifulcert->code);

                if ($filecontent !== false && !empty($filecontent)) {
                    $filename = 'mod_certificatebeautiful/' . $this->generate_certificatebeautiful_filename($beautifulcert);
                    $zip->addFromString($filename, $filecontent);
                    $filesadded++;
                } else {
                    $errors[] = 'Failed to download mod_certificatebeautiful PDF for user: ' .
                                $beautifulcert->userid . ' (code: ' . $beautifulcert->code . ')';
                }
            } catch (Exception $e) {
                $errors[] = 'Error with mod_certificatebeautiful ' . $beautifulcert->id . ': ' . $e->getMessage();
                continue;
            }
        }

        // Add error log if needed.
        if (!empty($errors)) {
            $errorlog = "Download errors:\n" . implode("\n", $errors);
            $zip->addFromString('download_errors.txt', $errorlog);
        }

        $zip->close();

        if ($filesadded === 0) {
            unlink($zippath);
            throw new moodle_exception('novalidcertificatescohort', 'block_download_certificates');
        }

        // Send ZIP file.
        $this->send_zip_file($zippath, $zipfilename);
        unlink($zippath);
    }

    /**
     * Get available cohorts with certificate counts.
     *
     * @return array Array of cohorts with certificate counts
     */
    public function get_cohorts_with_certificates() {
        global $DB;

        // Get all cohorts.
        $cohorts = $DB->get_records('cohort', null, 'name ASC', 'id, name, description');

        $cohortsdata = [];
        foreach ($cohorts as $cohort) {
            // Get members of this cohort.
            $membercount = $DB->count_records('cohort_members', ['cohortid' => $cohort->id]);

            if ($membercount > 0) {
                // Get tool_certificate count for cohort members.
                $sql = "SELECT COUNT(DISTINCT tci.id)
                        FROM {tool_certificate_issues} tci
                        JOIN {cohort_members} cm ON cm.userid = tci.userid
                        WHERE cm.cohortid = :cohortid";

                $toolcertcount = $DB->count_records_sql($sql, ['cohortid' => $cohort->id]);

                // Get customcert count for cohort members.
                $customcertcount = 0;
                $dbman = $DB->get_manager();
                if ($dbman->table_exists('customcert_issues')) {
                    $sql = "SELECT COUNT(DISTINCT ci.id)
                            FROM {customcert_issues} ci
                            JOIN {cohort_members} cm ON cm.userid = ci.userid
                            WHERE cm.cohortid = :cohortid";

                    $customcertcount = $DB->count_records_sql($sql, ['cohortid' => $cohort->id]);
                }

                // Get mod_certificate count for cohort members.
                $modcertcount = 0;
                if ($dbman->table_exists('certificate_issues')) {
                    $sql = "SELECT COUNT(DISTINCT ci.id)
                            FROM {certificate_issues} ci
                            JOIN {cohort_members} cm ON cm.userid = ci.userid
                            WHERE cm.cohortid = :cohortid";

                    $modcertcount = $DB->count_records_sql($sql, ['cohortid' => $cohort->id]);
                }

                // Get mod_simplecertificate count for cohort members.
                $simplecertcount = 0;
                if ($dbman->table_exists('simplecertificate_issues')) {
                    $sql = "SELECT COUNT(DISTINCT si.id)
                            FROM {simplecertificate_issues} si
                            JOIN {cohort_members} cm ON cm.userid = si.userid
                            WHERE cm.cohortid = :cohortid
                            AND (si.timedeleted IS NULL OR si.timedeleted = 0)";

                    $simplecertcount = $DB->count_records_sql($sql, ['cohortid' => $cohort->id]);
                }

                // Get mod_certificatebeautiful count for cohort members.
                $certificatebeautifulcount = 0;
                if ($dbman->table_exists('certificatebeautiful_issue')) {
                    $sql = "SELECT COUNT(DISTINCT cbi.id)
                            FROM {certificatebeautiful_issue} cbi
                            JOIN {cohort_members} cm ON cm.userid = cbi.userid
                            WHERE cm.cohortid = :cohortid";

                    $certificatebeautifulcount = $DB->count_records_sql($sql, ['cohortid' => $cohort->id]);
                }

                $totalcertcount = $toolcertcount + $customcertcount + $modcertcount + $simplecertcount + $certificatebeautifulcount;

                if ($totalcertcount > 0) {
                    $cohortsdata[] = [
                        'id' => $cohort->id,
                        'name' => $cohort->name,
                        'description' => $cohort->description,
                        'member_count' => $membercount,
                        'certificate_count' => $totalcertcount,
                    ];
                }
            }
        }

        return $cohortsdata;
    }

    /**
     * Get tool_certificate issues.
     *
     * @return array
     */
    private function get_tool_certificate_issues() {
        global $DB;

        // Check if tool_certificate tables exist.
        $dbman = $DB->get_manager();
        if (!$dbman->table_exists('tool_certificate_issues')) {
            return [];
        }

        $sql = "SELECT tci.id, tci.userid, tci.templateid, tci.code, tci.emailed,
                       tci.timecreated, tci.expires, tci.data, tci.component, tci.courseid,
                       u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                       u.middlename, u.alternatename, u.email,
                       c.fullname as coursename,
                       tc.name as templatename
                FROM {tool_certificate_issues} tci
                JOIN {user} u ON u.id = tci.userid
                LEFT JOIN {course} c ON c.id = tci.courseid
                LEFT JOIN {tool_certificate_templates} tc ON tc.id = tci.templateid
                ORDER BY tci.timecreated DESC";

        return $DB->get_records_sql($sql);
    }

    /**
     * Get customcert issues.
     *
     * @return array
     */
    private function get_customcert_issues() {
        global $DB;

        // Check if customcert tables exist.
        $dbman = $DB->get_manager();
        if (!$dbman->table_exists('customcert_issues')) {
            return [];
        }

        $sql = "SELECT ci.id, ci.userid, ci.customcertid, ci.code, ci.emailed, ci.timecreated,
                       u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                       u.middlename, u.alternatename, u.email,
                       c.fullname as coursename,
                       cc.name as certificatename
                FROM {customcert_issues} ci
                JOIN {user} u ON u.id = ci.userid
                JOIN {customcert} cc ON cc.id = ci.customcertid
                LEFT JOIN {course} c ON c.id = cc.course
                ORDER BY ci.timecreated DESC";

        $results = $DB->get_records_sql($sql);

        return $results;
    }

    /**
     * Get mod_certificate issues.
     *
     * @return array
     */
    private function get_mod_certificate_issues() {
        global $DB;

        // Check if certificate tables exist.
        $dbman = $DB->get_manager();
        if (!$dbman->table_exists('certificate_issues')) {
            return [];
        }

        $sql = "SELECT ci.id, ci.userid, ci.certificateid, ci.code, ci.timecreated,
                       u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                       u.middlename, u.alternatename, u.email,
                       c.fullname as coursename,
                       cert.name as certificatename
                FROM {certificate_issues} ci
                JOIN {user} u ON u.id = ci.userid
                JOIN {certificate} cert ON cert.id = ci.certificateid
                LEFT JOIN {course} c ON c.id = cert.course
                ORDER BY ci.timecreated DESC";

        $results = $DB->get_records_sql($sql);

        return $results;
    }

    /**
     * Get mod_simplecertificate issues.
     *
     * @return array
     */
    private function get_simplecertificate_issues() {
        global $DB;

        // Check if simplecertificate tables exist.
        $dbman = $DB->get_manager();
        if (!$dbman->table_exists('simplecertificate_issues')) {
            return [];
        }

        $sql = "SELECT si.id, si.certificateid, si.userid, si.certificatename, si.code,
                       si.timecreated, si.timedeleted, si.haschange, si.pathnamehash, si.coursename,
                       u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                       u.middlename, u.alternatename, u.email,
                       c.fullname as coursename_full
                FROM {simplecertificate_issues} si
                JOIN {user} u ON u.id = si.userid
                LEFT JOIN {course} c ON c.shortname = si.coursename OR c.fullname = si.coursename
                WHERE si.timedeleted IS NULL OR si.timedeleted = 0
                ORDER BY si.timecreated DESC";

        $results = $DB->get_records_sql($sql);

        return $results;
    }

    /**
     * Get mod_certificatebeautiful issues.
     *
     * @return array
     */
    private function get_certificatebeautiful_issues() {
        global $DB;

        // Check if certificatebeautiful tables exist.
        $dbman = $DB->get_manager();
        if (!$dbman->table_exists('certificatebeautiful_issue')) {
            return [];
        }

        $sql = "SELECT cbi.*,
                       u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                       u.middlename, u.alternatename, u.email,
                       c.fullname as coursename,
                       cb.name as certificatename
                FROM {certificatebeautiful_issue} cbi
                JOIN {user} u ON u.id = cbi.userid
                LEFT JOIN {certificatebeautiful} cb ON cb.id = cbi.certificatebeautifulid
                LEFT JOIN {course_modules} cm ON cm.id = cbi.cmid
                LEFT JOIN {course} c ON c.id = cm.course
                ORDER BY cbi.timecreated DESC";

        $results = $DB->get_records_sql($sql);

        return $results;
    }

    /**
     * Download a single customcert certificate file using internal API.
     *
     * @param int $userid User ID
     * @param int $certificateid Certificate ID
     */
    public function download_single_customcert($userid, $certificateid) {
        global $DB, $CFG, $USER, $SESSION;

        // Check permissions.
        $context = context_system::instance();
        require_capability('block/download_certificates:manage', $context);

        // Verify the certificate issue exists.
        $issue = $DB->get_record('customcert_issues',
            ['userid' => $userid, 'customcertid' => $certificateid],
            '*', IGNORE_MISSING);

        if (!$issue) {
            throw new moodle_exception('certificatenotfound', 'block_download_certificates');
        }

        // Get certificate and template info.
        $sql = "SELECT ci.*, u.firstname, u.lastname, cc.name as certificatename, c.fullname as coursename,
                       ct.id as templateid, ct.name as templatename, ct.contextid
                FROM {customcert_issues} ci
                JOIN {user} u ON u.id = ci.userid
                JOIN {customcert} cc ON cc.id = ci.customcertid
                LEFT JOIN {course} c ON c.id = cc.course
                JOIN {customcert_templates} ct ON ct.id = cc.templateid
                WHERE ci.userid = :userid AND ci.customcertid = :certificateid";

        $certificate = $DB->get_record_sql($sql, ['userid' => $userid, 'certificateid' => $certificateid]);

        if (!$certificate) {
            throw new moodle_exception('certificatenotfound', 'block_download_certificates');
        }

        try {
            // Use customcert's internal API to generate the PDF.
            $templatedata = new \stdClass();
            $templatedata->id = $certificate->templateid;
            $templatedata->name = $certificate->templatename;
            $templatedata->contextid = $certificate->contextid;

            // Create template instance.
            $template = new \mod_customcert\template($templatedata);

            // Generate PDF content using customcert's API.
            $filecontent = $template->generate_pdf(false, $userid, true);

            if ($filecontent === false || empty($filecontent)) {
                throw new moodle_exception('cannotdownloadcertificate', 'block_download_certificates');
            }

            // Generate filename.
            $filename = $this->generate_customcert_filename($certificate);

            // Send file to browser with download headers.
            $this->send_pdf_download($filecontent, $filename);

        } catch (Exception $e) {
            debugging('Error generating customcert PDF: ' . $e->getMessage());
            throw new moodle_exception('cannotdownloadcertificate', 'block_download_certificates');
        }
    }

    /**
     * Send PDF content as download.
     *
     * @param string $content PDF content
     * @param string $filename Filename
     * @return void
     */
    private function send_pdf_download($content, $filename) {
        // Clean any output buffers.
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Set headers for PDF download.
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($content));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        header('Expires: 0');

        // Output PDF content.
        echo $content;
        exit;
    }

    /**
     * Generate filename for customcert certificate.
     *
     * @param object $cert Certificate data
     * @return string Filename
     */
    private function generate_customcert_filename($cert) {
        global $DB;

        // Try to get user info if not already present.
        if (empty($cert->firstname) || empty($cert->lastname)) {
            $userid = !empty($cert->userid) ? $cert->userid : null;
            if ($userid) {
                $user = $DB->get_record('user', ['id' => $userid], 'firstname, lastname');
                if ($user) {
                    $cert->firstname = $user->firstname;
                    $cert->lastname = $user->lastname;
                }
            }
        }

        // Build username safely from available fields - use consistent format.
        $firstname = !empty($cert->firstname) ? clean_filename($cert->firstname) : 'Unknown';
        $lastname = !empty($cert->lastname) ? clean_filename($cert->lastname) : 'User';
        $username = $firstname . '_' . $lastname;

        $certname = !empty($cert->certificatename) ? clean_filename($cert->certificatename) : 'certificate';
        $code = !empty($cert->code) ? $cert->code : $cert->id;

        // Format: [nomapprenant]_[nomcertificat]_[codeducertificat].pdf.
        return $username . '_' . $certname . '_' . $code . '.pdf';
    }

    /**
     * Generate filename for mod_certificate certificate.
     *
     * @param object $cert Certificate data
     * @return string Filename
     */
    private function generate_mod_certificate_filename($cert) {
        global $DB;

        // Try to get user info if not already present.
        if (empty($cert->firstname) || empty($cert->lastname)) {
            $userid = !empty($cert->userid) ? $cert->userid : null;
            if ($userid) {
                $user = $DB->get_record('user', ['id' => $userid], 'firstname, lastname');
                if ($user) {
                    $cert->firstname = $user->firstname;
                    $cert->lastname = $user->lastname;
                }
            }
        }

        // Build username safely from available fields - use consistent format.
        $firstname = !empty($cert->firstname) ? clean_filename($cert->firstname) : 'Unknown';
        $lastname = !empty($cert->lastname) ? clean_filename($cert->lastname) : 'User';
        $username = $firstname . '_' . $lastname;

        $certname = !empty($cert->certificatename) ? clean_filename($cert->certificatename) : 'certificate';
        $code = !empty($cert->code) ? $cert->code : $cert->id;

        // Format: [nomapprenant]_[nomcertificat]_[codeducertificat].pdf.
        return $username . '_' . $certname . '_' . $code . '.pdf';
    }

    /**
     * Generate filename for mod_simplecertificate certificate.
     *
     * @param object $cert Certificate data
     * @return string Filename
     */
    private function generate_simplecertificate_filename($cert) {
        global $DB;

        // Try to get user info if not already present.
        if (empty($cert->firstname) || empty($cert->lastname)) {
            $userid = !empty($cert->userid) ? $cert->userid : null;
            if ($userid) {
                $user = $DB->get_record('user', ['id' => $userid], 'firstname, lastname');
                if ($user) {
                    $cert->firstname = $user->firstname;
                    $cert->lastname = $user->lastname;
                }
            }
        }

        // Build username safely from available fields - use consistent format.
        $firstname = !empty($cert->firstname) ? clean_filename($cert->firstname) : 'Unknown';
        $lastname = !empty($cert->lastname) ? clean_filename($cert->lastname) : 'User';
        $username = $firstname . '_' . $lastname;

        $certname = !empty($cert->certificatename) ? clean_filename($cert->certificatename) : 'simplecertificate';
        $code = !empty($cert->code) ? $cert->code : $cert->id;

        // Format: [nomapprenant]_[nomcertificat]_[codeducertificat].pdf.
        return $username . '_' . $certname . '_' . $code . '.pdf';
    }

    /**
     * Generate filename for mod_certificatebeautiful certificate.
     *
     * @param object $cert Certificate data
     * @return string Filename
     */
    private function generate_certificatebeautiful_filename($cert) {
        global $DB;

        // Try to get user info if not already present.
        if (empty($cert->firstname) || empty($cert->lastname)) {
            $userid = !empty($cert->userid) ? $cert->userid : null;
            if ($userid) {
                $user = $DB->get_record('user', ['id' => $userid], 'firstname, lastname');
                if ($user) {
                    $cert->firstname = $user->firstname;
                    $cert->lastname = $user->lastname;
                }
            }
        }

        // Build username safely from available fields - use consistent format.
        $firstname = !empty($cert->firstname) ? clean_filename($cert->firstname) : 'Unknown';
        $lastname = !empty($cert->lastname) ? clean_filename($cert->lastname) : 'User';
        $username = $firstname . '_' . $lastname;

        $certname = !empty($cert->certificatename) ? clean_filename($cert->certificatename) : 'certificatebeautiful';
        $code = !empty($cert->code) ? $cert->code : $cert->id;

        // Format: [nomapprenant]_[nomcertificat]_[codeducertificat].pdf.
        return $username . '_' . $certname . '_' . $code . '.pdf';
    }

    /**
     * Get list of users that have certificates.
     *
     * @return array Array of users with certificate counts
     */
    public function get_users_with_certificates() {
        global $DB;

        // Check capabilities.
        $context = context_system::instance();
        require_capability('block/download_certificates:manage', $context);

        // Get users with tool_certificate certificates.
        $sql = "SELECT u.id, u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                       u.middlename, u.alternatename, u.email, COUNT(tci.id) as tool_certificate_count
                FROM {user} u
                INNER JOIN {tool_certificate_issues} tci ON u.id = tci.userid
                WHERE u.deleted = 0
                GROUP BY u.id, u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                         u.middlename, u.alternatename, u.email";

        $toolcertusers = $DB->get_records_sql($sql);

        // Get users with customcert certificates.
        $customcertusers = [];
        $dbman = $DB->get_manager();
        if ($dbman->table_exists('customcert_issues')) {
            $sql = "SELECT u.id, u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                           u.middlename, u.alternatename, u.email, COUNT(ci.id) as customcert_count
                    FROM {user} u
                    INNER JOIN {customcert_issues} ci ON u.id = ci.userid
                    WHERE u.deleted = 0
                    GROUP BY u.id, u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                             u.middlename, u.alternatename, u.email";

            $customcertusers = $DB->get_records_sql($sql);
        }

        // Get users with mod_certificate certificates.
        $modcertusers = [];
        if ($dbman->table_exists('certificate_issues')) {
            $sql = "SELECT u.id, u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                           u.middlename, u.alternatename, u.email, COUNT(ci.id) as mod_certificate_count
                    FROM {user} u
                    INNER JOIN {certificate_issues} ci ON u.id = ci.userid
                    WHERE u.deleted = 0
                    GROUP BY u.id, u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                             u.middlename, u.alternatename, u.email";

            $modcertusers = $DB->get_records_sql($sql);
        }

        // Get users with simplecertificate certificates.
        $simplecertusers = [];
        if ($dbman->table_exists('simplecertificate_issues')) {
            $sql = "SELECT u.id, u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                           u.middlename, u.alternatename, u.email, COUNT(si.id) as simplecertificate_count
                    FROM {user} u
                    INNER JOIN {simplecertificate_issues} si ON u.id = si.userid
                    WHERE u.deleted = 0 AND (si.timedeleted IS NULL OR si.timedeleted = 0)
                    GROUP BY u.id, u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                             u.middlename, u.alternatename, u.email";

            $simplecertusers = $DB->get_records_sql($sql);
        }

        // Get users with mod_certificatebeautiful certificates.
        $certificatebeautifulusers = [];
        if ($dbman->table_exists('certificatebeautiful_issue')) {
            $sql = "SELECT u.id, u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                           u.middlename, u.alternatename, u.email, COUNT(cbi.id) as certificatebeautiful_count
                    FROM {user} u
                    INNER JOIN {certificatebeautiful_issue} cbi ON u.id = cbi.userid
                    WHERE u.deleted = 0
                    GROUP BY u.id, u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                             u.middlename, u.alternatename, u.email";

            $certificatebeautifulusers = $DB->get_records_sql($sql);
        }

        // Merge and combine counts.
        $allusers = [];
        foreach ($toolcertusers as $user) {
            $allusers[$user->id] = [
                'id' => $user->id,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'fullname' => fullname($user),
                'email' => $user->email,
                'tool_certificate_count' => $user->tool_certificate_count,
                'customcert_count' => 0,
                'mod_certificate_count' => 0,
                'simplecertificate_count' => 0,
                'certificatebeautiful_count' => 0,
            ];
        }

        foreach ($customcertusers as $user) {
            if (isset($allusers[$user->id])) {
                $allusers[$user->id]['customcert_count'] = $user->customcert_count;
            } else {
                $allusers[$user->id] = [
                    'id' => $user->id,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'fullname' => fullname($user),
                    'email' => $user->email,
                    'tool_certificate_count' => 0,
                    'customcert_count' => $user->customcert_count,
                    'mod_certificate_count' => 0,
                    'simplecertificate_count' => 0,
                    'certificatebeautiful_count' => 0,
                ];
            }
        }

        foreach ($modcertusers as $user) {
            if (isset($allusers[$user->id])) {
                $allusers[$user->id]['mod_certificate_count'] = $user->mod_certificate_count;
            } else {
                $allusers[$user->id] = [
                    'id' => $user->id,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'fullname' => fullname($user),
                    'email' => $user->email,
                    'tool_certificate_count' => 0,
                    'customcert_count' => 0,
                    'mod_certificate_count' => $user->mod_certificate_count,
                    'simplecertificate_count' => 0,
                    'certificatebeautiful_count' => 0,
                ];
            }
        }

        foreach ($simplecertusers as $user) {
            if (isset($allusers[$user->id])) {
                $allusers[$user->id]['simplecertificate_count'] = $user->simplecertificate_count;
            } else {
                $allusers[$user->id] = [
                    'id' => $user->id,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'fullname' => fullname($user),
                    'email' => $user->email,
                    'tool_certificate_count' => 0,
                    'customcert_count' => 0,
                    'mod_certificate_count' => 0,
                    'simplecertificate_count' => $user->simplecertificate_count,
                    'certificatebeautiful_count' => 0,
                ];
            }
        }

        foreach ($certificatebeautifulusers as $user) {
            if (isset($allusers[$user->id])) {
                $allusers[$user->id]['certificatebeautiful_count'] = $user->certificatebeautiful_count;
            } else {
                $allusers[$user->id] = [
                    'id' => $user->id,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'fullname' => fullname($user),
                    'email' => $user->email,
                    'tool_certificate_count' => 0,
                    'customcert_count' => 0,
                    'mod_certificate_count' => 0,
                    'simplecertificate_count' => 0,
                    'certificatebeautiful_count' => $user->certificatebeautiful_count,
                ];
            }
        }

        // Calculate total count and format for return.
        $userlist = [];
        foreach ($allusers as $user) {
            $totalcount = $user['tool_certificate_count'] + $user['customcert_count'] +
                            $user['mod_certificate_count'] + $user['simplecertificate_count'] + $user['certificatebeautiful_count'];
            $userlist[] = [
                'id' => $user['id'],
                'fullname' => $user['fullname'],
                'email' => $user['email'],
                'certificate_count' => $totalcount,
            ];
        }

        // Sort by user fullname.
        usort($userlist, function($a, $b) {
            return strcmp($a['fullname'], $b['fullname']);
        });

        return $userlist;
    }

}
