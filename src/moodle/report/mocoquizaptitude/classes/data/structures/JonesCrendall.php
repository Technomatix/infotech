<?php

namespace report_mocoquizaptitude\data\structures;

use report_mocoquizaptitude\collection\item\AcquiredProfession;
use report_mocoquizaptitude\collection\ProfessionCollection;
use report_mocoquizaptitude\data\Structure;
use report_mocoquizaptitude\data\structures\templates\JonesCrendallTemplate;

class JonesCrendall extends Structure
{
    public static function getInstance()
    {
        return new self(new JonesCrendallTemplate());
    }

    protected $tableName = 'qtype_jonescrendall_grades';

    protected $scales = [
        'none' => 'самоактуалізація',
    ];

    public function getData(\moodle_database $db, $courseId, $userIds = [])
    {
        $params = [];
        $where = [];

        $this->buildQueryWhereParams($where, $params, $courseId, $userIds);

        $sql = 'SELECT g.userid, g.amount FROM {' . $this->getTableName() . '} AS g WHERE g.id IN (' . $this->getSubQuery($where) . ')';

        return $this->makeProfessions($db->get_records_sql($sql, $params));
    }

    /**
     * @param array $data
     *
     * @return ProfessionCollection[]
     */
    public function makeProfessions(array $data)
    {
        $scaleName = $this->scales['none'];
        $professions = [];
        foreach ($data as $item) {
            $collection = new ProfessionCollection();
            $collection->push(new AcquiredProfession($scaleName, $this->identifyProfession($item->amount)));
            $professions[$item->userid] = $collection;
        }

        return $professions;
    }

    protected function identifyProfession($amount, $scale = null)
    {
        /**
         * Якщо Бал in (43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60), то «Слідчий», інакше
         * Якщо (Бал in (34,35,36,37,38,39) то «дільничний офіцер» », інакше
         * Якщо (Бал in (40,41,42), то «оперуповноважений» , інакше
         * Якщо (Бал in (0…28,29,30,31,32,33), то «патрульний», інакше «непридатний»))))
         */
        switch (true) {
            case (in_array($amount, [43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60])):
                return self::INQUIRER;
            case ($amount >= 0 && $amount <= 33):
                return self::PATROL;
            case (in_array($amount, [34, 35, 36, 37, 38, 39])):
                return self::DISTRICT_OFFICER;
            case (in_array($amount, [40, 41, 42])):
                return self::OPERATIVE;
            default:
                return self::UNSUITABLE;
        }
    }
}
