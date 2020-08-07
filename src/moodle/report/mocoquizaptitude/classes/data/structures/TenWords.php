<?php

namespace report_mocoquizaptitude\data\structures;

use report_mocoquizaptitude\collection\item\AcquiredProfession;
use report_mocoquizaptitude\collection\ProfessionCollection;
use report_mocoquizaptitude\data\Structure;
use report_mocoquizaptitude\data\structures\templates\TenWordsTemplate;

class TenWords extends Structure
{
    public static function getInstance()
    {
        return new self(new TenWordsTemplate());
    }

    protected $tableName = 'qtype_memorize_grades';

    protected $scoringMethod = 'ten_words';

    protected $scales = [
        'none' => 'пам\'ять',
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
         * Якщо Бал >=9, то «Слідчий», інакше Якщо (Бал =8 то «оперуповноважений» », інакше Якщо (Бал in (6,7), то «дільничний офіцер» , інакше Якщо (Бал =5, то «патрульний», інакше «непридатний»)))
         */
        $resultVariant = [
            0 => self::UNSUITABLE,
            1 => self::UNSUITABLE,
            2 => self::UNSUITABLE,
            3 => self::UNSUITABLE,
            4 => self::UNSUITABLE,
            5 => self::PATROL,
            6 => self::DISTRICT_OFFICER,
            7 => self::DISTRICT_OFFICER,
            8 => self::OPERATIVE,
            9 => self::INQUIRER,
            10 => self::INQUIRER,
        ];

        return !empty($resultVariant[$amount]) ? $resultVariant[$amount] : '';
    }
}
