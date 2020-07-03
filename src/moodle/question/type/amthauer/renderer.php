<?php

/**
 * @package     qtype_amthauer
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/outputcomponents.php');

/**
 * Subclass for generating the bits of output specific to amthauer questions.
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_amthauer_renderer extends qtype_renderer
{
    /**
     * Generate the display of the formulation part of the question.
     * This is the
     * area that contains the question text (stem), and the controls for students to
     * input their answers.
     *
     * @param question_attempt $qa the question attempt to display.
     * @param question_display_options $displayoptions controls what should and should not be displayed.
     *
     * @return string HTML fragment.
     * @throws coding_exception
     * @throws dml_exception
     */
    public function formulation_and_controls(question_attempt $qa, question_display_options $displayoptions)
    {
        /** @var qtype_amthauer_question $question */
        $question = $qa->get_question();

        $result = '';
        $result .= html_writer::tag('div', $question->format_questiontext($qa), ['class' => 'qtext']);

        switch ($question->scoringmethod) {
            case 'scoringsub2':
                $result .= $this->renderScoringMethod2($qa, $question, $displayoptions);
            break;
            case 'scoringsub3':
                $result .= $this->renderScoringMethod3($qa, $question, $displayoptions);
            break;
        }

        if ($qa->get_state() == question_state::$invalid) {
            $result .= html_writer::nonempty_tag('div', $question->get_validation_error($qa->get_last_qt_data()), ['class' => 'validationerror']);
        }

        if (!empty(get_config('qtype_amthauer')->showscoringmethod)) {
            $result .= $this->showScoringMethod($question);
        }

        return $result;
    }

    /**
     * @param $qa
     * @param qtype_amthauer_question $question
     * @param $displayoptions
     *
     * @return string
     * @throws coding_exception
     */
    private function renderScoringMethod2($qa, $question, $displayoptions)
    {
        $result = '';
        $response = $question->get_response($qa);

        $table = new html_table();
        $table->attributes['class'] = 'generaltable';

        $table->head = [];
        // Add empty header for option texts.
        $table->head[] = '№';

        // Add the response texts as table headers.
        for ($i = 0; $i < $question->numberofcolumns; $i++) {
            $table->head[] = new html_table_cell(get_string('amthauer_head_' . ($i + 1), 'qtype_amthauer'));
        }
        if ($displayoptions->correctness) {
            $cell = new html_table_cell();
            $cell->attributes['class'] = 'amthauercorrectness';
            $table->head[] = $cell;
        }

        $isreadonly = $displayoptions->readonly;

        foreach ($question->get_order($qa) as $key => $rowid) {
            $field = $question->field($key);
            $row = $question->rows[$rowid];

            // Holds the data for one table row.
            $rowdata = [];

            $rowdata[] = new html_table_cell('<span class="optiontext">' . ($key + 1) . '</span>');;

            // Add the response radio buttons to the table.
            foreach ($question->columns as $column) {
                if ($column->rowid !== $row->number) {
                    continue;
                }

                $columnLabel = $question->make_html_inline(
                    $question->format_text($column->responsetext, $column->responsetextformat, $qa, 'question', 'response', $column->id)
                );

                $buttonname = $qa->get_field_prefix() . $field;
                $ischecked = false;
                if (array_key_exists($field, $response) && ($response[$field] == $column->number)) {
                    $ischecked = true;
                }
                $radio = $this->radioButton($buttonname, $column->number, $ischecked, $isreadonly);

                // Show correctness icon with radio button if needed.
                if ($displayoptions->correctness) {
                    $weight = $question->weight($row->number, $column->number);
                    $radio .= '<span class="amthauergreyingout">' . $this->feedback_image($weight > 0.0) . '</span>';
                }
                $cell = new html_table_cell('<div class="cell-with-data"><div style="width: 80%;float: left;text-align: right;">' . $columnLabel . '</div>' . $radio . '</div>');
                $cell->attributes['class'] = 'amthauerresponsebutton';
                $rowdata[] = $cell;
            }

            // For correctness we have to grade the option...
            if ($displayoptions->correctness) {
                $rowgrade = $question->grading()->grade_row($question, $key, $row, $response);
                $cell = new html_table_cell($this->feedback_image($rowgrade));
                $cell->attributes['class'] = 'amthauercorrectness';
                $rowdata[] = $cell;
            }
            $table->data[] = $rowdata;
        }

        $result .= html_writer::table($table);

        return $result;
    }

    /**
     * @param $qa
     * @param qtype_amthauer_question $question
     * @param $displayoptions
     *
     * @return string
     * @throws coding_exception
     */
    private function renderScoringMethod3($qa, $question, $displayoptions)
    {
        $result = '';
        $response = $question->get_response($qa);

        $table = new html_table();
        $table->attributes['class'] = 'generaltable';

        $table->head = [];
        // Add empty header for option texts.
        $table->head[] = '№';

        // Add the response texts as table headers.
        for ($i = 0; $i < $question->numberofcolumns; $i++) {
            $table->head[] = new html_table_cell(get_string('amthauer_head_' . ($i + 1), 'qtype_amthauer'));
        }
        if ($displayoptions->correctness) {
            $cell = new html_table_cell();
            $cell->attributes['class'] = 'amthauercorrectness';
            $table->head[] = $cell;
        }

        $isreadonly = $displayoptions->readonly;

        foreach ($question->get_order($qa) as $key => $rowid) {
            $field = $question->field($key);
            $row = $question->rows[$rowid];
            $rowdata = [];

            $rowdata[] = new html_table_cell('<span>' . ($key + 1) . '</span>');;

            $rowtext = $question->make_html_inline($question->format_text($row->optiontext, $row->optiontextformat, $qa, 'qtype_amthauer', 'optiontext', $row->number));
            $cell = new html_table_cell('<span class="optiontext">' . $rowtext . '</span>');
            $cell->attributes['class'] = 'optiontext';
            $cell->colspan = 5;
            $rowdata[] = $cell;
            if ($displayoptions->correctness) {
                $cell = new html_table_cell();
                $cell->attributes['class'] = 'amthauercorrectness';
                $rowdata[] = $cell;
            }
            $table->data[] = $rowdata;

            $rowdata = [];
            $rowdata[] = new html_table_cell('');

            // Add the response radio buttons to the table.
            foreach ($question->columns as $column) {
                if ($column->rowid !== $row->number) {
                    continue;
                }

                $columnLabel = $question->make_html_inline(
                    $question->format_text($column->responsetext, $column->responsetextformat, $qa, 'question', 'response', $column->id)
                );

                $buttonname = $qa->get_field_prefix() . $field;
                $ischecked = false;
                if (array_key_exists($field, $response) && ($response[$field] == $column->number)) {
                    $ischecked = true;
                }
                $radio = $this->radioButton($buttonname, $column->number, $ischecked, $isreadonly);

                // Show correctness icon with radio button if needed.
                if ($displayoptions->correctness) {
                    $weight = $question->weight($row->number, $column->number);
                    $radio .= '<span class="amthauergreyingout">' . $this->feedback_image($weight > 0.0) . '</span>';
                }
                $cell = new html_table_cell('<div class="cell-with-data"><div style="width: 80%;float: left;text-align: right;">' . $columnLabel . '</div>' . $radio . '</div>');
                $cell->attributes['class'] = 'amthauerresponsebutton';
                $rowdata[] = $cell;
            }

            // Has a selection been made for this option?
            $isselected = $question->is_answered($response, $key);
            // For correctness we have to grade the option...
            if ($displayoptions->correctness) {
                $rowgrade = $question->grading()->grade_row($question, $key, $row, $response);
                $cell = new html_table_cell($this->feedback_image($rowgrade));
                $cell->attributes['class'] = 'amthauercorrectness';
                $rowdata[] = $cell;
            }

            $table->data[] = $rowdata;
        }

        $result .= html_writer::table($table);

        return $result;
    }

    /**
     * Returns a string containing the rendererd question's scoring method.
     * Appends an info icon containing information about the scoring method.
     *
     * @param qtype_amthauer_question $question
     *
     * @return string
     * @throws coding_exception
     */
    private function showScoringMethod($question)
    {
        global $OUTPUT;

        $result = '';

        if (get_string_manager()->string_exists($question->scoringmethod, 'qtype_amthauer')) {
            $outputScoringMethod = get_string($question->scoringmethod, 'qtype_amthauer');
        } else {
            $outputScoringMethod = $question->scoringmethod;
        }

        if (get_string_manager()->string_exists($question->scoringmethod . '_help', 'qtype_amthauer')) {
            $label = get_string('scoringmethod', 'qtype_amthauer') . ': <b>' . ucfirst($outputScoringMethod) . '</b>';
            $result .= html_writer::tag('div', '<br>' . $label . $OUTPUT->help_icon($question->scoringmethod, 'qtype_amthauer'), [
                'id' => 'scoringmethodinfo_q' . $question->id,
                'label' => $label,
            ]);
        }

        return $result;
    }

    /**
     * Returns the HTML representation of a radio button with the given attributes.
     *
     * @param string $name
     * @param int $value
     * @param bool $checked
     * @param bool $readonly
     *
     * @return string
     */
    protected static function radioButton($name, $value, $checked, $readonly)
    {
        $readonly = $readonly ? 'readonly="readonly" disabled="disabled"' : '';
        $checked = $checked ? 'checked="checked"' : '';

        return '<label><input type="radio" name="' . $name . '" value="' . $value . '" ' . $checked . ' ' .
               $readonly . '/></label>';
    }

    /**
     * @param question_attempt $qa
     *
     * @return string
     * @throws coding_exception
     * @see qtype_renderer::correct_response()
     */
    public function correct_response(question_attempt $qa)
    {
        $question = $qa->get_question();

        $result = [];
        $response = '';
        $correctResponse = $question->get_correct_response();

        foreach ($question->order as $key => $rowId) {
            $correctColumnResponseText = get_string('false', 'qtype_amthauer');
            if (isset($correctResponse[$rowId])) {
                if (isset($question->columns[$correctResponse[$rowId]])) {
                    $rowText = $question->scoringmethod === 'scoringsub3' ? ' ' . $question->rows[$rowId]->optiontext . ': ' : ' - ';
                    $correctColumnResponseText = ($key + 1) . $rowText . $question->columns[$correctResponse[$rowId]]->responsetext;
                }
            }

            $result[] = $question->make_html_inline($correctColumnResponseText);
        }

        if (!empty($result)) {
            $response = '<ul style="list-style-type: none;"><li>';
            $response .= implode('</li><li>', $result);
            $response .= '</li></ul>';
        }

        return $response;
    }
}
