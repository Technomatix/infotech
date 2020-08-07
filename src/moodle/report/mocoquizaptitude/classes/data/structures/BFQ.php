<?php

namespace report_mocoquizaptitude\data\structures;

use report_mocoquizaptitude\data\Structure;
use report_mocoquizaptitude\data\structures\templates\BFQTemplate;

class BFQ extends Structure
{
    public static function getInstance()
    {
        return new self(new BFQTemplate());
    }

    protected $tableName = 'qtype_bfq_grades';

    protected $scales = [
        '2' => 'поступливість',
        '3' => 'сумлінність',
        '5' => 'відкритість',
    ];

    protected function identifyProfession($amount, $scale = null)
    {
        switch ($scale) {
            /**
             * Якщо Бал in (37,38,39,40), то «Слідчий», інакше
             * Якщо (Бал in (41,42,43), то «патрульний» », інакше
             * Якщо (Бал in (33,34,35,36) то «дільничний офіцер» », інакше
             * Якщо (Бал in (23,24,25,26,27,28,29,30,31,32), то «оперуповноважений» , інакше «непридатний»))))
             */
            case '2':
                switch (true) {
                    case (in_array($amount, [37, 38, 39, 40])):
                        return self::INQUIRER;
                    case (in_array($amount, [41, 42, 43])):
                        return self::PATROL;
                    case (in_array($amount, [33, 34, 35, 36])):
                        return self::DISTRICT_OFFICER;
                    case (in_array($amount, [23, 24, 25, 26, 27, 28, 29, 30, 31, 32])):
                        return self::OPERATIVE;
                    default:
                        return self::UNSUITABLE;
                }
            break;
            /**
             * Якщо Бал in (42,43,44,45,46,47,48,49), то «Слідчий», інакше
             * Якщо (Бал in (30,31,32,33), то «патрульний» », інакше
             * Якщо (Бал in (34,35,36,37,38) то «дільничний офіцер» », інакше
             * Якщо (Бал in (39,40,41), то «оперуповноважений» , інакше «непридатний»))))
             */
            case '3':
                switch (true) {
                    case (in_array($amount, [42, 43, 44, 45, 46, 47, 48, 49])):
                        return self::INQUIRER;
                    case (in_array($amount, [30, 31, 32, 33])):
                        return self::PATROL;
                    case (in_array($amount, [34, 35, 36, 37, 38])):
                        return self::DISTRICT_OFFICER;
                    case (in_array($amount, [39, 40, 41])):
                        return self::OPERATIVE;
                    default:
                        return self::UNSUITABLE;
                }
            break;
            /**
             * Якщо Бал in (34,35,36,37,38,39,40), то «Слідчий», інакше
             * Якщо (Бал in (23,24,25), то «патрульний» », інакше
             * Якщо (Бал in (26,27,28,29,30) то «дільничний офіцер» », інакше
             * Якщо (Бал in (31,32,33), то «оперуповноважений» , інакше «непридатний»))))
             */
            case '5':
                switch (true) {
                    case (in_array($amount, [34, 35, 36, 37, 38, 39, 40])):
                        return self::INQUIRER;
                    case (in_array($amount, [23, 24, 25])):
                        return self::PATROL;
                    case (in_array($amount, [26, 27, 28, 29, 30])):
                        return self::DISTRICT_OFFICER;
                    case (in_array($amount, [31, 32, 33])):
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
