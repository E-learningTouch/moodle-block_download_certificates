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
 * Post installation and migration code for block_download_certificates.
 *
 * @package   block_download_certificates
 * @copyright 2015 Manieer Chhettri  (Original Idea for Moodle 2.x and 3.x)
 * @copyright 2025 E-learning Touch' contact@elearningtouch.com (Maintainer)
 * @author    Thomas Clément 222384061+ClementThomasELT@users.noreply.github.com (Coder)
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Post installation procedure.
 */
function xmldb_block_download_certificates_install() {
    global $DB;

    // Add the block to the default my page for all users.
    $defaultmypage = $DB->get_record('my_pages', [
        'userid' => null,
        'name' => '__default',
    ]);

    if ($defaultmypage) {
        // Check if the block is already added.
        $existingblock = $DB->get_record('block_instances', [
            'blockname' => 'download_certificat',
            'pagetypepattern' => 'my-index',
            'parentcontextid' => context_system::instance()->id,
        ]);

        if (!$existingblock) {
            // Add the block to the default my page.
            $blockinstance = new stdClass();
            $blockinstance->blockname = 'download_certificat';
            $blockinstance->parentcontextid = context_system::instance()->id;
            $blockinstance->showinsubcontexts = 0;
            $blockinstance->pagetypepattern = 'my-index';
            $blockinstance->subpagepattern = $defaultmypage->id;
            $blockinstance->defaultregion = 'content';
            $blockinstance->defaultweight = 1;
            $blockinstance->configdata = '';
            $blockinstance->timecreated = time();
            $blockinstance->timemodified = time();

            $DB->insert_record('block_instances', $blockinstance);
        }
    }

    return true;
}
