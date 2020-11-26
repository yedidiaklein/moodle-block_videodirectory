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
 * This class provides functionality for the videodirectory module.
 *
 * @package    block_videodirectory
 * @copyright  2020 Tovi Kurztag <tovi@openapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once( __DIR__ . '/../../config.php');

require_once($CFG->dirroot . '/local/video_directory/locallib.php');

function video($id , $courseid) {
    $output = "<div class='videostream'>";
    $config = get_config('videostream');

    if (($config->streaming == "symlink") || ($config->streaming == "php")) {
        $output .= get_video_source_elements_videojs($config->streaming, $id, $courseid);

    } else if ($config->streaming == "hls") {
        // Elements for video sources. (here we get the hls video).
        $output .= get_video_source_elements_hls($id, $courseid);
    } else {
        // Dash video.
        $output .= get_video_source_elements_dash($id, $courseid);
    }
    // Close video tag.
    $output .= html_writer::end_tag('video');
    // Close videostream div.
    $output .= "</div>";
    return $output;
}


function block_videodirectory_createHLS($videoid) {
    global $DB;

    $config = get_config('videostream');

    $id = $videoid;
    $streams = $DB->get_records("local_video_directory_multi", array("video_id" => $id));
    if ($streams) {
        foreach ($streams as $stream) {
                $files[] = $stream->filename;
        }
        $hls_streaming = $config->hls_base_url;
    } else {
        $files[] = local_video_directory_get_filename($id);
        $hls_streaming = $config->hlsingle_base_url;
    }

    $parts=array();
    foreach ($files as $file) {
            $parts[] = preg_split("/[_.]/", $file);
    }

    $hls_url = $hls_streaming . $parts[0][0];
    if ($streams) {
        $hls_url .= "_";

        foreach ($parts as $key => $value) {
            $hls_url .= "," . $value[1];
        }
    }
    $hls_url .= "," . ".mp4".$config->nginx_multi."/master.m3u8";

    return $hls_url;
}



function video_events($id, $courseid) {
    global $CFG, $DB;

    $context = context_course::instance($courseid);
    $sesskey = sesskey();
    $jsmediaevent = "<script language='JavaScript'>
        var v = document.getElementsByTagName('video')[0];

        v.addEventListener('seeked', function() { sendEvent('seeked'); }, true);
        v.addEventListener('play', function() { sendEvent('play'); }, true);
        v.addEventListener('stop', function() { sendEvent('stop'); }, true);
        v.addEventListener('pause', function() { sendEvent('pause'); }, true);
        v.addEventListener('ended', function() { sendEvent('ended'); }, true);
        v.addEventListener('ratechange', function() { sendEvent('ratechange'); }, true);

        function sendEvent(event) {
            console.log(event);
            require(['jquery'], function($) {
                $.post('" . $CFG->wwwroot . "/blocks/videodirectory/ajax.php',
                 {
                    videoid: " . $id . ",
                    contextid: ".$context->id .",
                    action: event,
                    sesskey: '" . $sesskey . "' } );
            });
        }

    </script>";
    return $jsmediaevent;
}

function get_bookmark_controls($videoid) {
    global $DB, $USER, $OUTPUT;
    $output = '';
    $bookmarks = $DB->get_records('videostreambookmarks', ['userid' => $USER->id, 'videoid' => $videoid]);
    $bookmarks = array_values(array_map(function ($a) {
        $a->bookmarkpositionvisible = gmdate("H:i:s", (int) $a->bookmarkposition);
        return $a;
    }, $bookmarks));
    $output .= $OUTPUT->render_from_template('block_videodirectory/bookmark_controls',
        ['bookmarks' => $bookmarks, 'videoid' => $videoid]);
    return $output;
}

function get_video_source_elements_dash($id, $courseid) {
        global $CFG;
        $width = '800px';
        $height = '500px';

        $output = '<video id=videostream class="video-js vjs-default-skin" data-setup=\'{}\'
                    style="position: relative !important; width: ' . $width . ' !important; height: ' . $height . ' !important;"
                    controls>
                    <track label="English" kind="subtitles" srclang="en"
                    src=
                    "' . $CFG->wwwroot . '/local/video_directory/play.php?video_id=' . $id . '" default>
                    </video>
                        <script src="https://vjs.zencdn.net/6.6.3/video.js"></script>
                        <script src="dash/dash.all.min.js"></script>
                        <script src="dash/videojs-dash.min.js"></script>
                    <script>
                        var player = videojs("videostream",{
                            playbackRates: [0.5, 1, 1.5, 2, 3]
                        });';
        $output .= 'player.src({ src: \'';
        $output .= createdash($id);
        $output .= '\', type: \'application/dash+xml\'});
                            player.play();
                        </script>';
        $output .= video_events($id, $courseid);
        return $output;
}



function get_video_source_elements_hls($id, $courseid) {
    global $CFG, $OUTPUT, $PAGE;
    $width = '800px';
    $height = '500px';
    $hlsstream = block_videodirectory_createHLS($id);
    $data = array('width' => $width, 'height' => $height, 'videostream' => $hlsstream, 'wwwroot' => $CFG->wwwroot, 'videoid' => $id, 'type' => 'application/x-mpegURL');
    $output = $OUTPUT->render_from_template("block_videodirectory/hls", $data);
    $output .= video_events($id, $courseid);
    return $output;
}

function get_video_source_elements_videojs($type, $id, $courseid) {
    global $CFG, $OUTPUT;

    $width = '800px';
    $height = '500px';

    if ($type == "symlink") {
        $videolink = createsymlink($id);
    } else {
        $videolink = $CFG->wwwroot . '/local/video_directory/play.php?video_id=' . $id;
    }

    $data = array('width' => $width, 'height' => $height, 'videostream' => $videolink, 'wwwroot' => $CFG->wwwroot, 'videoid' => $id, 'type' => 'video/mp4');
    $output = $OUTPUT->render_from_template("block_videodirectory/hls", $data);
    $output .= video_events($id, $courseid);
    return $output;
}

