<?php
defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/type/multirowselect/lib.php');

/**
 * The multirowselect question type.
 */
class qtype_multirowselect extends question_type
{
    /**
     * Sets the default options for the question.
     *
     * @param qtype_multirowselect_question $question
     *
     * @throws dml_exception
     */
    public function set_default_options($question)
    {
        $multirowselectconfig = get_config('qtype_multirowselect');

        if (!isset($question->options)) {
            $question->options = new stdClass();
        }
        if (!isset($question->options->scoringmethod)) {
            $question->options->scoringmethod = $multirowselectconfig->scoringmethod;
        }
        if (!isset($question->options->numberofrows)) {
            $question->options->numberofrows = QTYPE_MULTIROWSELECT_NUMBER_OF_ROWS;
        }
        if (!isset($question->options->shuffleanswers)) {
            $question->options->shuffleanswers = $multirowselectconfig->shuffleanswers;
        }
        if (!isset($question->options->rows)) {
            $rows = [];
            for ($i = 1; $i <= $question->options->numberofrows; ++$i) {
                $row = new stdClass();
                $row->number = $i;
                $row->optiontext = '';
                $rows[] = $row;
            }
            $question->options->rows = $rows;
        }
    }

    /**
     * Loads the question options, rows from the database.
     *
     * @param qtype_multirowselect_question $question
     *
     * @return bool
     * @throws dml_exception
     */
    public function get_question_options($question)
    {
        global $DB, $OUTPUT;

        parent::get_question_options($question);

        // Retrieve the question options.
        $question->options = $DB->get_record('qtype_multirowselect_options', ['questionid' => $question->id]);
        // Retrieve the question rows (multirowselect options).
        $question->options->rows = array_values($DB->get_records('qtype_multirowselect_rows', ['questionid' => $question->id], 'number ASC', '*', 0, $question->options->numberofrows));

        for ($i = 0; $i < $question->options->numberofrows; $i++) {
            $row = isset($question->options->rows[$i]) ? $question->options->rows[$i] : null;
            $question->{'option_' . ($i + 1)} = $row ? $row->optiontext : '';
        }

        return true;
    }

    /**
     * Stores the question options in the database.
     *
     * @param qtype_multirowselect_question $question
     *
     * @throws dml_exception
     */
    public function save_question_options($question)
    {
        global $DB;

        // Insert all the new options.
        $options = $DB->get_record('qtype_multirowselect_options', ['questionid' => $question->id]);
        if (!$options) {
            $options = new stdClass();
            $options->questionid = $question->id;
            $options->scoringmethod = '';
            $options->shuffleanswers = '';
            $options->numberofrows = '';
            $options->id = $DB->insert_record('qtype_multirowselect_options', $options);
        }

        $options->scoringmethod = $question->scoringmethod;
        $options->shuffleanswers = $question->shuffleanswers;
        $options->numberofrows = $question->numberofrows;
        $DB->update_record('qtype_multirowselect_options', $options);

        // Insert all the new rows.
        $oldRows = $DB->get_records('qtype_multirowselect_rows', ['questionid' => $question->id], 'number ASC');

        if (count($oldRows) > 0 && count($oldRows) !== $options->numberofrows) {
            $DB->delete_records('qtype_multirowselect_rows', ['questionid' => $question->id]);
            $oldRows = [];
        }

        for ($i = 1; $i <= $options->numberofrows; ++$i) {
            $row = array_shift($oldRows);
            if (!$row) {
                $row = new stdClass();
                $row->questionid = $question->id;
                $row->number = $i;
                $row->optiontext = '';

                $row->id = $DB->insert_record('qtype_multirowselect_rows', $row);
            }

            $row->optiontext = isset($_REQUEST['option_' . $i]) ? $_REQUEST['option_' . $i] : '';

            $DB->update_record('qtype_multirowselect_rows', $row);
        }
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
        $question->scoringmethod = $questiondata->options->scoringmethod;
        $question->numberofrows = $questiondata->options->numberofrows;
        $question->rows = $questiondata->options->rows;
    }

    /**
     * Custom method for deleting multirowselect questions.
     *
     * @param $questionid
     * @param $contextid
     *
     * @throws dml_exception
     */
    public function delete_question($questionid, $contextid)
    {
        global $DB;
        $DB->delete_records('qtype_multirowselect_options', ['questionid' => $questionid]);
        $DB->delete_records('qtype_multirowselect_rows', ['questionid' => $questionid]);
        parent::delete_question($questionid, $contextid);
    }
}
