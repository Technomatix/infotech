<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package     qtype_richiemartin
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class qtype_richiemartin_question extends question_graded_automatically_with_countback
{
    public $rows;

    public $columns;

    public $shuffleanswers;

    public $numberofrows;

    public $numberofcols;

    public $order = null;

    public $editedquestion;

    // All the methods needed for option shuffling.

    /**
     * (non-PHPdoc).
     *
     * @see question_definition::start_attempt()
     */
    public function start_attempt(question_attempt_step $step, $variant)
    {
        if(!empty($this->id)) {
            $this->grading()->cleanGradeResponseByScales($this->id);
        }
        $this->order = array_keys($this->rows);
        if ($this->shuffleanswers) {
            shuffle($this->order);
        }
        $step->set_qt_var('_order', implode(',', $this->order));
    }

    /**
     * (non-PHPdoc).
     *
     * @see question_definition::apply_attempt_state()
     */
    public function apply_attempt_state(question_attempt_step $step)
    {
        //$r = $this->get_response();
        $this->order = explode(',', $step->get_qt_var('_order'));

        // Add any missing answers. Sometimes people edit questions after they
        // have been attempted which breaks things.
        // Retrieve the question rows (richiemartin options).
        for ($i = 0; $i < count($this->order); $i++) {
            if (isset($this->rows[$this->order[$i]])) {
                continue;
            }
            $a = new stdClass();
            $a->id = 0;
            $a->questionid = $this->id;
            $a->number = -1;
            $a->optiontext = html_writer::span(get_string('deletedchoice', 'qtype_richiemartin'), 'notifyproblem');
            $a->optiontextformat = FORMAT_HTML;
            $a->optionfeedback = "";
            $a->optionfeedbackformat = FORMAT_HTML;
            $this->rows[$this->order[$i]] = $a;
            $this->editedquestion = 1;
        }
    }

    /**
     *
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
     * @param mixed $row
     * @param mixed $col
     *
     * @return type
     */
    public function field($key)
    {
        return 'option' . $key;
    }

    public function fieldItem($key, $itemKey)
    {
        return $this->field($key) . '_' . $itemKey;
    }

    /**
     * Checks whether an row is answered by a given response.
     *
     * @param array $response
     * @param int $rowKey
     * @param object $row
     *
     * @return bool
     */
    public function is_answered($response, $rowKey, $row)
    {
        /** Проверяем заполненность всех вариантов */
        foreach ($row->optionitems as $itemKey => $itemValue) {
            $fieldItem = $this->fieldItem($rowKey, $itemKey);
            if (!isset($response[$fieldItem]) || mb_strlen($response[$fieldItem]) === 0 || !is_int($response[$fieldItem] * 1)) {
                return false;
            }
        }

        /** Если есть массив вариантов и он не пустой, значит вопрос был отвечен */
        return is_array($row->optionitems) && count($row->optionitems);
    }

    public function isAnsweredRight($response, $rowKey, $row)
    {
        if($this->is_answered($response, $rowKey, $row)){
            $amount = 0;
            foreach ($row->optionitems as $itemKey => $itemValue) {
                $fieldItem = $this->fieldItem($rowKey, $itemKey);
                $amount += $response[$fieldItem];
            }
            if($amount === 11){
                return true;
            }
        }

        return false;
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
        $filteredResponse = array_filter($response, 'ctype_digit');
        $responseCount = count($filteredResponse);
        $sumResponse = array_sum($filteredResponse);

        return ($responseCount === count($this->rows) * $this->numberofcols) && $sumResponse === count($this->rows) * 11;
    }

    /**
     * @param array $response
     *
     * @return bool
     * @see question_graded_automatically::is_gradable_response()
     *
     */
    public function is_gradable_response(array $response)
    {
        /** Если в ответах есть не числовые значения то ответ - не оцениваемый */
        $filteredResponse = array_filter($response, 'ctype_digit');
        if (count($filteredResponse) !== count($response)) {
            return false;
        }

        /** подсчитываю сумму балов в пределах строки, если не равно 11 - то строка не корректно заполненна */
        foreach ($this->rebuildResponseData($response) as $rowNumber => $columns) {
            if (array_sum($columns) > 11) {
                return false;
            }
        }

        return true;
        //return $this->is_complete_response($response);
    }

    private function parseRowColumn($str)
    {
        $filteredStr = str_replace('option', '', $str);
        $arr = explode('_', $filteredStr);
        if (strlen($filteredStr) === strlen($str) || count($arr) !== 2 || !ctype_digit($arr[0]) || !ctype_digit($arr[1])) {
            return false;
        }
        $r = new stdClass();
        $r->rowNumber = $arr[0];
        $r->columnNumber = $arr[1];

        return $r;
    }

    private function rebuildResponseData(array $response)
    {
        unset($response['_order']);

        $answers = [];
        /** пересобираю данные в формат [ rowNumber => [ columnNumber => value, columnNumber => value ... ] ] */
        foreach ($response as $key => $value) {
            if (($rowColumnObject = $this->parseRowColumn($key)) === false) {
                continue;
            }

            $answers[$rowColumnObject->rowNumber][$rowColumnObject->columnNumber] = $value;
        }

        return $answers;
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
        return ($this->is_gradable_response($response)) ? '' : get_string('oneanswerperrow', 'qtype_richiemartin');
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
            $row = $this->rows[$rowid];
            $resultRow = $this->html_to_text($row->optiontext, $row->optiontextformat) . ': ';
            foreach ($row->optionitems as $itemKey => $itemValue) {
                $fieldItem = $this->fieldItem($key, $itemKey);
                if (isset($response[$fieldItem])) {
                    foreach ($this->columns as $column) {
                        if ($column->number == $itemKey) {
                            $resultRow .= $this->html_to_text($column->responsetext, $column->responsetextformat) . ' [' . $response[$fieldItem] . '] ';
                        }
                    }
                }
            }
            $result[] = $resultRow;
        }

        return implode('; ', $result);
    }

    /**
     * (non-PHPdoc).
     *
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
            $partialcredit = -999; // Due to non-linear math.

            $parts[$rowid] = new question_classified_response($column->id, $column->responsetext, $partialcredit);
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
    public function get_correct_response($rowidindex = false)
    {
        $result = [];

        return $result;
    }

    /**
     * Returns an instance of the grading class.
     *
     * @return qtype_richiemartin_grading
     */
    public function grading()
    {
        global $CFG;

        $gradingclass = 'qtype_richiemartin_grading';

        require_once($CFG->dirroot . '/question/type/richiemartin/grading/' . $gradingclass . '.class.php');

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
        $this->grading()->gradeResponseByScales($this->rebuildResponseData($response), $this->id);

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
        return 'use raw data';
    }

    /**
     * Makes HTML text (e.g.
     * option or feedback texts) suitable for inline presentation in renderer.php.
     *
     * @param string html The HTML code.
     *
     * @return string the purified HTML code without paragraph elements and line breaks.
     */
    public function make_html_inline($html)
    {
        $html = preg_replace('~\s*<p>\s*~u', '', $html);
        $html = preg_replace('~\s*</p>\s*~u', '<br />', $html);
        $html = preg_replace('~(<br\s*/?>)+$~u', '', $html);

        return trim($html);
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
     */
    public function compute_final_grade($responses, $totaltries)
    {
        $lastresponse = count($responses) - 1;
        $numpoints = isset($responses[$lastresponse]) ? $this->grading()->grade_question($this, $responses[$lastresponse]) : 0;

        return max(0, $numpoints - max(0, $lastresponse) * $this->penalty);
    }
}
