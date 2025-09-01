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
 * Provides the information to backup diagnosticadv questions
 *
 * @copyright  Devlion.ltd <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_qtype_diagnosticadv_plugin extends backup_qtype_plugin {

    /**
     * Returns the qtype information to attach to question element
     */
    protected function define_question_plugin_structure() {

        // Define the virtual plugin element with the condition to fulfill.
        // Note: we use $this->pluginname so for extended plugins this will work
        // automatically: diagnosticadvsimple and diagnosticadvmulti.
        $plugin = $this->get_plugin_element(null, '../../qtype', $this->pluginname);

        // Create one standard named plugin element (the visible container).
        $pluginwrapper = new backup_nested_element($this->get_recommended_name());

        // Connect the visible container ASAP.
        $plugin->add_child($pluginwrapper);

        // This qtype uses standard question_answers, add them here
        // to the tree before any other information that will use them.
        $this->add_question_question_answers($pluginwrapper);

        $diagnosticadvoptions = new backup_nested_element('diagnosticadv_options');
        $diagnosticadvoption = new backup_nested_element('diagnosticadv_option', array('id'), array(
            'security', 'required', 'teacherdesc', 'hidemark', 'usecase', 'anonymous', 'aianalytics',
            'promt', 'temperature', 'usermodified', 'timecreated', 'timemodified'));

        $pluginwrapper->add_child($diagnosticadvoptions);
        $diagnosticadvoptions->add_child($diagnosticadvoption);

        $diagnosticadvoption->set_source_table('qtype_diagnosticadv_options',
                array('questionid' => backup::VAR_PARENTID));

        // Don't need to annotate ids nor files.

        return $plugin;
    }

    /**
     * Returns one array with filearea => mappingname elements for the qtype
     *
     * Used by {@link get_components_and_fileareas} to know about all the qtype
     * files to be processed both in backup and restore.
     */
    public static function get_qtype_fileareas() {
        return array(
            'instruction' => 'question_created',
            'correctfeedback' => 'question_created',
            'partiallycorrectfeedback' => 'question_created',
            'incorrectfeedback' => 'question_created');
    }

    /**
     * Attach to $element (usually questions) the needed backup structures
     * for question_answers for a given question
     * Used by various qtypes (calculated, essay, multianswer,
     * multichoice, numerical, shortanswer, truefalse)
     */
    protected function add_question_question_answers($element) {
        // Check $element is one nested_backup_element
        if (! $element instanceof backup_nested_element) {
            throw new backup_step_exception('question_answers_bad_parent_element', $element);
        }

        // Define the elements
        $answers = new backup_nested_element('answers');
        $answer = new backup_nested_element('answer', array('id'),
            array('answertext', 'answerformat', 'fraction', 'feedback',
                'feedbackformat'));
        $answer_options = new backup_nested_element('answer_options', array('id'),
            array('custom'));

        // Build the tree
        $element->add_child($answers);
        $answers->add_child($answer);
        $answer->add_child($answer_options);

        // Set the sources
        $answer->set_source_table('question_answers', array('question' => backup::VAR_PARENTID), 'id ASC');

        $answer_options->set_source_table('qtype_diagnosticadv_answers', array('answerid' => backup::VAR_PARENTID));

        // Aliases
        $answer->set_source_alias('answer', 'answertext');

        // don't need to annotate ids nor files
    }
}
