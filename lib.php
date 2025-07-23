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
 * Main library functions for block_download_certificates plugin.
 *
 * @package   block_download_certificates
 * @copyright 2015 Manieer Chhettri  (Original Idea for Moodle 2.x and 3.x)
 * @copyright 2025 E-learning Touch' contact@elearningtouch.com (Maintainer)
 * @author    Thomas Clément 222384061+ClementThomasELT@users.noreply.github.com (Coder)
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Generate and download certificate for a user.
 *
 * @param int $userid User ID
 * @param int $courseid Course ID
 * @return bool True if certificate was generated successfully
 */
function block_download_certificates_v2_generate_certificate($userid, $courseid) {
    global $DB, $CFG;

    // Check if plugin is enabled.
    if (!get_config('block_download_certificates', 'enable')) {
        return false;
    }

    // Verify user has completed the course.
    $completion = new completion_info(get_course($courseid));
    if (!$completion->is_course_complete($userid)) {
        return false;
    }

    // Generate certificate logic here.
    // This is a placeholder for the actual certificate generation.

    return true;
}

/**
 * Check if user can download certificate.
 *
 * @param int $userid User ID
 * @param int $courseid Course ID
 * @return bool True if user can download certificate
 */
function block_download_certificates_v2_can_download($userid, $courseid) {
    global $DB;

    // Check capability.
    $context = context_course::instance($courseid);
    if (!has_capability('block/download_certificates:view', $context, $userid)) {
        return false;
    }

    // Check if plugin is enabled.
    if (!get_config('block_download_certificates', 'enable')) {
        return false;
    }

    // Check course completion.
    $completion = new completion_info(get_course($courseid));
    return $completion->is_course_complete($userid);
}

/**
 * Add certificate download link to course completion.
 *
 * @param stdClass $course Course object
 * @return string HTML for certificate link
 */
function block_download_certificates_v2_get_download_link($course) {
    global $USER, $OUTPUT;

    if (!block_download_certificates_v2_can_download($USER->id, $course->id)) {
        return '';
    }

    $url = new moodle_url('/blocks/download_certificates/download.php', ['courseid' => $course->id]);
    return html_writer::link($url, get_string('downloadcertificate', 'block_download_certificates'),
                           ['class' => 'btn btn-primary']);
}
