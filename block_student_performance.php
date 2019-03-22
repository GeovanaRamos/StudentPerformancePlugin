<?php
class block_student_performance extends block_base {
    
    public function init() {
        $this->title = get_string('student_performance', 'block_student_performance');
    }
    
    public function get_content() {
        if ($this->content !== null) {
          return $this->content;
        }
     
        $this->content         =  new stdClass;
        $this->content->text   = 'The content of our SimpleHTML block!';
        $this->content->footer = 'Footer here...';
     
        return $this->content;
    }
    
}