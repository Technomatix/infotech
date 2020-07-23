<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/outputcomponents.php');
require_once($CFG->dirroot . '/question/type/memorize/lib.php');

/**
 * Subclass for generating the bits of output specific to memorize questions.
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_memorize_renderer extends qtype_renderer
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
        /** @var qtype_memorize_question $question */
        $question = $qa->get_question();

        $result = '';
        $result .= html_writer::tag('div', $question->format_questiontext($qa), ['class' => 'qtext']);

        $result .= $this->renderScoringMethod($qa, $question, $displayoptions);

        if ($qa->get_state() == question_state::$invalid) {
            $result .= html_writer::nonempty_tag('div', $question->get_validation_error($qa->get_last_qt_data()), ['class' => 'validationerror']);
        }

        if (!empty(get_config('qtype_memorize')->showscoringmethod)) {
            $result .= $this->showScoringMethod($question);
        }

        return $result;
    }

    /**
     * @param $qa
     * @param qtype_memorize_question $question
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
        $table->head[] = new html_table_cell('<span id="clock"></span>');

        $isReadonly = $displayoptions->readonly;

        foreach ($question->get_order($qa) as $key => $rowid) {
            $field = $question->field($key);
            $row = $question->rows[$rowid];

            // Holds the data for one table row.
            $rowdata = [];

            $rowdata[] = new html_table_cell($key + 1);
            $inputName = $qa->get_field_prefix() . $field;
            $responseValue = '';
            if (array_key_exists($field, $response) && !empty($response[$field])) {
                $responseValue = $response[$field];
            }
            $cell = new html_table_cell('<span class="optiontext" style="-ms-user-select: none; -moz-user-select: none; -webkit-user-select: none; user-select: none;">' . $row->optiontext . '</span><span class="option_answer">' . $this->inputText($inputName, $responseValue, $isReadonly) . '</span>');
            $cell->attributes['class'] = 'question-row-text';
            $rowdata[] = $cell;

            $table->data[] = $rowdata;
        }

        $result .= html_writer::table($table);

        return $result;
    }

    /**
     * Returns a string containing the rendererd question's scoring method.
     * Appends an info icon containing information about the scoring method.
     *
     * @param qtype_memorize_question $question
     *
     * @return string
     * @throws coding_exception
     */
    private function showScoringMethod($question)
    {
        global $OUTPUT;

        $result = '';

        if (get_string_manager()->string_exists($question->scoringmethod, 'qtype_memorize')) {
            $outputScoringMethod = get_string($question->scoringmethod, 'qtype_memorize');
        } else {
            $outputScoringMethod = $question->scoringmethod;
        }

        if (get_string_manager()->string_exists($question->scoringmethod . '_help', 'qtype_memorize')) {
            $label = get_string('scoringmethod', 'qtype_memorize') . ': <b>' . ucfirst($outputScoringMethod) . '</b>';
            $result .= html_writer::tag('div', '<br>' . $label . $OUTPUT->help_icon($question->scoringmethod, 'qtype_memorize'), [
                'id' => 'scoringmethodinfo_q' . $question->id,
                'label' => $label,
            ]);
        }

        return $result;
    }

    /**
     * @param string $name
     * @param string $value
     * @param bool $readonly
     *
     * @return string
     */
    protected function inputText($name, $value, $readonly)
    {
        $readonly = $readonly ? 'readonly="readonly" disabled="disabled"' : '';

        return '<input type="text" name="' . $name . '" value="' . $value . '" ' . $readonly . '/>';
    }
}
