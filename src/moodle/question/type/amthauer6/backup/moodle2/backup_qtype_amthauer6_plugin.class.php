<?php

defined('MOODLE_INTERNAL') || die();

/**
 * Provides the backup for amthauer6 questions.
 */
class backup_qtype_amthauer6_plugin extends backup_qtype_plugin
{
    /**
     * Returns the qtype information to attach to the question element.
     */
    protected function define_question_plugin_structure()
    {
        // Define the virtual plugin element with the condition to fulfill.
        $plugin = $this->get_plugin_element(null, '../../qtype', 'amthauer6');
        // Create one standard named plugin element (the visible container).
        $name = $this->get_recommended_name();
        $pluginwrapper = new backup_nested_element($name);
        // Connect the visible container ASAP.
        $plugin->add_child($pluginwrapper);
        // Now create the qtype own structures.
        $amthauer6 = new backup_nested_element('amthauer6', ['id'], ['shuffleanswers', 'numberofrows']);
        $grades = new backup_nested_element('grades');
        $grade = new backup_nested_element('grade', ['id'], ['amount', 'value', 'userid']);
        // Now the qtype tree.
        $pluginwrapper->add_child($amthauer6);
        $pluginwrapper->add_child($grades);
        $grades->add_child($grade);
        // Set sources to populate the data.
        $amthauer6->set_source_table('qtype_amthauer6_options', ['questionid' => backup::VAR_PARENTID]);
        $grade->set_source_table('qtype_amthauer6_grades', ['questionid' => backup::VAR_PARENTID]);

        // We don't need to annotate ids nor files.
        return $plugin;
    }
}
