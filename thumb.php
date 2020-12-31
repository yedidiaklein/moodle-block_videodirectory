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
 * Showing thumnail
 *
 * @package    local_video_directory
 * @copyright  2017 Yedidia Klein <yedidia@openapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once( __DIR__ . '/../../config.php');
require_login();
defined('MOODLE_INTERNAL') || die();
require_once('locallib.php');

$settings = get_settings();

if (!CLI_SCRIPT) {
    require_login();


    // Check if user have permissionss.
    $context = context_system::instance();
}

$id = required_param('id', PARAM_INT);
$second = optional_param('second', 0, PARAM_INT);
$mini = optional_param('mini', 0, PARAM_INT);
$dirs = get_directories();
$streamingdir = $dirs['converted'];

$video = $DB->get_record('local_video_directory', ['id' => $id]);
if ($video->filename != $id . '.mp4') {
    $filename = $video->filename;
} else {
    $filename = $id;
}
header("Content-type: image/png");
// Tami
if (!file_exists($streamingdir . $filename . ($second ? "-" . $second : '') . "-mini.png")
    && !file_exists($streamingdir . $filename . ($second ? "-" . $second : '') . ".png")
    && $video->filename && $video->convert_status == 7){
    readfile($CFG->wwwroot . '/local/video_directory/pix/headphonesThumb.jpg');
} // >
if ($mini) {
    readfile($streamingdir . $filename . ($second ? "-" . $second : '') . "-mini.png");
} else {
    readfile($streamingdir . $filename . ($second ? "-" . $second : '') . ".png");
}
