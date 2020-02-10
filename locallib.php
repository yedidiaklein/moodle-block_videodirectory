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

$id = required_param('id', PARAM_INT);

function video($id) {
    // $output .= mod_videostream_renderer->output->container_start($vclass);
    $output = "<div class='videostream'>";
    // Open video tag.
    $vclass = 'videostream';
    // $output .= $output->container_start($vclass);

    $config = get_config('videostream');

    if (($config->streaming == "symlink") || ($config->streaming == "php")) {
        $output .= get_video_source_elements_videojs($config->streaming, $id);

    } else if ($config->streaming == "hls") {
        // Elements for video sources. (here we get the hls video).
        $output .= get_video_source_elements_hls($id);
    } else {
        // Dash video.
        $output .= get_video_source_elements_dash($id);
    }

    // Close video tag.
    $output .= html_writer::end_tag('video');
    $output .= get_bookmark_controls($id);
    // Close videostream div.
    //$output .= container_end();
    return $output;
}

function video_events($id) {
    global $CFG, $DB;

    $sql = "SELECT c2.*
    from mdl_context c
    join mdl_block_instances bi on c.id=bi.parentcontextid
    join mdl_context c2 on c2.contextlevel=80 and c2.instanceid = bi.id
    and bi.blockname = 'videodirectory'";
    $context = $DB->get_record_sql($sql, null, $strictness = IGNORE_MULTIPLE);
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




function get_video_source_elements_dash($id) {
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
        $output .= video_events($videostream);
        return $output;
    }



function get_video_source_elements_hls($videostream)
{
    global $CFG, $OUTPUT;
    $width = '800px';
    $height = '500px';

    $data = array('width' => $width,
        'height' => $height,
        'hlsstream' => createhls($id),
        'wwwroot' => $CFG->wwwroot);
    $output = $OUTPUT->render_from_template("block_videodirectory/hls", $data);
    $output .= video_events($videostream);
    return $output;
}




function get_video_source_elements_videojs($type, $id) {
    global $CFG;
    $width = '800px';
    $height = '500px';

    $output = '<video id=videostream class="video-js vjs-default-skin" data-setup=\'{}\'
                style="position: relative"'
    . 'controls >
                <track label="English" kind="subtitles" srclang="en"
                src="' . $CFG->wwwroot . '/local/video_directory/subs.php?video_id=' .
        $id . '" default>
                </video>
                    <script src="https://vjs.zencdn.net/6.6.3/video.js"></script>
                <script>
                    var player = videojs("videostream",{
                        playbackRates: [0.5, 1, 1.5, 2, 3]
                    });';
    $output .= 'player.src({ src: \'';
    if ($type == "symlink") {
        $output .= createsymlink($id);
    } else {
        $output .= $CFG->wwwroot . '/local/video_directory/play.php?video_id=' . $id;
    }
    $output .= '\', type: \'video/mp4\'});
                        player.play();
                    </script>';
     $output .= video_events($id);

    return $output;
}





