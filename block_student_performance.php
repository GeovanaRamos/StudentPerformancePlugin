<?php
class block_student_performance extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_student_performance');
    }

    public function applicable_formats() {
        return array('course-view' => true, 'site' => false);
    }

    public function get_content() {
        global $CFG;
        global $COURSE;
        global $USER;

        require_once($CFG->dirroot . '/blocks/student_performance/locallib.php');

        // Hide the block to non-logged in users and guests
        if ($this->content !== null || !isloggedin() || isguestuser()) {
          return $this->content;
        }

        // JavaScript
        $this->page->requires->jquery();
        // $this->page->requires->js("/blocks/student_performance/js/gauge.min.js");
        // $this->page->requires->js("/blocks/student_performance/js/gauge.js");
        $this->page->requires->js("/blocks/student_performance/js/chart-bundle.js");
        $this->page->requires->js("/blocks/student_performance/js/chart-gauge.js");
        $this->page->requires->js("/blocks/student_performance/js/chart-labels.js");
        //$this->page->requires->js(new moodle_url('https://unpkg.com/chart.js@2.8.0/dist/Chart.bundle.js'));
        $this->page->requires->js("/blocks/student_performance/js/word-gauge.js");

        // Calculation
        $activitiesfactor = block_student_performance_get_activities_factor($COURSE->id, $USER->id);
        $courseaverage = block_student_performance_get_course_average_factor($COURSE->id, $USER->id);

        if($courseaverage >= 0)
            $performancefactor = $activitiesfactor * 50;
        else
            $performancefactor = ($activitiesfactor - $courseaverage) * 50;

        $feedback = block_student_performance_get_feedback($activitiesfactor, $courseaverage);

        // HTML
        $this->content         =  new stdClass;

        $this->content->text   = html_writer::tag(
            'canvas',
            '',
            array(
                'id' => 'chart',
                'data-perf' => $performancefactor,
                'width'=>400,
                'height'=>400,
                'a'=> $activitiesfactor,
                'n' => $courseaverage
            )
        );
        $this->content->text   .= html_writer::tag(
            'p',
            $feedback,
            array('style'=> 'text-align:center;position:relative;bottom:20px;')
        );

        $this->content->footer = '';

        return $this->content;
    }

}
