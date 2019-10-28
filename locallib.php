<?php

function block_student_performance_get_course_average_factor($courseid, $userid){
    /*
      Factor(F) is given by:

      F = 10 			--> if student DP >= 0
	  F = DA * 10  		--> else
	  
	  DA = SUM(DP) / COUNT(DP)
	  DPn = (Student grade on activity n - Average on activity n) / Average on activity n

    */

	$usergrades = block_student_performance_get_user_grades($courseid, $userid);

	$diffpercentage = array();

	foreach($usergrades as $index => $usergrade){
		$grade = $usergrade->finalgrade;
		$itemid = $usergrade->itemid; 
		$average = block_student_performance_get_activity_average($userid, $itemid);

		$diffpercentage[$index] = ($grade - $average) / (float)$average;

	}

	$diffaverage = array_sum($diffpercentage)/ (float)count($diffpercentage);

	if ($diffaverage >= 0){
		return 10;
	} else {
		return $diffaverage * 10;	
	}
   
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

    // Gradable activities count
    $items = block_student_performance_get_grade_items($courseid);
    $itemscompleted = block_student_performance_get_items_completed($courseid, $userid);

    // Enrolment factor variables calculation
    $itemsperday = $items / block_student_performance_get_course_duration($enrolinfo);
    $completedperday = $itemscompleted / block_student_performance_get_days_enrolled($enrolinfo);

    // Enrolment factor formula
   	return $completedperday * 10 / (float)$itemsperday;

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

function block_student_performance_get_feedback($performancefactor, $activitiesfactor, $averagefactor){

    $performance = array(
      "low" => "Cuidado! ",
      "regular" => "Atenção! ",
      "high" => "Excelente! ",
    );

    $activities = array(
      "low" => "Realize atividades com mais frequência",
      "high" => "Continue com o bom ritmo de realização de atividades",
    );

    $average = array(
      "low" => "melhore suas notas nas atividades.",
      "high" => "mantenha as boas notas nas atividades.",
    );

    $fbperformance = block_student_performance_get_performance_feedback($performancefactor);
    $fbactivities = block_student_performance_get_activities_feedback($activitiesfactor);
    $fbaverage = block_student_performance_get_average_feedback($averagefactor);
    $conjunction = block_student_performance_get_conjunction($fbactivities, $fbaverage);

    return $performance[$fbperformance] . $activities[$fbactivities] . $conjunction . $average[$fbaverage];

}

function block_student_performance_get_performance_feedback($performancefactor){
    if ($performancefactor < 4)
      return "low";
    else if ($performancefactor > 6)
      return "high";
    else
      return "regular";
}

function block_student_performance_get_activities_feedback($activitiesfactor){
    if ($activitiesfactor < 10 )
      return "low";
    else
      return "high";
}

function block_student_performance_get_average_feedback($averagefactor){
    if ($averagefactor < 0 )
      return "low";
    else
      return "high";
}

function block_student_performance_get_conjunction($fbactivities, $fbaverage){
    if($fbactivities == $fbaverage)
        return " e ";
    else
        return ", entretanto ";
}
