<?php

/**
 * @package     qtype_amthauer
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/amthauer/grading/qtype_amthauer_grading.class.php');

class qtype_amthauer_grading_scoringsub2 extends qtype_amthauer_grading
{
    const TYPE = 'scoringsub2';

    protected function calculateResult($amount)
    {
        $scale = [
            '20' => 134,
            '19' => 130,
            '18' => 127,
            '17' => 123,
            '16' => 120,
            '15' => 117,
            '14' => 113,
            '13' => 110,
            '12' => 106,
            '11' => 103,
            '10' => 100,
            '9' => 96,
            '8' => 92,
            '7' => 89,
            '6' => 85,
            '5' => 82,
            '4' => 79,
            '3' => 75,
            '2' => 72,
            '1' => 69,
            '0' => 65,
        ];

        return $scale[$amount];
    }
}
