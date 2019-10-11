<?php

function block_student_performance_get_performance_factor($courseid, $userid){

    $enrolinfo = block_student_performance_get_enrol_info($courseid, $userid);

    $activitiesfactor = block_student_performance_get_activities_factor($courseid, $userid, $enrolinfo);
    $courseaverage = block_student_performance_get_course_average_factor($courseid, $userid, $enrolinfo->timestart);

    return $activitiesfactor*0.8 + $courseaverage*0.2;
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
    $factor = $completedperday * 10 / (float)$itemsperday;

    return $factor;

}

function block_student_performance_get_course_average_factor($courseid, $userid, $timestart){
    /*
      Factor(F) is given by:
      
      F = 0 --> if student current final grade is less than or equal to course average
      F = (CG - CA) * 10 / CA  --> else

    */

    // Grades
    $currentgrade = block_student_performance_get_current_grade($courseid, $userid);
    $courseaverage = block_student_performance_get_course_average($courseid, $userid, $timestart);

    // Average factor formula 
    $difference = $currentgrade - $courseaverage;
    if ($difference <= 0){
        return 0;
    } else {
        return ($difference * 10 / $currentgrade);
    }
}

function block_student_performance_get_course_average($courseid, $userid, $timestart){
    global $DB;

    $sql = "SELECT g.finalgrade FROM {grade_items} i
            INNER JOIN {grade_grades} g ON i.id=g.itemid
            INNER JOIN {user_enrolments} ue ON g.userid=ue.userid
            WHERE i.courseid=? AND i.itemtype='course' 
            AND g.userid!=? AND ue.timestart=?
            AND g.finalgrade IS NOT NULL";

    $coursegrades = $DB->get_records_sql($sql, [$courseid, $userid, $timestart]);

    $sum = 0;
    $count = 0;

    foreach ($coursegrades as $g){
        $sum += $g->finalgrade;
        $count++;
    }

    return ($sum/(float)$count);
}

function block_student_performance_get_current_grade($courseid, $userid){
    global $DB;

    $sql = "SELECT g.finalgrade FROM {grade_items} i
            INNER JOIN {grade_grades} g ON i.id=g.itemid
            WHERE i.courseid=? AND g.userid=?
            AND i.itemtype='course'";

    $usergrade = $DB->get_record_sql($sql, [$courseid, $userid]);

    return $usergrade->finalgrade;
}

function block_student_performance_get_grade_items($courseid){
    global $DB;

    $sql = "SELECT COUNT(*) FROM {grade_items}
            WHERE courseid=? AND itemtype='mod'";

    $count = $DB->count_records_sql($sql, [$courseid]);

    return $count;
}

function block_student_performance_get_items_completed($courseid, $userid){
    global $DB;

    $sql = "SELECT COUNT(*)
            FROM {grade_items} i
            INNER JOIN {grade_grades} g ON i.id=g.itemid
            WHERE i.courseid=? AND g.userid=?
            AND i.itemtype='mod' AND g.finalgrade IS NOT NULL";

    $count = $DB->count_records_sql($sql, [$courseid, $userid]);

    return $count;
}

function block_student_performance_get_enrol_info($courseid, $userid){
    global $DB;

    $sql = "SELECT ue.timestart, ue.timeend, e.enrolperiod
           FROM {user_enrolments} ue, {enrol} e
           WHERE ue.userid=? AND ue.enrolid=e.id AND e.courseid=?";

    $enrolinfo = $DB->get_record_sql($sql, [$userid, $courseid]);

    return $enrolinfo;

}

function block_student_performance_get_days_enrolled($enrolinfo){
    return (float) floor((time() - $enrolinfo->timestart) / 86400);
}

function block_student_performance_get_course_duration($enrolinfo){
    return (float) ceil(($enrolinfo->timeend - $enrolinfo->timestart) / 86400);
}
