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
 * @package    moodlecore
 * @subpackage backup-moodle2
 * @copyright  Devlion.ltd <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * restore plugin class that provides the necessary information
 * needed to restore one diagnosticadv qtype plugin
 *
 * @copyright  Devlion.ltd <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_qtype_diagnosticadv_plugin extends restore_qtype_plugin {

    /**
     * Returns the paths to be handled by the plugin at question level
     */
    protected function define_question_plugin_structure() {

        $paths = array();

        // This qtype uses question_answers, add them.
        $this->add_question_question_answers($paths);

        $elename = 'diagnosticadv_option';
        $elepath = $this->get_pathfor('/diagnosticadv_options/diagnosticadv_option');
        $paths[] = new restore_path_element($elename, $elepath);

        $elename = 'diagnosticadv_answer_options';
        $elepath = $this->get_pathfor('/answers/answer/answer_options');
        $paths[] = new restore_path_element($elename, $elepath);

        return $paths; // And we return the interesting paths.
    }

    /**
     * Process the qtype/diagnosticadv_option element
     */
    public function process_diagnosticadv_option($data) {
        global $DB;

        $data = (object)$data;
        unset($data->id);

        // Detect if the question is created or mapped.
        $oldquestionid   = $this->get_old_parentid('question');
        $newquestionid   = $this->get_new_parentid('question');
        $questioncreated = $this->get_mappingid('question_created', $oldquestionid);

        // If the question has been created by restore, we need to create its
        // question_diagnosticadv too.
        if ($questioncreated) {
            // Adjust some columns.
            $data->questionid = $newquestionid;
            // Insert record.
            $options = new \qtype_diagnosticadv\options(0, $data);
            $options->create();
        }
    }

    public function process_diagnosticadv_answer_options($data)
    {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        // Detect if the answer is created or mapped.
        $old_answerid   = $this->get_old_parentid('question_answer');
        $new_answerid   = $this->get_new_parentid('question_answer');

        //echo $oldAnswerId . ' : ' . $newAnswerId;
        //die();

        $answer_created = $this->get_mappingid('question_answer', $old_answerid) ? true : false;

        // If the answer has been created by restore, we need to create its
        // qtype_kekule_manswer_ans_ops too, if they are defined (the gui should ensure this).
        if ($answer_created) {
            $data->answerid = $new_answerid;

            //var_dump($data);
            //die();

            // It is possible for old backup files to contain unique key violations.
            // We need to check to avoid that.
            $tablename = 'qtype_diagnosticadv_answers';

            if (!\qtype_diagnosticadv\answers::get_record(['answerid' => $data->answerid])) {

                unset($data->id);

                $newanswer = new \qtype_diagnosticadv\answers(0, $data);
                $newanswer->create();
                $newitemid = $newanswer->get('id');
                $this->set_mapping($tablename, $oldid, $newitemid);
            }
        }
    }
}
