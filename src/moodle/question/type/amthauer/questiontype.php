<?php

/**
 * @package     qtype_amthauer
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/type/amthauer/lib.php');

/**
 * The amthauer question type.
 */
class qtype_amthauer extends question_type
{
    /**
     * Sets the default options for the question.
     *
     * @param qtype_amthauer_question $question
     * @throws dml_exception
     */
    public function set_default_options($question)
    {
        $amthauerconfig = get_config('qtype_amthauer');

        if (!isset($question->options)) {
            $question->options = new stdClass();
        }
        if (!isset($question->options->numberofrows)) {
            $question->options->numberofrows = QTYPE_AMTHAUER_NUMBER_OF_OPTIONS;
        }
        if (!isset($question->options->numberofcolumns)) {
            $question->options->numberofcolumns = QTYPE_AMTHAUER_NUMBER_OF_RESPONSES;
        }
        if (!isset($question->options->shuffleanswers)) {
            $question->options->shuffleanswers = $amthauerconfig->shuffleanswers;
        }
        if (!isset($question->options->scoringmethod)) {
            $question->options->scoringmethod = $amthauerconfig->scoringmethod;
        }
        if (!isset($question->options->rows)) {
            $rows = [];
            for ($i = 1; $i <= $question->options->numberofrows; ++$i) {
                $row = new stdClass();
                $row->number = $i;
                $row->optiontext = '';
                $row->optiontextformat = FORMAT_HTML;
                $row->optionfeedback = '';
                $row->optionfeedbackformat = FORMAT_HTML;
                $rows[] = $row;
            }
            $question->options->rows = $rows;
        }

        if (!isset($question->options->columns)) {
            $columns = [];
            for ($i = 1; $i <= $question->options->numberofrows; ++$i) {
                for ($j = 1; $j <= $question->options->numberofcolumns; ++$j) {
                    $column = new stdClass();
                    $column->rowid = $i;
                    $column->number = $j;
                    $column->responsetext = '';
                    $column->responsetextformat = FORMAT_MOODLE;
                    $columns[] = $column;
                }
            }
            $question->options->columns = $columns;
        }
    }

    /**
     * Loads the question options, rows, columns and weights from the database.
     *
     * @param qtype_amthauer_question $question
     *
     * @return bool
     * @throws dml_exception
     */
    public function get_question_options($question)
    {
        global $DB, $OUTPUT;

        parent::get_question_options($question);

        // Retrieve the question options.
        $question->options = $DB->get_record('qtype_amthauer_options', ['questionid' => $question->id]);
        // Retrieve the question rows (amthauer options).
        $question->options->rows = $DB->get_records('qtype_amthauer_rows', ['questionid' => $question->id], 'number ASC', '*', 0, $question->options->numberofrows);
        // Retrieve the question columns.
        $question->options->columns = $DB->get_records('qtype_amthauer_columns', ['questionid' => $question->id], 'number ASC', '*', 0, $question->options->numberofrows * $question->options->numberofcolumns);

        $weightrecords = $DB->get_records('qtype_amthauer_weights', ['questionid' => $question->id], 'rownumber ASC, columnnumber ASC');

        foreach ($question->options->rows as $key => $row) {
            $question->{'option_' . $row->number} = $row->optiontext;
            $question->{'feedback_' . $row->number}['text'] = $row->optionfeedback;
            $question->{'feedback_' . $row->number}['format'] = $row->optionfeedbackformat;
        }

        foreach ($question->options->columns as $key => $column) {
            $question->{'option_column_' . $column->rowid . '_' . $column->number} = $column->responsetext;
        }

        foreach ($weightrecords as $key => $weight) {
            if ($weight->weight == 1.0) {
                $question->{'weightbutton_' . $weight->rownumber} = $weight->columnnumber;
            }
        }
        // Put the weight records into an array indexed by rownumber and columnnumber.
        $question->options->weights = $this->weight_records_to_array($weightrecords);

        return true;
    }

    /**
     * Stores the question options in the database.
     *
     * @param qtype_amthauer_question $question
     * @throws dml_exception
     */
    public function save_question_options($question)
    {
        global $DB;

        $context = $question->context;
        $result = new stdClass();

        // Insert all the new options.
        $options = $DB->get_record('qtype_amthauer_options', ['questionid' => $question->id]);
        if (!$options) {
            $options = new stdClass();
            $options->questionid = $question->id;
            $options->scoringmethod = '';
            $options->shuffleanswers = '';
            $options->numberofcolumns = '';
            $options->numberofrows = '';
            $options->id = $DB->insert_record('qtype_amthauer_options', $options);
        }

        $options->scoringmethod = $question->scoringmethod;
        $options->shuffleanswers = $question->shuffleanswers;
        $options->numberofrows = $question->numberofrows;
        $options->numberofcolumns = $question->numberofcolumns;
        $DB->update_record('qtype_amthauer_options', $options);

        // Insert all the new rows.
        $oldrows = $DB->get_records('qtype_amthauer_rows', ['questionid' => $question->id], 'number ASC');

        for ($i = 1; $i <= $options->numberofrows; ++$i) {
            $row = array_shift($oldrows);
            if (!$row) {
                $row = new stdClass();
                $row->questionid = $question->id;
                $row->number = $i;
                $row->optiontext = '';
                $row->optiontextformat = FORMAT_HTML;
                $row->optionfeedback = '';
                $row->optionfeedbackformat = FORMAT_HTML;

                $row->id = $DB->insert_record('qtype_amthauer_rows', $row);
            }

            $row->optiontext = $question->{'option_' . $i};

            $DB->update_record('qtype_amthauer_rows', $row);
        }

        $rows = $DB->get_records('qtype_amthauer_rows', ['questionid' => $question->id], 'number ASC');
        $oldcolumns = $DB->get_records('qtype_amthauer_columns', ['questionid' => $question->id], 'number ASC');

        foreach($rows as $row) {
            // Insert all new columns.
            for ($i = 1; $i <= $options->numberofcolumns; ++$i) {
                $column = array_shift($oldcolumns);
                if (!$column) {
                    $column = new stdClass();
                    $column->questionid = $question->id;
                    $column->rowid = $row->number;
                    $column->number = $i;
                    $column->responsetext = '';
                    $column->responsetextformat = FORMAT_MOODLE;

                    $column->id = $DB->insert_record('qtype_amthauer_columns', $column);
                }

                // Perform an update.
                $column->responsetext = $question->{'option_column_' . $column->rowid . '_' . $column->number};
                $column->responsetextformat = FORMAT_MOODLE;
                $DB->update_record('qtype_amthauer_columns', $column);
            }
        }

        // Set all the new weights.
        $oldweightrecords = $DB->get_records('qtype_amthauer_weights', ['questionid' => $question->id], 'rownumber ASC, columnnumber ASC');

        // Put the old weights into an array.
        $oldweights = $this->weight_records_to_array($oldweightrecords);

        for ($i = 1; $i <= $options->numberofrows; ++$i) {
            for ($j = 1; $j <= $options->numberofcolumns; ++$j) {
                if (!empty($oldweights[$i][$j])) {
                    $weight = $oldweights[$i][$j];
                } else {
                    $weight = new stdClass();
                    $weight->questionid = $question->id;
                    $weight->rownumber = $i;
                    $weight->columnnumber = $j;
                    $weight->weight = 0.0;
                    $weight->id = $DB->insert_record('qtype_amthauer_weights', $weight);
                }

                // Perform the weight update.
                if (property_exists($question, 'weightbutton_' . $i)) {
                    if ($question->{'weightbutton_' . $i} == $j) {
                        $weight->weight = 1.0;
                    } else {
                        $weight->weight = 0.0;
                    }
                } else {
                    $weight->weight = 0.0;
                }
                $DB->update_record('qtype_amthauer_weights', $weight);
            }
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
        $question->numberofcolumns = $questiondata->options->numberofcolumns;
        $question->rows = $questiondata->options->rows;
        $question->columns = $questiondata->options->columns;
        $question->weights = $questiondata->options->weights;
    }

    /**
     * Custom method for deleting amthauer questions.
     *
     * @param $questionid
     * @param $contextid
     * @throws dml_exception
     */
    public function delete_question($questionid, $contextid)
    {
        global $DB;
        $DB->delete_records('qtype_amthauer_options', ['questionid' => $questionid]);
        $DB->delete_records('qtype_amthauer_rows', ['questionid' => $questionid]);
        $DB->delete_records('qtype_amthauer_columns', ['questionid' => $questionid]);
        $DB->delete_records('qtype_amthauer_weights', ['questionid' => $questionid]);
        parent::delete_question($questionid, $contextid);
    }

    /**
     * Turns an array of records from the table qtype_amthauer_weights into an array of array indexed
     * by rows and columns.
     *
     * @param array $weightrecords
     *
     * @return array
     */
    private function weight_records_to_array($weightrecords)
    {
        $weights = [];
        foreach ($weightrecords as $id => $weight) {
            if (!array_key_exists($weight->rownumber, $weights)) {
                $weights[$weight->rownumber] = [];
            }
            $weights[$weight->rownumber][$weight->columnnumber] = $weight;
        }

        return $weights;
    }
}
