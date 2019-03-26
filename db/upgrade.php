<?php

function xmldb_block_student_performance_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion <= 2019032612) {

        // Define table block_student_performance to be created.
        $table = new xmldb_table('block_student_performance');
        
        // Adding fields to table block_student_performance.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('value', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table block_student_performance.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
  
        // Conditionally launch create table for block_student_performance.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Savepoint reached.
        upgrade_block_savepoint(true, 2019032612, 'student_performance');
    
    }

}