<?php
/**
 * @package     qtype_amthauer
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot . '/question/type/amthauer/lib.php');

    // Introductory explanation that all the settings are defaults for the edit_amthauer_form.
    $settings->add(new admin_setting_heading('configintro', '', get_string('configintro', 'qtype_amthauer')));

    // Scoring methods.
    $options = [
        'scoringsub2' => get_string('scoringsub2', 'qtype_amthauer'),
        'scoringsub3' => get_string('scoringsub3', 'qtype_amthauer'),
        //'subpoints' => get_string('scoringsubpoints', 'qtype_amthauer')
    ];

    $settings->add(new admin_setting_configselect('qtype_amthauer/scoringmethod',
        get_string('scoringmethod', 'qtype_amthauer'),
        get_string('scoringmethod_help', 'qtype_amthauer'), 'scoringsub2', $options)
    );

    // Show Scoring Method in quizes.
    $settings->add(new admin_setting_configcheckbox('qtype_amthauer/showscoringmethod',
        get_string('showscoringmethod', 'qtype_amthauer'),
        get_string('showscoringmethod_help', 'qtype_amthauer'), 0));

    // Shuffle options.
    $settings->add(new admin_setting_configcheckbox('qtype_amthauer/shuffleanswers',
        get_string('shuffleanswers', 'qtype_amthauer'),
        get_string('shuffleanswers_help', 'qtype_amthauer'), 0));
}
