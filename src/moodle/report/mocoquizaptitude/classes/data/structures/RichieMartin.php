<?php

namespace report_mocoquizaptitude\data\structures;

use report_mocoquizaptitude\data\Structure;
use report_mocoquizaptitude\data\structures\templates\RichieMartinTemplate;

class RichieMartin extends Structure
{
    public static function getInstance()
    {
        return new self(new RichieMartinTemplate());
    }

    protected $tableName = 'qtype_richiemartin_grades';

    protected $scales = [
        '12' => 'Шкала 12',
        '11' => 'Шкала 11',
        '10' => 'Шкала 10',
        '7' => 'Шкала 7',
    ];

    protected function identifyProfession($amount, $scale = null)
    {
        switch ($scale) {
            /**
             * Якщо Бал >=34, то «Слідчий», інакше
             * Якщо (Бал in (30,31) то «дільничний офіцер» », інакше
             * Якщо (Бал in (32,33), то «оперуповноважений» , інакше «патрульний»))
             */
            case '7':
                switch (true) {
                    case ($amount >= 34):
                        return self::INQUIRER;
                    case (in_array($amount, [32, 33])):
                        return self::OPERATIVE;
                    case (in_array($amount, [30, 31])):
                        return self::DISTRICT_OFFICER;
                    default:
                        return self::PATROL;
                }
            break;
            /**
             * Якщо Бал >=27, то «оперуповноважений», інакше
             * Якщо (Бал in (21,24) то «дільничний офіцер» », інакше
             * Якщо (Бал in (25,26), то «Слідчий» , інакше «патрульний»))
             */
            case '10':
                switch (true) {
                    case ($amount >= 27):
                        return self::OPERATIVE;
                    case (in_array($amount, [25, 26])):
                        return self::INQUIRER;
                    case (in_array($amount, [21, 24])):
                        return self::DISTRICT_OFFICER;
                    default:
                        return self::PATROL;
                }
            break;
            /**
             * Якщо Бал >=34, то «Слідчий», інакше
             * Якщо (Бал in (30,31) то «дільничний офіцер» », інакше
             * Якщо (Бал in (32,33), то «оперуповноважений» , інакше «патрульний»))
             */
            case '11':
                switch (true) {
                    case ($amount >= 34):
                        return self::INQUIRER;
                    case (in_array($amount, [32, 33])):
                        return self::OPERATIVE;
                    case (in_array($amount, [30, 31])):
                        return self::DISTRICT_OFFICER;
                    default:
                        return self::PATROL;
                }
            break;
            /**
             * Якщо Бал >=37, то «Слідчий», інакше
             * Якщо (Бал in (33,34) то «дільничний офіцер» », інакше
             * Якщо (Бал in (35,36), то «оперуповноважений» , інакше «патрульний»))
             */
            case '12':
                switch (true) {
                    case ($amount >= 37):
                        return self::INQUIRER;
                    case (in_array($amount, [35, 36])):
                        return self::OPERATIVE;
                    case (in_array($amount, [33, 34])):
                        return self::DISTRICT_OFFICER;
                    default:
                        return self::PATROL;
                }
            break;
            default:
                return 'UNKNOWN SCALE';
        }
    }
}
