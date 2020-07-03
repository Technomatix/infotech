<?php

/**
 * @package     qtype_amthauer
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade code for the amthauer question type.
 *
 * @param int $oldversion the version we are upgrading from.
 */
function xmldb_qtype_amthauer_upgrade($oldversion) {
    global $CFG, $DB;

    //$dbman = $DB->get_manager();

    return true;
}
