<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Restore plugin class that provides the necessary information
 * needed to restore one multirowswitch qtype plugin.
 */
class restore_qtype_multirowswitch_plugin extends restore_qtype_plugin {

    /**
     * Returns the paths to be handled by the plugin at question level.
     */
    protected function define_question_plugin_structure() {
        $result = array();

        // We used get_recommended_name() so this works.
        $elename = 'multirowswitch';
        $elepath = $this->get_pathfor('/multirowswitch');
        $result[] = new restore_path_element($elename, $elepath);

        // We used get_recommended_name() so this works.
        $elename = 'row';
        $elepath = $this->get_pathfor('/rows/row');
        $result[] = new restore_path_element($elename, $elepath);

        // We used get_recommended_name() so this works.
        $elename = 'column';
        $elepath = $this->get_pathfor('/columns/column');
        $result[] = new restore_path_element($elename, $elepath);

        // We used get_recommended_name() so this works.
        $elename = 'weight';
        $elepath = $this->get_pathfor('/weights/weight');
        $result[] = new restore_path_element($elename, $elepath);

        return $result;
    }

    /**
     * Process the qtype/multichoice element.
     */
    public function process_multirowswitch($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        // Detect if the question is created or mapped.
        $oldquestionid = $this->get_old_parentid('question');
        $newquestionid = $this->get_new_parentid('question');

        $questioncreated = (bool) $this->get_mappingid('question_created', $oldquestionid);

        // If the question has been created by restore, we need to create its
        // qtype_multirowswitch_options too.
        if ($questioncreated) {
            // Adjust some columns.
            $data->questionid = $newquestionid;
            // Insert record.
            $newitemid = $DB->insert_record('qtype_multirowswitch_options', $data);
            // Create mapping (needed for decoding links).
            $this->set_mapping('qtype_multirowswitch_options', $oldid, $newitemid);
        }
    }

    /**
     * Detect if the question is created or mapped.
     *
     * @return bool
     */
    protected function is_question_created() {
        $oldquestionid = $this->get_old_parentid('question');
        $questioncreated = (bool) $this->get_mappingid('question_created', $oldquestionid);

        return $questioncreated;
    }

    /**
     * Process the qtype/multirowswitch/columns/column.
     */
    public function process_column($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        $oldquestionid = $this->get_old_parentid('question');
        $newquestionid = $this->get_new_parentid('question');

        if ($this->is_question_created()) {
            $data->questionid = $newquestionid;
            $newitemid = $DB->insert_record('qtype_multirowswitch_columns', $data);
        } else {
            $originalrecords = $DB->get_records('qtype_multirowswitch_columns', array('questionid' => $newquestionid));
            foreach ($originalrecords as $record) {
                if ($data->number == $record->number && $data->rowid == $record->rowid) {
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
            $this->set_mapping('qtype_multirowswitch_columns', $oldid, $newitemid);
        }
    }

    /**
     * Process the qtype/multirowswitch/rows/row element.
     */
    public function process_row($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        $oldquestionid = $this->get_old_parentid('question');
        $newquestionid = $this->get_new_parentid('question');

        if ($this->is_question_created()) {
            $data->questionid = $newquestionid;
            $newitemid = $DB->insert_record('qtype_multirowswitch_rows', $data);
        } else {
            $originalrecords = $DB->get_records('qtype_multirowswitch_rows', array('questionid' => $newquestionid));
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
            $info->answer = $data->optiontext;
            throw new restore_step_exception('error_question_answers_missing_in_db', $info);
        } else {
            $this->set_mapping('qtype_multirowswitch_rows', $oldid, $newitemid);
        }
    }
    /**
     * Process the qtype/multirowswitch/weights/weight element.
     */
    public function process_weight($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        $oldquestionid = $this->get_old_parentid('question');
        $newquestionid = $this->get_new_parentid('question');

        if ($this->is_question_created()) {
            $data->questionid = $newquestionid;
            $newitemid = $DB->insert_record('qtype_multirowswitch_weights', $data);
        } else {
            $originalrecords = $DB->get_records('qtype_multirowswitch_weights', array('questionid' => $newquestionid));
            foreach ($originalrecords as $record) {
                if ($data->rownumber == $record->rownumber
                    && $data->columnnumber == $record->columnnumber) {
                    $newitemid = $record->id;
                }
            }
        }
        if (!$newitemid) {
            $info = new stdClass();
            $info->filequestionid = $oldquestionid;
            $info->dbquestionid = $newquestionid;
            $info->answer = $data->weight;
            throw new restore_step_exception('error_question_answers_missing_in_db', $info);
        } else {
            $this->set_mapping('qtype_multirowswitch_weights', $oldid, $newitemid);
        }
    }

    public function recode_response($questionid, $sequencenumber, array $response) {
        if (array_key_exists('_order', $response)) {
            $response['_order'] = $this->recode_option_order($response['_order']);
        }
        return $response;
    }

    /**
     * Recode the option order as stored in the response.
     *
     * @param string $order the original order.
     *
     * @return string the recoded order.
     */
    protected function recode_option_order($order) {
        $neworder = array();
        foreach (explode(',', $order) as $id) {
            if ($newid = $this->get_mappingid('qtype_multirowswitch_rows', $id)) {
                $neworder[] = $newid;
            }
        }
        return implode(',', $neworder);
    }

    /**
     * Return the contents of this qtype to be processed by the links decoder.
     */
    public static function define_decode_contents() {
        $contents = array();

        $fields = array('optiontext', 'optionfeedback'
        );
        $contents[] = new restore_decode_content('qtype_multirowswitch_rows', $fields, 'qtype_multirowswitch_rows');

        return $contents;
    }
}
