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
 * @package    local
 * @subpackage diagnostic
 * @copyright  2021 Devlion.co
 * @author  Evgeniy Voevodin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * local_departments function.
 * @param string $oldversion the version we are upgrading from.
 */
function xmldb_local_diagnostic_upgrade($oldversion)
{
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2021092900) {
        $table = new xmldb_table('local_diagnostic_cache');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('requestid', XMLDB_TYPE_CHAR, '32');
        $table->add_field('data', XMLDB_TYPE_BINARY, 'medium');

        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('requestid', XMLDB_KEY_UNIQUE, ['requestid']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
    }

    if ($oldversion < 2021100300) {
        $DB->execute('TRUNCATE TABLE {local_diagnostic_cache}');
        $table = new xmldb_table('local_diagnostic_cache');

        $key = new \xmldb_key('requestid', XMLDB_KEY_UNIQUE, ['requestid']);
        $dbman->drop_key($table, $key);

        $field = new \xmldb_field('requestid', XMLDB_TYPE_CHAR, '32');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        $field = new \xmldb_field('mid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'id');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $key = new \xmldb_key('mid', XMLDB_KEY_UNIQUE, ['mid']);
        $dbman->add_key($table, $key);
    }

    if ($oldversion < 2021100601) {
        $table = new xmldb_table('local_diagnostic_cache');
        $field = new \xmldb_field('data', XMLDB_TYPE_BINARY, 'big');
        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_precision($table, $field);
        }
    }

    if ($oldversion < 2021121200) {
        $table = new xmldb_table('local_diagnostic_cache_log');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('mid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('data', XMLDB_TYPE_BINARY, 'big');

        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        $table = new xmldb_table('local_diagnostic_cache');
        $field = new \xmldb_field('rebuild', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, 0, 'mid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

    }

    if ($oldversion < 2021121500) {
        $table = new xmldb_table('local_diagnostic_cache_log');
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        $table = new xmldb_table('local_diagnostic_cache');
        $field = new \xmldb_field('centroids', XMLDB_TYPE_BINARY, 'small', null, null, null, null, 'data');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    if ($oldversion < 2021122600) {

        $table = new xmldb_table('local_diagnostic_cache');
        $field = new \xmldb_field('buildtime', XMLDB_TYPE_CHAR, 10, null, null, null, null, 'centroids');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    if ($oldversion < 2022051700) {

        $table = new xmldb_table('local_diagnostic_cache');
        $field = new \xmldb_field('activities', XMLDB_TYPE_BINARY, 'medium', null, null, null, null, 'centroids');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    if ($oldversion < 2022060100) {
        $table = new xmldb_table('local_diagnostic_cache');
        $field = new \xmldb_field('readytouse', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, 0, 'rebuild');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    if ($oldversion < 2022122000) {
        $table = new xmldb_table('local_diagnostic_cache');
        $field = new \xmldb_field('extra', XMLDB_TYPE_BINARY, 'big', null, null, null, null, 'data');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    if ($oldversion < 2023010500) {

        $table = new xmldb_table('local_diagnostic_cache');
        $field = new \xmldb_field('extracentroids', XMLDB_TYPE_BINARY, 'small', null, null, null, null, 'centroids');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    if ($oldversion < 2023112301) {

        $table = new xmldb_table('local_diagnostic_brad');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);

        $table->add_field('mid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('bradclusternum', XMLDB_TYPE_INTEGER, '10');
        $table->add_field('attempts', XMLDB_TYPE_INTEGER, '10');
        $table->add_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('mid', XMLDB_KEY_UNIQUE, ['mid']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
    }

    if ($oldversion < 2023120302) {

        $table = new xmldb_table('local_diagnostic_brad');
        $field = new \xmldb_field('questions', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'attempts');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    if ($oldversion < 2023121800) {

        $table = new xmldb_table('local_diagnostic_brad');
        $field = new \xmldb_field('allbradclusters', XMLDB_TYPE_CHAR, '100', null, null, null, null, 'bradclusternum');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new \xmldb_field('bradmin', XMLDB_TYPE_INTEGER, '2', null, null, null, null, 'allbradclusters');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new \xmldb_field('bradmax', XMLDB_TYPE_INTEGER, '2', null, null, null, null, 'bradmin');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    return true;
}
