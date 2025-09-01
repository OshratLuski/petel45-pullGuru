<?php

defined('MOODLE_INTERNAL') || die();

class restore_qtype_diagnosticadvdesc_plugin extends restore_qtype_plugin {
    
     /**
     * Returns the paths to be handled by the plugin at question level
     */
    protected function define_question_plugin_structure() {
        // Define the path for diagnosticadvdesc_option
        $paths = [];

        $elename = 'diagnosticadvdesc_option';
        $elepath = $this->get_pathfor('/diagnosticadvdesc_options/diagnosticadvdesc_option');
        $paths[] = new restore_path_element($elename, $elepath);
        return $paths;  // Return the paths to be processed
    }

    /**
     * Process the qtype/diagnosticadvdesc_option element
     */
    public function process_diagnosticadvdesc_option($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        // Detect if the question is created or mapped
        $oldquestionid = $this->get_old_parentid('question');
        $newquestionid = $this->get_new_parentid('question');
        $questioncreated = $this->get_mappingid('question_created', $oldquestionid) ? true : false;

        // If the question has been created by restore, we need to create its
        // qtype_diagnosticadvdesc record too
        if ($questioncreated) {
            // Adjust some columns
            $data->questionid = $newquestionid;

            // Map the relatedqid if it exists in the backup
            if ($data->relatedqid) {
                $newrelatedqid = $this->get_mappingid('question', $data->relatedqid);
                $data->relatedqid = $newrelatedqid ?: $data->relatedqid;
            }

            if ($data->quizid) {
                $newquizid = $this->get_mappingid('quiz', $data->quizid);
                $data->quizid = $newquizid ?: $data->quizid;
            }

            $existing = $DB->get_record('qtype_diagnosticadvdesc', ['questionid' => $data->questionid]);
            if (!$existing) {
                $newitemid = $DB->insert_record('qtype_diagnosticadvdesc', $data);
                $this->set_mapping('qtype_diagnosticadvdesc', $oldid, $newitemid);
            } else {
                $existing->relatedqid = $data->relatedqid;
                $existing->quizid = $data->quizid;
                $existing->timemodified = time();
                $DB->update_record('qtype_diagnosticadvdesc', $existing);
            }
        }
    }

    /**
     * After restore processing, handle any additional mappings or cleanup
     */
    public function after_execute_question() {
        // No additional file annotations needed, as this qtype doesn't use files
        // Add file annotations here if your qtype starts using files in the future
    }
}