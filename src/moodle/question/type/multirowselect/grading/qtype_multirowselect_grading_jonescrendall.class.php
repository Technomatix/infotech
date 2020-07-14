<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/multirowselect/grading/qtype_multirowselect_grading.class.php');

class qtype_multirowselect_grading_jonescrendall extends qtype_multirowselect_grading
{
    const TYPE = 'jonescrendall';
    const FOR_CALCULATE_ADDITIONAL_VALUE = 5;

    protected function gradesTableName()
    {
        return 'qtype_jonescrendall_grades';
    }

    protected function getSelectedValue($question, $response, $number)
    {
        $field = $question->field($number);

        return isset($response[$field]) && $response[$field] > 0 ? $response[$field] : 0;
    }

    protected function getAdditionalCalculatedRows()
    {
        return [2, 5, 6, 8, 9, 11, 13, 14,];
    }

    private function calculateAdditionalValue($value)
    {
        $value = (int)$value;
        $result = self::FOR_CALCULATE_ADDITIONAL_VALUE - $value;

        return $value === 0 ? 0 : $result;
    }

    /**
     * @param qtype_multirowselect_question $question
     * @param $response
     *
     * @return mixed
     */
    protected function calculateResult($question, $response)
    {
        /**
         * (Вибір «Оцінка» для «№ твердження»=1) +
         * (Вибір (Якщо «Оцінка»=1 то 4 інакше (Якщо «Оцінка»=2 то 3 інакше (Якщо «Оцінка»=3  то 2 інакше 1))) для «№ твердження»=2) +
         * (Вибір «Оцінка» для «№ твердження»=3)+
         * (Вибір «Оцінка» для «№ твердження»=4)+
         * (Вибір (Якщо «Оцінка»=1 то 4 інакше (Якщо «Оцінка»=2 то 3 інакше (Якщо «Оцінка»=3  то 2 інакше 1))) для «№ твердження»=5) +
         * (Вибір (Якщо «Оцінка»=1 то 4 інакше (Якщо «Оцінка»=2 то 3 інакше (Якщо «Оцінка»=3  то 2 інакше 1))) для «№ твердження»=6) +
         * (Вибір «Оцінка» для «№ твердження»=7)+
         * (Вибір (Якщо «Оцінка»=1 то 4 інакше (Якщо «Оцінка»=2 то 3 інакше (Якщо «Оцінка»=3  то 2 інакше 1))) для «№ твердження»=8) +
         * (Вибір (Якщо «Оцінка»=1 то 4 інакше (Якщо «Оцінка»=2 то 3 інакше (Якщо «Оцінка»=3  то 2 інакше 1))) для «№ твердження»=9) +
         * (Вибір «Оцінка» для «№ твердження»=10)+
         * (Вибір (Якщо «Оцінка»=1 то 4 інакше (Якщо «Оцінка»=2 то 3 інакше (Якщо «Оцінка»=3  то 2 інакше 1))) для «№ твердження»=11) +
         * (Вибір «Оцінка» для «№ твердження»=12)+
         * (Вибір (Якщо «Оцінка»=1 то 4 інакше (Якщо «Оцінка»=2 то 3 інакше (Якщо «Оцінка»=3  то 2 інакше 1))) для «№ твердження»=13) +
         * (Вибір (Якщо «Оцінка»=1 то 4 інакше (Якщо «Оцінка»=2 то 3 інакше (Якщо «Оцінка»=3  то 2 інакше 1))) для «№ твердження»=14) +
         * (Вибір «Оцінка» для «№ твердження»=15)
         */
        $amount = 0;
        foreach ($question->order as $key => $rowid) {
            $row = $question->rows[$rowid];

            $value = $this->getSelectedValue($question, $response, $key);
            $additionalValue = (in_array($row->number, $this->getAdditionalCalculatedRows())) ? $this->calculateAdditionalValue($value) : $value;
            $amount += $additionalValue;
        }

        return $amount;
    }

    /**
     * @param $response
     * @param qtype_multirowselect_question $question
     *
     * @throws dml_exception
     */
    public function gradeResponseByScale($response, $question)
    {
        global $USER, $DB;

        $grade = new stdClass();
        $grade->questionid = $question->id;
        $grade->userid = $USER->id;
        $grade->amount = $this->calculateResult($question, $response);
        $DB->insert_record($this->gradesTableName(), $grade);
    }
}
