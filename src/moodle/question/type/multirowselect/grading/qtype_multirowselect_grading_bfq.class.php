<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/multirowselect/grading/qtype_multirowselect_grading.class.php');

class qtype_multirowselect_grading_bfq extends qtype_multirowselect_grading
{
    const TYPE = 'bfq';
    const FOR_CALCULATE_ADDITIONAL_VALUE = 6;

    protected function gradesTableName()
    {
        return 'qtype_bfq_grades';
    }

    protected function getSelectedValue($question, $response, $number)
    {
        $field = $question->field($number);

        return isset($response[$field]) && $response[$field] > 0 ? $response[$field] : 0;
    }

    protected function getCalculatedRowsByScale($scale)
    {
        $rows = [
            '2' => [2, 7, 12, 17, 22, 27, 32, 37, 42,],
            '3' => [3, 8, 13, 18, 23, 28, 33, 38, 43,],
            '5' => [5, 10, 15, 20, 25, 30, 35, 40, 41, 44,],
        ];

        return isset($rows[$scale]) ? $rows[$scale] : [];
    }

    protected function getAdditionalCalculatedRowsByScale($scale)
    {
        $rows = [
            '2' => [2, 12, 27, 37,],
            '3' => [8, 18, 23, 43,],
            '5' => [35, 41,],
        ];

        return isset($rows[$scale]) ? $rows[$scale] : [];
    }

    private function calculateAdditionalValue($value)
    {
        $value = (int)$value;
        $value = $value === 4 ? 1 : $value;
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
         * 2 Поступливість
         * (Вибір (Якщо «Оцінка»=1 то 5 інакше (Якщо «Оцінка»=2 то 4 інакше (Якщо «Оцінка»=4  то 5 інакше (Якщо «Оцінка»=5 то 1 інакше 3))))для «№ твердження»=2) +
         * (Вибір «Оцінка» для «№ твердження»=7) +
         * (Вибір (Якщо «Оцінка»=1 то 5 інакше (Якщо «Оцінка»=2 то 4 інакше (Якщо «Оцінка»=4  то 5 інакше (Якщо «Оцінка»=5 то 1 інакше 3))))для «№ твердження»=12) +
         * (Вибір «Оцінка» для «№ твердження»=17) +
         * (Вибір «Оцінка» для «№ твердження»=22) +
         * (Вибір (Якщо «Оцінка»=1 то 5 інакше (Якщо «Оцінка»=2 то 4 інакше (Якщо «Оцінка»=4  то 5 інакше (Якщо «Оцінка»=5 то 1 інакше 3))))для «№ твердження»=27) +
         * (Вибір «Оцінка» для «№ твердження»=32) +
         * (Вибір (Якщо «Оцінка»=1 то 5 інакше (Якщо «Оцінка»=2 то 4 інакше (Якщо «Оцінка»=4  то 5 інакше (Якщо «Оцінка»=5 то 1 інакше 3))))для «№ твердження»=37) +
         * (Вибір «Оцінка» для «№ твердження»=42)
         *
         * 3 Сумлінність
         * (Вибір «Оцінка» для «№ твердження»=3) +
         * (Вибір (Якщо «Оцінка»=1 то 5 інакше (Якщо «Оцінка»=2 то 4 інакше (Якщо «Оцінка»=4  то 5 інакше (Якщо «Оцінка»=5 то 1 інакше 3))))для «№ твердження»=8) +
         * (Вибір «Оцінка» для «№ твердження»=13) +
         * (Вибір (Якщо «Оцінка»=1 то 5 інакше (Якщо «Оцінка»=2 то 4 інакше (Якщо «Оцінка»=4  то 5 інакше (Якщо «Оцінка»=5 то 1 інакше 3))))для «№ твердження»=18) +
         * (Вибір (Якщо «Оцінка»=1 то 5 інакше (Якщо «Оцінка»=2 то 4 інакше (Якщо «Оцінка»=4  то 5 інакше (Якщо «Оцінка»=5 то 1 інакше 3))))для «№ твердження»=23) +
         * (Вибір «Оцінка» для «№ твердження»=28) +
         * (Вибір «Оцінка» для «№ твердження»=33) +
         * (Вибір «Оцінка» для «№ твердження»=38) +
         * (Вибір (Якщо «Оцінка»=1 то 5 інакше (Якщо «Оцінка»=2 то 4 інакше (Якщо «Оцінка»=4  то 5 інакше (Якщо «Оцінка»=5 то 1 інакше 3))))для «№ твердження»=43)
         *
         * 5 Відкритість досвіду
         * (Вибір «Оцінка» для «№ твердження»=5) +
         * (Вибір «Оцінка» для «№ твердження»=10) +
         * (Вибір «Оцінка» для «№ твердження»=15) +
         * (Вибір «Оцінка» для «№ твердження»=20) +
         * (Вибір «Оцінка» для «№ твердження»=25) +
         * (Вибір «Оцінка» для «№ твердження»=30) +
         * (Вибір (Якщо «Оцінка»=1 то 5 інакше (Якщо «Оцінка»=2 то 4 інакше (Якщо «Оцінка»=4  то 5 інакше (Якщо «Оцінка»=5 то 1 інакше 3))))для «№ твердження»=35) +
         * (Вибір «Оцінка» для «№ твердження»=40) +
         * (Вибір (Якщо «Оцінка»=1 то 5 інакше (Якщо «Оцінка»=2 то 4 інакше (Якщо «Оцінка»=4  то 5 інакше (Якщо «Оцінка»=5 то 1 інакше 3))))для «№ твердження»=41) +
         * (Вибір «Оцінка» для «№ твердження»=44)
         */
        $scales = ['2', '3', '5',];
        $amount = [];
        foreach ($question->order as $key => $rowid) {
            $row = $question->rows[$rowid];
            foreach ($scales as $scale) {
                $amount[$scale] = 0;
                $calculateRows = $this->getCalculatedRowsByScale($scale);
                $additionalCalculateRows = $this->getAdditionalCalculatedRowsByScale($scale);
                if (in_array($row->number, $calculateRows)) {
                    $value = $this->getSelectedValue($question, $response, $key);
                    $additionalValue = (in_array($row->number, $additionalCalculateRows)) ? $this->calculateAdditionalValue($value) : $value;
                    $amount[$scale] += $additionalValue;
                }
            }
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

        foreach ($this->calculateResult($question, $response) as $scale => $amount) {
            $grade = new stdClass();
            $grade->questionid = $question->id;
            $grade->userid = $USER->id;
            $grade->scale = $scale;
            $grade->amount = $amount;
            $DB->insert_record($this->gradesTableName(), $grade);
        }
    }
}
