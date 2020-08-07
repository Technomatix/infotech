<?php

namespace report_mocoquizaptitude\data\structures;

use local_moco_library\db\Helper;
use report_mocoquizaptitude\data\Structure;
use report_mocoquizaptitude\data\structures\templates\AmthauerTemplate;

class Amthauer extends Structure
{
    public static function getInstance()
    {
        return new self(new AmthauerTemplate());
    }

    protected $tableName = 'qtype_amthauer_grades';

    protected $tableName6 = 'qtype_amthauer6_grades';

    protected $scales = [
        '2' => 'Субтест 2',
        '3' => 'Субтест 3',
        '6' => 'Субтест 6',
    ];

    public function getData(\moodle_database $db, $courseId, $userIds = [])
    {
        $subQueryWhere = [];
        $subQueryParams = [];
        $subQuery6Where = [];
        $subQuery6Params = [];

        $dbHelper = Helper::getInstance();

        $dbHelper->addWhereInParams($subQueryWhere, $subQueryParams, 'q.course', $courseId);
        $dbHelper->addWhereInParams($subQuery6Where, $subQuery6Params, 'q.course', $courseId, 'amt6');
        $dbHelper->addWhereInParams($subQueryWhere, $subQueryParams, 'o.scoringmethod', ["scoringsub2", "scoringsub3"]);
        count($userIds) && $dbHelper->addWhereInParams($subQueryWhere, $subQueryParams, 'grade.userid', $userIds);
        count($userIds) && $dbHelper->addWhereInParams($subQuery6Where, $subQuery6Params, 'grade.userid', $userIds, 'amt6');
        $this->scoringMethod && $dbHelper->addWhereInParams($subQueryWhere, $subQueryParams, 'grade.scoringmethod', $this->scoringMethod);


        $sql = 'SELECT concat("23", g.id), g.userid, if(op.scoringmethod = "scoringsub2", "2", "3") AS scale, g.value as amount
                FROM {' . $this->getTableName() . '} AS g
                JOIN {qtype_amthauer_options} AS op ON op.questionid = g.questionid
                WHERE g.id IN (' . $this->getSubQuery($subQueryWhere) . ')
                UNION
                SELECT CONCAT("6", g.id), g.userid, "6" AS scale, g.value as amount
                FROM {' . $this->tableName6 . '} AS g
                WHERE g.id IN (' . $this->getSubQueryFor6($subQuery6Where) . ')';

        return $this->makeProfessionsWithScales($db->get_records_sql($sql, array_merge($subQueryParams, $subQuery6Params)));
    }

    protected function getSubQueryFor6($where)
    {
        $where = count($where) ? $where : ['TRUE'];

        $groupBy = [];
        $groupBy[] = 'grade.userid';

        return 'SELECT max(grade.id) from {' . $this->tableName6 . '} AS grade
                JOIN {quiz_slots} AS s ON s.questionid = grade.questionid
                JOIN {quiz} AS q ON q.id = s.quizid
                WHERE ' . implode(' AND ', $where) . '
                GROUP BY ' . implode(', ', array_filter($groupBy));
    }

    protected function getSubQuery($where = [])
    {
        $where = count($where) ? $where : ['TRUE'];

        $groupBy = [];
        $groupBy[] = 'grade.userid';
        $groupBy[] = 'o.scoringmethod';

        return 'SELECT max(grade.id) from {' . $this->getTableName() . '} AS grade
                JOIN {qtype_amthauer_options} AS o ON o.questionid = grade.questionid
                JOIN {quiz_slots} AS s ON s.questionid = grade.questionid
                JOIN {quiz} AS q ON q.id = s.quizid
                WHERE ' . implode(' AND ', $where) . '
                GROUP BY ' . implode(', ', array_filter($groupBy));
    }

    protected function identifyProfession($amount, $scale = null)
    {
        /**
         * Якщо Показник IQ in (86,87,88,89,90) то «патрульний» інакше
         * Якщо Показник IQ in (91,92,93,94,95,96,97,98,99,100,101,102,103,104,105) то «дільничний офіцер» інакше
         * Якщо Показник IQ in (106,107,108,109,110,111,112) то «оперуповноважений» інакше
         * Якщо Показник IQ >112 то «Слідчий» інакше «непридатний»
         */
        switch (true) {
            case ($amount > 112):
                return self::INQUIRER;
            case (in_array($amount, [106, 107, 108, 109, 110, 111, 112])):
                return self::OPERATIVE;
            case (in_array($amount, [91, 92, 93, 94, 95, 96, 97, 98, 99, 100, 101, 102, 103, 104, 105])):
                return self::DISTRICT_OFFICER;
            case (in_array($amount, [86, 87, 88, 89, 90])):
                return self::PATROL;
            default:
                return self::UNSUITABLE;
        }
    }
}
