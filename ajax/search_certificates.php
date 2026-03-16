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
 * AJAX endpoint for searching and paginating certificates.
 *
 * @package   block_download_certificates
 * @copyright 2025 E-learning Touch' contact@elearningtouch.com (Maintainer)
 * @author    Thomas Clément 222384061+ClementThomasELT@users.noreply.github.com (Coder)
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/blocks/download_certificates/classes/certificate_query.php');

require_login();
require_sesskey();

$context = context_system::instance();
require_capability('block/download_certificates:manage', $context);

// Parameters.
$search  = optional_param('search', '', PARAM_TEXT);
$page    = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 50, PARAM_INT);
$sort    = optional_param('sort', 'timecreated', PARAM_ALPHANUMEXT);
$order   = optional_param('order', 'DESC', PARAM_ALPHA);

// Sanitize.
$perpage = min(max($perpage, 10), 200);
$page = max($page, 0);
$offset = $page * $perpage;

try {
    $query = new block_download_certificates_query();

    // Get total count (with search filter).
    $total = $query->count_search_results($search);

    // Get paginated results.
    $records = $query->search_all_certificates($search, $offset, $perpage, $sort, $order);

    // Format results for JSON.
    $data = [];
    foreach ($records as $record) {
        // Build download URL depending on cert type.
        $downloadparams = [
            'type' => $record->cert_type,
            'sesskey' => sesskey(),
        ];
        if ($record->cert_type === 'tool_certificate') {
            $downloadparams['timecreated'] = $record->timecreated;
            $downloadparams['code'] = $record->code;
        } else {
            $downloadparams['userid'] = $record->userid;
            $downloadparams['certificateid'] = $record->original_id ?? $record->id;
            if (!empty($record->code)) {
                $downloadparams['code'] = $record->code;
            }
        }
        $downloadurl = new moodle_url('/blocks/download_certificates/download_precise.php', $downloadparams);

        $data[] = [
            'id' => $record->id,
            'cert_type' => $record->cert_type,
            'username' => fullname($record),
            'email' => $record->email,
            'coursename' => $record->coursename ?: get_string('unknown', 'block_download_certificates'),
            'templatename' => $record->templatename ?: get_string('unknown', 'block_download_certificates'),
            'code' => $record->code,
            'timecreated' => userdate($record->timecreated),
            'timecreated_raw' => $record->timecreated,
            'userid' => $record->userid,
            'download_url' => $downloadurl->out(false),
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $data,
        'total' => (int) $total,
        'page' => $page,
        'perpage' => $perpage,
        'pages' => ceil($total / $perpage),
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
    ]);
}
