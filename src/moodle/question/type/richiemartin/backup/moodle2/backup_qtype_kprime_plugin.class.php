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

/**
 * Provides the backup for richiemartin questions.
 */
class backup_qtype_richiemartin_plugin extends backup_qtype_plugin
{
    /**
     * Returns the qtype information to attach to the question element.
     */
    protected function define_question_plugin_structure()
    {
        // Define the virtual plugin element with the condition to fulfill.
        $plugin = $this->get_plugin_element(null, '../../qtype', 'richiemartin');

        // Create one standard named plugin element (the visible container).
        $name = $this->get_recommended_name();
        $pluginwrapper = new backup_nested_element($name);

        // Connect the visible container ASAP.
        $plugin->add_child($pluginwrapper);

        // Now create the qtype own structures.
        $richiemartin = new backup_nested_element('richiemartin', ['id'], ['shuffleanswers', 'numberofrows', 'numberofcolumns']);
        $columns = new backup_nested_element('columns');
        $column = new backup_nested_element('column', ['id'], ['number', 'responsetext', 'responsetextformat']);

        // Now the qtype tree.
        $pluginwrapper->add_child($richiemartin);
        $pluginwrapper->add_child($columns);
        $columns->add_child($column);

        // Set sources to populate the data.
        $richiemartin->set_source_table('qtype_richiemartin_options', ['questionid' => backup::VAR_PARENTID]);
        $column->set_source_table('qtype_richiemartin_columns', ['questionid' => backup::VAR_PARENTID], 'number ASC');

        // We don't need to annotate ids nor files.
        return $plugin;
    }
}
