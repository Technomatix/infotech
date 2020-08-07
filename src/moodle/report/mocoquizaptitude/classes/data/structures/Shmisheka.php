<?php

namespace report_mocoquizaptitude\data\structures;

use report_mocoquizaptitude\data\Structure;
use report_mocoquizaptitude\data\structures\templates\ShmishekaTemplate;

class Shmisheka extends Structure
{
    public static function getInstance()
    {
        return new self(new ShmishekaTemplate());
    }

    protected $tableName = 'qtype_shmisheka_grades';

    protected $scales = [
        '1' => 'Шкала 1',
        '5' => 'Шкала 5',
        '6' => 'Шкала 6',
        '7' => 'Шкала 7',
    ];

    protected function identifyProfession($amount, $scale = null)
    {
        switch ($scale) {
            /**
             * Якщо Бал in (4,5,6,7,8,9), то «Слідчий», інакше
             * Якщо (Бал in (18,19), то «патрульний» », інакше
             * Якщо (Бал in (10,11) то «дільничний офіцер» », інакше
             * Якщо (Бал in (12,13,14,15,16,17), то «оперуповноважений» , інакше «непридатний»)))
             */
            case '1':
                switch (true) {
                    case (in_array($amount, [4, 5, 6, 7, 8, 9])):
                        return self::INQUIRER;
                    case (in_array($amount, [18, 19])):
                        return self::PATROL;
                    case (in_array($amount, [10, 11])):
                        return self::DISTRICT_OFFICER;
                    case (in_array($amount, [12, 13, 14, 15, 16, 17])):
                        return self::OPERATIVE;
                    default:
                        return self::UNSUITABLE;
                }
            break;
            /**
             * Якщо Бал in (4,5,6,7,8,9,10), то «Слідчий», інакше
             * Якщо (Бал in (18,19), то «патрульний» », інакше
             * Якщо (Бал in (11,12,13) то «дільничний офіцер» », інакше
             * Якщо (Бал in (14,15,16,17), то «оперуповноважений» , інакше «непридатний»)))
             */
            case '5':
                switch (true) {
                    case (in_array($amount, [4, 5, 6, 7, 8, 9, 10])):
                        return self::INQUIRER;
                    case (in_array($amount, [18, 19])):
                        return self::PATROL;
                    case (in_array($amount, [11, 12, 13])):
                        return self::DISTRICT_OFFICER;
                    case (in_array($amount, [14, 15, 16, 17])):
                        return self::OPERATIVE;
                    default:
                        return self::UNSUITABLE;
                }
            break;
            /**
             * Якщо Бал in (9,10,11,12), то «Слідчий», інакше
             * Якщо (Бал in (13,14,15,16,17), то «патрульний» », інакше
             * Якщо (Бал in (7,8) то «дільничний офіцер» », інакше
             * Якщо (Бал in (2,3,4,5,6), то «оперуповноважений» , інакше «непридатний»)))
             */
            case '6':
                switch (true) {
                    case (in_array($amount, [9, 10, 11, 12])):
                        return self::INQUIRER;
                    case (in_array($amount, [13, 14, 15, 16, 17])):
                        return self::PATROL;
                    case (in_array($amount, [7, 8])):
                        return self::DISTRICT_OFFICER;
                    case (in_array($amount, [2, 3, 4, 5, 6])):
                        return self::OPERATIVE;
                    default:
                        return self::UNSUITABLE;
                }
            break;
            /**
             * Якщо Бал in (6,7), то «Слідчий», інакше
             * Якщо (Бал in (8,9), то «патрульний» », інакше
             * Якщо (Бал in (4,5) то «дільничний офіцер» », інакше
             * Якщо (Бал in (1,2,3), то «оперуповноважений» , інакше «непридатний»)))
             */
            case '7':
                switch (true) {
                    case (in_array($amount, [6, 7])):
                        return self::INQUIRER;
                    case (in_array($amount, [8, 9])):
                        return self::PATROL;
                    case (in_array($amount, [4, 5])):
                        return self::DISTRICT_OFFICER;
                    case (in_array($amount, [1, 2, 3])):
                        return self::OPERATIVE;
                    default:
                        return self::UNSUITABLE;
                }
            break;
            default:
                return 'UNKNOWN SCALE';
        }
    }
}
