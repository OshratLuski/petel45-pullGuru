<?php

defined('MOODLE_INTERNAL') || die();

class backup_qtype_diagnosticadvdesc_plugin extends backup_qtype_plugin {

    /**
     * Returns the qtype information to attach to question element
     */
    protected function define_question_plugin_structure() {
        // Define the virtual plugin element with the condition to fulfill
        $plugin = $this->get_plugin_element(null, '../../qtype', 'diagnosticadvdesc');
        
        // Create one standard named plugin element (the visible container)
        $pluginwrapper = new backup_nested_element($this->get_recommended_name());

        // Connect the visible container ASAP
        $plugin->add_child($pluginwrapper);

        // Define the structure for diagnosticadvdesc options
        $diagnosticadvdescoptions = new backup_nested_element('diagnosticadvdesc_options');
        $diagnosticadvdescoption = new backup_nested_element('diagnosticadvdesc_option', ['id'], [
            'questionid', 'relatedqid', 'quizid' ,'timecreated', 'timemodified'
        ]);

        // Build the tree
        $pluginwrapper->add_child($diagnosticadvdescoptions);
        $diagnosticadvdescoptions->add_child($diagnosticadvdescoption);

        // Set the source table for the options
        $diagnosticadvdescoption->set_source_table('qtype_diagnosticadvdesc', ['questionid' => backup::VAR_PARENTID]);

        // No need to annotate ids or files since this qtype doesn't use them directly
        return $plugin;
    }

    /**
     * Returns one array with filearea => mappingname elements for the qtype
     *
     * Used by {@link get_components_and_fileareas} to know about all the qtype
     * files to be processed both in backup and restore.
     */
    public static function get_qtype_fileareas() {
        // Since this question type doesn't have specific file areas (e.g., feedback or instructions),
        // we return an empty array. Adjust if your question type uses files.
        return [];
    }
}