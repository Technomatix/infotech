<?php
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/question/type/memorize/lib.php');

class qtype_memorize_question extends question_graded_automatically_with_countback
{
    public $rows;

    public $scoringmethod;

    public $shuffleanswers;

    public $numberofrows;

    public $expectedrows;

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
        // Retrieve the question rows (memorize options).
        for ($i = 0; $i < count($this->order); $i++) {
            if (isset($this->rows[$this->order[$i]])) {
                continue;
            }
            $a = new stdClass();
            $a->id = 0;
            $a->questionid = $this->id;
            $a->number = -1;
            $a->optiontext = html_writer::span(get_string('deletedchoice', 'qtype_memorize'), 'notifyproblem');
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
        return (count($response) === count($this->rows));
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

        foreach($response as $item){
            if(mb_strlen(trim($item)) > 0){
                continue;
            }
            return false;
        }

        return true;
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
        return $this->is_gradable_response($response) ? '' : get_string('oneanswerperrow', 'qtype_memorize');
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
        foreach (array_keys($this->order) as $key) {
            $field = $this->field($key);

            if (isset($response[$field])) {
                $result[] = $response[$field];
            }
        }

        return implode('; ', $result);
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

    public function get_correct_response()
    {
        return null;
    }

    /**
     * Returns an instance of the grading class according to the scoringmethod of the question.
     *
     * @return qtype_memorize_grading The grading object.
     */
    public function grading()
    {
        global $CFG;

        $type = $this->scoringmethod;
        $gradingclass = 'qtype_memorize_grading_' . $type;

        require_once($CFG->dirroot . '/question/type/memorize/grading/' . $gradingclass . '.class.php');

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
     * @throws dml_exception
     */
    public function grade_response(array $response)
    {
        /** Сохраняем расчитанные результаты вопроса */
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
        foreach (array_keys($this->order) as $key) {
            $result[$this->field($key)] = PARAM_RAW;
        }

        return $result;
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
