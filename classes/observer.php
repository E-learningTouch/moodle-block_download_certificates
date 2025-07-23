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
 * Course completion event observers for block_download_certificates plugin.
 *
 * @package   block_download_certificates
 * @copyright 2015 Manieer Chhettri  (Original Idea for Moodle 2.x and 3.x)
 * @copyright 2025 E-learning Touch' contact@elearningtouch.com (Maintainer)
 * @author    Thomas Clément 222384061+ClementThomasELT@users.noreply.github.com (Coder)
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Event observer for course completion.
 */
class block_download_certificates_observer {

    /**
     * Observer for course completion event.
     *
     * @param \core\event\course_completed $event
     */
    public static function course_completed(\core\event\course_completed $event) {
        global $DB;

        // Check if plugin is enabled.
        if (!get_config('block_download_certificates', 'enable')) {
            return;
        }

        $data = new stdClass();
        $data->userid = $event->userid;
        $data->courseid = $event->courseid;
        $data->timecompleted = $event->timecreated;

    }
}
