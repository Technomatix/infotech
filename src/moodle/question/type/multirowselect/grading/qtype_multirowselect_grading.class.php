<?php
defined('MOODLE_INTERNAL') || die();

abstract class qtype_multirowselect_grading
{
    abstract protected function gradesTableName();

    abstract protected function calculateResult($question, $response);

    /**
     * @param $response
     * @param qtype_multirowselect_question $question
     *
     * @throws dml_exception
     */
    abstract public function gradeResponseByScale($response, $question);

    public function get_name()
    {
        return self::TYPE;
    }

    public function get_title()
    {
        return get_string('scoring' . self::TYPE, 'qtype_multirowselect');
    }

    /**
     * Grade a specific row.
     * This is the same for all grading methods.
     * Either the student chose the correct response or not (single choice).
     *
     * @param qtype_multirowselect_question $question The question object.
     * @param string $key The field key of the row.
     * @param array $answers The answers array.
     *
     * @return bool
     */
    public function grade_row(qtype_multirowselect_question $question, $key, $answers)
    {
        return $question->is_answered($answers, $key) && $question->is_correct($answers[$question->field($key)]);
    }

    /**
     * Returns the question's grade.
     *
     * @param $question
     * @param $answers
     *
     * @return float|int
     * @see qtype_multirowselect_grading::grade_question()
     */
    public function grade_question($question, $answers)
    {
        $correctRows = 0;
        foreach ($question->order as $key => $rowid) {
            if ($this->grade_row($question, $key, $answers)) {
                ++$correctRows;
            }
        }

        return $correctRows / count($question->order);
    }
}
