<?php

namespace report_mocoquizaptitude\data\structures;

use report_mocoquizaptitude\data\Structure;
use report_mocoquizaptitude\data\structures\templates\ITOTemplate;

class ITO extends Structure
{
    public static function getInstance()
    {
        return new self(new ITOTemplate());
    }

    protected $tableName = 'qtype_ito_grades';

    protected $scales = [
        'L' => 'Шкала L',
        'K' => 'Шкала K',
        '2' => 'Шкала 2',
        '4' => 'Шкала 4',
        '7' => 'Шкала 7',
    ];

    protected function identifyProfession($amount, $scale = null)
    {
        switch ($scale) {
            case 'L':
            case 'K':
                return ($amount > 4) ? self::SUITABLE : self::UNSUITABLE;
            break;
            case '2': //Якщо Бал = 9, то «непридатний», інакше Якщо (Бал = 8, то «патрульний» », інакше Якщо (Бал = 7, то «дільничний офіцер» », інакше Якщо (Бал = 6, то «оперуповноважений» , інакше «слідчий»)))
                switch ($amount) {
                    case 9:
                        return self::UNSUITABLE;
                    case 8:
                        return self::PATROL;
                    case 7:
                        return self::DISTRICT_OFFICER;
                    case 6:
                        return self::OPERATIVE;
                    default:
                        return self::INQUIRER;
                }
            break;
            case '4': //Якщо Бал = 9, то «непридатний», інакше Якщо (Бал = 8, то «патрульний» », інакше Якщо (Бал in (5,6) то «дільничний офіцер» », інакше Якщо (Бал = 7, то «оперуповноважений» , інакше «слідчий»)))
                switch ($amount) {
                    case 9:
                        return self::UNSUITABLE;
                    case 8:
                        return self::PATROL;
                    case 7:
                        return self::OPERATIVE;
                    case 6:
                    case 5:
                        return self::DISTRICT_OFFICER;
                    default:
                        return self::INQUIRER;
                }
            break;
            case '7': //Якщо Бал = 9, то «непридатний», інакше Якщо (Бал = 8, то «патрульний» », інакше Якщо (Бал in (3,4) то «дільничний офіцер» », інакше Якщо (Бал in (0,1,2), то «оперуповноважений» , інакше «слідчий»)))
                switch ($amount) {
                    case 9:
                        return self::UNSUITABLE;
                    case 8:
                        return self::PATROL;
                    case 4:
                    case 3:
                        return self::DISTRICT_OFFICER;
                    case 2:
                    case 1:
                    case 0:
                        return self::OPERATIVE;
                    default:
                        return self::INQUIRER;
                }
            break;
            default:
                return 'UNKNOWN SCALE';
        }
    }
}
