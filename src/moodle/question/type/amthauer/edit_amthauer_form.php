<?php

/**
 * @package     qtype_amthauer
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/edit_question_form.php');
require_once($CFG->dirroot . '/question/type/amthauer/lib.php');
require_once($CFG->dirroot . '/question/engine/bank.php');

/**
 * Amthauer editing form definition.
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_amthauer_edit_form extends question_edit_form
{
    private $numberofrows;

    private $numberofcolumns;

    /**
     * @return string
     */
    public function qtype()
    {
        return 'amthauer';
    }

    /**
     * Build the form definition.
     *
     * This adds all the form fields that the default question type supports.
     * If your question type does not support all these fields, then you can
     * override this method and remove the ones you don't want with $mform->removeElement().
     */
    protected function definition()
    {
        global $PAGE, $DB;

        $mform = $this->_form;

        // Standard fields at the start of the form.
        $mform->addElement('header', 'categoryheader', get_string('category', 'question'));

        if (!isset($this->question->id)) {
            if (!empty($this->question->formoptions->mustbeusable)) {
                $contexts = $this->contexts->having_add_and_use();
            } else {
                $contexts = $this->contexts->having_cap('moodle/question:add');
            }

            // Adding question.
            $mform->addElement('questioncategory', 'category', get_string('category', 'question'), ['contexts' => $contexts]);
        } else if (!($this->question->formoptions->canmove || $this->question->formoptions->cansaveasnew)) {
            // Editing question with no permission to move from category.
            $mform->addElement('questioncategory', 'category', get_string('category', 'question'), ['contexts' => [$this->categorycontext]]);
            $mform->addElement('hidden', 'usecurrentcat', 1);
            $mform->setType('usecurrentcat', PARAM_BOOL);
            $mform->setConstant('usecurrentcat', 1);
        } else if (isset($this->question->formoptions->movecontext)) {
            // Moving question to another context.
            $mform->addElement('questioncategory', 'categorymoveto', get_string('category', 'question'), ['contexts' => $this->contexts->having_cap('moodle/question:add')]);
            $mform->addElement('hidden', 'usecurrentcat', 1);
            $mform->setType('usecurrentcat', PARAM_BOOL);
            $mform->setConstant('usecurrentcat', 1);
        } else {
            // Editing question with permission to move from category or save as new q.
            $currentgrp = [];
            $currentgrp[0] = $mform->createElement('questioncategory', 'category', get_string('categorycurrent', 'question'), ['contexts' => [$this->categorycontext]]);
            if ($this->question->formoptions->canedit || $this->question->formoptions->cansaveasnew) {
                // Not move only form.
                $currentgrp[1] = $mform->createElement('checkbox', 'usecurrentcat', '', get_string('categorycurrentuse', 'question'));
                $mform->setDefault('usecurrentcat', 1);
            }
            $currentgrp[0]->freeze();
            $currentgrp[0]->setPersistantFreeze(false);
            $mform->addGroup($currentgrp, 'currentgrp', get_string('categorycurrent', 'question'), null, false);

            $mform->addElement('questioncategory', 'categorymoveto', get_string('categorymoveto', 'question'), ['contexts' => [$this->categorycontext]]);
            if ($this->question->formoptions->canedit || $this->question->formoptions->cansaveasnew) {
                // Not move only form.
                $mform->disabledIf('categorymoveto', 'usecurrentcat', 'checked');
            }
        }

        $mform->addElement('header', 'generalheader', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('tasktitle', 'qtype_amthauer'), ['size' => 50, 'maxlength' => 255]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('text', 'defaultmark', get_string('maxpoints', 'qtype_amthauer'), ['size' => 7]);
        $mform->setType('defaultmark', PARAM_FLOAT);
        $mform->setDefault('defaultmark', 1);
        $mform->addRule('defaultmark', null, 'required', null, 'client');

        $mform->addElement('editor', 'questiontext', get_string('stem', 'qtype_amthauer'), ['rows' => 15], $this->editoroptions);
        $mform->setType('questiontext', PARAM_RAW);
        $mform->addRule('questiontext', null, 'required', null, 'client');
        $mform->setDefault('questiontext', /*['text' => */get_string('enterstemhere', 'qtype_amthauer')/*]*/);

        $mform->addElement('editor', 'generalfeedback', get_string('generalfeedback', 'question'), ['rows' => 10], $this->editoroptions);
        $mform->setType('generalfeedback', PARAM_RAW);
        $mform->addHelpButton('generalfeedback', 'generalfeedback', 'qtype_amthauer');

        // Any questiontype specific fields.
        $this->definition_inner($mform);

        // TAGS - See API 3 https://docs.moodle.org/dev/Tag_API_3_Specification
        //if (class_exists('core_tag_tag')) { // Started from moodle 3.1 but we dev for 2.6+.
        //    if (core_tag_tag::is_enabled('core_question', 'question')) {
        //        $mform->addElement('header', 'tagshdr', get_string('tags', 'tag'));
        //        $mform->addElement('tags', 'tags', get_string('tags'),
        //            [
        //                'itemtype' => 'question',
        //                'component' => 'core_question',
        //            ]);
        //    }
        //}
        //
        //$this->add_interactive_settings(true, true);

        if (!empty($this->question->id)) {
            $mform->addElement('header', 'createdmodifiedheader', get_string('createdmodifiedheader', 'question'));
            $a = new stdClass();
            if (!empty($this->question->createdby)) {
                $a->time = userdate($this->question->timecreated);
                $a->user = fullname($DB->get_record('user', ['id' => $this->question->createdby]));
            } else {
                $a->time = get_string('unknown', 'question');
                $a->user = get_string('unknown', 'question');
            }
            $mform->addElement('static', 'created', get_string('created', 'question'), get_string('byandon', 'question', $a));
            if (!empty($this->question->modifiedby)) {
                $a = new stdClass();
                $a->time = userdate($this->question->timemodified);
                $a->user = fullname($DB->get_record('user', ['id' => $this->question->modifiedby]));
                $mform->addElement('static', 'modified', get_string('modified', 'question'), get_string('byandon', 'question', $a));
            }
        }
        // Save and Keep Editing and Preview (if possible)
        // LMDL-133.
        $buttonarray = [];
        $buttonarray[] = $mform->createElement('submit', 'updatebutton', get_string('savechangesandcontinueediting', 'question'));
        if ($this->can_preview()) {
            $previewlink = $PAGE->get_renderer('core_question')->question_preview_link($this->question->id, $this->context, true);
            $buttonarray[] = $mform->createElement('static', 'previewlink', '', $previewlink);
        }

        $mform->addGroup($buttonarray, 'updatebuttonar', '', [' '], false);
        $mform->closeHeaderBefore('updatebuttonar');

        if ((!empty($this->question->id)) && (!($this->question->formoptions->canedit || $this->question->formoptions->cansaveasnew))) {
            $mform->hardFreezeAllVisibleExcept([
                'categorymoveto',
                'buttonar',
                'currentgrp',
            ]);
        }

        $this->add_hidden_fields();
        $this->add_action_buttons();
    }

    /**
     * Adds question-type specific form fields.
     *
     * @param object $mform the form being built.
     *
     * @throws coding_exception
     * @throws dml_exception
     */
    protected function definition_inner($mform)
    {
        global $PAGE;
        $amthauerconfig = get_config('qtype_amthauer');
        $jsModule = array(
            'name'     => 'question_edit',
            'fullpath' => '/question/type/amthauer/js/edit/script.js',
            'requires' => array(),
            'strings' => array()
        );
        $vars = [[
            'scoringMethod' => $this->question->options->scoringmethod ?: $amthauerconfig->scoringmethod
        ]];
        $PAGE->requires->js_init_call('M.question_edit.init', $vars, false, $jsModule);

        if (isset($this->question->options->rows) && count($this->question->options->rows) > 0) {
            $this->numberofrows = count($this->question->options->rows);
        } else {
            $this->numberofrows = QTYPE_AMTHAUER_NUMBER_OF_OPTIONS;
        }
        $this->numberofcolumns = QTYPE_AMTHAUER_NUMBER_OF_RESPONSES;
        $this->editoroptions['changeformat'] = 1;
        $mform->addElement('hidden', 'numberofrows', $this->numberofrows);
        $mform->setType('numberofrows', PARAM_INT);
        $mform->addElement('hidden', 'numberofcolumns', $this->numberofcolumns);
        $mform->setType('numberofcolumns', PARAM_INT);

        $mform->addElement('header', 'scoringmethodheader', get_string('scoringmethod', 'qtype_amthauer'));
        // Add the scoring method radio buttons.
        $scoringbuttons = [];
        $scoringbuttons[] = &$mform->createElement('radio', 'scoringmethod', '', get_string('scoringsub2', 'qtype_amthauer'), 'scoringsub2');
        $scoringbuttons[] = &$mform->createElement('radio', 'scoringmethod', '', get_string('scoringsub3', 'qtype_amthauer'), 'scoringsub3');
        //$scoringbuttons[] = &$mform->createElement('radio', 'scoringmethod', '', get_string('scoringsubpoints', 'qtype_amthauer'), 'subpoints', $attributes);
        $mform->addGroup($scoringbuttons, 'radiogroupscoring', get_string('scoringmethod', 'qtype_amthauer'), [' <br/> '], false);
        $mform->addHelpButton('radiogroupscoring', 'scoringmethod', 'qtype_amthauer');
        $mform->setDefault('scoringmethod', 'scoringsub2');

        // Add the shuffleanswers checkbox.
        $mform->addElement('advcheckbox', 'shuffleanswers', get_string('shuffleanswers', 'qtype_amthauer'), null, null, [0, 1]);
        $mform->addHelpButton('shuffleanswers', 'shuffleanswers', 'qtype_amthauer');

        $mform->addElement('header', 'optionsandfeedbackheader', get_string('optionsandfeedback', 'qtype_amthauer'));

        // Add an option text editor, response radio buttons and a feedback editor for each option.
        for ($i = 1; $i <= $this->numberofrows; ++$i) {
            // Add the option editor.
            $mform->addElement('html', '<br/><br/>');
            $mform->addElement('html', '<div class="optionbox">'); // Open div.optionbox.
            $mform->addElement('html', '<div class="option_question">'); // Open div.option_question.
            $mform->addElement('html', '<div class="optionandresponses">'); // Open div.optionbox.
            $mform->addElement('html', '<div class="optiontext">'); // Open div.optiontext.

            $mform->addElement('text', 'option_' . $i, get_string('optionno', 'qtype_amthauer', $i), ['size' => 50]);
            $mform->setDefault('option_' . $i, get_string('enteroptionhere', 'qtype_amthauer'));
            $mform->setType('option_' . $i, PARAM_RAW);
            //$mform->addRule('option_' . $i, null, 'required', null, 'client');

            $mform->addElement('html', '</div>'); // Close div.optiontext.
            $mform->addElement('html', '</div>'); // Close div.optionsandresponses.

            $mform->addElement('html', '</div>'); // Close div.option_question.

            $mform->addElement('html', '<div class="option_answer">');
            // Add the radio buttons for responses.
            $mform->addElement('html', '<div class="responses">'); // Open div.responses.

            for ($j = 1; $j <= $this->numberofcolumns; ++$j) {
                $mform->addElement('html', '<div class="response-item block_form-line">');
                $mform->addElement('text', 'option_column_' . $i . '_' . $j, $j, ['size' => 20]);
                $mform->setDefault('option_column_' . $i . '_' . $j, get_string('enteroptionhere', 'qtype_amthauer'));
                $mform->setType('option_column_' . $i . '_' . $j, PARAM_RAW);
                $mform->addRule('option_column_' . $i . '_' . $j, null, 'required', null, 'client');
                $mform->addElement('radio', 'weightbutton_' . $i, '', '', $j);
                $mform->addElement('html', '</div>');
            }
            $mform->setDefault('weightbutton_' . $i, 1);

            $mform->addElement('html', '</div>'); // Close div.responses.
            $mform->addElement('html', '</div>'); // Close div.option_answer.
            $mform->addElement('html', '</div>'); // Close div.optionbox.
            if ($i < $this->numberofrows) {
                $mform->addElement('html', '<hr>');
            }
        }

        $mform->addElement('hidden', 'qtype');
        $mform->setType('qtype', PARAM_ALPHA);
        $mform->addElement('hidden', 'makecopy');
        $mform->setType('makecopy', PARAM_ALPHA);
        $this->add_hidden_fields();
    }

    /**
     * @param qtype_amthauer_question $question
     *
     * @return object|qtype_amthauer_question
     */
    protected function data_preprocessing($question)
    {
        $question = parent::data_preprocessing($question);

        if (isset($question->options)) {
            $question->shuffleanswers = $question->options->shuffleanswers;
            $question->scoringmethod = $question->options->scoringmethod;
            $question->rows = $question->options->rows;
            $question->columns = $question->options->columns;
            $question->numberofrows = count($question->rows);
            $question->numberofcolumns = QTYPE_AMTHAUER_NUMBER_OF_RESPONSES;
        }

        return $question;
    }

    /**
     * @param array $data
     * @param $files
     *
     * @return array
     * @throws coding_exception
     */
    public function validation($data, $files)
    {
        $errors = parent::validation($data, $files);

        // Check for empty option texts.
        for ($i = 1; $i <= $this->numberofrows; ++$i) {
            for ($j = 1; $j <= $this->numberofcolumns; ++$j) {
                $optioncolumntext = $data['option_column_' . $i . '_' . $j];
                // LMDL-201.
                $optioncolumntext = trim(strip_tags($optioncolumntext, '<img><video><audio><iframe><embed>'));
                // Remove newlines.
                $optioncolumntext = preg_replace("/[\r\n]+/i", '', $optioncolumntext);
                // Remove whitespaces and tabs.
                $optioncolumntext = preg_replace("/[\s\t]+/i", '', $optioncolumntext);
                // Also remove UTF-8 non-breaking whitespaces.
                $optioncolumntext = trim($optioncolumntext, "\xC2\xA0\n");
                // Now check whether the string is empty.
                if (empty($optioncolumntext)) {
                    $errors['option_column_' . $i . '_' . $j] = get_string('mustsupplyvalue', 'qtype_amthauer');
                }
            }
        }

        return $errors;
    }
}
