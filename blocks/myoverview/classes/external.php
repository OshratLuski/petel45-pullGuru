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
 * External course API
 *
 * @package    block_myoverview_external
 * @category   external
 * @copyright  2009 Petr Skodak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/externallib.php');

require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/course/externallib.php');

require_once($CFG->dirroot . '/calendar/lib.php');
require_once($CFG->dirroot . '/blocks/myoverview/lib.php');

use \core_calendar\local\api as local_api;
use \core_calendar\local\event\container as event_container;
use \core_calendar\local\event\forms\create as create_event_form;
use \core_calendar\local\event\forms\update as update_event_form;
use \core_calendar\local\event\mappers\create_update_form_mapper;
use \core_calendar\external\event_exporter;
use \core_calendar\external\events_exporter;
use \core_calendar\external\events_grouped_by_course_exporter;
use \core_calendar\external\events_related_objects_cache;

use core_course\external\course_summary_exporter;

/**
 * Course external functions
 *
 * @package    core_course
 * @category   external
 * @copyright  2011 Jerome Mouneyrac
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.2
 */
class block_myoverview_external extends core_course_external {

    public static function get_custom_info_by_course_parameters() {
        return new external_function_parameters(
                array(
                        'courseids' => new external_value(PARAM_RAW, 'Course ids', VALUE_DEFAULT, 0)
                )
        );
    }

    public static function get_custom_info_by_course($courseids) {
        global $OUTPUT, $USER;

        $courses = json_decode($courseids);
        $result = array();

        foreach($courses as $courseid) {
            $c = new \StdClass();

            $context = context_course::instance($courseid);

            $access = 0;
            if (is_siteadmin()) {
                $access = 1;
            }
            if (!$access) {
                $roles = get_user_roles($context, $USER->id);
                foreach ($roles as $role) {
                    if (in_array(trim($role->shortname), array('teacher', 'editingteacher', 'coursecreator', 'manager'))) {
                        $access = 1;
                    }
                }
            }

            $c->isteacher = $access;

            $events = self::get_calendar_action_events_by_course($courseid);
            $newevents = [];
            if (get_config('block_myoverview', 'events')) {
                foreach ($events->events as $event) {
                    if ($humandate = block_myoverview_assignment_human_dates($event)) {
                        $event->humandate = $humandate;
                        $newevents[] = $event;
                    }
                }
            }
            $c->events = $newevents;

            $c->activities = self::get_recent_items($courseid, 10);

            $c->grademe = self::get_grade_me($courseid);

            if ($access) {
                $c->links = self::get_course_links_teacher($courseid);
            } else {
                $c->links = self::get_course_links_student($courseid);
            }
            $c->quickaccesslink = self::get_quick_access($courseid);

            $c->isteacher = $access;

            $links_html = '';
            foreach ($c->links as $item) {
                $links_html .= $OUTPUT->render_from_template('block_myoverview/petel/course-links-block', $item);
            }

            $links_html .= '<input type="hidden" id="context_' . $courseid . '" value="' . $context->id . '">';

            $events_html = '';
            foreach ($c->events as $item) {
                $events_html .= $OUTPUT->render_from_template('block_myoverview/petel/event-list-item', $item);
            }

            $activities_html = '';
            if(!empty($c->activities)){
                $activities_html .= $OUTPUT->render_from_template('block_myoverview/petel/activity-list-item', array('course_modules' => $c->activities));
            }

            if (!empty($c->quickaccesslink)) {
                $c->quickaccesslink = '<div class="quickaccessbar py-1 px-2">' . $c->quickaccesslink . '</div>';
            }

            $result[] = array(
                'courseid' => $courseid,
                'links' => json_encode($links_html),
                'events' => json_encode($events_html),
                'activities' => json_encode($activities_html),
                'grademe' => json_encode($c->grademe),
                'quickaccesslink' => json_encode($c->quickaccesslink),
                'isteacher' => $c->isteacher,
                'noactivity' => json_encode('<div class="no-events" tabindex="0"><p>' . get_string('noactivities',
                        'block_myoverview') . '</p><div class="no-events-img"></div></div>'),
            );
        }

        return array('result' => json_encode($result));
    }

    public static function get_custom_info_by_course_returns() {
        return new external_single_structure([
            'result' => new external_value(PARAM_RAW, 'Json result'),
        ]);
    }

    public static function get_enrolled_courses_by_timeline_classification_parameters() {
        return new external_function_parameters(
                array(
                        'classification' => new external_value(PARAM_ALPHA, 'future, inprogress, or past'),
                        'limit' => new external_value(PARAM_INT, 'Result set limit', VALUE_DEFAULT, 0),
                        'offset' => new external_value(PARAM_INT, 'Result set offset', VALUE_DEFAULT, 0),
                        'sort' => new external_value(PARAM_TEXT, 'Sort string', VALUE_DEFAULT, null),
                        'customfieldname' => new external_value(PARAM_ALPHANUMEXT, 'Used when classification = customfield',
                                VALUE_DEFAULT, null),
                        'customfieldvalue' => new external_value(PARAM_RAW, 'Used when classification = customfield',
                                VALUE_DEFAULT, null),
                        'searchvalue' => new external_value(PARAM_RAW, 'The value a user wishes to search against',
                            VALUE_DEFAULT, null),
                        'requiredfields' => new core_external\external_multiple_structure(
                                new external_value(PARAM_ALPHANUMEXT, 'Field name to be included from the results', VALUE_DEFAULT),
                                'Array of the only field names that need to be returned. If empty, all fields will be returned.',
                                VALUE_DEFAULT, []
                        ),
                )
        );
    }

    public static function get_enrolled_courses_by_timeline_classification(
            string $classification,
            int $limit = 0,
            int $offset = 0,
            ?string $sort = null,
            ?string $customfieldname = null,
            ?string $customfieldvalue = null,
            ?string $searchvalue = null,
            array $requiredfields = []
    ) {
        global $CFG, $USER, $DB;

        require_once($CFG->dirroot. '/course/externallib.php');

        $params = self::validate_parameters(self::get_enrolled_courses_by_timeline_classification_parameters(),
                array(
                        'classification' => $classification,
                        'limit' => $limit,
                        'offset' => $offset,
                        'sort' => $sort,
                        'customfieldname' => $customfieldname,
                        'customfieldvalue' => $customfieldvalue,
                        'searchvalue' => $searchvalue,
                        'requiredfields' => $requiredfields
                )
        );

        if (get_config('block_myoverview', 'cacheenable')) {

            // Initial cache.
            $cachekey = "";
            $cache = cache::make('block_myoverview', 'enrolled_courses');

            // Timeout.
            if (get_config('block_myoverview', 'cachetimeout') > 0) {

                // Create cache key.
                $cachekey = $USER->id.'_'.$params['classification'].'_'.$params['limit'].'_'.$params['offset'].'_'.$params['sort'].
                        '_'.$params['customfieldname'].'_'.$params['customfieldvalue'].'_'.$params['searchvalue'];

                $cachekey = str_replace('.', '_', $cachekey);
                $cachekey = str_replace(' ', '', $cachekey);
                $cachekey = md5($cachekey);

                $lastaccess = $cache->get('user_' . $USER->id . '_last_access');

                if (empty($lastaccess)) {
                    $cache->delete($cachekey);
                }

                // Delete cache.
                if (!empty($lastaccess) && $lastaccess + get_config('block_myoverview', 'cachetimeout') * 60 < time()) {
                    foreach ($cache->get('user_' . $USER->id) as $key) {
                        $cache->delete($key);
                    }

                    $cache->delete('user_' . $USER->id);
                    $cache->delete('user_' . $USER->id . '_last_access');
                    $cache->delete($cachekey);
                }
            }

            if (!$cache->has($cachekey)) {
                $data = core_course_external::get_enrolled_courses_by_timeline_classification(
                        $params['classification'],
                        $params['limit'],
                        $params['offset'],
                        $params['sort'],
                        $params['customfieldname'],
                        $params['customfieldvalue'],
                        $params['searchvalue'],
                        $params['requiredfields']
                );

                $data = self::filter_course($data);

                $data['nextoffset'] = count($data['courses']);

                $cache->set($cachekey, $data);

                if (!$cache->has('user_' . $USER->id)) {
                    $cache->set('user_' . $USER->id, array($cachekey));
                } else {
                    $arr = $cache->get('user_' . $USER->id);
                    $arr[] = $cachekey;
                    $arr = array_unique($arr);
                    $cache->set('user_' . $USER->id, $arr);
                }
            } else {
                $data = $cache->get($cachekey);
            }

            $cache->set('user_' . $USER->id.'_last_access', time());

            // Sort by last access course.
            if($params['sort'] == 'ul.timeaccess desc'){

                $arrids = [];
                foreach($data['courses'] as $item){
                    $arrids[] = $item->id;
                }

                if(!empty($arrids)) {

                    $sql = "SELECT * 
                            FROM {user_lastaccess}
                            WHERE userid = " . $USER->id . " AND courseid IN(".implode(',', $arrids).")";

                    $arrlastaccess = [];
                    foreach($DB->get_records_sql($sql) as $item){
                        $arrlastaccess[$item->courseid] = $item->timeaccess;
                    }

                    foreach($data['courses'] as $key => $item){
                        $data['courses'][$key]->lastaccess = (isset($arrlastaccess[$item->id])) ? $arrlastaccess[$item->id] : 0;
                    }

                    usort($data['courses'], "block_myoverview_cmp");
                }

            }

        }else{
            $data = core_course_external::get_enrolled_courses_by_timeline_classification(
                    $params['classification'],
                    $params['limit'],
                    $params['offset'],
                    $params['sort'],
                    $params['customfieldname'],
                    $params['customfieldvalue'],
                    $params['searchvalue'],
                    $params['requiredfields']
            );

            $data = self::filter_course($data);
        }

        return $data;
    }

    public static function filter_course($data) {
        global $DB, $USER;

        // Check if course from community_oer catalog.
        list($oercategories, $oercourses, $oeractivities) = \community_oer\main_oer::get_main_structure_elements();
        foreach ($data['courses'] as $key => $course) {
            if (in_array($course->id, $oercourses)) {
                unset($data['courses'][$key]);
            }
        }

        // Check if course from community_social.
        $namerole = 'teachercolleague';
        foreach ($data['courses'] as $key => $course) {
            $context = context_course::instance($course->id);
            if ($roles = get_user_roles($context, $USER->id)) {

                $tmproles = [];
                foreach ($roles as $role) {
                    $tmproles[] = $role->shortname;
                }

                if(in_array($namerole, $tmproles) && !in_array('teacher', $tmproles) && !in_array('editingteacher', $tmproles)){
                    unset($data['courses'][$key]);
                }
            }
        }

        // Check tag of course השתלמות.
        foreach ($data['courses'] as $key => $course) {
            $excludetags = get_config('block_myoverview', 'excludetags');
            if (empty($excludetags)) {
                continue;
            }

            foreach (explode(',', $excludetags) as $tag) {

                $sql = "
                            SELECT *
                            FROM {tag} AS t
                            LEFT JOIN {tag_instance} AS ti ON (t.id=ti.tagid)
                            WHERE ti.itemtype='course' AND t.name=? AND ti.itemid=?                    
                        ";

                $rows = $DB->get_records_sql($sql, array(trim($tag),$course->id));

                if (!empty($rows)) {
                    unset($data['courses'][$key]);
                }
            }
        }

        return $data;
    }



    public static function get_enrolled_courses_by_timeline_classification_returns() {
        return new external_single_structure(
                array(
                        'courses' => new external_multiple_structure(course_summary_exporter::get_read_structure(), 'Course'),
                        'nextoffset' => new external_value(PARAM_INT, 'Offset for the next request')
                )
        );
    }

    public static function set_favourite_courses_parameters() {
        return new external_function_parameters(
                array(
                        'courses' => new external_multiple_structure(
                                new external_single_structure(
                                        array(
                                                'id' => new external_value(PARAM_INT, 'course ID'),
                                                'favourite' => new external_value(PARAM_BOOL, 'favourite status')
                                        )
                                )
                        )
                )
        );
    }

    public static function set_favourite_courses(array $courses) {
        global $USER, $CFG;

        require_once($CFG->dirroot. '/course/externallib.php');

        $params = self::validate_parameters(self::set_favourite_courses_parameters(),
                array(
                        'courses' => $courses
                )
        );

        // Initial cache.
        $cache = cache::make('block_myoverview', 'enrolled_courses');

        foreach($cache->get('user_'.$USER->id) as $key){
            $cache->delete($key);
        }

        $cache->delete('user_'.$USER->id);
        $cache->delete('user_' . $USER->id.'_last_access');

        return core_course_external::set_favourite_courses($params['courses']);
    }

    public static function set_favourite_courses_returns() {
        return new external_single_structure(
                array(
                        'warnings' => new external_warnings()
                )
        );
    }

    public static function update_user_preferences_parameters() {
        return new external_function_parameters(
                array(
                        'userid' => new external_value(PARAM_INT, 'id of the user, default to current user', VALUE_DEFAULT, 0),
                        'emailstop' => new external_value(core_user::get_property_type('emailstop'),
                                'Enable or disable notifications for this user', VALUE_DEFAULT, null),
                        'preferences' => new external_multiple_structure(
                                new external_single_structure(
                                        array(
                                                'type'  => new external_value(PARAM_RAW, 'The name of the preference'),
                                                'value' => new external_value(PARAM_RAW, 'The value of the preference, do not set this field if you
                                want to remove (unset) the current value.', VALUE_DEFAULT, null),
                                        )
                                ), 'User preferences', VALUE_DEFAULT, array()
                        )
                )
        );
    }

    public static function update_user_preferences($userid = 0, $emailstop = null, $preferences = array()) {
        global $USER, $CFG;

        require_once($CFG->dirroot. '/user/externallib.php');

        if(isset($preferences[0]['type']) && !in_array($preferences[0]['type'],
                        array('block_myoverview_user_grouping_preference',
                              'block_myoverview_user_sort_preference',
                              'block_myoverview_user_view_preference'
                        )
                )){

            // Initial cache.
            $cache = cache::make('block_myoverview', 'enrolled_courses');

            foreach ($cache->get('user_' . $USER->id) as $key) {
                $cache->delete($key);
            }

            $cache->delete('user_' . $USER->id);
            $cache->delete('user_' . $USER->id.'_last_access');
        }

        return core_user_external::update_user_preferences($userid, $emailstop, $preferences);
    }

    public static function update_user_preferences_returns() {
        return null;
    }


    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.2
     */
    public static function send_course_messages_parameters() {
        return new external_function_parameters(
                array(
                        'message' => new external_value(PARAM_RAW, 'Message text'),
                        'courseid' => new external_value(PARAM_INT, 'Course ID'),
                        'target' => new external_value(PARAM_TEXT, 'Message target, class/teacher')
                )
        );
    }

    /**
     * Send private messages from the current USER to other users
     *
     * @param array $messages An array of message to send.
     * @return array
     * @since Moodle 2.2
     */
    public static function send_course_messages($message, $courseid, $target) {
        global $CFG, $USER, $DB;

        // Check if messaging is enabled.
        if (empty($CFG->messaging)) {
            throw new moodle_exception('disabled', 'message');
        }

        $params = self::validate_parameters(self::send_course_messages_parameters(), array(
                'message' => (string) $message,
                'courseid' => (int) $courseid,
                'target' => (string) $target,
        ));

        // Ensure the current user is allowed to run this function
        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/site:sendmessage', $context);

        if ($params['target'] == 'class') {
            // Ensure the current user is allowed to delete message for everyone.
            $candeletemessagesforallusers = has_capability('moodle/site:deleteanymessage', $context);

            //retrieve all tousers of the messages
            $sql = "SELECT u.id
                    FROM {course} c
                    JOIN {context} ct ON c.id = ct.instanceid
                    JOIN {role_assignments} ra ON ra.contextid = ct.id
                    JOIN {user} u ON u.id = ra.userid
                    JOIN {role} r ON r.id = ra.roleid
                    WHERE c.id = ?";
            $receivers = $DB->get_records_sql($sql, array($params['courseid']));
            $tousers = array_keys($receivers);

        } else {
            //retrieve teacher ID, the first teacher in a course
            $sql = "SELECT u.id, ra.timemodified
                    FROM {course} c
                    JOIN {context} ct ON c.id = ct.instanceid
                    JOIN {role_assignments} ra ON ra.contextid = ct.id
                    JOIN {user} u ON u.id = ra.userid
                    JOIN {role} r ON r.id = ra.roleid
                    WHERE c.id = ?
                        AND r.shortname IN ('teacher','editingteacher')
                    ORDER BY ra.timemodified ASC, u.id ASC
                    LIMIT 1
                    ";
            $receivers = $DB->get_records_sql($sql, array($params['courseid']));
            $tousers = array_keys($receivers);
        }

        $resultmessages = array();
        $messageids = array();
        foreach ($tousers as $touser) {

            if ($touser == $USER->id) {
                continue;
            }

            $resultmsg = array(); //the infos about the success of the operation

            // We are going to do some checking.
            // Code should match /messages/index.php checks.
            $success = true;

            // TODO MDL-31118 performance improvement - edit the function so we can pass an array instead userid
            // Check if the recipient can be messaged by the sender.
            if ($success && !\core_message\api::can_post_message(\core_user::get_user($touser))) {
                $success = false;
                $errormessage = get_string('usercantbemessaged', 'message', fullname(\core_user::get_user($touser)));
            }

            // Now we can send the message (at least try).
            if ($success) {
                // TODO MDL-31118 performance improvement - edit the function so we can pass an array instead one touser object.
                $success = message_post_message($USER, $touser,
                        $params['message'], external_validate_format(FORMAT_MOODLE));
            }

            // Build the resultmsg.
            if ($success) {
                $resultmsg['msgid'] = $success;
                $resultmsg['timecreated'] = time();
                $resultmsg['candeletemessagesforallusers'] = $candeletemessagesforallusers;
                $messageids[] = $success;
            } else {
                // WARNINGS: for backward compatibility we return this errormessage.
                //          We should have thrown exceptions as these errors prevent results to be returned.
                // See http://docs.moodle.org/dev/Errors_handling_in_web_services#When_to_send_a_warning_on_the_server_side .
                $resultmsg['msgid'] = -1;
                $resultmsg['errormessage'] = $errormessage;
            }

            $resultmessages[] = $resultmsg;
        }

        if (!empty($messageids)) {
            $messagerecords = $DB->get_records_list(
                    'messages',
                    'id',
                    $messageids,
                    '',
                    'id, conversationid, smallmessage, fullmessageformat, fullmessagetrust');
            $resultmessages = array_map(function($resultmessage) use ($messagerecords, $USER) {
                $id = $resultmessage['msgid'];
                $resultmessage['conversationid'] = isset($messagerecords[$id]) ? $messagerecords[$id]->conversationid : null;
                $resultmessage['useridfrom'] = $USER->id;
                $resultmessage['text'] = message_format_message_text((object) [
                        'smallmessage' => $messagerecords[$id]->smallmessage,
                        'fullmessageformat' => external_validate_format($messagerecords[$id]->fullmessageformat),
                        'fullmessagetrust' => $messagerecords[$id]->fullmessagetrust
                ]);
                return $resultmessage;
            }, $resultmessages);
        }

        return $resultmessages;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.2
     */
    public static function send_course_messages_returns() {
        return new external_multiple_structure(
                new external_single_structure(
                        array(
                                'msgid' => new external_value(PARAM_INT,
                                        'test this to know if it succeeds:  id of the created message if it succeeded, -1 when failed'),
                                'clientmsgid' => new external_value(PARAM_ALPHANUMEXT, 'your own id for the message',
                                        VALUE_OPTIONAL),
                                'errormessage' => new external_value(PARAM_TEXT, 'error message - if it failed', VALUE_OPTIONAL),
                                'text' => new external_value(PARAM_RAW, 'The text of the message', VALUE_OPTIONAL),
                                'timecreated' => new external_value(PARAM_INT, 'The timecreated timestamp for the message',
                                        VALUE_OPTIONAL),
                                'conversationid' => new external_value(PARAM_INT, 'The conversation id for this message',
                                        VALUE_OPTIONAL),
                                'useridfrom' => new external_value(PARAM_INT, 'The user id who sent the message', VALUE_OPTIONAL),
                                'candeletemessagesforallusers' => new external_value(PARAM_BOOL,
                                        'If the user can delete messages in the conversation for all users', VALUE_DEFAULT, false),
                        )
                )
        );
    }

    public static function get_calendar_action_events_by_course(
            $courseid, $timesortfrom = null, $timesortto = null, $aftereventid = 0, $limitnum = 20) {

        global $PAGE, $USER, $OUTPUT, $DB;

        $user = null;
        $params = [
                'courseid' => $courseid,
                'timesortfrom' => $timesortfrom,
                'timesortto' => $timesortto,
                'aftereventid' => $aftereventid,
                'limitnum' => $limitnum,
        ];
        $context = \context_user::instance($USER->id);
        self::validate_context($context);

        if (empty($params['aftereventid'])) {
            $params['aftereventid'] = null;
        }

        $courses = enrol_get_my_courses('*', null, 0, [$courseid]);
        $courses = array_values($courses);

        if (empty($courses)) {
            return [];
        }

        $course = $courses[0];
        $renderer = $PAGE->get_renderer('core_calendar');
        $events = local_api::get_action_events_by_course(
                $course,
                $params['timesortfrom'],
                $params['timesortto'],
                $params['aftereventid'],
                $params['limitnum']
        );

        $exportercache = new events_related_objects_cache($events, $courses);
        $exporter = new events_exporter($events, ['cache' => $exportercache]);

        $res = $exporter->export($renderer);
        foreach ($res->events as $key => $item){
            $modname = $res->events[$key]->icon->component;
            $iconalt = get_string('pluginname', $modname);
            $res->events[$key]->icon->icon_html = $OUTPUT->image_icon('icon', $iconalt, $modname, array('title' => ''));

            $row = $DB->get_record('course_modules', ['id' => $item->instance]);
            $row2 = $DB->get_record($item->modulename, ['id' => $row->instance]);

            $a = new \StdClass();
            $a->modname = get_string('pluginname', $modname);
            $a->name = $row2->name;

            $date = new DateTime();
            $date->setTimestamp($item->timesort);
            $a->date = $date->format('F j, Y');

            //$a->date = userdate($item->timesort, get_string('strftimerecentfull', 'langconfig'));

            $res->events[$key]->arialabel = get_string('submissionactivity', 'block_myoverview', $a);
        }

        return $res;
    }

    /**
     * This function does the work to query ungraded assignments according
     * to plugins present in the /grade_me/plugins directory which are
     * gradeable by the current user, and returns the block content to be
     * displayed.
     *
     * @return stdClass The content being rendered for this block
     */
    public static function get_grade_me($courseid) {
        global $CFG, $USER, $COURSE, $DB, $OUTPUT, $PAGE;

        require_once($CFG->dirroot . '/blocks/grade_me/lib.php');
        $PAGE->requires->jquery();
        $PAGE->requires->js('/blocks/grade_me/javascript/grademe.js');

        // Create the content class.
        $content = new stdClass;
        $content->text = '';
        $content->footer = '';

        if (!isloggedin()) {
            return $content;
        }

        // Setup arrays.
        $gradeables = array();

        $groups = null;

        $enabledplugins = block_grade_me_enabled_plugins();

        $maxcourses = (isset($CFG->block_grade_me_maxcourses)) ? $CFG->block_grade_me_maxcourses : 10;
        $coursecount = 0;
        $additional = null;

        $course = get_course($courseid);

        unset($params);
        $gradeables = array();
        $gradebookusers = array();
        $context = context_course::instance($courseid);
        foreach (explode(',', $CFG->gradebookroles) as $roleid) {
            $roleid = trim($roleid);
            if ((groups_get_course_groupmode($course) == SEPARATEGROUPS) &&
                    !has_capability('moodle/site:accessallgroups', $context)) {
                $groups = groups_get_user_groups($courseid, $USER->id);
                foreach ($groups[0] as $groupid) {
                    $gradebookusers = array_merge($gradebookusers,
                            array_keys(get_role_users($roleid, $context, false, 'u.id', 'u.id ASC', null, $groupid)));
                }
            } else {
                $gradebookusers = array_merge($gradebookusers,
                        array_keys(get_role_users($roleid, $context, false, 'u.id', 'u.id ASC')));
            }
        }

        $params['courseid'] = $courseid;

        foreach ($enabledplugins as $plugin => $a) {
            if (has_capability($a['capability'], $context)) {
                $fn = 'block_grade_me_query_' . $plugin;
                $pluginfn = $fn($gradebookusers);
                if ($pluginfn !== false) {
                    list($sql, $inparams) = $fn($gradebookusers);
                    $query = block_grade_me_query_prefix() . $sql . block_grade_me_query_suffix($plugin);
                    $values = array_merge($inparams, $params);
                    $rs = $DB->get_recordset_sql($query, $values);

                    foreach ($rs as $r) {
                        if ($r->itemmodule == 'assign' && $r->maxattempts != '1') {
                            /* Check to be sure its the most recent attempt being graded */
                            $iteminstance = $r->iteminstance;
                            $userid = $r->userid;
                            $attemptnumber = $r->attemptnumber;
                            $sql = 'select MAX(attemptnumber) from {assign_submission} where assignment = ' . $iteminstance .
                                    ' and userid = ' . $userid;
                            $maxattempt = $DB->get_field_sql($sql);
                            if ($maxattempt == $attemptnumber) {
                                $gradeables = block_grade_me_array($gradeables, $r);
                            }
                        } else {
                            $gradeables = block_grade_me_array($gradeables, $r);
                        }
                    }
                }
            }
        }
        if (count($gradeables) > 0) {
            $coursecount++;
            ksort($gradeables);
            $content->text .= self::grade_me_tree($gradeables);
        }
        unset($gradeables);

        $graderroles = array();
        foreach ($enabledplugins as $plugin => $a) {
            foreach (array_keys(get_roles_with_capability($a['capability'])) as $role) {
                $graderroles[$role] = true;
            }
        }
        $showempty = false;
        foreach ($graderroles as $roleid => $value) {
            if (user_has_role_assignment($USER->id, $roleid) || is_siteadmin()) {
                $showempty = true;
            }
        }

        if (!empty($content->text)) {
            $content->text = '<h6>' .get_string('grademelabeltext', 'block_myoverview'). '</h6><dl>' . $content->text . '</dl>';
        }

        return $content;
    }

    /**
     * Construct the tree of ungraded items
     *
     * @param array $course The array of ungraded items for a specific course
     * @return string $text
     */
    public static function grade_me_tree($course) {
        global $CFG, $DB, $OUTPUT, $SESSION;

        // Get time format string.
        $datetimestring = get_string('datetime', 'block_grade_me', array());
        // Grading image.
        $gradeimg = $CFG->wwwroot . '/blocks/grade_me/pix/check_mark.png';
        // Define text variable.
        $text = '';

        $courseid = $course['meta']['courseid'];
        $coursename = $course['meta']['coursename'];
        unset($course['meta']);

        ksort($course);

        foreach ($course as $item) {
            $itemmodule = $item['meta']['itemmodule'];
            $itemname = $item['meta']['itemname'];
            $coursemoduleid = $item['meta']['coursemoduleid'];
            unset($item['meta']);

            $modulelink = $CFG->wwwroot . '/mod/' . $itemmodule . '/view.php?id=' . $coursemoduleid;
            $gradelink = $CFG->wwwroot;
            if ($itemmodule == 'assignment') {
                $gradelink .= '/mod/assignment/submissions.php?id=' . $coursemoduleid;
            } else if ($itemmodule == 'quiz') {
                $gradelink .= '/mod/quiz/report.php?id=' . $coursemoduleid;
            } else {
                $gradelink = $modulelink;
            }

            $moduletitle = get_string('grademelabel', 'block_myoverview', array('itemname' => $itemname));
            $moduleicon = $OUTPUT->pix_icon('icon', '', $itemmodule, array('class' => 'icon'));

            $text .= '<dd id="cmid' . $coursemoduleid . '" class="module">' . "\n";  // Open module.
            $text .= '<div class="toggle" onclick="$(\'dd#cmid' . $coursemoduleid .
                    ' > div.toggle\').toggleClass(\'open\');$(\'dd#cmid' . $coursemoduleid .
                    ' > ul\').toggleClass(\'block_grade_me_hide\');"></div>' . "\n";
            $text .= '<a class="icon-size-4 align-self-top" href="' . $modulelink . '" aria-label="' . $moduletitle . '">' . $moduleicon .
                        '<span>' . $itemname . '</span> (' . count($item) . ')' . "\n";

            $text .= '<ul class="block_grade_me_hide">' . "\n";

            ksort($item);

            // Assign module needs to have a rownum and useridlist.
            $rownum = 0;
            $useridlistid = $coursemoduleid . time();
            $useridlist = array();

            foreach ($item as $l3 => $submission) {
                $timesubmitted = $l3;
                $userid = $submission['meta']['userid'];
                $submissionid = $submission['meta']['submissionid'];

                $submissionlink = $CFG->wwwroot;
                if ($itemmodule == 'assignment') {
                    $submissionlink .= '/mod/assignment/submissions.php?id=' . $coursemoduleid . '&amp;userid=' . $userid .
                            '&amp;mode=single&amp;filter=0&amp;offset=0';
                } else if ($itemmodule == 'assign') {
                    $submissionlink .= "/mod/assign/view.php?id=$coursemoduleid&action=grade&rownum=$rownum&useridlistid=$useridlistid";
                    $rownum++;
                    $useridlist[] = $userid;
                } else if ($itemmodule == 'data') {
                    $submissionlink .= '/mod/data/view.php?rid=' . $submissionid . '&amp;mode=single';
                } else if ($itemmodule == 'forum') {
                    $forumdiscussionid = $submission['meta']['forum_discussion_id'];
                    $submissionlink .= '/mod/forum/discuss.php?d=' . $forumdiscussionid . '#p' . $submissionid;
                } else if ($itemmodule == 'glossary') {
                    $submissionlink .= '/mod/glossary/view.php?id=' . $coursemoduleid . '#postrating' . $submissionid;
                } else if ($itemmodule == 'quiz') {
                    $submissionlink .= '/mod/quiz/review.php?attempt=' . $submissionid;
                }

                unset($submission['meta']);

                $submissiontitle = get_string('link_grade_img', 'block_grade_me', array());
                $altmark = get_string('alt_mark', 'block_grade_me', array());

                $user = $DB->get_record('user', array('id' => $userid));

                $userfirst = $user->firstname;
                $userfirstlast = $user->firstname . ' ' . $user->lastname;
                $userprofiletitle = get_string('link_user_profile', 'block_grade_me', array('first_name' => $userfirst));

                $text .= '<li class="gradable">';  // Open gradable.
                $text .= '<a href="' . $submissionlink . '" title="' . $submissiontitle . '"><img src="' . $gradeimg .
                        '" class="gm_icon" alt="' . $altmark . '" /></a>';  // Grade icon.
                $text .= $OUTPUT->user_picture($user, array('size' => 16, 'courseid' => $courseid, 'link' => true));
                $text .= '<a href="' . $CFG->wwwroot . '/user/view.php?id=' . $userid . '&amp;course=' .
                        $courseid . '" title="' . $userprofiletitle . '">' . $userfirstlast .
                        '</a>';  // User name and profile link.
                $text .= '<br />' . userdate($timesubmitted, $datetimestring);  // Output submission date.
                $text .= '</li>' . "\n";  // End gradable.
            }

            if ($itemmodule == 'assign') {
                $useridlistkey = $coursemoduleid . '_' . $useridlistid;
                $SESSION->mod_assign_useridlist[$useridlistkey] = $useridlist;
            }

            $text .= '</ul>' . "\n";
            $text .= '</dd>' . "\n";  // Close module.
        }

        return $text;
    }

    public static function get_course_links_teacher($course) {
        $links = array();

        //if (get_config('block_myoverview', 'badgelink')) {
        //    $url = new moodle_url('/badges/view.php', array('type' => 2, 'id' => $course));
        //    $link = new stdClass();
        //    $link->title = get_string('badges', 'block_myoverview', array());
        //    $link->url = $url->out();
        //    $link->action = '';
        //    $link->icon = 'trophy-star';
        //    $links[] = $link;
        //}

        if (get_config('block_myoverview', 'gradelink')) {
            $url = new moodle_url('/grade/report/index.php', array('id' => $course));
            $link = new stdClass();
            $link->title = get_string('grades', 'block_myoverview', array());
            $link->url = $url->out();
            $link->action = '';
            $link->icon = 'bar-chart';
            $links[] = $link;
        }

        if (get_config('block_myoverview', 'studentlistlink')) {
            $url = new moodle_url('/user/index.php', array('id' => $course));
            $link = new stdClass();
            $link->title = get_string('studentlist', 'block_myoverview', array());
            $link->url = $url->out();
            $link->action = '';
            $link->icon = 'users';
            $links[] = $link;
        }

        $link = new stdClass();
        $link->title = get_string('sendmessagetoclass', 'block_myoverview', array());
        $link->url = '#';
        $link->action = 'send-message-to-class';
        $link->icon = 'comment-dots';
        $links[] = $link;

        $link = new stdClass();
        $link->title = get_string('new_event', 'block_myoverview', array());
        $link->url = '#';
        $link->action = 'new-course-event';
        $link->icon = 'calendar';
        $links[] = $link;

        $quickaccesses = json_decode(get_config('theme_petel', 'quick_access') ?? '[]', true);
        if (isset($quickaccesses[$course]) and $quickaccesses[$course]) {
            $modinfo = get_fast_modinfo($course);
            $cm = $modinfo->get_cm($quickaccesses[$course]);

            $url = new moodle_url('/mod/' . $cm->modname . '/view.php', array('id' => $cm->id));
            $link = new stdClass();
            $link->title = get_string('quickaccess', 'block_myoverview', array());
            $link->url = $url->out();
            $link->action = '';
            $link->icon = 'link';
            $links[] = $link;
        }
        return $links;
    }

    public static function get_course_links_student($course) {
        $links = array();

        if (get_config('block_myoverview', 'badgelink')) {
            $url = new moodle_url('/badges/view.php', array('type' => 2, 'id' => $course));
            $link = new stdClass();
            $link->title = get_string('badges', 'block_myoverview', array());
            $link->url = $url->out();
            $link->action = '';
            $link->icon = 'trophy-star';
            $links[] = $link;
        }

        if (get_config('block_myoverview', 'gradelink')) {
            $url = new moodle_url('/grade/report/index.php', array('id' => $course));
            $link = new stdClass();
            $link->title = get_string('grades', 'block_myoverview', array());
            $link->url = $url->out();
            $link->action = '';
            $link->icon = 'bar-chart';
            $links[] = $link;
        }

        $link = new stdClass();
        $link->title = get_string('sendmessagetoteacher', 'block_myoverview', array());
        $link->url = '#';
        $link->action = 'send-message-to-teacher';
        $link->icon = 'envelope';
        $links[] = $link;

        return $links;
    }

    public static function get_quick_access($course) {
        global $OUTPUT;
        $html = '';
        $quickaccesses = json_decode(get_config('theme_petel', 'quick_access') ?? '[]', true);
        if (isset($quickaccesses[$course]) and $quickaccesses[$course]) {
            $modinfo = get_fast_modinfo($course);
            $cm = $modinfo->get_cm($quickaccesses[$course]);

            $url = new moodle_url('/mod/' . $cm->modname . '/view.php', array('id' => $cm->id));
            $moduleicon = $OUTPUT->pix_icon('icon', $cm->name, $cm->modname, array('class' => 'gm_icon'));
            $html .= '<a href="' . $url->out() . '" title="' . $cm->name . '">' . $moduleicon . ' ' . $cm->name . '</a>';
        }
        return $html;

    }

    public static function get_recent_items($courseid, $limit = 0) {
        global $USER, $DB, $OUTPUT;

        $courses = array();
        $recentitems = array();

        if (!isloggedin() or \core\session\manager::is_loggedinas() or isguestuser()) {
            // No access tracking.
            return $recentitems;
        }

        $paramsql = array('userid' => $USER->id, 'courseid' => $courseid);
        $sql = "SELECT rai.*
                  FROM {block_recentlyaccesseditems} rai
                  JOIN {course} c ON c.id = rai.courseid
                 WHERE userid = :userid AND c.id = :courseid
                 ORDER BY rai.timeaccess DESC";
        $records = $DB->get_records_sql($sql, $paramsql);
        $order = 0;

        // Get array of items by course. Use $order index to keep sql sorted results.
        foreach ($records as $record) {
            $courses[$record->courseid][$order++] = $record;
        }

        // Group by courses to reduce get_fast_modinfo requests.
        foreach ($courses as $key => $items) {
            $modinfo = get_fast_modinfo($key);
            if (!can_access_course($modinfo->get_course(), null, '', true)) {
                continue;
            }
            foreach ($items as $key => $item) {
                // Exclude not visible items.
                if (!$modinfo->cms[$item->cmid]->uservisible) {
                    continue;
                }
                $item->modname = $modinfo->cms[$item->cmid]->modname;
                $item->name = $modinfo->cms[$item->cmid]->name;
                $item->coursename = get_course_display_name_for_list($modinfo->get_course());

                $modname = $modinfo->cms[$item->cmid]->modname;
                $iconalt = get_string('pluginname', $modname);

                $item->viewurl = (new moodle_url('/mod/'.$modname.'/view.php', array('id' => $item->cmid)))->out(false);
                $item->icon = $OUTPUT->image_icon('icon', $iconalt, $modname, array('title' => ''));
                $item->dateaccess =  date('d/m/Y', $item->timeaccess);
                $item->arialabel =  get_string('continuelastactivity', 'block_myoverview').' '.$modinfo->cms[$item->cmid]->name;
                $recentitems[$key] = $item;
            }
        }

        ksort($recentitems);

        // Apply limit.
        if (!$limit) {
            $limit = count($recentitems);
        }
        $recentitems = array_slice($recentitems, 0, $limit);

        return $recentitems;
    }
}

function block_myoverview_cmp($a, $b){
    if ($a->lastaccess == $b->lastaccess) return 0;
    return ($a->lastaccess < $b->lastaccess) ? 1 : -1;
}
