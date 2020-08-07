<?php

namespace report_mocoquizaptitude\data;

use report_mocoquizaptitude\collection\item\AcquiredProfession;
use local_moco_library\collection\SimpleCollectionItem;
use local_moco_library\db\Helper;
use report_mocoquizaptitude\collection\ProfessionCollection;
use report_mocoquizaptitude\data\structures\templates\BaseTemplate;

abstract class Structure implements SimpleCollectionItem
{
    const INQUIRER = 'Слідчий';
    const OPERATIVE = 'Оперуповноважений';
    const DISTRICT_OFFICER = 'Дільничний офіцер';
    const PATROL = 'Патрульний';
    const UNSUITABLE = 'Непридатний';
    const SUITABLE = 'Придатний';

    /** @var BaseTemplate */
    protected $template;

    /** @var string  moodle format (without table prefix) */
    protected $tableName;

    /** @var string */
    protected $scoringMethod;

    /** @var string */
    protected $scale;

    /** @var array */
    protected $scales = [];

    public function __construct(BaseTemplate $template)
    {
        $this->template = $template;
    }

    /**
     * @param \moodle_database $db
     * @param int $courseId
     * @param array $userIds
     *
     * @return ProfessionCollection[]
     * @throws \dml_exception
     */
    public function getData(\moodle_database $db, $courseId, $userIds = [])
    {
        return $this->getDataWithScales($db, $courseId, $userIds);
    }

    /**
     * @param int $amount
     * @param string $scale
     *
     * @return string
     */
    abstract protected function identifyProfession($amount, $scale = null);

    protected function buildQueryWhereParams(&$where, &$params, $courseId, $userIds = [])
    {
        $dbHelper = Helper::getInstance();

        $dbHelper->addWhereInParams($where, $params, 'q.course', $courseId);
        $this->scoringMethod && $dbHelper->addWhereInParams($where, $params, 'grade.scoringmethod', $this->scoringMethod);
        count($userIds) && $dbHelper->addWhereInParams($where, $params, 'grade.userid', $userIds);
        //Если шкал нет то массив $this->scales содержит один элемент с ключем 'none' иначе ищем по ключам массива
        !(count($this->scales) === 1 && !empty($this->scales['none'])) && $dbHelper->addWhereInParams($where, $params, 'grade.scale', array_keys($this->scales));
    }

    /**
     * @param \moodle_database $db
     * @param int $courseId
     * @param array $userIds
     *
     * @return ProfessionCollection[]
     * @throws \dml_exception
     */
    protected function getDataWithScales(\moodle_database $db, $courseId, $userIds = [])
    {
        $params = [];
        $where = [];

        $this->buildQueryWhereParams($where, $params, $courseId, $userIds);

        $sql = 'SELECT g.id, g.userid, g.scale, g.amount FROM {' . $this->getTableName() . '} AS g WHERE g.id IN (' . $this->getSubQuery($where) . ') ORDER BY g.userid, g.scale';

        return $this->makeProfessionsWithScales($db->get_records_sql($sql, $params));
    }

    /**
     * @param array $data
     *
     * @return ProfessionCollection[]
     */
    public function makeProfessionsWithScales(array $data)
    {
        $usersRows = $this->prepareDataWithScales($data);

        $professions = [];
        foreach ($usersRows as $userId => $rows) {
            $collection = new ProfessionCollection();
            foreach ($rows as $row) {
                $collection->push(new AcquiredProfession($this->scales[$row->scale], $this->identifyProfession($row->amount, $row->scale)));
            }
            $professions[$userId] = $collection;
        }

        return $professions;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function prepareDataWithScales($data)
    {
        $usersRows = [];
        foreach ($data as $row) {
            $userId = $row->userid;
            if (!isset($usersRows[$userId])) {
                $usersRows[$userId] = [];
            }
            $usersRows[$userId][] = $row;
        }

        return $usersRows;
    }

    protected function getSubQuery($where = [])
    {
        $where = count($where) ? $where : ['TRUE'];
        $groupBy = [];
        $groupBy[] = 'grade.userid';
        $groupBy[] = (count($this->scales) === 1 && !empty($this->scales['none'])) ? false : 'grade.scale';

        return 'SELECT max(grade.id) from {' . $this->getTableName() . '} AS grade
                JOIN {quiz_slots} AS s ON s.questionid = grade.questionid
                JOIN {quiz} AS q ON q.id = s.quizid
                WHERE ' . implode(' AND ', $where) . '
                GROUP BY ' . implode(', ', array_filter($groupBy));
    }

    /**
     * @return array
     */
    public function getScales()
    {
        return $this->scales;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @return BaseTemplate
     */
    public function getTemplate()
    {
        return $this->template;
    }
}
