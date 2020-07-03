<?php

/**
 * @package     qtype_amthauer
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

abstract class qtype_amthauer_grading
{
    abstract protected function calculateResult($amount);

    public function get_name()
    {
        return self::TYPE;
    }

    public function get_title()
    {
        return get_string('scoring' . self::TYPE, 'qtype_amthauer');
    }

    /**
     * Grade a specific row.
     * This is the same for all grading methods.
     * Either the student chose the correct response or not (single choice).
     *
     * @param qtype_amthauer_question $question The question object.
     * @param string $key The field key of the row.
     * @param object $row The row object.
     * @param array $answers The answers array.
     *
     * @return float
     */
    public function grade_row(qtype_amthauer_question $question, $key, $row, $answers)
    {
        if (!$question->is_answered($answers, $key)) {
            return 0;
        }
        $field = $question->field($key);
        $answercolumn = $answers[$field];
        if ($question->is_correct($row, $answercolumn)) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * Returns the question's grade.
     *
     * @param $question
     * @param $answers
     *
     * @return float|int
     * @see qtype_amthauer_grading::grade_question()
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
     * @param qtype_amthauer_question $question
     *
     * @throws dml_exception
     */
    public function gradeResponseByScale($response, $question)
    {
        global $USER, $DB;
        $amount = 0;

        foreach ($question->order as $rowid) {
            $row = $question->rows[$rowid];

            foreach ($question->columns as $column) {
                if ($column->rowid !== $row->number) {
                    continue;
                }
                $numberOfColumn = ($row->number - 1) > -1 ? ($row->number - 1) : '';
                $indexKey = 'option' . $numberOfColumn;
                $responseColumnNumber = isset($response[$indexKey]) ? (int)$response[$indexKey] : 0;
                if (!$responseColumnNumber || (int)$column->number !== $responseColumnNumber) {
                    continue;
                }
                $weight = $question->weight($row, $column);
                if ($weight > 0) {
                    $amount++;
                }
            }
        }

        $grade = new stdClass();
        $grade->questionid = $question->id;
        $grade->userid = $USER->id;
        $grade->amount = $amount;
        $grade->value = $this->calculateResult($amount);
        $DB->insert_record('qtype_amthauer_grades', $grade);
    }
}
