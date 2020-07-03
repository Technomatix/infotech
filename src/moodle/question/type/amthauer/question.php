<?php

/**
 * @package     qtype_amthauer
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class qtype_amthauer_question extends question_graded_automatically_with_countback
{
    public $rows;

    public $columns;

    public $weights;

    public $scoringmethod;

    public $shuffleanswers;

    public $numberofrows;

    public $numberofcols;

    public $order = null;

    public $editedquestion;

    // All the methods needed for option shuffling.

    /**
     * @param question_attempt_step $step
     * @param $variant
     *
     * @throws coding_exception
     * @see question_definition::start_attempt()
     */
    public function start_attempt(question_attempt_step $step, $variant)
    {
        $this->order = array_keys($this->rows);
        if ($this->shuffleanswers) {
            shuffle($this->order);
        }
        $step->set_qt_var('_order', implode(',', $this->order));
    }

    /**
     * @param question_attempt_step $step
     *
     * @throws coding_exception
     * @see question_definition::apply_attempt_state()
     */
    public function apply_attempt_state(question_attempt_step $step)
    {
        $this->order = explode(',', $step->get_qt_var('_order'));

        // Add any missing answers. Sometimes people edit questions after they
        // have been attempted which breaks things.
        // Retrieve the question rows (amthauer options).
        for ($i = 0; $i < count($this->order); $i++) {
            if (isset($this->rows[$this->order[$i]])) {
                continue;
            }
            $a = new stdClass();
            $a->id = 0;
            $a->questionid = $this->id;
            $a->number = -1;
            $a->optiontext = html_writer::span(get_string('deletedchoice', 'qtype_amthauer'), 'notifyproblem');
            $a->optiontextformat = FORMAT_HTML;
            $a->optionfeedback = "";
            $a->optionfeedbackformat = FORMAT_HTML;
            $this->rows[$this->order[$i]] = $a;
            $this->editedquestion = 1;
        }
    }

    /**
     * @param question_attempt $qa
     *
     * @return array
     * @throws coding_exception
     */
    public function get_order(question_attempt $qa)
    {
        $this->init_order($qa);

        return $this->order;
    }

    /**
     * Initialises the order (if it is not set yet) by decoding
     * the question attempt variable '_order'.
     *
     * @param question_attempt $qa
     *
     * @throws coding_exception
     */
    protected function init_order(question_attempt $qa)
    {
        if (is_null($this->order)) {
            $this->order = explode(',', $qa->get_step(0)->get_qt_var('_order'));
        }
    }

    /**
     * Returns the name field name for input cells in the questiondisplay.
     * The column parameter is ignored for now since we don't use multiple answers.
     *
     * @param int $key
     *
     * @return string
     */
    public function field($key)
    {
        return 'option' . $key;
    }

    /**
     * Checks whether an row is answered by a given response.
     *
     * @param array $response
     * @param int $rownumber
     *
     * @return bool
     */
    public function is_answered($response, $rownumber)
    {
        $field = $this->field($rownumber);

        // Get the value of the radiobutton array, if it exists in the response.
        return isset($response[$field]) && !empty($response[$field]);
    }

    /**
     * Checks whether a given column (response) is the correct answer for a given row (option).
     *
     * @param string $row The row number.
     * @param string $col The column number
     *
     * @return bool
     */
    public function is_correct($row, $col)
    {
        $weight = $this->weight($row, $col);

        if ($weight > 0.0) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * Returns the weight for the given row and column.
     *
     * @param mixed $row A row object or a row number.
     * @param mixed $col A column object or a column number.
     *
     * @return float
     */
    public function weight($row = null, $col = null)
    {
        $rownumber = is_object($row) ? $row->number : $row;
        $colnumber = is_object($col) ? $col->number : $col;
        if (isset($this->weights[$rownumber][$colnumber])) {
            $weight = (float)$this->weights[$rownumber][$colnumber]->weight;
        } else {
            $weight = 0;
        }

        return $weight;
    }

    public function get_response(question_attempt $qa)
    {
        return $qa->get_last_qt_data();
    }

    /**
     * Used by many of the behaviours, to work out whether the student's
     * response to the question is complete.
     * That is, whether the question attempt
     * should move to the COMPLETE or INCOMPLETE state.
     *
     * @param array $response responses, as returned by
     *        {@link question_attempt_step::get_qt_data()}.
     *
     * @return bool whether this response is a complete answer to this question.
     */
    public function is_complete_response(array $response)
    {
        if (count($response) == count($this->rows)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param array $response
     *
     * @return bool
     * @see question_graded_automatically::is_gradable_response()
     */
    public function is_gradable_response(array $response)
    {
        unset($response['_order']);
        if ($this->scoringmethod == 'scoringsub2' || $this->scoringmethod == 'scoringsub3') {
            if (count($response) > 0) {
                return true;
            } else {
                return false;
            }
        } else {
            return $this->is_complete_response($response);
        }
    }

    /**
     * In situations where is_gradable_response() returns false, this method
     * should generate a description of what the problem is.
     *
     * @param array $response
     *
     * @return string the message.
     * @throws coding_exception
     */
    public function get_validation_error(array $response)
    {
        return $this->is_gradable_response($response) ? '' : get_string('oneanswerperrow', 'qtype_amthauer');
    }

    /**
     * Produce a plain text summary of a response.
     *
     * @param array $response a response, as might be passed to {@link grade_response()}.
     *
     * @return string a plain text summary of that response, that could be used in reports.
     */
    public function summarise_response(array $response)
    {
        $result = [];
        foreach ($this->order as $key => $rowid) {
            $field = $this->field($key);
            $row = $this->rows[$rowid];

            if (isset($response[$field])) {
                foreach ($this->columns as $column) {
                    if ($column->rowid !== $row->number) {
                        continue;
                    }
                    if ($column->number == $response[$field]) {
                        $rowText = $this->scoringmethod === 'scoringsub3' ? ' ' . $this->html_to_text($row->optiontext, $row->optiontextformat) . ': ' : ' - ';
                        $result[] = $row->number . $rowText . $this->html_to_text($column->responsetext, $column->responsetextformat);
                    }
                }
            }
        }

        return implode('; ', $result);
    }

    /**
     * @param array $response
     *
     * @return array
     * @see question_with_responses::classify_response()
     */
    public function classify_response(array $response)
    {
        // See which column numbers have been selected.
        $selectedcolumns = [];
        foreach ($this->order as $key => $rowid) {
            $field = $this->field($key);
            $row = $this->rows[$rowid];

            if (array_key_exists($field, $response) && $response[$field]) {
                $selectedcolumns[$rowid] = $response[$field];
            } else {
                $selectedcolumns[$rowid] = 0;
            }
        }

        // Now calculate the classification.
        $parts = [];
        foreach ($this->rows as $rowid => $row) {
            $field = $this->field($key);
            if (empty($selectedcolumns[$rowid])) {
                $parts[$rowid] = question_classified_response::no_response();
                continue;
            }
            // Find the chosen column by columnnumber.
            $column = null;
            foreach ($this->columns as $colid => $col) {
                if ($col->number == $selectedcolumns[$rowid]) {
                    $column = $col;
                    break;
                }
            }
            // Calculate the partial credit.
            if ($this->scoringmethod == 'subpoints') {
                $partialcredit = 0.0;
            } else {
                $partialcredit = -999; // Due to non-linear math.
            }
            if ($this->scoringmethod == 'subpoints' &&
                $this->weights[$row->number][$column->number]->weight > 0) {
                $partialcredit = 1 / count($this->rows);
            }

            $parts[$rowid] = new question_classified_response($column->id, $column->responsetext,
                $partialcredit);
        }

        return $parts;
    }

    /**
     * Use by many of the behaviours to determine whether the student's
     * response has changed.
     * This is normally used to determine that a new set
     * of responses can safely be discarded.
     *
     * @param array $prevresponse the responses previously recorded for this question,
     *        as returned by {@link question_attempt_step::get_qt_data()}
     * @param array $newresponse the new responses, in the same format.
     *
     * @return bool whether the two sets of responses are the same - that is
     *         whether the new set of responses can safely be discarded.
     */
    public function is_same_response(array $prevresponse, array $newresponse)
    {
        if (count($prevresponse) != count($newresponse)) {
            return false;
        }
        foreach ($prevresponse as $field => $previousvalue) {
            if (!isset($newresponse[$field])) {
                return false;
            }
            $newvalue = $newresponse[$field];
            if ($newvalue != $previousvalue) {
                return false;
            }
        }

        return true;
    }

    /**
     * What data would need to be submitted to get this question correct.
     * If there is more than one correct answer, this method should just
     * return one possibility.
     *
     * @return array parameter name => value.
     */
    public function get_correct_response()
    {
        $result = [];
        foreach ($this->order as $key => $rowid) {
            $row = $this->rows[$rowid];

            foreach ($this->columns as $key => $column) {
                if ($column->rowid !== $row->number) {
                    continue;
                }
                $weight = $this->weight($row, $column);
                if ($weight > 0) {
                    $result[$rowid] = $key;
                }
            }
        }

        return $result;
    }

    /**
     * Returns an instance of the grading class according to the scoringmethod of the question.
     *
     * @return qtype_amthauer_grading The grading object.
     */
    public function grading()
    {
        global $CFG;

        $type = $this->scoringmethod;
        $gradingclass = 'qtype_amthauer_grading_' . $type;

        require_once($CFG->dirroot . '/question/type/amthauer/grading/' . $gradingclass . '.class.php');

        return new $gradingclass();
    }

    /**
     * Grade a response to the question, returning a fraction between
     * get_min_fraction() and 1.0, and the corresponding {@link question_state}
     * right, partial or wrong.
     *
     * @param array $response responses, as returned by
     *        {@link question_attempt_step::get_qt_data()}.
     *
     * @return array (number, integer) the fraction, and the state.
     */
    public function grade_response(array $response)
    {
        /** Сохранем расчитанные результаты вопроса */
        $this->grading()->gradeResponseByScale($response, $this);

        $grade = $this->grading()->grade_question($this, $response);
        $state = question_state::graded_state_for_fraction($grade);

        return [
            $grade,
            $state,
        ];
    }

    /**
     * What data may be included in the form submission when a student submits
     * this question in its current state?
     *
     * This information is used in calls to optional_param. The parameter name
     * has {@link question_attempt::get_field_prefix()} automatically prepended.
     *
     * @return array|string variable name => PARAM_... constant, or, as a special case
     *         that should only be used in unavoidable, the constant question_attempt::USE_RAW_DATA
     *         meaning take all the raw submitted data belonging to this question.
     */
    public function get_expected_data()
    {
        $result = [];
        foreach ($this->order as $key => $notused) {
            $field = $this->field($key);
            $result[$field] = PARAM_INT;
        }

        return $result;
    }

    /**
     * Returns an array where keys are the cell names and the values
     * are the weights.
     *
     * @return array
     */
    public function cells()
    {
        $result = [];
        foreach ($this->order as $key => $rowid) {
            $row = $this->rows[$rowid];
            $field = $this->field($key);
            foreach ($this->columns as $column) {
                $result[$field] = $this->weight($row->number, $column->number);
            }
        }

        return $result;
    }

    /**
     * Convert some part of the question text to plain text.
     * This might be used,
     * for example, by get_response_summary().
     *
     * @param string $text The HTML to reduce to plain text.
     * @param int $format the FORMAT_... constant.
     *
     * @return string the equivalent plain text.
     */
    public function html_to_text($text, $format)
    {
        return question_utils::to_plain_text($text, $format);
    }

    /**
     * Computes the final grade when "Multiple Attempts" or "Hints" are enabled
     *
     * @param array $responses Contains the user responses. 1st dimension = attempt, 2nd dimension = answers
     * @param int $totaltries Not needed
     *
     * @return int
     */
    public function compute_final_grade($responses, $totaltries)
    {
        $lastresponse = count($responses) - 1;
        $numpoints = isset($responses[$lastresponse]) ? $this->grading()->grade_question($this, $responses[$lastresponse]) : 0;

        return max(0, $numpoints - max(0, $lastresponse) * $this->penalty);
    }
}
