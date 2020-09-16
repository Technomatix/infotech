<?php

defined('MOODLE_INTERNAL') || die;

function xmldb_report_mocoquizaptitude_install()
{
    global $CFG, $DB;

    require_once($CFG->libdir . '/adminlib.php');

    // Выдача привилегий для роли, не имеющей прототипа
    $hasRoleEmployeemanager = false;
    foreach (get_all_roles() as $role) {
        if ($role->shortname === 'employeemanager') {
            $hasRoleEmployeemanager = true;
        }
    }
    if (!$hasRoleEmployeemanager) {
        $roleid = create_role('Employee Manager', 'employeemanager', 'This role has access to reports on subordinates');
        set_role_contextlevels($roleid, ['10']);
    }

    foreach (get_all_roles() as $role) {
        if ($role->shortname === 'employeemanager') {
            $capabilities = ['report/mocoquizaptitude:view'];
            $context = context::instance_by_id(1);
            foreach ($capabilities as $capability) {
                setCapabilityMocoquizaptitude($capability, '1', $role->id, $context, true);
                $context->mark_dirty();
            }
        }
    }
}

function setCapabilityMocoquizaptitude($capability, $permission, $roleid, $contextid, $overwrite = false)
{
    global $USER, $DB;

    if ($contextid instanceof context) {
        $context = $contextid;
    } else {
        $context = context::instance_by_id($contextid);
    }

    if (empty($permission) || $permission == CAP_INHERIT) { // if permission is not set
        unassign_capability($capability, $roleid, $context->id);

        return true;
    }

    $existing = $DB->get_record('role_capabilities', ['contextid' => $context->id, 'roleid' => $roleid, 'capability' => $capability]);

    if ($existing && !$overwrite) {   // We want to keep whatever is there already
        return true;
    }

    $cap = new stdClass();
    $cap->contextid = $context->id;
    $cap->roleid = $roleid;
    $cap->capability = $capability;
    $cap->permission = $permission;
    $cap->timemodified = time();
    $cap->modifierid = empty($USER->id) ? 0 : $USER->id;

    if ($existing) {
        $cap->id = $existing->id;
        $DB->update_record('role_capabilities', $cap);
    } else {
        if ($DB->record_exists('context', ['id' => $context->id])) {
            $DB->insert_record('role_capabilities', $cap);
        }
    }

    return true;
}
