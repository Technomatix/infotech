<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/outputcomponents.php');

/**
 * Subclass for generating the bits of output specific to amthauer6 questions.
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_amthauer6_renderer extends qtype_renderer
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
     */
    public function formulation_and_controls(question_attempt $qa, question_display_options $displayoptions)
    {
        /** @var qtype_amthauer6_question $question */
        $question = $qa->get_question();

        $result = '';
        $result .= html_writer::tag('div', $question->format_questiontext($qa), ['class' => 'qtext']);

        $result .= $this->renderMethod($qa, $question, $displayoptions);

        if ($qa->get_state() == question_state::$invalid) {
            $result .= html_writer::nonempty_tag('div', $question->get_validation_error($qa->get_last_qt_data()), ['class' => 'validationerror']);
        }

        return $result;
    }

    /**
     * @param $qa
     * @param qtype_amthauer6_question $question
     * @param $displayoptions
     *
     * @return string
     * @throws coding_exception
     */
    private function renderMethod($qa, $question, $displayoptions)
    {
        $result = '';
        $response = $question->get_response($qa);

        $table = new html_table();
        $table->attributes['class'] = 'generaltable';

        $table->head = [];
        // Add empty header for option texts.
        $table->head[] = '№';

        // Add the response texts as table headers.
        $cell = new html_table_cell('');
        $cell->colspan = 7;
        $table->head[] = $cell;
        $table->head[] = new html_table_cell('');

        if ($displayoptions->correctness) {
            $cell = new html_table_cell('<div></div>');
            $table->head[] = $cell;
        }

        $isreadonly = $displayoptions->readonly;

        foreach ($question->get_order($qa) as $key => $rowid) {
            $field = $question->field($key);
            $row = $question->rows[$rowid];
            $rowdata = [];

            $rowdata[] = new html_table_cell('<span>' . ($key + 1) . '</span>');

            foreach ($row->items as $item) {
                $rowdata[] = new html_table_cell('<div>' . $item . '</div>');
            }

            $inputValue = array_key_exists($field, $response) && ($response[$field] > -1) ? $response[$field] : '';
            $field = $question->field($key);
            $buttonname = $qa->get_field_prefix() . $field;
            $rowdata[] = new html_table_cell('<div>' . $this->inputNumber($buttonname, $inputValue, $isreadonly) . '</div>');

            if ($displayoptions->correctness) {
                $rowgrade = $question->grading()->grade_row($question, $key, $row, $response);
                $cell = new html_table_cell($this->feedback_image($rowgrade));
                $cell->attributes['class'] = 'amthauer6correctness';
                $rowdata[] = $cell;
            }

            $table->data[] = $rowdata;
        }

        $result .= html_writer::table($table);

        return $result;
    }

    /**
     * @param string $name
     * @param string $value
     * @param bool $readonly
     *
     * @return string
     */
    protected function inputNumber($name, $value, $readonly)
    {
        $readonly = $readonly ? 'readonly="readonly" disabled="disabled"' : '';

        return '<input type="number" min="0" name="' . $name . '" value="' . $value . '" ' . $readonly . '/>';
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
        /** @var qtype_amthauer6_question $question */
        $question = $qa->get_question();

        //$result = [];
        //$response = '';
        $correctResponse = $question->get_correct_response();

        $table = new html_table();
        $table->attributes['class'] = 'correct-values-table';
        $rowData = [];

        foreach ($question->order as $key => $rowId) {
            //$correctColumnResponseText = get_string('false', 'qtype_amthauer6');
            if (isset($correctResponse[$rowId]) && isset($question->correctAnswers[$correctResponse[$rowId]])) {
                $cell = new html_table_cell(($key + 1));
                $table->head[] = $cell;
                $rowData[] = $question->correctAnswers[$correctResponse[$rowId]];
                //$correctColumnResponseText = ($key + 1) . ' - ' . $question->correctAnswers[$correctResponse[$rowId]];
            }

            //$result[] = $question->make_html_inline($correctColumnResponseText);
        }

        $table->data[] = $rowData;

        //if (!empty($result)) {
        //    $response = '<ul style="list-style-type: none;"><li>';
        //    $response .= implode('</li><li>', $result);
        //    $response .= '</li></ul>';
        //}

        return html_writer::table($table);
    }
}
