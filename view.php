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

require_once(__DIR__ . '/../../config.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once( __DIR__ . '/../../mod/videostream/renderer.php');
require_once(__DIR__ . '/../../mod/videostream/locallib.php');

$id = required_param('id', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);

global $DB, $OUTPUT, $PAGE, $CFG, $USER, $COURSE;
// TODO
// check that user belong to course


$sql1 = "SELECT id,fullname,shortname
FROM mdl_course
WHERE id=?";

// $sql = "SELECT c2.*
// from mdl_context c
// join mdl_block_instances bi on c.id=bi.parentcontextid
// join mdl_context c2 on c2.contextlevel=80 and c2.instanceid = bi.id
// and bi.blockname = 'videodirectory'";
// $context1 = $DB->get_record_sql($sql, null, $strictness = IGNORE_MULTIPLE);


$course = $DB->get_record_sql($sql1, [$courseid]);
$videoname = $DB->get_field('local_video_directory', 'orig_filename', ['id' => $id]);
$url = new moodle_url('/blocks/videodirectory/view.php', array('id' => $id, 'courseid' => $courseid));
$PAGE->set_url($url);
$PAGE->set_heading($course->fullname);
$PAGE->set_title($course->shortname.': '.$videoname);
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add($course->fullname, new moodle_url('/course/view.php?id='.$courseid));
$PAGE->navbar->add($videoname);

$PAGE->requires->css('/blocks/videodirectory/videojs-seek-buttons/videojs-seek-buttons.css');


require_login();

$_SESSION['videoid'] = $id;
$context = context_course::instance($courseid);
$event = \block_videodirectory\event\video_view::create(array(
    'objectid' => $context->$id,
    'contextid' => $context->id,
    ));
$event->trigger();

$output = '';
$output .= video($id, $courseid);
echo $OUTPUT->header();
echo $OUTPUT->heading($videoname);
echo $output;
echo $OUTPUT->footer();
