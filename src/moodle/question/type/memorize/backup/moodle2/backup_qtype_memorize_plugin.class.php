<?php

defined('MOODLE_INTERNAL') || die();

/**
 * Provides the backup for memorize questions.
 */
class backup_qtype_memorize_plugin extends backup_qtype_plugin
{
    /**
     * Returns the qtype information to attach to the question element.
     */
    protected function define_question_plugin_structure()
    {
        // Define the virtual plugin element with the condition to fulfill.
        $plugin = $this->get_plugin_element(null, '../../qtype', 'memorize');
        // Create one standard named plugin element (the visible container).
        $name = $this->get_recommended_name();
        $pluginwrapper = new backup_nested_element($name);
        // Connect the visible container ASAP.
        $plugin->add_child($pluginwrapper);
        // Now create the qtype own structures.
        $memorize = new backup_nested_element('memorize', ['id',], ['scoringmethod', 'shuffleanswers', 'numberofrows']);
        $rows = new backup_nested_element('rows');
        $row = new backup_nested_element('row', ['id',], ['number', 'optiontext']);
        // Now the qtype tree.
        $pluginwrapper->add_child($memorize);
        $pluginwrapper->add_child($rows);
        $rows->add_child($row);
        // Set sources to populate the data.
        $memorize->set_source_table('qtype_memorize_options', ['questionid' => backup::VAR_PARENTID]);
        $row->set_source_table('qtype_memorize_rows', ['questionid' => backup::VAR_PARENTID], 'number ASC');

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
            'optiontext' => 'qtype_memorize_rows',
            'feedbacktext' => 'qtype_memorize_rows',
        ];
    }
}
