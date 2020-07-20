<?php
defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot . '/question/type/multirowswitch/lib.php');

    // Introductory explanation that all the settings are defaults for the edit_multirowswitch_form.
    $settings->add(new admin_setting_heading('configintro', '', get_string('configintro', 'qtype_multirowswitch')));

    // Scoring methods.
    $options = [
        'ito' => get_string('ito', 'qtype_multirowswitch'),
        'shmisheka' => get_string('shmisheka', 'qtype_multirowswitch'),
    ];

    $settings->add(new admin_setting_configselect(
            'qtype_multirowswitch/scoringmethod',
            get_string('scoringmethod', 'qtype_multirowswitch'),
            get_string('scoringmethod_help', 'qtype_multirowswitch'), 'ito',
            $options
        )
    );

    // Show Scoring Method in quizes.
    $settings->add(new admin_setting_configcheckbox(
            'qtype_multirowswitch/showscoringmethod',
            get_string('showscoringmethod', 'qtype_multirowswitch'),
            get_string('showscoringmethod_help', 'qtype_multirowswitch'),
            0
        )
    );

    // Shuffle options.
    $settings->add(new admin_setting_configcheckbox(
            'qtype_multirowswitch/shuffleanswers',
            get_string('shuffleanswers', 'qtype_multirowswitch'),
            get_string('shuffleanswers_help', 'qtype_multirowswitch'),
            0
        )
    );
}
