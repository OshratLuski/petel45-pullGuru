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
 * External functions.
 *
 * @package    local_petel
 * @copyright  2017 Nadav Kavalerchik <nadav.kavalerchik@weizmann.ac.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot . '/local/petel/locallib.php');
require_once($CFG->dirroot . '/cohort/lib.php');

class local_petel_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function store_applet_data_parameters() {
        return new external_function_parameters(
                array('appletid' => new external_value(PARAM_ALPHANUM, 'appletid'),
                        'data' => new external_value(PARAM_RAW, 'data')
                )
        );
    }

    /**
     * Store student's applet activity data.
     *
     * @return array of settings
     */
    public static function store_applet_data($appletid, $data) {
        global $DB;

        $params = self::validate_parameters(self::store_applet_data_parameters(),
                array('appletid' => $appletid, 'data' => $data));

        // Save data.
        $userdata = new \stdClass();
        $userdata->appletid = $appletid;
        $userdata->data = $data;
        $userdata->timecreated = time();
        $recordid = $DB->insert_record('applets_store', $userdata);

        return ['id' => 200, 'msg' => 'Ok'];
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function store_applet_data_returns() {
        return new external_single_structure(
                array(
                        'id' => new external_value(PARAM_INT, 'ID error message'),
                        'msg' => new external_value(PARAM_TEXT, 'Text error message'),
                )
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function create_courses_for_teachers_parameters() {
        return new external_function_parameters(
                array('categoryid' => new external_value(PARAM_INT, 'categoryid'),
                        'courseid' => new external_value(PARAM_INT, 'courseid'),
                        'roleid' => new external_value(PARAM_INT, 'roleid'),
                        'groups' => new external_value(PARAM_RAW, 'groups'),
                        'nullcheck' => new external_value(PARAM_BOOL, 'nullcheck'),
                        'currentuserid' => new external_value(PARAM_INT, 'currentuserid'),
                        'users' => new external_value(PARAM_RAW, 'users'),
                )
        );
    }

    /**
     * Store student's applet activity data.
     *
     * @return array of settings
     */
    public static function create_courses_for_teachers($categoryid, $courseid, $roleid, $groups, $nullcheck, $currentuserid,
            $users) {
        global $DB;

        $params = self::validate_parameters(self::create_courses_for_teachers_parameters(),
                array(
                        'categoryid' => $categoryid,
                        'courseid' => $courseid,
                        'roleid' => $roleid,
                        'groups' => $groups,
                        'nullcheck' => $nullcheck,
                        'currentuserid' => $currentuserid,
                        'users' => $users,
                ));

        // Run ADHOC.
        $task = new \local_petel\task\adhoc_participiant();
        $task->set_custom_data(
                array(
                        'userids' => $users,
                        'categoryid' => $categoryid,
                        'courseid' => $courseid,
                        'roleid' => $roleid,
                        'groups' => $groups,
                        'nullcheck' => $nullcheck,
                        'currentuserid' => $currentuserid
                )
        );
        \core\task\manager::queue_adhoc_task($task);

        return ['result' => true];
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function create_courses_for_teachers_returns() {
        return new external_single_structure(
                array(
                        'result' => new external_value(PARAM_BOOL, 'boolean'),
                )
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function create_system_groups_for_teachers_parameters() {
        return new external_function_parameters(
                array(
                        'groupids' => new external_value(PARAM_RAW, 'groupids'),
                        'currentuserid' => new external_value(PARAM_INT, 'currentuserid'),
                        'users' => new external_value(PARAM_RAW, 'users'),
                )
        );
    }

    /**
     * Store student's applet activity data.
     *
     * @return array of settings
     */
    public static function create_system_groups_for_teachers($groupids, $currentuserid, $users) {
        global $DB;

        $params = self::validate_parameters(self::create_system_groups_for_teachers_parameters(),
                array(
                        'groupids' => $groupids,
                        'currentuserid' => $currentuserid,
                        'users' => $users,
                ));

        foreach (json_decode($params['users']) as $userid) {
            foreach (json_decode($params['groupids']) as $cohortid) {
                if (!$DB->get_record('cohort_members', ['cohortid' => $cohortid, 'userid' => $userid])) {
                    $DB->insert_record('cohort_members', [
                            'cohortid' => $cohortid,
                            'userid' => $userid,
                            'timeadded' => time()
                    ]);
                }
            }
        }

        return ['result' => true];
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function create_system_groups_for_teachers_returns() {
        return new external_single_structure(
                array(
                        'result' => new external_value(PARAM_BOOL, 'boolean'),
                )
        );
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.3
     */
    public static function get_categories_ac_parameters() {
        return new external_function_parameters(
                array('query' => new external_value(PARAM_TEXT, 'query'))
        );
    }

    /**
     * Get categories
     *
     * @param array $criteria Criteria to match the results
     * @param booln $addsubcategories obtain only the category (false) or its subcategories (true - default)
     * @return array list of categories
     * @since Moodle 2.3
     */
    public static function get_categories_ac($query = '') {
        global $DB;

        $categories = [];
        $query = (string) trim($query);

        if (empty($query)) {
            $sql = "SELECT cc.id, cc.name FROM {course_categories} cc LIMIT 10";
            $categoriesselect = $DB->get_records_sql($sql);
        }

        if (!empty($query)) {
            $query = $DB->sql_like_escape($query);

            $sql = "SELECT cc.id, cc.name FROM {course_categories} cc WHERE cc.name LIKE '%" . $query . "%' LIMIT 10";
            $categoriesselect = $DB->get_records_sql($sql);
        }

        foreach ($categoriesselect as $key => $cat) {
            $categories[$cat->id] = $cat->name;
        }

        return json_encode($categories);
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.3
     */
    public static function get_categories_ac_returns() {
        return new external_value(PARAM_RAW, 'JSON categories');
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.3
     */
    public static function get_courses_ac_parameters() {
        return new external_function_parameters(
                array('query' => new external_value(PARAM_TEXT, 'query'))
        );
    }

    /**
     * Get categories
     *
     * @param array $criteria Criteria to match the results
     * @param booln $addsubcategories obtain only the category (false) or its subcategories (true - default)
     * @return array list of categories
     * @since Moodle 2.3
     */
    public static function get_courses_ac($query = '') {
        global $CFG, $DB;

        $courses = [];
        $query = (string) trim($query);

        if (empty($query)) {
            $sql = "SELECT c.id, c.shortname FROM {course} c WHERE c.id > 1 AND c.visible = 1 LIMIT 10";
            $coursesselect = $DB->get_records_sql($sql);
        }

        if (!empty($query)) {
            $query = $DB->sql_like_escape($query);

            $sql = "SELECT c.id, c.shortname
                    FROM {course} c
                    WHERE c.id > 1 AND c.visible = 1 AND c.shortname LIKE '%" . $query . "%'
                    LIMIT 10
                    ";
            $coursesselect = $DB->get_records_sql($sql);
        }

        foreach ($coursesselect as $key => $c) {
            $courses[$c->id] = $c->shortname . ' (' . $c->id . ')';
        }

        return json_encode($courses);
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.3
     */
    public static function get_courses_ac_returns() {
        return new external_value(PARAM_RAW, 'JSON courses');
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.3
     */
    public static function get_roles_ac_parameters() {
        return new external_function_parameters(
                array('query' => new external_value(PARAM_TEXT, 'query'))
        );
    }

    /**
     * Get roles
     *
     * @param array $criteria Criteria to match the results
     * @param booln $addsubcategories obtain only the category (false) or its subcategories (true - default)
     * @return array list of categories
     * @since Moodle 2.3
     */
    public static function get_roles_ac($query = '') {
        global $DB;

        $roles = [];
        $query = (string) trim($query);

        // Get roles.
        $excluderoles = ['guest', 'user', 'frontpage'];
        foreach ($DB->get_records('role') as $role) {

            if (in_array($role->shortname, $excluderoles)) {
                continue;
            }

            if (!empty($role->name)) {
                $rolename = $role->name;
            } else {
                switch ($role->shortname) {
                    case 'manager':
                        $rolename = get_string('manager', 'role');
                        break;
                    case 'coursecreator':
                        $rolename = get_string('coursecreators');
                        break;
                    case 'editingteacher':
                        $rolename = get_string('defaultcourseteacher');
                        break;
                    case 'teacher':
                        $rolename = get_string('noneditingteacher');
                        break;
                    case 'student':
                        $rolename = get_string('defaultcoursestudent');
                        break;
                    case 'guest':
                        $rolename = get_string('guest');
                        break;
                    case 'user':
                        $rolename = get_string('authenticateduser');
                        break;
                    case 'frontpage':
                        $rolename = get_string('frontpageuser', 'role');
                        break;
                    default:
                        $rolename = $role->shortname;
                        break;
                }
            }

            $roles[$role->id] = $rolename;
        }

        if (!empty($query)) {
            foreach ($roles as $key => $rolename) {
                if (mb_strpos($rolename, $query) === false) {
                    unset($roles[$key]);
                }
            }
        }

        return json_encode($roles);
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.3
     */
    public static function get_roles_ac_returns() {
        return new external_value(PARAM_RAW, 'JSON roles');
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.3
     */
    public static function get_system_groups_ac_parameters() {
        return new external_function_parameters(
                array()
        );
    }

    /**
     * Get roles
     *
     * @param array $criteria Criteria to match the results
     * @param booln $addsubcategories obtain only the category (false) or its subcategories (true - default)
     * @return string list of groups
     * @since Moodle 2.3
     */
    public static function get_system_groups_ac() {
        global $DB;

        $groups = [];

        foreach ($DB->get_records('cohort', ['visible' => 1]) as $cohort) {
            $groups[$cohort->id] = $cohort->name;
        }

        return json_encode($groups);
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.3
     */
    public static function get_system_groups_ac_returns() {
        return new external_value(PARAM_RAW, 'JSON groups');
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.3
     */
    public static function check_user_idnumber_parameters() {
        return new external_function_parameters(
                array('currentuserid' => new external_value(PARAM_INT, 'userid'))
        );
    }

    /**
     * Check user idnumber
     *
     * @param array $criteria Criteria to match the results
     * @param booln $addsubcategories obtain only the category (false) or its subcategories (true - default)
     * @return array list of categories
     * @since Moodle 2.3
     */
    public static function check_user_idnumber($currentuserid) {
        global $DB;

        $result = false;
        $user = $DB->get_record('user', array('id' => $currentuserid));

        if (isset($user->idnumber) && !empty($user->idnumber)) {
            $cat = $DB->get_record('course_categories', array('idnumber' => $user->idnumber));

            if (!empty($cat)) {
                $result = true;
            }
        }

        return ['result' => $result];
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.3
     */
    public static function check_user_idnumber_returns() {
        return new external_single_structure(
                array(
                        'result' => new external_value(PARAM_BOOL, 'boolean'),
                )
        );

    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.3
     */
    public static function create_course_for_teacher_parameters() {
        return new external_function_parameters(
                array(
                        'currentuserid' => new external_value(PARAM_INT, 'userid'),
                        'coursename' => new external_value(PARAM_RAW, 'coursename'),
                )
        );
    }

    /**
     * Create course for teacher
     *
     * @param array $criteria Criteria to match the results
     * @param booln $addsubcategories obtain only the category (false) or its subcategories (true - default)
     * @return array list of categories
     * @since Moodle 2.3
     */
    public static function create_course_for_teacher($currentuserid, $coursename) {
        global $DB;

        if (empty(get_config('local_petel', 'default_course'))
                || empty(get_config('local_petel', 'admin_email')) || empty($currentuserid) || empty($coursename)
        ) {
            return ['result' => false];
        }

        $categoryid = 0;
        $user = $DB->get_record('user', array('id' => $currentuserid));
        if (isset($user->idnumber) && !empty($user->idnumber)) {
            $cat = $DB->get_record('course_categories', array('idnumber' => $user->idnumber));

            if (!empty($cat)) {
                $categoryid = $cat->id;
            } else {
                return ['result' => false];
            }
        } else {
            return ['result' => false];
        }

        // Run ADHOC.
        $task = new \local_petel\task\adhoc_createcourse();
        $task->set_custom_data(
                array(
                        'categoryid' => $categoryid,
                        'courseid' => get_config('local_petel', 'default_course'),
                        'currentuserid' => $currentuserid,
                        'coursename' => $coursename,
                )
        );
        \core\task\manager::queue_adhoc_task($task);

        return ['result' => true];
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.3
     */
    public static function create_course_for_teacher_returns() {
        return new external_single_structure(
                array(
                        'result' => new external_value(PARAM_BOOL, 'boolean'),
                )
        );

    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function send_event_parameters() {
        return new external_function_parameters(
                array(
                        'type' => new external_value(PARAM_RAW, 'event type')
                )
        );
    }

    /**
     * Store student's applet activity data.
     *
     * @return string of settings
     */
    public static function send_event($type) {
        global $USER;

        $context = \context_system::instance();
        self::validate_context($context);

        $params = self::validate_parameters(self::send_event_parameters(),
                array(
                        'type' => $type,
                )
        );

        switch ($params['type']) {
            case 'notification':
                $eventdata = [];
                $eventdata['context'] = $context;
                $eventdata['userid'] = $USER->id;
                $eventdata['other'] = ['type' => $params['type'], 'subject' => ''];
                $eventdata['objectid'] = $context->instanceid;

                \local_petel\event\notification_click::create($eventdata)->trigger();
                break;

            case 'chat':
                $eventdata = [];
                $eventdata['context'] = $context;
                $eventdata['userid'] = $USER->id;
                $eventdata['other'] = ['type' => $params['type'], 'subject' => ''];
                $eventdata['objectid'] = $context->instanceid;

                \local_petel\event\chat_click::create($eventdata)->trigger();
                break;
        }

        return '';
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function send_event_returns() {
        return new external_value(PARAM_RAW, 'Result');
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function popup_update_course_metadata_parameters() {
        return new external_function_parameters(
                array(
                        'courseid' => new external_value(PARAM_INT, 'course id')
                )
        );
    }

    /**
     * Store student's applet activity data.
     *
     * @return string of settings
     */
    public static function popup_update_course_metadata($courseid) {

        $context = \context_system::instance();
        self::validate_context($context);

        $params = self::validate_parameters(self::popup_update_course_metadata_parameters(),
                array(
                        'courseid' => $courseid,
                )
        );

        $result = [];
        $result['courseid'] = $params['courseid'];

        // Cclass.
        $field = \local_metadata\mcontext::course()->getField('cclass');

        // Default.
        $arr = json_decode(\local_metadata\mcontext::course()->get($params['courseid'], 'cclass'));
        if ($arr == null || empty($arr)) {
            $arr = [];
        }

        foreach (preg_split('/\R/', $field->param1) as $name) {
            $tmp = [
                    'id' => $name,
                    'name' => $name,
                    'selected' => in_array($name, $arr) ? true : false,
            ];

            $result['cclassdata'][] = $tmp;
        }

        $result['cclassname'] = $field->name;

        // Cclasslevel.
        $field = \local_metadata\mcontext::course()->getField('cclasslevel');

        // Default.
        $arr = json_decode(\local_metadata\mcontext::course()->get($params['courseid'], 'cclasslevel'));
        if ($arr == null || empty($arr)) {
            $arr = [];
        }

        foreach (preg_split('/\R/', $field->param1) as $name) {
            $tmp = [
                    'id' => $name,
                    'name' => $name,
                    'selected' => in_array($name, $arr) ? true : false,
            ];

            $result['cclassleveldata'][] = $tmp;
        }

        $result['cclasslevelname'] = $field->name;

        return json_encode($result);
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function popup_update_course_metadata_returns() {
        return new external_value(PARAM_RAW, 'Result');
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function update_course_metadata_parameters() {
        return new external_function_parameters(
                array(
                        'courseid' => new external_value(PARAM_INT, 'course id'),
                        'cclass' => new external_value(PARAM_RAW, 'cclass'),
                        'cclasslevel' => new external_value(PARAM_RAW, 'cclasslevel')
                )
        );
    }

    /**
     * Store student's applet activity data.
     *
     * @return string of settings
     */
    public static function update_course_metadata($courseid, $cclass, $cclasslevel) {

        $context = \context_system::instance();
        self::validate_context($context);

        $params = self::validate_parameters(self::update_course_metadata_parameters(),
                array(
                        'courseid' => $courseid,
                        'cclass' => $cclass,
                        'cclasslevel' => $cclasslevel,
                )
        );

        if (!empty($params['cclass'])) {
            $value = json_encode([$params['cclass']], JSON_UNESCAPED_UNICODE);
            \local_metadata\mcontext::course()->save($params['courseid'], 'cclass', $value);
        }

        if (!empty($params['cclasslevel'])) {
            $value = json_encode([$params['cclasslevel']], JSON_UNESCAPED_UNICODE);
            \local_metadata\mcontext::course()->save($params['courseid'], 'cclasslevel', $value);
        }

        return '';
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function update_course_metadata_returns() {
        return new external_value(PARAM_RAW, 'Result');
    }

}
