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

require_once($CFG->dirroot . '/question/type/edit_question_form.php');
require_once($CFG->dirroot . '/question/type/richiemartin/lib.php');
require_once($CFG->dirroot . '/question/engine/bank.php');

/**
 * Richiemartin editing form definition.
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_richiemartin_edit_form extends question_edit_form
{
    private $numberofrows;

    private $numberofcolumns;

    /**
     * (non-PHPdoc).
     *
     * @see myquestion_edit_form::qtype()
     */
    public function qtype()
    {
        return 'richiemartin';
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
        global $COURSE, $CFG, $DB;

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
        $mform->addElement('text', 'name', get_string('tasktitle', 'qtype_richiemartin'), ['size' => 50, 'maxlength' => 255]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('text', 'defaultmark', get_string('maxpoints', 'qtype_richiemartin'), ['size' => 7]);
        $mform->setType('defaultmark', PARAM_FLOAT);
        $mform->setDefault('defaultmark', 1);
        $mform->addRule('defaultmark', null, 'required', null, 'client');

        $mform->addElement('editor', 'questiontext', get_string('stem', 'qtype_richiemartin'), ['rows' => 15], $this->editoroptions);
        $mform->setType('questiontext', PARAM_RAW);
        $mform->addRule('questiontext', null, 'required', null, 'client');
        $mform->setDefault('questiontext', ['text' => get_string('enterstemhere', 'qtype_richiemartin')]);

        $mform->addElement('editor', 'generalfeedback', get_string('generalfeedback', 'question'), ['rows' => 10], $this->editoroptions);
        $mform->setType('generalfeedback', PARAM_RAW);
        $mform->addHelpButton('generalfeedback', 'generalfeedback', 'qtype_richiemartin');

        // Any questiontype specific fields.
        $this->definition_inner($mform);

        // Блок создано обновлено
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
        global $PAGE;
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
     * @throws coding_exception
     */
    protected function definition_inner($mform)
    {
        if (isset($this->question->options->rows) && count($this->question->options->rows) > 0) {
            $this->numberofrows = count($this->question->options->rows);
        } else {
            $this->numberofrows = QTYPE_RICHIEMARTIN_NUMBER_OF_OPTIONS;
        }
        if (isset($this->question->options->columns) && count($this->question->options->columns) > 0) {
            $this->numberofcolumns = count($this->question->options->columns);
        } else {
            $this->numberofcolumns = QTYPE_RICHIEMARTIN_NUMBER_OF_RESPONSES;
        }
        $this->editoroptions['changeformat'] = 1;
        $mform->addElement('hidden', 'numberofrows', $this->numberofrows);
        $mform->setType('numberofrows', PARAM_INT);
        $mform->addElement('hidden', 'numberofcolumns', $this->numberofcolumns);
        $mform->setType('numberofcolumns', PARAM_INT);

        // Add the shuffleanswers checkbox.
        $mform->addElement('advcheckbox', 'shuffleanswers', get_string('shuffleanswers', 'qtype_richiemartin'), null, null, [0, 1]);
        $mform->addHelpButton('shuffleanswers', 'shuffleanswers', 'qtype_richiemartin');

        $mform->addElement('header', 'optionsandfeedbackheader', get_string('optionsandfeedback', 'qtype_richiemartin'));

        for ($i = 1; $i <= $this->numberofcolumns; ++$i) {
            $label = '';
            if ($i == 1) {
                $label = get_string('responsetexts', 'qtype_richiemartin');
            }
            $mform->addElement('text', 'responsetext_' . $i, $label, ['size' => 6]);
            $mform->setType('responsetext_' . $i, PARAM_TEXT);
            $mform->addRule('responsetext_' . $i, null, 'required', null, 'client');

            if ($this->numberofcolumns == 4) {
                $mform->setDefault('responsetext_' . $i, get_string('responsetext' . $i, 'qtype_richiemartin'));
            }
        }

        $mform->addElement('hidden', 'qtype');
        $mform->setType('qtype', PARAM_ALPHA);
        $mform->addElement('hidden', 'makecopy');
        $mform->setType('makecopy', PARAM_ALPHA);
        $this->add_hidden_fields();
    }

    /**
     * (non-PHPdoc).
     *
     * @see question_edit_form::data_preprocessing()
     */
    protected function data_preprocessing($question)
    {
        $question = parent::data_preprocessing($question);

        if (isset($question->options)) {
            $question->shuffleanswers = $question->options->shuffleanswers;
            $question->rows = $question->options->rows;
            $question->columns = $question->options->columns;
            $question->numberofrows = count($question->rows);
            $question->numberofcolumns = count($question->columns);
        }

        return $question;
    }

    /**
     * @param $data
     * @param $files
     *
     * @return array
     * @throws coding_exception
     * @see question_edit_form::validation()
     */
    public function validation($data, $files)
    {
        $errors = parent::validation($data, $files);

        // Check for empty response texts.
        for ($j = 1; $j <= $this->numberofcolumns; ++$j) {
            if (trim(strip_tags($data['responsetext_' . $j])) == false) {
                $errors['responsetext_' . $j] = get_string('mustsupplyvalue', 'qtype_richiemartin');
            }
        }

        return $errors;
    }
}
