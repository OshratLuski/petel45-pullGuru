<?php

defined('MOODLE_INTERNAL') || die();

class backup_qtype_diagnosticadvai_plugin extends backup_qtype_plugin {
   
    /**
     * Returns the qtype information to attach to question element
     */
    protected function define_question_plugin_structure() {
        $plugin = $this->get_plugin_element(null, '../../qtype', 'diagnosticadvai');
        $pluginwrapper = new backup_nested_element($this->get_recommended_name());

        $plugin->add_child($pluginwrapper);

        $diagnosticadvaioptions = new backup_nested_element('diagnosticadvai_options');
        $diagnosticadvaioption = new backup_nested_element('diagnosticadvai_option', ['id'], [
            'questionid', 'relatedqid', 'quizid', 'cmid', 'temperature', 'teacherprompt', 'timecreated'
        ]);

        $pluginwrapper->add_child($diagnosticadvaioptions);
        $diagnosticadvaioptions->add_child($diagnosticadvaioption);

        $diagnosticadvaioption->set_source_table('qtype_diagadvai_options', ['questionid' => backup::VAR_PARENTID]);

        return $plugin;
    }

    /**
     * Returns one array with filearea => mappingname elements for the qtype
     *
     * Used by {@link get_components_and_fileareas} to know about all the qtype
     * files to be processed both in backup and restore.
     */
    public static function get_qtype_fileareas() {
        // This question type doesn't use file areas by default.
        // Add file areas here if you extend it to include files (e.g., for teacherprompt).
        return [];
    }
}