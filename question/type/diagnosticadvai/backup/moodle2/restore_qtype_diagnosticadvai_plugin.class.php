<?php

defined('MOODLE_INTERNAL') || die();

class restore_qtype_diagnosticadvai_plugin extends restore_qtype_plugin {
    
    /**
     * Returns the paths to be handled by the plugin at question level
     */
    protected function define_question_plugin_structure() {
        $paths = [];

         // Define the path for diagnosticadvai_option
        $elename = 'diagnosticadvai_option';
        $elepath = $this->get_pathfor('/diagnosticadvai_options/diagnosticadvai_option');
        $paths[] = new restore_path_element($elename, $elepath);

        return $paths; // Return the paths to be processed
    }

    /**
     * Process the qtype/diagnosticadvai_option element
     */
    public function process_diagnosticadvai_option($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $oldquestionid = $this->get_old_parentid('question');
        $newquestionid = $this->get_new_parentid('question');
        $questioncreated = $this->get_mappingid('question_created', $oldquestionid) ? true : false;

        if ($questioncreated) {
            $data->questionid = $newquestionid;

            if (!empty($data->relatedqid)) {
                $newrelatedqid = $this->get_mappingid('question', $data->relatedqid);
                $data->relatedqid = $newrelatedqid ?: $data->relatedqid;
            } else {
                $data->relatedqid = 0;
            }

            if ($data->quizid) {
                $newquizid = $this->get_mappingid('quiz', $data->quizid);
                $data->quizid = $newquizid ?: $data->quizid;
            }

            if (!empty($data->cmid)) {
                $newcmid = $this->get_mappingid('course_module', $data->cmid);
                $data->cmid = $newcmid ?: $data->cmid;
            } else {
                $data->cmid = 0;
            }

            $existing = $DB->get_record('qtype_diagadvai_options', ['questionid' => $data->questionid]);
            if (!$existing) {
                $newitemid = $DB->insert_record('qtype_diagadvai_options', $data);
                $this->set_mapping('qtype_diagadvai_options', $oldid, $newitemid);
            } else {
                $existing->relatedqid = $data->relatedqid;
                $existing->quizid = $data->quizid;
                $existing->cmid = $data->cmid;
                $existing->temperature = $data->temperature;
                $existing->teacherprompt = $data->teacherprompt;
                $existing->timecreated = $data->timecreated;
                $DB->update_record('qtype_diagadvai_options', $existing);
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