<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade code for the multirowswitch question type.
 *
 * @param int $oldversion the version we are upgrading from.
 */
function xmldb_qtype_multirowswitch_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2020072200) {

        // Define table to be created.
        $table = new xmldb_table('qtype_leary_grades');

        // Adding fields to table
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('questionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'id');
        $table->add_field('scale', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null, 'questionid');
        $table->add_field('amount', XMLDB_TYPE_INTEGER, '3', null, XMLDB_NOTNULL, null, null, 'scale');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'amount');

        // Adding keys to table
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        upgrade_plugin_savepoint(true, 2020072200, 'qtype', 'multirowswitch');
    }

    return true;
}
