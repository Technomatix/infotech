<?php
defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot . '/question/type/multirowselect/lib.php');

    // Introductory explanation that all the settings are defaults for the edit_multirowselect_form.
    $settings->add(new admin_setting_heading('configintro', '', get_string('configintro', 'qtype_multirowselect')));

    // Scoring methods.
    $options = [
        'jonescrendall' => get_string('jonescrendall', 'qtype_multirowselect'),
        'bfq' => get_string('bfq', 'qtype_multirowselect'),
    ];

    $settings->add(new admin_setting_configselect(
            'qtype_multirowselect/scoringmethod',
            get_string('scoringmethod', 'qtype_multirowselect'),
            get_string('scoringmethod_help', 'qtype_multirowselect'), 'jonescrendall',
            $options
        )
    );

    // Show Scoring Method in quizes.
    $settings->add(new admin_setting_configcheckbox(
            'qtype_multirowselect/showscoringmethod',
            get_string('showscoringmethod', 'qtype_multirowselect'),
            get_string('showscoringmethod_help', 'qtype_multirowselect'),
            0
        )
    );

    // Shuffle options.
    $settings->add(new admin_setting_configcheckbox(
            'qtype_multirowselect/shuffleanswers',
            get_string('shuffleanswers', 'qtype_multirowselect'),
            get_string('shuffleanswers_help', 'qtype_multirowselect'),
            0
        )
    );
}
