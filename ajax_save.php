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
 * Prints a particular instance of videodirectory from block
 *
 * @package    block_videodirectory
 * @copyright  2020 Tovi Kurztag <tovi@openapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('AJAX_SCRIPT', true);
require(__DIR__ . '/../../config.php');

if (!isloggedin() || isguestuser()) {
     print_error('No permissions');
}

if ($id = optional_param('delete', null, PARAM_INT)) {
    $DB->delete_records('videostreambookmarks', ['id' => $id, 'userid' => $USER->id]);
    die('1');
}

$moduleid = optional_param('moduleid', null, PARAM_INT);
$videoid = required_param('videoid', PARAM_INT);
$bookmarkposition = required_param('bookmarkposition', PARAM_FLOAT);
$bookmarkname = required_param('bookmarkname', PARAM_RAW);
$bookmarkflag = required_param('bookmarkflag', PARAM_RAW);
$userid = $USER->id;
$object = compact('userid', 'bookmarkposition', 'bookmarkname', 'bookmarkflag', 'moduleid', 'videoid');
$id = $DB->insert_record('videostreambookmarks', $object);
echo json_encode($DB->get_record('videostreambookmarks', ['id' => $id]));
