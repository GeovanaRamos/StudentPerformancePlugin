<?php

function block_student_performance_get_course_average_factor($courseid, $userid){
    
    $diffaverage = block_student_performance_get_average_diff_from_gradepass($courseid, $userid);

    if ($diffaverage >= 0.1)
        return 10;
    else
        return $diffaverage * 10;
}

function block_student_performance_get_average_diff_from_gradepass($courseid, $userid){

    $usergrades = block_student_performance_get_user_grades($courseid, $userid);
    $diffpercentage = array(); 
    
    foreach($usergrades as $index => $usergrade){
        $gradepasspercentage = block_student_performance_get_course_gradepass_percentage($courseid);
        $itemgradepass = $usergrade->grademax * $gradepasspercentage;
        $diffpercentage[$index] = ($usergrade->finalgrade - $itemgradepass) / (float)$itemgradepass;
    }

    if (count($diffpercentage) == 0)
        return 0.1;
    else
        return array_sum($diffpercentage)/ (float)count($diffpercentage);
}

function block_student_performance_get_course_gradepass_percentage($courseid){
    global $DB;

    $sql = "SELECT gradepass FROM {course_completion_criteria} 
            WHERE course=?";

    $record = $DB->get_record_sql($sql, [$courseid]);
    
    if ($record){
        $coursegrademax = block_student_performance_get_course_grademax($courseid);
        return $record->gradepass / (float)$coursegrademax;
    } else { 
        return 0.6;
    }
}

function block_student_performance_get_course_grademax($courseid){
    global $DB;

    $sql = "SELECT grademax FROM {grade_items} 
            WHERE courseid=? AND itemtype='course'";

    $record = $DB->get_record_sql($sql, [$courseid]);

    return $record->grademax;
}

function block_student_performance_get_user_grades($courseid, $userid){
    global $DB;

    $sql = "SELECT g.finalgrade, i.grademax FROM {grade_items} i
            INNER JOIN {grade_grades} g ON i.id=g.itemid
            WHERE i.courseid=? AND g.userid=? AND i.itemtype='mod'
            AND g.finalgrade IS NOT NULL";

    return $DB->get_records_sql($sql, [$courseid, $userid]);
}

function block_student_performance_get_activities_factor($courseid, $userid){

    $enrolinfo = block_student_performance_get_enrol_info($courseid, $userid);
    $items = block_student_performance_count_grade_items($courseid);
    $itemscompleted = block_student_performance_count_items_completed($courseid, $userid);
	$courseduration = block_student_performance_get_course_duration($enrolinfo);
	$daysenrolled = block_student_performance_get_days_enrolled($enrolinfo);

	if($courseduration == 0 || $daysenrolled == 0)
		return 10;
    
    $itemsperday = $items / $courseduration;
    $completedperday = $itemscompleted / $daysenrolled;

   	$factor = $completedperday * 10 / (float)$itemsperday;
   	
   	if ($factor < 10)
   		return $factor;
   	else
   		return 10;

}

function block_student_performance_count_grade_items($courseid){
    global $DB;

    $sql = "SELECT COUNT(*) FROM {grade_items}
            WHERE courseid=? AND itemtype='mod'";

    return $DB->count_records_sql($sql, [$courseid]);
}

function block_student_performance_count_items_completed($courseid, $userid){
    global $DB;

    $sql = "SELECT COUNT(*) FROM {grade_items} i
            INNER JOIN {grade_grades} g ON i.id=g.itemid
            WHERE i.courseid=? AND g.userid=?
            AND i.itemtype='mod' AND g.finalgrade IS NOT NULL";

   	return $DB->count_records_sql($sql, [$courseid, $userid]);
}

function block_student_performance_get_enrol_info($courseid, $userid){
    global $DB;

    $sql = "SELECT ue.timestart, ue.timeend, e.enrolperiod
           FROM {user_enrolments} ue, {enrol} e
           WHERE ue.userid=? AND ue.enrolid=e.id AND e.courseid=?";

		return $DB->get_record_sql($sql, [$userid, $courseid]);
}

function block_student_performance_get_days_enrolled($enrolinfo){
    return (float) floor((time() - $enrolinfo->timestart) / 86400);
}

function block_student_performance_get_course_duration($enrolinfo){
    return (float) ceil(($enrolinfo->timeend - $enrolinfo->timestart) / 86400);
}

function block_student_performance_get_feedback($activitiesfactor, $averagefactor){

    if ($activitiesfactor < 10 && $averagefactor < 0)
        return "Atenção! Realize atividades com mais frequência e melhore suas notas nas atividades!";
    else if ($activitiesfactor < 10)
        return "Atenção! Realize atividades com mais frequência!" ;
    else if ($averagefactor < 0)
        return "Atenção! Melhore suas notas nas atividades";
    else
        return "Continue assim!";
}
