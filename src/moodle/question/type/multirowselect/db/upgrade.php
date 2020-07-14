<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade code for the multirowselect question type.
 *
 * @param int $oldversion the version we are upgrading from.
 */
function xmldb_qtype_multirowselect_upgrade($oldversion) {
    global $CFG, $DB;

    //$dbman = $DB->get_manager();

    return true;
}
