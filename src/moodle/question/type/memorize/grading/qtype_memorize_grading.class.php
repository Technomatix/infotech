<?php
defined('MOODLE_INTERNAL') || die();

abstract class qtype_memorize_grading
{
    protected function gradesTableName()
    {
        return 'qtype_memorize_grades';
    }

    public function get_name()
    {
        return self::TYPE;
    }

    public function get_title()
    {
        return get_string('scoring' . self::TYPE, 'qtype_memorize');
    }

    protected function getCorrectRowCnt(qtype_memorize_question $question, $answers)
    {
        $rows = array_map(function($row){
            return mb_strtolower(trim($row->optiontext));
        }, array_filter($question->rows, function($row){
            return !empty($row->optiontext);
        }));

        $answerRows = array_map(function($row){
            return mb_strtolower(trim($row));
        }, array_filter($answers, function($row){
            return !empty(trim($row));
        }));

        $answerRows = array_unique($answerRows, SORT_LOCALE_STRING);

        $correctRows = 0;
        foreach($answerRows as $answerRow){
            if(in_array($answerRow, $rows)){
                ++$correctRows;
            }
        }

        return $correctRows;
    }

    /**
     * Grade a specific row.
     * This is the same for all grading methods.
     * Either the student chose the correct response or not (single choice).
     *
     * @param qtype_memorize_question $question The question object.
     * @param string $key The field key of the row.
     * @param object $row
     * @param array $answers The answers array.
     *
     * @return bool
     */
    public function grade_row(qtype_memorize_question $question, $key, $row, $answers)
    {
        return $question->is_answered($answers, $key);
    }

    /**
     * Returns the question's grade.
     *
     * @param $question
     * @param $answers
     *
     * @return float|int
     * @see qtype_memorize_grading::grade_question()
     */
    public function grade_question(qtype_memorize_question $question, $answers)
    {
        return $this->getCorrectRowCnt($question, $answers) / count($question->order);
    }
}
