<?php
defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot . '/question/type/memorize/lib.php');

    // Introductory explanation that all the settings are defaults for the edit_memorize_form.
    $settings->add(new admin_setting_heading('configintro', '', get_string('configintro', 'qtype_memorize')));

    // Scoring methods.
    $options = [
        'ten_words' => get_string('ten_words', 'qtype_memorize'),
    ];

    $settings->add(new admin_setting_configselect(
            'qtype_memorize/scoringmethod',
            get_string('scoringmethod', 'qtype_memorize'),
            get_string('scoringmethod_help', 'qtype_memorize'), 'ten_words',
            $options
        )
    );

    // Show Scoring Method in quizes.
    $settings->add(new admin_setting_configcheckbox(
            'qtype_memorize/showscoringmethod',
            get_string('showscoringmethod', 'qtype_memorize'),
            get_string('showscoringmethod_help', 'qtype_memorize'),
            0
        )
    );

    // Shuffle options.
    $settings->add(new admin_setting_configcheckbox(
            'qtype_memorize/shuffleanswers',
            get_string('shuffleanswers', 'qtype_memorize'),
            get_string('shuffleanswers_help', 'qtype_memorize'),
            0
        )
    );
}
