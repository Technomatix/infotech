<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/memorize/grading/qtype_memorize_grading.class.php');

class qtype_memorize_grading_ten_words extends qtype_memorize_grading
{
    const TYPE = 'ten_words';

    /**
     * @param $response
     * @param qtype_memorize_question $question
     *
     * @throws dml_exception
     */
    public function gradeResponseByScale($response, $question)
    {
        global $USER, $DB;
        $amount = $this->getCorrectRowCnt($question, $response);

        $grade = new stdClass();
        $grade->questionid = $question->id;
        $grade->userid = $USER->id;
        $grade->scoringmethod = $question->scoringmethod;
        //$grade->scale = $scale;
        $grade->amount = $amount;
        $DB->insert_record($this->gradesTableName(), $grade);
    }
}
