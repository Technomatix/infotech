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

require_once($CFG->libdir . '/outputcomponents.php');

/**
 * Subclass for generating the bits of output specific to richiemartin questions.
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_richiemartin_renderer extends qtype_renderer
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
     */
    public function formulation_and_controls(question_attempt $qa, question_display_options $displayoptions)
    {
        $question = $qa->get_question();
        $response = $question->get_response($qa);

        $result = '';
        $result .= html_writer::tag('div', $question->format_questiontext($qa), ['class' => 'qtext']);

        $table = new html_table();
        $table->attributes['class'] = 'generaltable';

        $isreadonly = $displayoptions->readonly;

        foreach ($question->get_order($qa) as $key => $rowid) {
            $row = $question->rows[$rowid];

            $tableRow = [];

            // Add the formated option text to the table.
            $rowtext = $question->make_html_inline($question->format_text($row->optiontext, $row->optiontextformat, $qa, 'qtype_richiemartin', 'optiontext', $row->number));

            $cell = new html_table_cell($row->number);
            $tableRow[] = $cell;
            $cell = new html_table_cell('<span class="optiontext">' . $rowtext . '</span>');
            $cell->attributes['class'] = 'optiontext';
            $tableRow[] = $cell;
            $table->data[] = $tableRow;

            $rowTable = new html_table();
            $rowTable->attributes['class'] = 'row-table';
            // Add the response inputs to the table.
            foreach ($question->columns as $itemKey => $column) {
                $tableRow = [];
                $tableRow[] = new html_table_cell(
                    $question->make_html_inline(
                        $question->format_text($column->responsetext, $column->responsetextformat, $qa, 'question', 'response', $column->id)
                    )
                );

                $tableRow[] = new html_table_cell($row->optionitems[$itemKey]);
                $field = $question->fieldItem($key, $itemKey);
                $buttonname = $qa->get_field_prefix() . $field;
                $columnVariantValue = 0;
                if (array_key_exists($field, $response) && !empty($response[$field])) {
                    $columnVariantValue = $response[$field];
                }
                $inputNumber = $this->inputNumber($buttonname, $columnVariantValue, $isreadonly);

                $cell = new html_table_cell($inputNumber);
                $cell->attributes['class'] = 'richiemartinresponsebutton';
                $tableRow[] = $cell;

                $rowTable->data[] = $tableRow;
            }

            $table->data[] = [new html_table_cell(''), new html_table_cell(html_writer::table($rowTable))];
        }

        $result .= html_writer::table($table);

        if ($qa->get_state() == question_state::$invalid) {
            $result .= html_writer::nonempty_tag('div', $question->get_validation_error($qa->get_last_qt_data()), ['class' => 'validationerror']);
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
    protected static function inputNumber($name, $value, $readonly)
    {
        $readonly = $readonly ? 'readonly="readonly" disabled="disabled"' : '';

        return '<input type="number" max="11" min="0" name="' . $name . '" value="' . $value . '" ' . $readonly . '/>';
    }
}
