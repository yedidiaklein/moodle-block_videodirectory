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
 * This class provides functionality for the videostream module.
 *
 * @package    block_videodirectory
 * @copyright  2020 Tovi Kurztag <tovi@openapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
class block_videodirectory extends block_base {
    public function init() {
        $this->title = get_string('videodirectory', 'block_videodirectory');
    }

    public function get_content() {

        global $CFG, $OUTPUT, $COURSE, $DB, $PAGE, $USER;
        if ($this->content !== null) {
            return $this->content;
        }
        $this->content         = new stdClass();
        $this->content->text   = '';
        $this->content->footer = '';

        $sql = "SELECT DISTINCT vv.id, orig_filename, thumb
                    FROM  {local_video_directory} vv
                    LEFT JOIN {local_video_directory_zoom} vz
                    ON vv.id = vz.video_id
                    LEFT JOIN {zoom} z
                    ON z.meeting_id = vz.zoom_meeting_id
 	            WHERE z.course = ?";
        $videos = $DB->get_records_sql($sql, [$COURSE->id]);
        $this->content->text = $OUTPUT->render_from_template
        ("block_videodirectory/list",
        array('videos' => array_values($videos),
        'wwwroot' => $CFG->wwwroot,
        'courseid' => $COURSE->id));
        return $this->content;
    }

}
