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
 * Download specific certificate.
 *
 * @package   block_download_certificates
 * @copyright 2015 Manieer Chhettri  (Original Idea for Moodle 2.x and 3.x)
 * @copyright 2025 E-learning Touch' contact@elearningtouch.com (Maintainer)
 * @author    Thomas Clément 222384061+ClementThomasELT@users.noreply.github.com (Coder)
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/blocks/download_certificates/classes/controller.php');

require_login();

// Check permissions.
$context = context_system::instance();
require_capability('block/download_certificates:manage', $context);

// Get parameters.
$type = optional_param('type', 'tool_certificate', PARAM_ALPHANUMEXT);
$sesskey = required_param('sesskey', PARAM_RAW);

// Confirm sesskey.
confirm_sesskey($sesskey);

$PAGE->set_context($context);
$PAGE->set_url('/blocks/download_certificates/download_precise.php');

try {
    $controller = new block_download_certificates_controller();

    if ($type === 'customcert') {
        // For customcert, download PDF via URL and serve it.
        $userid = required_param('userid', PARAM_INT);
        $certificateid = required_param('certificateid', PARAM_INT);

        $controller->download_single_customcert($userid, $certificateid);

    } else if ($type === 'mod_certificate') {
        // For mod_certificate, use the new method.
        $userid = required_param('userid', PARAM_INT);
        $certificateid = required_param('certificateid', PARAM_INT);

        $controller->download_single_mod_certificate($userid, $certificateid);

    } else if ($type === 'mod_simplecertificate') {
        // For mod_simplecertificate, use the API method.
        $userid = required_param('userid', PARAM_INT);
        $certificateid = required_param('certificateid', PARAM_INT);
        $code = required_param('code', PARAM_ALPHANUMEXT);

        $controller->download_single_simplecertificate($userid, $certificateid, $code);

    } else if ($type === 'mod_certificatebeautiful') {
        // For mod_certificatebeautiful, use the API method.
        $userid = required_param('userid', PARAM_INT);
        $certificateid = required_param('certificateid', PARAM_INT);
        $code = required_param('code', PARAM_ALPHANUMEXT);

        $controller->download_single_certificatebeautiful($userid, $certificateid, $code);

    } else {
        // For tool_certificate, use the existing method.
        $timecreated = required_param('timecreated', PARAM_INT);
        $code = required_param('code', PARAM_ALPHANUMEXT);

        $controller->download_single_certificate($timecreated, $code);
    }

} catch (Exception $e) {
    debugging('Certificate download error: ' . $e->getMessage());

    // Redirect back to the main page with error.
    $returnurl = new moodle_url('/blocks/download_certificates/index.php');
    redirect($returnurl, get_string('cannotdownloadcertificate', 'block_download_certificates'), null,
                                    \core\output\notification::NOTIFY_ERROR);
}
