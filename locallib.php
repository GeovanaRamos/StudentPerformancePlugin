<?php

function block_student_performance_get_performance_factor($courseid, $userid){
    /*
      Factor(F) is given by:

      F =  AC * 10 / AT

      AC = (Activities completed by student / Days of enrolment)
      AT = (Total gradable activities / Course duration in days)

    */

    // Enrolment duration informations
    $enrolinfo = block_student_performance_get_enrol_info($courseid, $userid);

    // Gradable activities count
    $itemscompleted = block_student_performance_get_items_completed($courseid, $userid);
    $gradeitems = block_student_performance_get_grade_items($courseid);

    // Variables for performance factor calculation
    $itemsperday = $gradeitems / float(ceil($enrolinfo->enrolperiod / 86400));
    $timeenrolled = time() - $enrolinfo->timestart;
    $completedperday = $itemscompleted / float(ceil($timeenrolled / 86400));

    // Performance factor formula
    $factor = $completedperday * 10 / float($itemsperday);

    return $factor;

}

function block_student_performance_get_grade_items($courseid){
    global $DB;

    $sql = "SELECT COUNT(*) FROM {grade_items}
            WHERE courseid=? AND itemtype!='course'";

    $count = $DB->count_records_sql($sql, [$courseid]);

    return $count;
}

function block_student_performance_get_items_completed($courseid, $userid){
    global $DB;

    $sql = "SELECT COUNT(*)
            FROM {grade_items} i
            INNER JOIN {grade_grades} g ON i.id=g.itemid
            WHERE i.courseid=? AND g.userid=?
            AND itemtype='mod' AND g.aggregationstatus='used'";

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
