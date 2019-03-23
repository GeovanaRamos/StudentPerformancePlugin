<?php
class block_student_performance extends block_base {
    
    public function init() {
        $this->title = get_string('student_performance', 'block_student_performance');
    }
    
    public function get_content() {
     

        if ($this->content !== null) {
          return $this->content;
        }

        $this->page->requires->jquery();
        $this->page->requires->js("/blocks/student_performance/gauge.min.js");
        $this->page->requires->js("/blocks/student_performance/gauge.js");


        $this->content         =  new stdClass;
        $this->content->text   = '<canvas id="gauge"></canvas>';
        $this->content->footer = '';
     
        return $this->content;
    }

}