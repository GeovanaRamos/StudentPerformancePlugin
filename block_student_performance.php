<?php
class block_student_performance extends block_base {
    
    public function init() {
        $this->title = get_string('student_performance', 'block_student_performance');
    }
    
    public function get_content() {
        global $CFG;
        global $COURSE;

        require_once($CFG->dirroot . '/blocks/student_performance/locallib.php');

        // Hide the block to non-logged in users and guests 
        if ($this->content !== null || !isloggedin() || isguestuser()) {
          return $this->content;
        }

        $this->page->requires->jquery();
        $this->page->requires->js("/blocks/student_performance/js/gauge.min.js");
        $this->page->requires->js("/blocks/student_performance/js/gauge.js");

        $courseid = $COURSE->id;
        $gradeitems = block_student_performance_get_grade_items($courseid);

        //$valueId = block_student_performance_set_random_data();
        //$value = block_student_performance_get_value($valueId);

        $this->content         =  new stdClass;
        $this->content->text   = html_writer::tag(
            'canvas',
            '', 
            array('id' => 'gauge', 'data-perf' => $gradeitems)
        );
        $this->content->footer = '';
     
        return $this->content;
    }

}