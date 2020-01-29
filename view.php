<?php

require_once(dirname(__FILE__) . '/locallib.php');

$id = required_param('id', PARAM_INT);

// $cm = get_coursemodule_from_id('videostream', $id, 0, false, MUST_EXIST);
// $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
// $context = context_module::instance($cm->id);
// $videostream = new videostream($context, $cm, $course);

// require_login($course, true, $cm);
// require_capability('mod/videostream:view', $context);

// $PAGE->set_pagelayout('incourse');

$url = new moodle_url('/mod/videostream/view.php', array('videoid' => $id));
$PAGE->set_url('/mod/videostream/view.php', array('videoid' =>$id));


//echo $renderer->video_page($videostream);
//echo sesskey();
