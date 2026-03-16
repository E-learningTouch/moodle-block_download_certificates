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
 * AJAX endpoint to create an async certificate download task.
 *
 * @package   block_download_certificates
 * @copyright 2025 E-learning Touch' contact@elearningtouch.com (Maintainer)
 * @author    Thomas Clément 222384061+ClementThomasELT@users.noreply.github.com (Coder)
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/blocks/download_certificates/classes/certificate_retriever.php');
require_once($CFG->dirroot . '/blocks/download_certificates/classes/certificate_query.php');
require_once($CFG->dirroot . '/blocks/download_certificates/classes/certificate_packager.php');

require_login();
require_sesskey();

$context = context_system::instance();
require_capability('block/download_certificates:manage', $context);

$type = required_param('type', PARAM_ALPHANUMEXT);
$paramsraw = optional_param('params', '{}', PARAM_RAW);
$params = json_decode($paramsraw, true);

if (!is_array($params)) {
    $params = [];
}

// Validate type.
$validtypes = ['all', 'course', 'user', 'cohort', 'range'];
if (!in_array($type, $validtypes)) {
    echo json_encode(['success' => false, 'error' => 'Invalid download type']);
    die();
}

try {
    // Count certificates to set the total.
    $retriever = new block_download_certificates_retriever();
    $query = new block_download_certificates_query();
    $packager = new block_download_certificates_packager($retriever, $query);
    $total = $packager->count_certificates_for_type($type, $params);

    if ($total === 0) {
        echo json_encode(['success' => false, 'error' => get_string('nocertificates', 'block_download_certificates')]);
        die();
    }

    // Create task record.
    $taskrecord = new stdClass();
    $taskrecord->userid = $USER->id;
    $taskrecord->type = $type;
    $taskrecord->params = json_encode($params);
    $taskrecord->status = 'pending';
    $taskrecord->progress = 0;
    $taskrecord->total = $total;
    $taskrecord->batchoffset = 0;
    $taskrecord->timecreated = time();
    $taskrecord->timemodified = time();

    $taskid = $DB->insert_record('block_download_cert_tasks', $taskrecord);

    // Schedule the adhoc task.
    $adhoctask = new \block_download_certificates\task\generate_zip();
    $adhoctask->set_custom_data((object) ['taskid' => $taskid]);
    $adhoctask->set_userid($USER->id);
    \core\task\manager::queue_adhoc_task($adhoctask);

    echo json_encode([
        'success' => true,
        'taskid' => $taskid,
        'total' => $total,
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
    ]);
}
