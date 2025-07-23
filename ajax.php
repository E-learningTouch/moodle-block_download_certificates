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
 * AJAX handler for block_download_certificates plugin.
 *
 * @package   block_download_certificates
 * @copyright 2015 Manieer Chhettri  (Original Idea for Moodle 2.x and 3.x)
 * @copyright 2025 E-learning Touch' contact@elearningtouch.com (Maintainer)
 * @author    Thomas Clément 222384061+ClementThomasELT@users.noreply.github.com (Coder)
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/blocks/download_certificates/classes/controller.php');

require_login();
require_sesskey();

$action = required_param('action', PARAM_ALPHANUMEXT);

$context = context_system::instance();
$PAGE->set_context($context);

$controller = new block_download_certificates_controller();

header('Content-Type: application/json');

try {
    switch ($action) {
        case 'get_certificates_count':
            require_capability('block/download_certificates:manage', $context);
            $data = $controller->get_certificates_data();
            echo json_encode(['success' => true, 'count' => $data['total_count']]);
            break;

        case 'validate_certificates':
            require_capability('block/download_certificates:manage', $context);
            // Add validation logic here if needed.
            echo json_encode(['success' => true, 'message' => 'Validation completed']);
            break;

        default:
            throw new moodle_exception('invalidaction', 'block_download_certificates');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
