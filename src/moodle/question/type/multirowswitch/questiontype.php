<?php
defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/type/multirowswitch/lib.php');

/**
 * The multirowswitch question type.
 */
class qtype_multirowswitch extends question_type
{
    /**
     * Sets the default options for the question.
     *
     * @param qtype_multirowswitch_question $question
     *
     * @throws dml_exception
     */
    public function set_default_options($question)
    {
        $multirowswitchconfig = get_config('qtype_multirowswitch');

        if (!isset($question->options)) {
            $question->options = new stdClass();
        }
        if (!isset($question->options->scoringmethod)) {
            $question->options->scoringmethod = $multirowswitchconfig->scoringmethod;
        }
        if (!isset($question->options->numberofrows)) {
            $question->options->numberofrows = QTYPE_MULTIROWSWITCH_NUMBER_OF_ROWS;
        }
        if (!isset($question->options->numberofcolumns)) {
            $question->options->numberofcolumns = QTYPE_MULTIROWSWITCH_NUMBER_OF_COLUMNS;
        }
        if (!isset($question->options->shuffleanswers)) {
            $question->options->shuffleanswers = $multirowswitchconfig->shuffleanswers;
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
        if (!isset($question->options->columns)) {
            $columns = [];
            for ($i = 1; $i <= $question->options->numberofcolumns; ++$i) {
                $column = new stdClass();
                $column->number = $i;
                if (isset($multirowswitchconfig->{'responsetext' . $i})) {
                    $responsetextcol = $multirowswitchconfig->{'responsetext' . $i};
                } else {
                    $responsetextcol = '';
                }
                $column->responsetext = $responsetextcol;
                $columns[] = $column;
            }
            $question->options->columns = $columns;
        }
    }

    /**
     * Loads the question options, rows from the database.
     *
     * @param qtype_multirowswitch_question $question
     *
     * @return bool
     * @throws dml_exception
     */
    public function get_question_options($question)
    {
        global $DB;

        parent::get_question_options($question);

        $question->options = $DB->get_record('qtype_multirowswitch_options', ['questionid' => $question->id]);

        $question->options->rows = array_values($DB->get_records('qtype_multirowswitch_rows', ['questionid' => $question->id], 'number ASC', '*', 0, $question->options->numberofrows));

        $question->options->columns = $DB->get_records('qtype_multirowswitch_columns', ['questionid' => $question->id], 'number ASC', '*', 0, $question->options->numberofcolumns);

        $weightRecords = $DB->get_records('qtype_multirowswitch_weights', ['questionid' => $question->id], 'rownumber ASC, columnnumber ASC');

        for ($i = 0; $i < $question->options->numberofrows; $i++) {
            $row = isset($question->options->rows[$i]) ? $question->options->rows[$i] : null;
            $question->{'option_' . ($i + 1)} = $row ? $row->optiontext : '';
        }

        foreach ($question->options->columns as $key => $column) {
            $question->{'responsetext_' . $column->number} = $column->responsetext;
        }

        foreach ($weightRecords as $key => $weight) {
            if ($weight->weight == 1.0) {
                $question->{'weightbutton_' . $weight->rownumber} = $weight->columnnumber;
            }
        }
        // Put the weight records into an array indexed by rownumber and columnnumber.
        $question->options->weights = $this->weight_records_to_array($weightRecords);

        return true;
    }

    /**
     * Stores the question options in the database.
     *
     * @param qtype_multirowswitch_question $question
     *
     * @throws dml_exception
     */
    public function save_question_options($question)
    {
        global $DB;

        // Insert all the new options.
        $options = $DB->get_record('qtype_multirowswitch_options', ['questionid' => $question->id]);
        if (!$options) {
            $options = new stdClass();
            $options->questionid = $question->id;
            $options->scoringmethod = '';
            $options->shuffleanswers = '';
            $options->numberofcolumns = '';
            $options->numberofrows = '';
            $options->id = $DB->insert_record('qtype_multirowswitch_options', $options);
        }

        $options->scoringmethod = $question->scoringmethod;
        $options->shuffleanswers = $question->shuffleanswers;
        $options->numberofrows = $question->numberofrows;
        $options->numberofcolumns = $question->numberofcolumns;
        $DB->update_record('qtype_multirowswitch_options', $options);

        // Insert all the new rows.
        $oldRows = $DB->get_records('qtype_multirowswitch_rows', ['questionid' => $question->id], 'number ASC');

        for ($i = 1; $i <= $options->numberofrows; ++$i) {
            $row = array_shift($oldRows);
            if (!$row) {
                $row = new stdClass();
                $row->questionid = $question->id;
                $row->number = $i;
                $row->optiontext = '';

                $row->id = $DB->insert_record('qtype_multirowswitch_rows', $row);
            }

            $row->optiontext = isset($_REQUEST['option_' . $i]) ? $_REQUEST['option_' . $i] : '';

            $DB->update_record('qtype_multirowswitch_rows', $row);
        }

        $oldcolumns = $DB->get_records('qtype_multirowswitch_columns', ['questionid' => $question->id], 'number ASC');

        // Insert all new columns.
        for ($i = 1; $i <= $options->numberofcolumns; ++$i) {
            $column = array_shift($oldcolumns);
            if (!$column) {
                $column = new stdClass();
                $column->questionid = $question->id;
                $column->number = $i;
                $column->responsetext = '';

                $column->id = $DB->insert_record('qtype_multirowswitch_columns', $column);
            }

            // Perform an update.
            $column->responsetext = $question->{'responsetext_' . $i};
            $DB->update_record('qtype_multirowswitch_columns', $column);
        }

        if(MultiRowSwitchHelper::getInstance()->useWeight($question->scoringmethod)) {
            // Set all the new weights.
            $oldweightrecords = $DB->get_records('qtype_multirowswitch_weights', ['questionid' => $question->id], 'rownumber ASC, columnnumber ASC');

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
                        $weight->id = $DB->insert_record('qtype_multirowswitch_weights', $weight);
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
                    $DB->update_record('qtype_multirowswitch_weights', $weight);
                }
            }
        }
    }

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

    public function delete_question($questionid, $contextid)
    {
        global $DB;
        $DB->delete_records('qtype_multirowswitch_options', ['questionid' => $questionid]);
        $DB->delete_records('qtype_multirowswitch_rows', ['questionid' => $questionid]);
        $DB->delete_records('qtype_multirowswitch_columns', ['questionid' => $questionid]);
        $DB->delete_records('qtype_multirowswitch_weights', ['questionid' => $questionid]);
        parent::delete_question($questionid, $contextid);
    }

    private function weight_records_to_array($weightRecords)
    {
        $weights = [];
        foreach ($weightRecords as $id => $weight) {
            if (!array_key_exists($weight->rownumber, $weights)) {
                $weights[$weight->rownumber] = [];
            }
            $weights[$weight->rownumber][$weight->columnnumber] = $weight;
        }

        return $weights;
    }
}
