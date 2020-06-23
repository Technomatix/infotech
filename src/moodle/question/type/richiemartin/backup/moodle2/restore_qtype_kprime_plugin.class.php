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
 * Restore plugin class that provides the necessary information
 * needed to restore one richiemartin qtype plugin.
 */
class restore_qtype_richiemartin_plugin extends restore_qtype_plugin
{
    /**
     * Returns the paths to be handled by the plugin at question level.
     */
    protected function define_question_plugin_structure()
    {
        $result = [];

        // We used get_recommended_name() so this works.
        $elename = 'richiemartin';
        $elepath = $this->get_pathfor('/richiemartin');
        $result[] = new restore_path_element($elename, $elepath);

        // We used get_recommended_name() so this works.
        $elename = 'column';
        $elepath = $this->get_pathfor('/columns/column');
        $result[] = new restore_path_element($elename, $elepath);

        return $result;
    }

    /**
     * Process the qtype/multichoice element.
     */
    public function process_richiemartin($data)
    {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        // Detect if the question is created or mapped.
        $oldquestionid = $this->get_old_parentid('question');
        $newquestionid = $this->get_new_parentid('question');

        $questioncreated = (bool)$this->get_mappingid('question_created', $oldquestionid);

        // If the question has been created by restore, we need to create its
        // qtype_richiemartin_options too.
        if ($questioncreated) {
            // Adjust some columns.
            $data->questionid = $newquestionid;
            // Insert record.
            $newitemid = $DB->insert_record('qtype_richiemartin_options', $data);
            // Create mapping (needed for decoding links).
            $this->set_mapping('qtype_richiemartin_options', $oldid, $newitemid);
        }
    }

    /**
     * Detect if the question is created or mapped.
     *
     * @return bool
     */
    protected function is_question_created()
    {
        $oldquestionid = $this->get_old_parentid('question');
        $questioncreated = (bool)$this->get_mappingid('question_created', $oldquestionid);

        return $questioncreated;
    }

    /**
     * Process the qtype/richiemartin/columns/column.
     */
    public function process_column($data)
    {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $oldquestionid = $this->get_old_parentid('question');
        $newquestionid = $this->get_new_parentid('question');

        if ($this->is_question_created()) {
            $data->questionid = $newquestionid;
            $newitemid = $DB->insert_record('qtype_richiemartin_columns', $data);
        } else {
            $originalrecords = $DB->get_records('qtype_richiemartin_columns', ['questionid' => $newquestionid]);
            foreach ($originalrecords as $record) {
                if ($data->number == $record->number) {
                    $newitemid = $record->id;
                }
            }
        }
        if (!$newitemid) {
            $info = new stdClass();
            $info->filequestionid = $oldquestionid;
            $info->dbquestionid = $newquestionid;
            $info->answer = $data->responsetext;
            throw new restore_step_exception('error_question_answers_missing_in_db', $info);
        } else {
            $this->set_mapping('qtype_richiemartin_columns', $oldid, $newitemid);
        }
    }

    //public function recode_response($questionid, $sequencenumber, array $response) {
    //    if (array_key_exists('_order', $response)) {
    //        $response['_order'] = $this->recode_option_order($response['_order']);
    //    }
    //    return $response;
    //}
}
