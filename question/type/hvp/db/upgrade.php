<?php

defined('MOODLE_INTERNAL') || die();
global $DB;

function xmldb_qtype_hvp_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();


    if ($oldversion < 2022122700) {
        // Define field hidegrader to be added to assign.
        $table = new xmldb_table('qtype_hvp');
        $fieldtitle = new xmldb_field('title', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'question');


        if (!$dbman->field_exists($table, $fieldtitle)) {
            $dbman->add_field($table, $fieldtitle);
        }
        $fieldintro = new xmldb_field('intro');
        if ($dbman->field_exists($table, $fieldintro)) {
            $dbman->drop_field($table, $fieldintro);
        }
        $fieldintroformat = new xmldb_field('$fieldintroformat');
        if ($dbman->field_exists($table, $fieldintroformat)) {
            $dbman->drop_field($table, $fieldintroformat);
        }

        upgrade_plugin_savepoint(true, 2022122700, 'qtype', 'hvp');
    }
    if ($oldversion < 2023011000) {
        $table = new xmldb_table('qtype_hvp');
        $fieldtitle = new xmldb_field('title', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'question');
        $newfieldtitle = new xmldb_field('title', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null, 'question');
        if ($dbman->field_exists($table, $fieldtitle)) {
            $dbman->change_field_type($table, $newfieldtitle);
        }
        upgrade_plugin_savepoint(true, 2023011000, 'qtype', 'hvp');
    }

    if ($oldversion < 2023040300) {
        if (!$dbman->table_exists('qtype_hvp_auth')) {
            $dbman->install_one_table_from_xmldb_file($CFG->dirroot . '/question/type/hvp/db/install.xml', 'qtype_hvp_auth');
        }
        upgrade_plugin_savepoint(true, 2023040300, 'qtype', 'hvp');
    }

    /**
     * Adds css to hvp table
     */
    if ($oldversion < 2023091101) {

        $table = new xmldb_table('qtype_hvp');

        // Define field intro to be added to hvp.
        $css = new xmldb_field('css', XMLDB_TYPE_TEXT, null, null, null, null, null, 'timemodified');

        // Add field intro if not defined already.
        if (!$dbman->field_exists($table, $css)) {
            $dbman->add_field($table, $css);
        }
        upgrade_plugin_savepoint(true, 2023091101, 'qtype', 'hvp');
    }

    if ($oldversion < 2023091103) {
        // Define the new capability.
        $capability = 'qtype/hvp:saveresults';

        // Assign capability to editingteacher if not already assigned.
        $role = $DB->get_record('role', ['shortname' => 'editingteacher']);
        if ($role) {
            assign_capability($capability, CAP_ALLOW, $role->id, context_system::instance());
        }

        // Update the plugin version.
        upgrade_plugin_savepoint(true, 2023091103, 'qtype', 'hvp');
    }

    return true;
}
