<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Restore plugin class that provides the necessary information
 * needed to restore one amthauer6 qtype plugin.
 */
class restore_qtype_amthauer6_plugin extends restore_qtype_plugin
{
    /**
     * Returns the paths to be handled by the plugin at question level.
     */
    protected function define_question_plugin_structure()
    {
        $result = [];

        // We used get_recommended_name() so this works.
        $elename = 'amthauer6';
        $elepath = $this->get_pathfor('/amthauer6');
        $result[] = new restore_path_element($elename, $elepath);

        // We used get_recommended_name() so this works.
        $elename = 'grade';
        $elepath = $this->get_pathfor('/grades/grade');
        $result[] = new restore_path_element($elename, $elepath);

        return $result;
    }

    /**
     * Process the qtype/multichoice element.
     */
    public function process_amthauer6($data)
    {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        // Detect if the question is created or mapped.
        $oldquestionid = $this->get_old_parentid('question');
        $newquestionid = $this->get_new_parentid('question');

        $questioncreated = (bool)$this->get_mappingid('question_created', $oldquestionid);

        // If the question has been created by restore, we need to create its
        // qtype_amthauer6_options too.
        if ($questioncreated) {
            // Adjust some columns.
            $data->questionid = $newquestionid;
            // Insert record.
            $newitemid = $DB->insert_record('qtype_amthauer6_options', $data);
            // Create mapping (needed for decoding links).
            $this->set_mapping('qtype_amthauer6_options', $oldid, $newitemid);
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
     * Process the qtype/amthauer6/grades/grade element.
     */
    public function process_grade($data)
    {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $oldquestionid = $this->get_old_parentid('question');
        $newquestionid = $this->get_new_parentid('question');

        if ($this->is_question_created()) {
            $data->questionid = $newquestionid;
            $newitemid = $DB->insert_record('qtype_amthauer6_grades', $data);
        } else {
            $originalrecords = $DB->get_records('qtype_amthauer6_grades', ['questionid' => $newquestionid]);
            foreach ($originalrecords as $record) {
                if ($data->amount == $record->amount && $data->value == $record->value && $data->userid == $record->userid) {
                    $newitemid = $record->id;
                }
            }
        }
        if (!$newitemid) {
            $info = new stdClass();
            $info->filequestionid = $oldquestionid;
            $info->dbquestionid = $newquestionid;
            $info->answer = $data->value;
            throw new restore_step_exception('error_question_answers_missing_in_db', $info);
        } else {
            $this->set_mapping('qtype_amthauer6_grades', $oldid, $newitemid);
        }
    }

    public function recode_response($questionid, $sequencenumber, array $response)
    {
        if (array_key_exists('_order', $response)) {
            $response['_order'] = $this->recode_option_order($response['_order']);
        }

        return $response;
    }
}
