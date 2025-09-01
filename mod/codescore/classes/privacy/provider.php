<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace mod_codescore\privacy;


// Privacy API implementation for the Code Score plugin.

// @package     mod_codescore
// @category    privacy
// @copyright   2024 Devlion <info@devlion.co>
// @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later


use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\writer;

class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {
    public static function get_metadata(collection $collection): collection {

        // Here you will add more items into the collection.
        $collection->add_database_table('codescore_attempts', [
            'userid' => 'privacy:metadata:codescore_attempts:userid',
            'attempt' => 'privacy:metadata:codescore_attempts:attempt',
            'timestart' => 'privacy:metadata:codescore_attempts:timestart',
            'timefinish' => 'privacy:metadata:codescore_attempts:timefinish',
            'timemodified' => 'privacy:metadata:codescore_attempts:timemodified',
            'timemodifiedoffline' => 'privacy:metadata:codescore_attempts:timemodifiedoffline',
            'timegraded' => 'privacy:metadata:codescore_attempts:code',
            'studentnotes' => 'privacy:metadata:codescore_attempts:studentnotes',
            'grade' => 'privacy:metadata:codescore_attempts:grade',
        ], 'privacy:metadata:codescore_attempts');

        $collection->add_external_location_link('codescore', [
            'task' => 'privacy:metadata:codescore:task',
            'programminglang' => 'privacy:metadata:codescore:programminglang',
            'syntaxgrading' => 'privacy:metadata:codescore:syntaxgrading',
            'outputgrading' => 'privacy:metadata:codescore:outputgrading',
            'problemsolutiongrading' => 'privacy:metadata:codescore:problemsolutiongrading',
            'allcasesgrading' => 'privacy:metadata:codescore:allcasesgrading',
        ], 'privacy:metadata:codescore');

        return $collection;
    }
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();

        // Users who attempted the codescore.
        $sql = "SELECT ctx.id
                  FROM {codescore_attempts} ac
                  JOIN {context} ctx
                    ON ctx.instanceid = ac.userid AND ctx.contextlevel = :contextlevel
                 WHERE ac.userid = :userid";

        $params = ['userid' => $userid, 'contextlevel' => CONTEXT_USER];

        $contextlist->add_from_sql($sql, $params);
        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (!$context instanceof \context_user) {
            return;
        }

        $params = [
                'contextid' => $context->id,
                'contextuser' => CONTEXT_USER,
        ];

        $sql = "SELECT ac.userid as ownerid
                  FROM {codescore_attempts} ac
                  JOIN {context} ctx
                       ON ctx.instanceid = ac.codescore
                       AND ctx.contextlevel = :contextuser
                 WHERE ctx.id = :contextid";

        $userlist->add_from_sql('userid', $sql, $params);
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        $codescoredata = [];
        $sql = "SELECT CONCAT(u.firstname, ' ', u.lastname) AS fullname ,ca.*
                  FROM {mod_codescore_attempts} ca
                  JOIN {user} u ON u.id = ca.userid
                 WHERE ca.userid = :userid";

        $params = ['userid' => $contextlist->get_user()->id];
        $results = $DB->get_records_sql($sql, $params);

        foreach ($results as $result) {
            $codescoredata[] = (object) [
                    'fullname' => format_string($result->fullname, true),
                    'userid' => $result->userid,
                    'code' => $result->code,
                    'grade' => $result->grade,
                    'studentnotes' => $result->studentnotes,
                    'timecreated' => transform::datetime($result->timecreated),
                    'timemodified' => transform::datetime($result->timemodified),
                    'timegraded' => transform::datetime($result->timegraded),
                    'timestarted' => transform::datetime($result->timestart),
                    'timefinished' => transform::datetime($result->timefinish),
            ];
        }
        if (!empty($reportsdata)) {
            $data = (object) [
                    'codescore_attempts' => $codescoredata,
            ];
            writer::with_context($contextlist->current())->export_data([
                    get_string('pluginname', 'mod_codescore')], $data);
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        if ($context instanceof \context_user) {
            static::delete_data($context->instanceid);
        }
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        $context = $userlist->get_context();

        if ($context instanceof \context_user) {
            static::delete_data($context->instanceid);
        }
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        static::delete_data($contextlist->get_user()->id);
    }

    /**
     * Delete data related to a userid.
     *
     * @param  int $userid The user ID
     */
    protected static function delete_data($userid) {
        global $DB;

        // Reports are considered to be 'owned' by the institution, even if they were originally written by a specific
        // user. They are still exported in the list of a users data, but they are not removed.
        // The ownerid is instead anonymised.
        $params['userid'] = $userid;
        $DB->set_field_select('codescore_attempts', 'userid', 0, "userid = :userid", $params);
    }
}
