<?php
defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/type/amthauer6/lib.php');

/**
 * The amthauer6 question type.
 */
class qtype_amthauer6 extends question_type
{
    /**
     * Sets the default options for the question.
     *
     * @param qtype_amthauer6_question $question
     * @throws dml_exception
     */
    public function set_default_options($question)
    {
        $amthauer6config = get_config('qtype_amthauer6');

        if (!isset($question->options)) {
            $question->options = new stdClass();
        }
        if (!isset($question->options->numberofrows)) {
            $question->options->numberofrows = QTYPE_AMTHAUER6_NUMBER_OF_OPTIONS;
        }
        if (!isset($question->options->shuffleanswers)) {
            $question->options->shuffleanswers = $amthauer6config->shuffleanswers;
        }
    }

    /**
     * Loads the question options, rows, columns and weights from the database.
     *
     * @param qtype_amthauer6_question $question
     *
     * @return bool
     * @throws dml_exception
     */
    public function get_question_options($question)
    {
        global $DB, $OUTPUT;

        parent::get_question_options($question);

        // Retrieve the question options.
        $question->options = $DB->get_record('qtype_amthauer6_options', ['questionid' => $question->id]);
        return true;
    }

    /**
     * Stores the question options in the database.
     *
     * @param qtype_amthauer6_question $question
     * @throws dml_exception
     */
    public function save_question_options($question)
    {
        global $DB;

        // Insert all the new options.
        $options = $DB->get_record('qtype_amthauer6_options', ['questionid' => $question->id]);
        if (!$options) {
            $options = new stdClass();
            $options->questionid = $question->id;
            $options->shuffleanswers = '';
            $options->numberofrows = '';
            $options->id = $DB->insert_record('qtype_amthauer6_options', $options);
        }

        $options->shuffleanswers = $question->shuffleanswers;
        $options->numberofrows = $question->numberofrows;
        $DB->update_record('qtype_amthauer6_options', $options);
    }

    /**
     * Initialise the common question_definition fields.
     *
     * @param question_definition $question the question_definition we are creating.
     * @param object $questiondata the question data loaded from the database.
     */
    protected function initialise_question_instance(question_definition $question, $questiondata)
    {
        parent::initialise_question_instance($question, $questiondata);

        $question->shuffleanswers = $questiondata->options->shuffleanswers;
        $question->numberofrows = $questiondata->options->numberofrows;
        $question->rows = $this->getQuestionRows();
        $question->correctAnswers = $this->getCorrectAnswers();
    }

    /**
     * Custom method for deleting amthauer6 questions.
     *
     * @param $questionid
     * @param $contextid
     * @throws dml_exception
     */
    public function delete_question($questionid, $contextid)
    {
        global $DB;
        $DB->delete_records('qtype_amthauer6_options', ['questionid' => $questionid]);
        $DB->delete_records('qtype_amthauer6_grades', ['questionid' => $questionid]);
        parent::delete_question($questionid, $contextid);
    }

    private function getQuestionRows()
    {
        $rows = [
            [
                'number' => 1,
                'items' => [
                    '1' => 6,
                    '2' => 9,
                    '3' => 12,
                    '4' => 15,
                    '5' => 18,
                    '6' => 21,
                    '7' => 24,
                ],
            ],
            [
                'number' => 2,
                'items' => [
                    '1' => 16,
                    '2' => 17,
                    '3' => 19,
                    '4' => 20,
                    '5' => 22,
                    '6' => 23,
                    '7' => 25,
                ],
            ],
            [
                'number' => 3,
                'items' => [
                    '1' => 19,
                    '2' => 16,
                    '3' => 22,
                    '4' => 19,
                    '5' => 25,
                    '6' => 22,
                    '7' => 28,
                ],
            ],
            [
                'number' => 4,
                'items' => [
                    '1' => 17,
                    '2' => 13,
                    '3' => 18,
                    '4' => 14,
                    '5' => 19,
                    '6' => 15,
                    '7' => 20,
                ],
            ],
            [
                'number' => 5,
                'items' => [
                    '1' => 4,
                    '2' => 6,
                    '3' => 12,
                    '4' => 14,
                    '5' => 28,
                    '6' => 30,
                    '7' => 60,
                ],
            ],
            [
                'number' => 6,
                'items' => [
                    '1' => 26,
                    '2' => 28,
                    '3' => 25,
                    '4' => 29,
                    '5' => 24,
                    '6' => 30,
                    '7' => 23,
                ],
            ],
            [
                'number' => 7,
                'items' => [
                    '1' => 29,
                    '2' => 26,
                    '3' => 13,
                    '4' => 39,
                    '5' => 36,
                    '6' => 18,
                    '7' => 54,
                ],
            ],
            [
                'number' => 8,
                'items' => [
                    '1' => 21,
                    '2' => 7,
                    '3' => 9,
                    '4' => 12,
                    '5' => 6,
                    '6' => 2,
                    '7' => 4,
                ],
            ],
            [
                'number' => 9,
                'items' => [
                    '1' => 5,
                    '2' => 6,
                    '3' => 4,
                    '4' => 6,
                    '5' => 7,
                    '6' => 5,
                    '7' => 7,
                ],
            ],
            [
                'number' => 10,
                'items' => [
                    '1' => 17,
                    '2' => 15,
                    '3' => 18,
                    '4' => 14,
                    '5' => 19,
                    '6' => 13,
                    '7' => 20,
                ],
            ],
            [
                'number' => 11,
                'items' => [
                    '1' => 279,
                    '2' => 93,
                    '3' => 90,
                    '4' => 30,
                    '5' => 27,
                    '6' => 9,
                    '7' => 6,
                ],
            ],
            [
                'number' => 12,
                'items' => [
                    '1' => 4,
                    '2' => 7,
                    '3' => 8,
                    '4' => 7,
                    '5' => 10,
                    '6' => 11,
                    '7' => 10,
                ],
            ],
            [
                'number' => 13,
                'items' => [
                    '1' => 9,
                    '2' => 12,
                    '3' => 16,
                    '4' => 20,
                    '5' => 25,
                    '6' => 30,
                    '7' => 36,
                ],
            ],
            [
                'number' => 14,
                'items' => [
                    '1' => 5,
                    '2' => 1,
                    '3' => 6,
                    '4' => 2,
                    '5' => 8,
                    '6' => 3,
                    '7' => 11,
                ],
            ],
            [
                'number' => 15,
                'items' => [
                    '1' => 15,
                    '2' => 19,
                    '3' => 22,
                    '4' => 11,
                    '5' => 15,
                    '6' => 18,
                    '7' => 9,
                ],
            ],
            [
                'number' => 16,
                'items' => [
                    '1' => 8,
                    '2' => 11,
                    '3' => 16,
                    '4' => 23,
                    '5' => 32,
                    '6' => 43,
                    '7' => 56,
                ],
            ],
            [
                'number' => 17,
                'items' => [
                    '1' => 9,
                    '2' => 6,
                    '3' => 18,
                    '4' => 21,
                    '5' => 7,
                    '6' => 4,
                    '7' => 12,
                ],
            ],
            [
                'number' => 18,
                'items' => [
                    '1' => 7,
                    '2' => 8,
                    '3' => 10,
                    '4' => 7,
                    '5' => 11,
                    '6' => 16,
                    '7' => 10,
                ],
            ],
            [
                'number' => 19,
                'items' => [
                    '1' => 15,
                    '2' => 6,
                    '3' => 18,
                    '4' => 10,
                    '5' => 30,
                    '6' => 23,
                    '7' => 69,
                ],
            ],
            [
                'number' => 20,
                'items' => [
                    '1' => 3,
                    '2' => 27,
                    '3' => 36,
                    '4' => 4,
                    '5' => 13,
                    '6' => 117,
                    '7' => 126,
                ],
            ],
        ];

        foreach ($rows as &$row) {
            $row = (object)$row;
        }

        return $rows;
    }

    private function getCorrectAnswers()
    {
        return [
            '1' => 27,
            '2' => 26,
            '3' => 25,
            '4' => 16,
            '5' => 62,
            '6' => 31,
            '7' => 51,
            '8' => 7,
            '9' => 8,
            '10' => 12,
            '11' => 2,
            '12' => 13,
            '13' => 42,
            '14' => 4,
            '15' => 13,
            '16' => 71,
            '17' => 15,
            '18' => 17,
            '19' => 63,
            '20' => 14,
        ];
    }
}
