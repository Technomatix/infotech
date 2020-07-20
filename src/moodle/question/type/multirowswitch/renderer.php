<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/outputcomponents.php');
require_once($CFG->dirroot . '/question/type/multirowswitch/lib.php');

/**
 * Subclass for generating the bits of output specific to multirowswitch questions.
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_multirowswitch_renderer extends qtype_renderer
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
        /** @var qtype_multirowswitch_question $question */
        $question = $qa->get_question();

        $result = '';
        $result .= html_writer::tag('div', $question->format_questiontext($qa), ['class' => 'qtext']);

        $result .= $this->renderScoringMethod($qa, $question, $displayoptions);

        if ($qa->get_state() == question_state::$invalid) {
            $result .= html_writer::nonempty_tag('div', $question->get_validation_error($qa->get_last_qt_data()), ['class' => 'validationerror']);
        }

        if (!empty(get_config('qtype_multirowswitch')->showscoringmethod)) {
            $result .= $this->showScoringMethod($question);
        }

        return $result;
    }

    /**
     * @param $qa
     * @param qtype_multirowswitch_question $question
     * @param $displayoptions
     *
     * @return string
     * @throws coding_exception
     */
    private function renderScoringMethod($qa, $question, $displayoptions)
    {
        $result = '';
        $response = $question->get_response($qa);

        $table = new html_table();
        $table->attributes['class'] = 'generaltable';

        $table->head = [];
        // Add empty header for option texts.
        $table->head[] = '№';
        $table->head[] = new html_table_cell();
        foreach($question->columns as $column){
            $table->head[] = new html_table_cell($column->responsetext);
        }
        if($displayoptions->correctness){
            $table->head[] = new html_table_cell();
        }

        $isReadonly = $displayoptions->readonly;

        foreach ($question->get_order($qa) as $key => $rowid) {
            $field = $question->field($key);
            $row = $question->rows[$rowid];

            // Holds the data for one table row.
            $rowdata = [];

            $rowdata[] = new html_table_cell($key + 1);
            $cell = new html_table_cell('<span class="optiontext">' . $row->optiontext . '</span>');
            $cell->attributes['class'] = 'question-row-text';
            $rowdata[] = $cell;

            foreach ($question->columns as $column) {
                $buttonname = $qa->get_field_prefix() . $field;
                $ischecked = false;
                if (array_key_exists($field, $response) && ($response[$field] == $column->number)) {
                    $ischecked = true;
                }
                $radio = $this->radiobutton($buttonname, $column->number, $ischecked, $isReadonly);

                // Show correctness icon with radio button if needed.
                if ($displayoptions->correctness && MultiRowSwitchHelper::getInstance()->useWeight($question->scoringmethod)) {
                    $weight = $question->weight($row->number, $column->number);
                    $radio .= '<span class="select-row-switch-greyingout">' . $this->feedback_image($weight > 0.0) . '</span>';
                }
                $cell = new html_table_cell($radio);
                $cell->attributes['class'] = 'select-row-switch-response-button';
                $rowdata[] = $cell;
            }

            // For correctness we have to grade the option...
            if ($displayoptions->correctness) {
                $rowgrade = $question->grading()->grade_row($question, $key, $row, $response);
                $cell = new html_table_cell($this->feedback_image($rowgrade));
                $cell->attributes['class'] = 'multirowswitchcorrectness';
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
     * @param qtype_multirowswitch_question $question
     *
     * @return string
     * @throws coding_exception
     */
    private function showScoringMethod($question)
    {
        global $OUTPUT;

        $result = '';

        if (get_string_manager()->string_exists($question->scoringmethod, 'qtype_multirowswitch')) {
            $outputScoringMethod = get_string($question->scoringmethod, 'qtype_multirowswitch');
        } else {
            $outputScoringMethod = $question->scoringmethod;
        }

        if (get_string_manager()->string_exists($question->scoringmethod . '_help', 'qtype_multirowswitch')) {
            $label = get_string('scoringmethod', 'qtype_multirowswitch') . ': <b>' . ucfirst($outputScoringMethod) . '</b>';
            $result .= html_writer::tag('div', '<br>' . $label . $OUTPUT->help_icon($question->scoringmethod, 'qtype_multirowswitch'), [
                'id' => 'scoringmethodinfo_q' . $question->id,
                'label' => $label,
            ]);
        }

        return $result;
    }

    protected static function radioButton($name, $value, $checked, $readonly)
    {
        $readonly = $readonly ? 'readonly="readonly" disabled="disabled"' : '';
        $checked = $checked ? 'checked="checked"' : '';

        return '<label><input type="radio" name="' . $name . '" value="' . $value . '" ' . $checked . ' ' .
               $readonly . '/></label>';
    }
}
