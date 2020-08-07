<?php

namespace report_mocoquizaptitude\data\structures;

use report_mocoquizaptitude\data\Structure;
use report_mocoquizaptitude\data\structures\templates\LearyTemplate;

class Leary extends Structure
{
    public static function getInstance()
    {
        return new self(new LearyTemplate());
    }

    protected $tableName = 'qtype_leary_grades';

    protected $scales = [
        '3' => '3-й октант',
        '4' => '4-й октант',
        '5' => '5-й октант',
        '8' => '8-й октант',
    ];

    protected function identifyProfession($amount, $scale = null)
    {
        switch ($scale) {
            /**
             * Якщо Бал in (3,4), то «Слідчий», інакше
             * Якщо (Бал in (8,9,10,11) то «оперуповноважений» », інакше
             * Якщо (Бал in (5,6,7), то «дільничний офіцер» , інакше
             * Якщо (Бал in (0,1,2) , то «патрульний», інакше «непридатний»)))
             */
            case '3':
                switch (true) {
                    case (in_array($amount, [3, 4])):
                        return self::INQUIRER;
                    case (in_array($amount, [8, 9, 10, 11])):
                        return self::OPERATIVE;
                    case (in_array($amount, [5, 6, 7])):
                        return self::DISTRICT_OFFICER;
                    case (in_array($amount, [0, 1, 2])):
                        return self::PATROL;
                    default:
                        return self::UNSUITABLE;
                }
            break;
            /**
             * Якщо Бал in (0,1,2), то «Слідчий», інакше
             * Якщо (Бал in (3,4,5) то «оперуповноважений» », інакше
             * Якщо (Бал in (5,6,7,8), то «дільничний офіцер» , інакше
             * Якщо (Бал in (9,10,11) , то «патрульний», інакше «непридатний»)))
             */
            case '4':
                switch (true) {
                    case (in_array($amount, [0, 1, 2])):
                        return self::INQUIRER;
                    case (in_array($amount, [3, 4, 5])):
                        return self::OPERATIVE;
                    case (in_array($amount, [6, 7, 8])):
                        return self::DISTRICT_OFFICER;
                    case (in_array($amount, [9, 10, 11])):
                        return self::PATROL;
                    default:
                        return self::UNSUITABLE;
                }
            break;
            /**
             * Якщо Бал in (3,4,5), то «Слідчий», інакше
             * Якщо (Бал in (0,1,2) то «оперуповноважений» », інакше
             * Якщо (Бал in (5,6,7,8), то «дільничний офіцер» , інакше
             * Якщо (Бал in (9,10,11) , то «патрульний», інакше «непридатний»)))
             */
            case '5':
                switch (true) {
                    case (in_array($amount, [3, 4, 5])):
                        return self::INQUIRER;
                    case (in_array($amount, [0, 1, 2])):
                        return self::OPERATIVE;
                    case (in_array($amount, [5, 6, 7, 8])):
                        return self::DISTRICT_OFFICER;
                    case (in_array($amount, [9, 10, 11])):
                        return self::PATROL;
                    default:
                        return self::UNSUITABLE;
                }
            break;
            /**
             * Якщо Бал in (7,8,9,10 11), то «Слідчий», інакше
             * Якщо (Бал in (5,6) то «оперуповноважений» », інакше
             * Якщо (Бал in (3,4), то «дільничний офіцер» , інакше
             * Якщо (Бал in (0,1,2) , то «патрульний»)
             */
            case '8':
                switch (true) {
                    case (in_array($amount, [7, 8, 9, 10, 11])):
                        return self::INQUIRER;
                    case (in_array($amount, [5, 6])):
                        return self::OPERATIVE;
                    case (in_array($amount, [3, 4])):
                        return self::DISTRICT_OFFICER;
                    case (in_array($amount, [0, 1, 2])):
                        return self::PATROL;
                    default:
                        return self::UNSUITABLE;
                }
            break;
            default:
                return 'UNKNOWN SCALE';
        }
    }
}
