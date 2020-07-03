<?php

defined('MOODLE_INTERNAL') || die();

/**
 * Provides the backup for amthauer questions.
 */
class backup_qtype_amthauer_plugin extends backup_qtype_plugin {

    /**
     * Returns the qtype information to attach to the question element.
     */
    protected function define_question_plugin_structure() {
        // Define the virtual plugin element with the condition to fulfill.
        $plugin = $this->get_plugin_element(null, '../../qtype', 'amthauer');
        // Create one standard named plugin element (the visible container).
        $name = $this->get_recommended_name();
        $pluginwrapper = new backup_nested_element($name);
        // Connect the visible container ASAP.
        $plugin->add_child($pluginwrapper);
        // Now create the qtype own structures.
        $amthauer = new backup_nested_element('amthauer', array('id'
        ), array('scoringmethod', 'shuffleanswers', 'numberofrows', 'numberofcolumns'));
        $rows = new backup_nested_element('rows');
        $row = new backup_nested_element('row', array('id'
        ), array('number', 'optiontext', 'optiontextformat', 'optionfeedback', 'optionfeedbackformat'));
        $columns = new backup_nested_element('columns');
        $column = new backup_nested_element('column', array('id'), array('rowid','number', 'responsetext', 'responsetextformat'));
        $weights = new backup_nested_element('weights');
        $weight = new backup_nested_element('weight', array('id'), array('rownumber', 'columnnumber', 'weight'));
        $grades = new backup_nested_element('grades');
        $grade = new backup_nested_element('grade', array('id'), array('amount', 'value', 'userid'));
        // Now the qtype tree.
        $pluginwrapper->add_child($amthauer);
        $pluginwrapper->add_child($rows);
        $pluginwrapper->add_child($columns);
        $pluginwrapper->add_child($weights);
        $pluginwrapper->add_child($grades);
        $rows->add_child($row);
        $columns->add_child($column);
        $weights->add_child($weight);
        $grades->add_child($grade);
        // Set sources to populate the data.
        $amthauer->set_source_table('qtype_amthauer_options', array('questionid' => backup::VAR_PARENTID));
        $row->set_source_table('qtype_amthauer_rows', array('questionid' => backup::VAR_PARENTID), 'number ASC');
        $column->set_source_table('qtype_amthauer_columns', array('questionid' => backup::VAR_PARENTID), 'number ASC');
        $weight->set_source_table('qtype_amthauer_weights', array('questionid' => backup::VAR_PARENTID));
        $grade->set_source_table('qtype_amthauer_grades', array('questionid' => backup::VAR_PARENTID));
        // We don't need to annotate ids nor files.
        return $plugin;
    }

    /**
     * Returns one array with filearea => mappingname elements for the qtype.
     *
     * Used by {@link get_components_and_fileareas} to know about all the qtype
     * files to be processed both in backup and restore.
     */
    public static function get_qtype_fileareas() {
        return array('optiontext' => 'qtype_amthauer_rows', 'feedbacktext' => 'qtype_amthauer_rows'
        );
    }
}
