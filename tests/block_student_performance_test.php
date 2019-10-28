<?php

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/blocks/student_performance/locallib.php');

class student_performance_testcase extends advanced_testcase {

	protected $course;
	protected $users = array();
    protected $grade_items = array();
	protected $grade_grades = array();
    protected $courseid;
	protected $userid;
	
    protected function setUp() {
        global $CFG;
        parent::setup();
        $this->resetAfterTest(true);
		
		$this->course = $this->getDataGenerator()->create_course();
		$this->courseid = $this->course->id;
		
		$this->load_users();
		$this->load_enrollments();
        $this->load_grade_items();
        $this->load_grade_grades();
	}

	protected function load_users() {
		for ($us = 0; $us < 10; $us++) {
			$this->users[$us] = $this->getDataGenerator()->create_user();
		} 
		$this->userid = $this->users[0]->id;
	}
	
	protected function load_enrollments(){
		// 10 days - 19 days
		$timends = array(864000, 950400, 1036800, 1123200, 1209600, 1296000, 1382400, 1468800, 1555200, 1641600 );
		
		for ($us = 0; $us < 10; $us++) {
			$this->getDataGenerator()->enrol_user(
				$this->users[$us]->id, 
				$this->courseid, 
				null,
				'manual',
				0,
				$timends[$us]
			);
		} 
		
	}

	protected function load_grade_items() {
        global $DB;
		
		for ($act = 0; $act < 6; $act++) {
			$grade_item = new stdClass();
			$grade_item->courseid = $this->course->id;
			$grade_item->itemname = 'unittestgradeitem' . $act;
			$grade_item->itemtype = 'mod';
			$grade_item->id = $DB->insert_record('grade_items', $grade_item);
			$this->grade_items[$act] = $grade_item;
		} 
    }
 
    private function load_grade_grades() {
		global $DB;

	    $finalgrades = array(
			60, 98, null, 83, 78, 83,
			83, 98, 95, 93, 16, 56,
			100, 38, 49, null, 84, null,
			93, 28, 72, 98, 38, 13,
			94, 37, null, null, null, 38,
			null, 89, 36, 83, 59, 26,
			85, 74, 36, 84, 84, 93,
			23, null, 45, 58, 83, 36, 
			94, null, null, null, null, 93,
			15, 38, null, 89, null, 93,
		);
		
		for ($act = 0; $act < 6; $act++) {
			for ($us = 0; $us < 10; $us++){
				$grade = new stdClass();
				$grade->itemid = $this->grade_items[$act]->id;
				$grade->userid = $this->users[$us]->id;
				$grade->timecreated = time();
				$grade->finalgrade = $finalgrades[6*$us + $act];
				$grade->timemodified = time();
				$grade->id = $DB->insert_record('grade_grades', $grade);
				$this->grade_grades[0] = $grade;	
					
			}
			
		} 
		
    }
		

	function test_get_activity_average(){
		$averages = array(
			73.375, 70.5, 68.375, 69.25, 69.125, 71.888, 70.25, 78.0, 69.125, 79.0,
			57.42857, 57.428, 66.0, 67.4285, 66.14285, 58.714, 60.8571, 62.5, 62.5, 66.0,
			55.5, 47.6, 56.8, 52.2, 55.5, 59.4, 59.4, 57.6, 55.5, 55.5, 
			84.166, 82.5, 84.0, 81.666, 84.0, 84.166, 84.0, 88.333, 84.0, 83.166,
			60.666, 71.0, 59.666, 67.333, 63.142, 63.833, 59.666, 59.833, 63.142, 63.142,
			56.0, 59.375, 59.0, 64.75, 61.625, 63.125, 54.75, 61.875, 54.75, 54.75
		);
		
		for ($act = 0; $act < 6; $act++) {
			for ($us = 0; $us < 10; $us++){
				$average = block_student_performance_get_activity_average(
					$this->users[$us]->id,
					$this->grade_items[$act]->id
				);
				$this->assertEquals($averages[$us + 10*$act], $average, '', 0.01);
			}
		}

	}

	function test_get_grade_items(){
		$count =  block_student_performance_get_grade_items($this->courseid); 
		$this->assertEquals($count, 6);
	}
	
	function test_get_items_completed(){

		$completed = array(5,6,4,6,3,5,6,5,2,4);

		for ($us = 0; $us < 10; $us++){
			$count = block_student_performance_get_items_completed(
				$this->courseid, 
				$this->users[$us]->id
			);
			$this->assertEquals($completed[$us], $count);
		}
	}

	
	function test_get_course_duration(){
		$days = array(10,11,12,13,14,15,16,17,18,19);
		for ($us = 0; $us < 10; $us++) {
			$enrolinfo = block_student_performance_get_enrol_info($this->courseid, $this->users[$us]->id);
			$duration = block_student_performance_get_course_duration($enrolinfo);
			$this->assertEquals($days[$us], $duration);
		} 
	}

}