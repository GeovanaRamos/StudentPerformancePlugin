<?php

function block_student_performance_get_course_average_factor($courseid, $userid){
    /*
    
    Factor(F) is given by:

    F = 10 			--> if student DP >= 0
	  F = DA * 10  		--> else

	  DA = SUM(DPn) / COUNT(DP)
	  DPn = (Student grade on activity n - Average on activity n) / Average on activity n

    */

    $usergrades = block_student_performance_get_user_grades($courseid, $userid);

    $diffpercentage = array();

    foreach($usergrades as $index => $usergrade){
        $grade = $usergrade->finalgrade;
        $itemid = $usergrade->itemid;
        $average = block_student_performance_get_activity_average($userid, $itemid);

        if ($average == 0)
            $diffpercentage[$index] = 0;
        else
            $diffpercentage[$index] = ($grade - $average) / (float)$average;
    }

    if (count($diffpercentage) == 0)
        return 10;
    else
        $diffaverage = array_sum($diffpercentage)/ (float)count($diffpercentage);

    if ($diffaverage >= 0)
        return 10;
    else
        return $diffaverage * 10;
}

function block_student_performance_get_activity_average($userid, $itemid){
		global $DB;

		$sql = "SELECT AVG(finalgrade) AS average FROM {grade_grades} g , {grade_items} i
				WHERE g.itemid=? AND g.userid!=?";

		$record = $DB->get_record_sql($sql, [$itemid, $userid]);

		return $record->average;
}

function block_student_performance_get_user_grades($courseid, $userid){
    global $DB;

    $sql = "SELECT g.finalgrade, itemid	FROM {grade_items} i
            INNER JOIN {grade_grades} g ON i.id=g.itemid
            WHERE i.courseid=? AND g.userid=? AND i.itemtype='mod'
            AND g.finalgrade IS NOT NULL";

    return $DB->get_records_sql($sql, [$courseid, $userid]);
}

function block_student_performance_get_activities_factor($courseid, $userid, $enrolinfo){
    /*
      Factor(F) is given by:

      F =  AC * 10 / AT

      AC = (Activities completed by student / Days of enrolment)
      TA = (Total gradable activities / Course duration in days)

    */

    $items = block_student_performance_get_grade_items($courseid);
    $itemscompleted = block_student_performance_get_items_completed($courseid, $userid);
	$courseduration = block_student_performance_get_course_duration($enrolinfo);
	$daysenrolled = block_student_performance_get_days_enrolled($enrolinfo);

	if($courseduration == 0 || $daysenrolled == 0)
		return 10;

    // Enrolment factor variables calculation
    $itemsperday = $items / $courseduration;
    $completedperday = $itemscompleted / $daysenrolled;

    // Enrolment factor formula
   	$factor = $completedperday * 10 / (float)$itemsperday;
   	
   	if ($factor < 10)
   		return $factor;
   	else
   		return 10;

}

function block_student_performance_get_grade_items($courseid){
    global $DB;

    $sql = "SELECT COUNT(*) FROM {grade_items}
            WHERE courseid=? AND itemtype='mod'";

    return $DB->count_records_sql($sql, [$courseid]);
}

function block_student_performance_get_items_completed($courseid, $userid){
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
        return "Atenção! Realize atividades com mais frequência e melhore suas médias nas atividades!";
    else if ($activitiesfactor < 10)
        return "Atenção! Realize atividades com mais frequência!" ;
    else if ($averagefactor < 0)
        return "Atenção! Melhore suas médias nas atividades";
    else
        return "Continue assim!";
}
