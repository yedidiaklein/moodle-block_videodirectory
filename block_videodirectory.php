<?php
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
     
        $sql = 'select DISTINCT vv.id, orig_filename, thumb 
        from mdl_local_video_directory_catvid cv 
        left join mdl_local_video_directory_cats c 
        on cv.cat_id=c.id left join mdl_local_video_directory vv 
        on vv.id = cv.video_id left join mdl_course co 
        on locate(c.cat_name, co.shortname) > 0 WHERE vv.id IS NOT NULL and co.id = ?';
    
      
        $videos = $DB->get_records_sql($sql, [$COURSE->id]);
        $this->content->text = $OUTPUT->render_from_template
        ("block_videodirectory/list",
        array('videos' => array_values($videos), 
        'wwwroot' => $CFG->wwwroot));
        
        return $this->content;
    }

}
    
  