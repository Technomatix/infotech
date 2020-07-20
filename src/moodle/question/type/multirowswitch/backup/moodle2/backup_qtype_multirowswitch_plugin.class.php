<?php

defined('MOODLE_INTERNAL') || die();

/**
 * Provides the backup for multirowswitch questions.
 */
class backup_qtype_multirowswitch_plugin extends backup_qtype_plugin
{
    /**
     * Returns the qtype information to attach to the question element.
     */
    protected function define_question_plugin_structure()
    {
        // Define the virtual plugin element with the condition to fulfill.
        $plugin = $this->get_plugin_element(null, '../../qtype', 'multirowswitch');
        // Create one standard named plugin element (the visible container).
        $name = $this->get_recommended_name();
        $pluginwrapper = new backup_nested_element($name);
        // Connect the visible container ASAP.
        $plugin->add_child($pluginwrapper);
        // Now create the qtype own structures.
        $multirowswitch = new backup_nested_element('multirowswitch', ['id',], ['scoringmethod', 'shuffleanswers', 'numberofrows']);
        $rows = new backup_nested_element('rows');
        $row = new backup_nested_element('row', ['id',], ['number', 'optiontext']);
        $columns = new backup_nested_element('columns');
        $column = new backup_nested_element('column', array('id'), array('rowid','number', 'responsetext'));
        $weights = new backup_nested_element('weights');
        $weight = new backup_nested_element('weight', array('id'), array('rownumber', 'columnnumber', 'weight'));
        // Now the qtype tree.
        $pluginwrapper->add_child($multirowswitch);
        $pluginwrapper->add_child($rows);
        $pluginwrapper->add_child($columns);
        $pluginwrapper->add_child($weights);
        $rows->add_child($row);
        // Set sources to populate the data.
        $multirowswitch->set_source_table('qtype_multirowswitch_options', ['questionid' => backup::VAR_PARENTID]);
        $row->set_source_table('qtype_multirowswitch_rows', ['questionid' => backup::VAR_PARENTID], 'number ASC');
        $column->set_source_table('qtype_amthauer_columns', array('questionid' => backup::VAR_PARENTID), 'number ASC');
        $weight->set_source_table('qtype_amthauer_weights', array('questionid' => backup::VAR_PARENTID));

        // We don't need to annotate ids nor files.
        return $plugin;
    }

    /**
     * Returns one array with filearea => mappingname elements for the qtype.
     *
     * Used by {@link get_components_and_fileareas} to know about all the qtype
     * files to be processed both in backup and restore.
     */
    public static function get_qtype_fileareas()
    {
        return [
            'optiontext' => 'qtype_multirowswitch_rows',
            'feedbacktext' => 'qtype_multirowswitch_rows',
        ];
    }
}
