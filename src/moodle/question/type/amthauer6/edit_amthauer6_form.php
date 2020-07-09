<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/edit_question_form.php');
require_once($CFG->dirroot . '/question/type/amthauer6/lib.php');
require_once($CFG->dirroot . '/question/engine/bank.php');

/**
 * Amthauer sub 6 editing form definition.
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_amthauer6_edit_form extends question_edit_form
{
    private $numberofrows;

    /**
     * @return string
     */
    public function qtype()
    {
        return 'amthauer6';
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
        $mform->addElement('text', 'name', get_string('tasktitle', 'qtype_amthauer6'), ['size' => 50, 'maxlength' => 255]);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('text', 'defaultmark', get_string('maxpoints', 'qtype_amthauer6'), ['size' => 7]);
        $mform->setType('defaultmark', PARAM_FLOAT);
        $mform->setDefault('defaultmark', 1);
        $mform->addRule('defaultmark', null, 'required', null, 'client');

        $mform->addElement('editor', 'questiontext', get_string('stem', 'qtype_amthauer6'), ['rows' => 15], $this->editoroptions);
        $mform->setType('questiontext', PARAM_RAW);
        $mform->addRule('questiontext', null, 'required', null, 'client');
        $mform->setDefault('questiontext', /*['text' => */ get_string('enterstemhere', 'qtype_amthauer6')/*]*/);

        $mform->addElement('editor', 'generalfeedback', get_string('generalfeedback', 'question'), ['rows' => 10], $this->editoroptions);
        $mform->setType('generalfeedback', PARAM_RAW);
        $mform->addHelpButton('generalfeedback', 'generalfeedback', 'qtype_amthauer6');

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
     */
    protected function definition_inner($mform)
    {
        if (isset($this->question->options->rows) && count($this->question->options->rows) > 0) {
            $this->numberofrows = count($this->question->options->rows);
        } else {
            $this->numberofrows = QTYPE_AMTHAUER6_NUMBER_OF_OPTIONS;
        }
        $this->editoroptions['changeformat'] = 1;
        $mform->addElement('hidden', 'numberofrows', $this->numberofrows);
        $mform->setType('numberofrows', PARAM_INT);

        $this->editoroptions['changeformat'] = 1;

        // Add the shuffleanswers checkbox.
        $mform->addElement('advcheckbox', 'shuffleanswers', get_string('shuffleanswers', 'qtype_amthauer6'), null, null, [0, 1]);
        $mform->addHelpButton('shuffleanswers', 'shuffleanswers', 'qtype_amthauer6');

        $mform->addElement('hidden', 'qtype');
        $mform->setType('qtype', PARAM_ALPHA);
        $mform->addElement('hidden', 'makecopy');
        $mform->setType('makecopy', PARAM_ALPHA);
        $this->add_hidden_fields();
    }

    /**
     * @param qtype_amthauer6_question $question
     *
     * @return object|qtype_amthauer6_question
     */
    protected function data_preprocessing($question)
    {
        $question = parent::data_preprocessing($question);

        if (isset($question->options)) {
            $question->shuffleanswers = $question->options->shuffleanswers;
        }

        return $question;
    }

    /**
     * @param array $data
     * @param $files
     *
     * @return array
     */
    public function validation($data, $files)
    {
        return parent::validation($data, $files);
    }
}
