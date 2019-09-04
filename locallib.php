<?php
function block_student_performance_set_random_data() {
    global $DB;

    $num = mt_rand(1, 10);

    $newData = new stdClass();
    $newData->value = $num;

    return $DB->insert_record('block_student_performance', $newData);

}

function block_student_performance_get_value($id){
    global $DB;

    $record = $DB->get_record('block_student_performance', ['id' => $id]);

    return $record->value;
}

function block_student_performance_get_grade_items($courseid){
    global $DB;

    $sql = "SELECT COUNT(*) FROM {grade_items} WHERE courseid=? AND itemtype!='course'";

    $count = $DB->count_records_sql($sql, [$courseid]);

    return $count;
}