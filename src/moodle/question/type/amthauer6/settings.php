<?php
defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot . '/question/type/amthauer6/lib.php');

    // Introductory explanation that all the settings are defaults for the edit_amthauer6_form.
    $settings->add(new admin_setting_heading('configintro', '', get_string('configintro', 'qtype_amthauer6')));

    // Shuffle options.
    $settings->add(new admin_setting_configcheckbox('qtype_amthauer6/shuffleanswers',
        get_string('shuffleanswers', 'qtype_amthauer6'),
        get_string('shuffleanswers_help', 'qtype_amthauer6'),
        0
    ));
}
