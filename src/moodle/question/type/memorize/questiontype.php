<?php
defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/type/memorize/lib.php');

/**
 * The memorize question type.
 */
class qtype_memorize extends question_type
{
    /**
     * Sets the default options for the question.
     *
     * @param qtype_memorize_question $question
     *
     * @throws dml_exception
     */
    public function set_default_options($question)
    {
        $memorizeconfig = get_config('qtype_memorize');

        if (!isset($question->options)) {
            $question->options = new stdClass();
        }
        if (!isset($question->options->scoringmethod)) {
            $question->options->scoringmethod = $memorizeconfig->scoringmethod;
        }
        if (!isset($question->options->numberofrows)) {
            $question->options->numberofrows = QTYPE_MEMORIZE_NUMBER_OF_ROWS;
        }
        if (!isset($question->options->expectedrows)) {
            $question->options->expectedrows = QTYPE_MEMORIZE_EXPECTED_ROWS;
        }
        if (!isset($question->options->shuffleanswers)) {
            $question->options->shuffleanswers = $memorizeconfig->shuffleanswers;
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
     * @param qtype_memorize_question $question
     *
     * @return bool
     * @throws dml_exception
     */
    public function get_question_options($question)
    {
        global $DB;

        parent::get_question_options($question);

        $question->options = $DB->get_record('qtype_memorize_options', ['questionid' => $question->id]);

        $question->options->rows = array_values($DB->get_records('qtype_memorize_rows', ['questionid' => $question->id], 'number ASC', '*', 0, $question->options->numberofrows));

        for ($i = 0; $i < $question->options->numberofrows; $i++) {
            $row = isset($question->options->rows[$i]) ? $question->options->rows[$i] : null;
            $question->{'option_' . ($i + 1)} = $row ? $row->optiontext : '';
        }

        return true;
    }

    /**
     * Stores the question options in the database.
     *
     * @param qtype_memorize_question $question
     *
     * @throws dml_exception
     */
    public function save_question_options($question)
    {
        global $DB;

        // Insert all the new options.
        $options = $DB->get_record('qtype_memorize_options', ['questionid' => $question->id]);
        if (!$options) {
            $options = new stdClass();
            $options->questionid = $question->id;
            $options->scoringmethod = '';
            $options->shuffleanswers = '';
            $options->expectedrows = '';
            $options->numberofrows = '';
            $options->id = $DB->insert_record('qtype_memorize_options', $options);
        }

        $options->scoringmethod = $question->scoringmethod;
        $options->shuffleanswers = $question->shuffleanswers;
        $options->numberofrows = $question->numberofrows;
        $options->expectedrows = $question->expectedrows;
        $DB->update_record('qtype_memorize_options', $options);

        // Insert all the new rows.
        $oldRows = $DB->get_records('qtype_memorize_rows', ['questionid' => $question->id], 'number ASC');

        for ($i = 1; $i <= $options->numberofrows; ++$i) {
            $row = array_shift($oldRows);
            if (!$row) {
                $row = new stdClass();
                $row->questionid = $question->id;
                $row->number = $i;
                $row->optiontext = '';

                $row->id = $DB->insert_record('qtype_memorize_rows', $row);
            }

            $row->optiontext = isset($_REQUEST['option_' . $i]) ? $_REQUEST['option_' . $i] : '';

            $DB->update_record('qtype_memorize_rows', $row);
        }
    }

    protected function initialise_question_instance(question_definition $question, $questiondata)
    {
        parent::initialise_question_instance($question, $questiondata);

        $question->shuffleanswers = $questiondata->options->shuffleanswers;
        $question->scoringmethod = $questiondata->options->scoringmethod;
        $question->numberofrows = $questiondata->options->numberofrows;
        $question->expectedrows = $questiondata->options->expectedrows;
        $question->rows = $questiondata->options->rows;
    }

    public function delete_question($questionid, $contextid)
    {
        global $DB;
        $DB->delete_records('qtype_memorize_options', ['questionid' => $questionid]);
        $DB->delete_records('qtype_memorize_rows', ['questionid' => $questionid]);
        parent::delete_question($questionid, $contextid);
    }
}
