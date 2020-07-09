<?php
defined('MOODLE_INTERNAL') || die();

class qtype_amthauer6_grading
{
    protected function calculateResult($amount)
    {
        $scale = [
            '20' => 129,
            '19' => 126,
            '18' => 124,
            '17' => 121,
            '16' => 118,
            '15' => 115,
            '14' => 113,
            '13' => 110,
            '12' => 107,
            '11' => 104,
            '10' => 102,
            '9' => 99,
            '8' => 96,
            '7' => 93,
            '6' => 91,
            '5' => 88,
            '4' => 85,
            '3' => 83,
            '2' => 80,
            '1' => 77,
            '0' => 75,
        ];

        return $scale[$amount];
    }

    /**
     * Grade a specific row.
     * This is the same for all grading methods.
     * Either the student chose the correct response or not (single choice).
     *
     * @param qtype_amthauer6_question $question The question object.
     * @param string $key The field key of the row.
     * @param object $row The row object.
     * @param array $answers The answers array.
     *
     * @return float
     */
    public function grade_row(qtype_amthauer6_question $question, $key, $row, $answers)
    {
        if (!$question->is_answered($answers, $key)) {
            return 0;
        }
        $field = $question->field($key);
        $answercolumn = $answers[$field];

        return $question->is_correct($row, $answercolumn);
    }

    /**
     * Returns the question's grade.
     *
     * @param $question
     * @param $answers
     *
     * @return float|int
     * @see qtype_amthauer6_grading::grade_question()
     */
    public function grade_question($question, $answers)
    {
        $correctRows = 0;
        foreach ($question->order as $key => $rowid) {
            $row = $question->rows[$rowid];
            $grade = $this->grade_row($question, $key, $row, $answers);
            if ($grade > 0) {
                ++$correctRows;
            }
        }

        return $correctRows / count($question->order);
    }

    /**
     * @param $response
     * @param qtype_amthauer6_question $question
     *
     * @throws dml_exception
     */
    public function gradeResponseByScale($response, $question)
    {
        global $USER, $DB;
        $amount = 0;

        foreach ($question->order as $rowid) {
            $row = $question->rows[$rowid];

            $numberOfColumn = ($row->number - 1) > -1 ? ($row->number - 1) : '';
            $indexKey = 'option' . $numberOfColumn;
            $responseColumnNumber = isset($response[$indexKey]) && isset($question->correctAnswers[$row->number]) ? (int)((int)$response[$indexKey] === (int)$question->correctAnswers[$row->number]) : 0;

            if ($responseColumnNumber > 0) {
                $amount++;
            }
        }

        $grade = new stdClass();
        $grade->questionid = $question->id;
        $grade->userid = $USER->id;
        $grade->amount = $amount;
        $grade->value = $this->calculateResult($amount);
        $DB->insert_record('qtype_amthauer6_grades', $grade);
    }
}
