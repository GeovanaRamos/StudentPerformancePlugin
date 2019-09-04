<?php

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