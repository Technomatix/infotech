<?php

/**
 * @package     qtype_amthauer
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/amthauer/grading/qtype_amthauer_grading.class.php');

class qtype_amthauer_grading_scoringsub3 extends qtype_amthauer_grading
{
    const TYPE = 'scoringsub3';

    protected function calculateResult($amount)
    {
        $scale = [
            '20' => 122,
            '19' => 120,
            '18' => 118,
            '17' => 116,
            '16' => 113,
            '15' => 111,
            '14' => 109,
            '13' => 107,
            '12' => 104,
            '11' => 102,
            '10' => 100,
            '9' => 98,
            '8' => 96,
            '7' => 93,
            '6' => 90,
            '5' => 88,
            '4' => 86,
            '3' => 84,
            '2' => 82,
            '1' => 80,
            '0' => 78,
        ];

        return $scale[$amount];
    }
}
