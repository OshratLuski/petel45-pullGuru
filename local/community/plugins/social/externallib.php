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
 * External interface library for customfields component
 *
 * @package   community_social
 * @copyright 2019 Devlion <info@devlion.co>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . "/externallib.php");
require_once($CFG->libdir . '/datalib.php');

/**
 * Class community_social_external
 *
 * @copyright 2018 David Matamoros <davidmc@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class community_social_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function follow_teacher_parameters() {
        return new external_function_parameters(
                array(
                        'follow_enable' => new external_value(PARAM_INT, 'follow_enable', VALUE_DEFAULT, null),
                        'page_userid' => new external_value(PARAM_INT, 'page_userid', VALUE_DEFAULT, null),
                        'current_userid' => new external_value(PARAM_INT, 'current_userid', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function follow_teacher_returns() {
        return new external_single_structure(
                array()
        );
    }

    /**
     * Follow teacher
     *
     * @param int $followenable
     * @param int $pageuserid
     * @param int $currentuserid
     * @return array
     */
    public static function follow_teacher($followenable, $pageuserid, $currentuserid) {
        global $USER, $DB;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $isactive = 1;

        $obj = $DB->get_record('community_social_followers', ['userid' => $currentuserid, 'followuserid' => $pageuserid]);
        if (!empty($obj)) {
            switch ($obj->isactive) {
                case 0:
                    $isactive = 1;
                    break;
                case 1:
                    $isactive = 0;
                    break;
                default:
                    $isactive = 0;
            }

            $obj->isactive = $isactive;
            $DB->update_record('community_social_followers', $obj);
        } else {
            $dataobject = new \stdClass();
            $dataobject->userid = $currentuserid;
            $dataobject->followuserid = $pageuserid;
            $dataobject->isactive = $isactive;
            $dataobject->timecreated = time();
            $dataobject->timemodified = time();
            $DB->insert_record('community_social_followers', $dataobject);

            // Send message to user.
            \community_social\message::send_to_teacher($currentuserid, $pageuserid, '', 'community_social',
                    'social_folowers');
        }

        // Recache user.
        $social = new \community_social\social();
        $social->refreshUser($currentuserid);
        $social->refreshUser($pageuserid);

        // Save Moodle Log.
        $eventdata = [
                'userid' => $currentuserid,
                'followuserid' => $pageuserid,
                'isactive' => $isactive,
        ];
        \community_social\event\social_followed::create_event($USER->id, $eventdata)->trigger();

        return [];
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function render_block_user_data_parameters() {
        return new external_function_parameters(
                array(
                        'userid' => new external_value(PARAM_INT, 'userid', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function render_block_user_data_returns() {
        return new external_single_structure(
                array(
                        'data' => new external_value(PARAM_RAW, 'result data'),
                )
        );
    }

    /**
     * Render aside user data
     *
     * @param int $userid
     * @return array
     */
    public static function render_block_user_data($userid) {
        global $USER;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $social = new \community_social\social();
        $data = $social->getSingleDataUser($userid);

        return ['data' => json_encode($data)];
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function user_collegues_list_parameters() {
        return new external_function_parameters(
                array(
                        'page_userid' => new external_value(PARAM_INT, 'page_userid', VALUE_REQUIRED, null),
                        'current_userid' => new external_value(PARAM_INT, 'current_userid', VALUE_REQUIRED, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function user_collegues_list_returns() {
        return new external_single_structure(
                array(
                        'data' => new external_value(PARAM_RAW, 'json data'),
                        'header' => new external_value(PARAM_RAW, 'header popup'),
                )
        );
    }

    /**
     * Request user collegues list
     *
     * @param int $pageuserid
     * @param int $currentuserid
     * @return array
     */
    public static function user_collegues_list($pageuserid, $currentuserid) {
        global $USER;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $social = new \community_social\social();
        $data = $social->getSingleDataUser($pageuserid);

        // Group by userid.
        $users = [];
        foreach ($data->colleagues as $item) {
            $users[] = $item->userid;
        }
        $users = array_unique($users);

        $result = [];
        foreach ($users as $userid) {
            if ($tmp = $social->getSingleDataUser($userid)) {
                $tmp->active_collegue = 1;

                $result[] = $tmp;
            }
        }

        $totalusers = count($result);

        $arrcontent = [
                'data' => json_encode(['data' => $result]),
                'header' => get_string('usercollegueslist', 'community_social') . ' ' . $data->firstname . ' ' .
                        $data->lastname . ' (' . $totalusers . ')'
        ];

        return $arrcontent;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function user_follower_list_parameters() {
        return new external_function_parameters(
                array(
                        'page_userid' => new external_value(PARAM_INT, 'page_userid', VALUE_REQUIRED, null),
                        'current_userid' => new external_value(PARAM_INT, 'current_userid', VALUE_REQUIRED, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function user_follower_list_returns() {
        return new external_single_structure(
                array(
                        'data' => new external_value(PARAM_RAW, 'json data'),
                        'header' => new external_value(PARAM_RAW, 'header popup'),
                )
        );
    }

    /**
     * Request user follower list
     *
     * @param int $pageuserid
     * @param int $currentuserid
     * @return array
     */
    public static function user_follower_list($pageuserid, $currentuserid) {
        global $DB, $USER;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $social = new \community_social\social();
        $userpage = $social->getSingleDataUser($pageuserid);

        $result = [];
        $followers = $DB->get_records('community_social_followers', ['followuserid' => $pageuserid, 'isactive' => 1]);
        foreach ($followers as $item) {

            if (!in_array($item->userid, $userpage->followers)) {
                continue;
            }

            if ($tmp = $social->getSingleDataUser($item->userid)) {
                $tmp->active_follower = 0;
                if ($item->isactive) {
                    $tmp->active_follower = 1;
                }

                $tmp->active_collegue = 0;
                if ($social->if_user_colleagues($item->userid, $pageuserid)) {
                    $tmp->active_collegue = 1;
                }

                $result[] = $tmp;
            }
        }

        $totalusers = count($result);

        $arrcontent = [
                'data' => json_encode(['data' => $result]),
                'header' => get_string('userfollowerlist', 'community_social') . ' ' . $userpage->firstname . ' ' .
                        $userpage->lastname . ' (' . $totalusers . ')'
        ];

        return $arrcontent;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function render_teacher_block_parameters() {
        return new external_function_parameters(
                array(
                        'teacher_tab' => new external_value(PARAM_INT, 'teacher_tab', VALUE_DEFAULT, null),
                        'search' => new external_value(PARAM_TEXT, 'search', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function render_teacher_block_returns() {
        return new external_single_structure(
                array(
                        'content' => new external_value(PARAM_RAW, 'result html'),
                        'header' => new external_value(PARAM_RAW, 'result html'),
                )
        );
    }

    /**
     * Render teacher block after click on tab button
     *
     * @param int $teachertab
     * @param string $type
     * @return array
     */
    public static function render_teacher_block($teachertab, $search) {
        global $OUTPUT, $USER;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $social = new \community_social\social();

        $data = new \StdClass;
        $data = $social->data_list_teachers($data, $teachertab, $search);

        $html = $OUTPUT->render_from_template('community_social/teachers/card-block', $data);

        $arrcontent = [
                'content' => $html,
                'header' => ''
        ];

        return $arrcontent;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function popup_public_course_parameters() {
        return new external_function_parameters(
                array(
                        'userid' => new external_value(PARAM_INT, 'userid', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function popup_public_course_returns() {
        return new external_single_structure(
                array(
                        'data' => new external_value(PARAM_RAW, 'json data'),
                        'header' => new external_value(PARAM_RAW, 'header popup'),
                )
        );
    }

    /**
     * Get courses for pombim
     *
     * @param int $userid
     * @return array
     */
    public static function popup_public_course($userid) {
        global $CFG, $USER;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $allcourses = \community_social\funcs::get_users_courses($userid);

        $social = new \community_social\social();
        $data = $social->getSingleDataUser($userid);

        $ifcopy = false;
        $result = [];
        foreach ($allcourses as $key => $courseid) {
            $tmp = get_course($courseid);
            $tmp->checked = '';
            $tmp->counter = 'customid' . $key;
            $tmp->imageurl = \community_social\funcs::get_course_image($courseid);
            $tmp->courseurl = $CFG->wwwroot . '/course/view.php?id=' . $courseid;

            foreach ($data->courses_pombim as $pombim) {
                if ($courseid == $pombim->id) {
                    $tmp->checked = 'checked';
                }

                if ($pombim->ifcopy == 1) {
                    $ifcopy = true;
                }
            }

            $result[] = $tmp;
        }

        $data = ['courses' => $result, 'ifcopy' => $ifcopy];

        $arrcontent = [
                'data' => json_encode($data),
                'header' => get_string('choosingpubliccourses', 'community_social')
        ];

        return $arrcontent;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function save_selected_pombim_courses_parameters() {
        return new external_function_parameters(
                array(
                        'userid' => new external_value(PARAM_INT, 'userid', VALUE_DEFAULT, null),
                        'ids' => new external_value(PARAM_TEXT, 'ids', VALUE_DEFAULT, null),
                        'ifcopy' => new external_value(PARAM_BOOL, 'ifcopy', VALUE_DEFAULT, false),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function save_selected_pombim_courses_returns() {
        return new external_single_structure(
                array()
        );
    }

    /**
     * Save selected pombim courses
     *
     * @param int $userid
     * @param string $ids
     * @return array
     */
    public static function save_selected_pombim_courses($userid, $ids, $ifcopy) {
        global $DB;

        $arrids = json_decode($ids);

        // Delete rows.
        $allrows = $DB->get_records('community_social_shrd_crss', ['userid' => $userid]);
        foreach ($allrows as $item) {
            if (!in_array($item->courseid, $arrids)) {

                // Delete permission by course from all followed users.
                $row = $DB->get_record('community_social_shrd_crss', ['userid' => $userid, 'courseid' => $item->courseid]);
                $collegues = $DB->get_records('community_social_collegues', ['social_shared_courses_id' => $row->id]);
                foreach ($collegues as $colleg) {
                    $DB->update_record('community_social_collegues', ['id' => $colleg->id, 'approved' => 0]);
                    \community_social\funcs::close_permission_course($colleg->userid, $item->courseid);
                }

                $DB->delete_records('community_social_shrd_crss', ['userid' => $userid, 'courseid' => $item->courseid]);
            }
        }

        foreach ($arrids as $courseid) {
            if ($row = $DB->get_record('community_social_shrd_crss', ['userid' => $userid, 'courseid' => $courseid])) {
                $row->userid = $userid;
                $row->courseid = $courseid;
                $row->ifcopy = $ifcopy ? 1 : 0;
                $row->timemodified = time();
                $DB->update_record('community_social_shrd_crss', $row);
            } else {
                $dataobject = new \stdClass();
                $dataobject->userid = $userid;
                $dataobject->courseid = $courseid;
                $dataobject->ifcopy = $ifcopy ? 1 : 0;
                $dataobject->timecreated = time();
                $dataobject->timemodified = time();
                $DB->insert_record('community_social_shrd_crss', $dataobject);
            }
        }

        // Recache user.
        $social = new \community_social\social();
        $social->refreshUser($userid);

        return [];
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function render_block_aside_courses_pombim_parameters() {
        return new external_function_parameters(
                array(
                        'userid' => new external_value(PARAM_INT, 'userid', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function render_block_aside_courses_pombim_returns() {
        return new external_single_structure(
                array(
                        'data' => new external_value(PARAM_RAW, 'result data'),
                )
        );
    }

    /**
     * Render aside courses pombim
     *
     * @param int $userid
     * @return array
     */
    public static function render_block_aside_courses_pombim($userid) {
        global $USER;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $social = new \community_social\social();
        $data = $social->getSingleDataUser($userid);

        return ['data' => json_encode($data)];
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function render_profile_blocks_parameters() {
        return new external_function_parameters(
                array(
                        'userid' => new external_value(PARAM_INT, 'userid', VALUE_DEFAULT, null),
                        'search' => new external_value(PARAM_TEXT, 'search', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function render_profile_blocks_returns() {
        return new external_single_structure(
                array(
                        'data' => new external_value(PARAM_RAW, 'result data'),
                )
        );
    }

    /**
     * Render courses pombim
     *
     * @param int $userid
     * @return array
     */
    public static function render_profile_blocks($userid, $search) {
        global $USER;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $social = new \community_social\social();
        $data = $social->getSingleDataUser($userid);

        // Search.
        $arrsearch = json_decode($search);
        foreach ($arrsearch as $searchval) {
            $searchval = trim($searchval);
            if (!empty($searchval)) {

                // Search in courses pombim.
                $coursespombim = [];
                foreach ($data->courses_pombim as $course) {
                    if (strpos($course->fullname, strval($searchval)) !== false || strpos($course->summary, strval($searchval)) !== false) {
                        $coursespombim[] = $course;
                    }
                }

                $data->courses_pombim = $coursespombim;
                $data->count_courses_pombim = count($coursespombim);

                // Search in oercatalog activities.
                $oeractivities = [];
                foreach ($data->oercatalog_activities as $act) {
                    if (strpos($act->mod_name, strval($searchval)) !== false || strpos($act->mod_intro, strval($searchval)) !== false) {
                        $oeractivities[] = $act;
                    }
                }

                $data->oercatalog_activities = $oeractivities;
                $data->count_oercatalog_activities = count($oeractivities);

                // Search in oercatalog courses.
                $oercourses = [];
                foreach ($data->oercatalog_courses as $course) {
                    if (strpos($course->fullname, strval($searchval)) !== false || strpos($course->metadata_cdescription, strval($searchval)) !== false) {
                        $oercourses[] = $course;
                    }
                }

                $data->oercatalog_courses = $oercourses;
                $data->oercatalog_courses_count = count($oercourses);

                $data->all_courses_count = $data->oercatalog_courses_count + $data->count_courses_pombim;
            }
        }

        if (!empty($data)) {

            // Prepare oer activities block.
            foreach ($data->oercatalog_activities as $item) {
                $data->oercatalog_activities['blocks'][] = $item;

                // Update counter community_oer_wht_new.
                \community_oer\activity_oer::funcs()::whats_new_update_counter($item->cmid);
            }

            $data->oercatalog_activities_enable = !empty($data->oercatalog_activities) ? true : false;

            // Prepare oer courses block.
            $oercourses = $data->oercatalog_courses;
            if (!empty($oercourses)) {
                $data->oercatalog_courses['blocks'] = $oercourses;
                $data->count_oercatalog_courses = count($oercourses);
                $data->oercatalog_courses_enable = true;
            }
        }

        return ['data' => json_encode($data)];
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function school_settings_parameters() {
        return new external_function_parameters(
                array(
                        'userid' => new external_value(PARAM_INT, 'userid', VALUE_REQUIRED, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function school_settings_returns() {
        return new external_single_structure(
                array(
                        'data' => new external_value(PARAM_RAW, 'json data'),
                        'header' => new external_value(PARAM_RAW, 'header popup'),
                )
        );
    }

    /**
     * School settings
     *
     * @param int $userid
     * @return array
     */
    public static function school_settings($userid) {
        global $USER;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $social = new \community_social\social();
        $data = $social->getSingleDataUser($userid);

        $arrcontent = [
                'data' => json_encode($data),
                'header' => get_string('editingtheschool', 'community_social')
        ];

        return $arrcontent;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function school_save_parameters() {
        return new external_function_parameters(
                array(
                        'userid' => new external_value(PARAM_INT, 'userid', VALUE_DEFAULT, null),
                        'value' => new external_value(PARAM_TEXT, 'value', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function school_save_returns() {
        return new external_single_structure(
                array()
        );
    }

    /**
     * school_save
     *
     * @param int $userid
     * @param string $value
     * @return array
     */
    public static function school_save($userid, $value) {
        global $DB;

        $social = new \community_social\social();

        if ($social->if_editable_profile($userid)) {

            // Update school.
            $teudat = $DB->get_record('user_info_field', array('shortname' => 'school'));

            if (!empty($teudat)) {
                $res = $DB->get_record('user_info_data', array('fieldid' => $teudat->id, 'userid' => $userid));

                if (!empty($res)) {
                    $res->data = $value;
                    $DB->update_record('user_info_data', $res, $bulk = false);
                } else {
                    $dataobject = new stdClass();
                    $dataobject->userid = $userid;
                    $dataobject->fieldid = $teudat->id;
                    $dataobject->data = $value;
                    $DB->insert_record('user_info_data', $dataobject);
                }
            }

            // Recache user.
            $social->refreshUser($userid);
        }

        return [];
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function social_disable_parameters() {
        return new external_function_parameters(
                array()
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function social_disable_returns() {
        return new external_single_structure(
                array(
                        'data' => new external_value(PARAM_RAW, 'json data'),
                        'header' => new external_value(PARAM_RAW, 'header popup'),
                )
        );
    }

    /**
     * social_disable
     *
     * @return array
     */
    public static function social_disable() {
        global $USER;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $link = new moodle_url('/local/community/plugins/social/index.php',
                array_filter(['id' => null, 'socialenable' => 0], '\community_social\funcs::filter_userid'));

        $data = [
                'social_disable' => $link->out(false),
        ];

        $arrcontent = [
                'data' => json_encode($data),
                'header' => get_string('disablingsocialarea', 'community_social')
        ];

        return $arrcontent;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function send_followed_courses_parameters() {
        return new external_function_parameters(
                array(
                        'page_userid' => new external_value(PARAM_INT, 'page_userid', VALUE_REQUIRED, null),
                        'current_userid' => new external_value(PARAM_INT, 'current_userid', VALUE_REQUIRED, null),
                        'ids' => new external_value(PARAM_TEXT, 'ids', VALUE_REQUIRED, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function send_followed_courses_returns() {
        return new external_single_structure(
                array()
        );
    }

    /**
     * Send followed courses
     *
     * @param int $pageuserid
     * @param int $currentuserid
     * @param string $ids
     * @return array
     */
    public static function send_followed_courses($pageuserid, $currentuserid, $ids) {
        global $USER, $DB;

        $arrids = json_decode($ids);

        // Get social_shared_courses_ids by courseid.
        $sharedcoursesid = '';
        foreach ($arrids as $courseid) {
            $row = $DB->get_record('community_social_shrd_crss', ['userid' => $pageuserid, 'courseid' => $courseid]);
            if (!empty($row)) {
                $sharedcoursesid = $row->id;
            }
        }

        // Send message to user with request.
        if (!empty($sharedcoursesid)) {

            // Send message to user.
            $messageid = \community_social\message::send_to_teacher($USER->id, $pageuserid, $sharedcoursesid,
                    'community_social', 'social_request');

            // Delete old row.
            $res = $DB->get_record('community_social_requests', ['userid' => $pageuserid, 'usersendid' => $USER->id,
                    'social_shared_courses_ids' => $sharedcoursesid]);
            if (!empty($res)) {
                $DB->delete_records('community_social_requests', ['userid' => $pageuserid, 'usersendid' => $USER->id,
                        'social_shared_courses_ids' => $sharedcoursesid]);
            }

            $dataobject = new \stdClass();
            $dataobject->userid = $pageuserid;
            $dataobject->usersendid = $USER->id;
            $dataobject->social_shared_courses_ids = $sharedcoursesid;
            $dataobject->status = 0;
            $dataobject->messageid = $messageid;
            $dataobject->timecreated = time();
            $dataobject->timemodified = time();
            $DB->insert_record('community_social_requests', $dataobject);

            // Save Moodle Log.
            $eventdata = [
                    'userid' => $USER->id,
                    'targetuserid' => $pageuserid,
                    'courses' => $sharedcoursesid,
            ];
            \community_social\event\request_colleague::create_event($USER->id, $eventdata)->trigger();
        }

        return [];
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function remove_teacher_from_course_parameters() {
        return new external_function_parameters(
                array(
                        'userid' => new external_value(PARAM_INT, 'userid', VALUE_DEFAULT, null),
                        'courseid' => new external_value(PARAM_INT, 'courseid', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function remove_teacher_from_course_returns() {
        return new external_single_structure(
                array()
        );
    }

    /**
     * Remove teacher from course
     *
     * @param int userid
     * @param int courseid
     * @return array
     */
    public static function remove_teacher_from_course($userid, $courseid) {
        global $DB;

        $obj = $DB->get_record('community_social_collegues', ['userid' => $userid, 'social_shared_courses_id' => $courseid]);
        if (!empty($obj)) {
            $obj->approved = 0;
            $DB->update_record('community_social_collegues', $obj);

            $row = $DB->get_record('community_social_shrd_crss', ['id' => $courseid]);
            if (!empty($row)) {
                \community_social\funcs::close_permission_course($userid, $row->courseid);
            }

            // Recache user.
            $social = new \community_social\social();
            $social->refreshUser($userid);
        }

        return [];
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function request_followed_courses_parameters() {
        return new external_function_parameters(
                array(
                        'page_userid' => new external_value(PARAM_INT, 'page_userid', VALUE_DEFAULT, null),
                        'current_userid' => new external_value(PARAM_INT, 'current_userid', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function request_followed_courses_returns() {
        return new external_single_structure(
                array(
                        'data' => new external_value(PARAM_RAW, 'json data'),
                        'header' => new external_value(PARAM_RAW, 'header popup'),
                )
        );
    }

    /**
     * Request followed courses
     *
     * @param int page_userid
     * @param int current_userid
     * @return array
     */
    public static function request_followed_courses($pageuserid, $currentuserid) {
        global $CFG;

        $social = new \community_social\social();
        $data = $social->getSingleDataUser($pageuserid);

        $result = [];
        foreach ($data->courses_pombim as $key => $course) {
            $tmp = get_course($course->id);
            $tmp->checked = '';
            $tmp->counter = 'customid' . $key;
            $tmp->imageurl = \community_social\funcs::get_course_image($course->id);
            $tmp->courseurl = $CFG->wwwroot . '/course/view.php?id=' . $course->id;

            $result[] = $tmp;
        }

        $arrcontent = [
                'data' => json_encode(['data' => $result]),
                'header' => 'select followed courses'
        ];
        return $arrcontent;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function change_follow_teacher_by_user_parameters() {
        return new external_function_parameters(
                array(
                        'current_userid' => new external_value(PARAM_INT, 'current_userid', VALUE_DEFAULT, null),
                        'custom_userid' => new external_value(PARAM_INT, 'custom_userid', VALUE_DEFAULT, null),
                        'page_userid' => new external_value(PARAM_INT, 'page_userid', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function change_follow_teacher_by_user_returns() {
        return new external_single_structure(
                array()
        );
    }

    /**
     * Change follow teacher by user
     *
     * @param int page_userid
     * @param int current_userid
     * @param int custom_userid
     * @return array
     */
    public static function change_follow_teacher_by_user($currentuserid, $customuserid, $pageuserid) {
        global $USER, $DB;

        $obj = $DB->get_record('community_social_followers', ['userid' => $customuserid, 'followuserid' => $pageuserid]);
        if (!empty($obj)) {
            $obj->isactive = 0;
            $DB->update_record('community_social_followers', $obj);
        }

        // Send message.
        \community_social\message::send_to_teacher($customuserid, $pageuserid, '', '',
                'community_social', 'social_folowers');

        $social = new \community_social\social();
        $social->refreshUser($customuserid);
        $social->refreshUser($pageuserid);

        // Save Moodle Log.
        $eventdata = [
                'userid' => $customuserid,
                'followuserid' => $pageuserid,
                'isactive' => 0,
        ];
        \community_social\event\social_followed::create_event($USER->id, $eventdata)->trigger();

        return [];
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function approve_message_from_teacher_parameters() {
        return new external_function_parameters(
                array(
                        'messageid' => new external_value(PARAM_INT, 'message id', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function approve_message_from_teacher_returns() {
        return new external_single_structure(
                array()
        );
    }

    /**
     * Approve message from teacher
     *
     * @param int $messageid
     * @return array
     */
    public static function approve_message_from_teacher($messageid) {
        global $USER, $DB, $CFG;

        $obj = $DB->get_record('community_social_requests', ['messageid' => $messageid, 'userid' => $USER->id]);
        if (!empty($obj)) {
            \community_social\funcs::approve_courses_to_user($obj->usersendid, $obj->social_shared_courses_ids);

            $obj->status = 1;
            $DB->update_record('community_social_requests', $obj);

            // Send message to user with result.
            $row = $DB->get_record('notifications', ['id' => $messageid]);
            if (!empty($row)) {
                \community_social\message::send_to_teacher($USER->id, $row->useridfrom, $obj->social_shared_courses_ids,
                        'community_social', 'social_approve');
            }

            // Change eventtype to notification.
            $rowobj = $DB->get_record('notifications', ['id' => $messageid]);
            if (!empty($rowobj)) {
                $rowobj->eventtype = 'social_approve_complete';
                $rowobj->timeread = time();

                $tmp = json_decode($rowobj->customdata);
                $tmp->social_request = false;
                $tmp->social_approve_complete = true;
                $tmp->content = get_string('requestapprovecompletetocourse', 'community_social');
                $rowobj->customdata = json_encode($tmp);

                $DB->update_record('notifications', $rowobj);
            }

            // Send mail to user.
            $userto = $DB->get_record('user', ['id' => $obj->usersendid]);
            $userfrom = $CFG->noreplyaddress;
            $subject = get_string('infomessageforteacher', 'community_social');

            $requestuser = $DB->get_record('user', ['id' => $obj->userid]);

            $a = new \stdClass();
            $a->userName = $requestuser->firstname . ' ' . $requestuser->lastname;
            $a->courseNames = '';

            $sharedcoursesids = explode(',', $obj->social_shared_courses_ids);
            foreach ($sharedcoursesids as $courseid) {
                $row = $DB->get_record('community_social_shrd_crss', ['id' => $courseid]);
                $course = get_course($row->courseid);
                $a->courseNames .= $course->fullname . ' ';
                $a->courseUrl .= $CFG->wwwroot . '/course/view.php?id=' . $course->id;
            }

            $bodyhtml = get_string('coursepombimapproveforuser', 'community_social', $a);
            email_to_user($userto, $userfrom, $subject, '', $bodyhtml);

            // Save Moodle Log.
            $eventdata = [
                    'userid' => $obj->userid,
                    'targetuserid' => $obj->usersendid,
                    'courses' => $obj->social_shared_courses_ids,
            ];
            \community_social\event\approve_colleague::create_event($USER->id, $eventdata)->trigger();

            // Recache user.
            $social = new \community_social\social();
            $social->refreshUser($obj->userid);
            $social->refreshUser($obj->usersendid);
        }

        return [];
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function decline_message_from_teacher_parameters() {
        return new external_function_parameters(
                array(
                        'messageid' => new external_value(PARAM_INT, 'message id', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function decline_message_from_teacher_returns() {
        return new external_single_structure(
                array()
        );
    }

    /**
     * Decline message from teacher
     *
     * @param int $messageid
     * @return array
     */
    public static function decline_message_from_teacher($messageid) {
        global $USER, $DB, $CFG;

        $obj = $DB->get_record('community_social_requests', ['messageid' => $messageid, 'userid' => $USER->id]);
        if (!empty($obj)) {
            $obj->status = 2;
            $DB->update_record('community_social_requests', $obj);

            // Send message to user with result.
            $row = $DB->get_record('notifications', ['id' => $messageid]);
            if (!empty($row)) {
                \community_social\message::send_to_teacher($USER->id, $row->useridfrom, $obj->social_shared_courses_ids,
                        'community_social', 'social_decline');
            }

            // Change eventtype to notification.
            $rowobj = $DB->get_record('notifications', ['id' => $messageid]);
            if (!empty($rowobj)) {
                $rowobj->eventtype = 'social_decline_complete';

                $tmp = json_decode($rowobj->customdata);
                $tmp->social_request = false;
                $tmp->social_decline_complete = true;
                $tmp->content = get_string('requestdeclinecompletetocourse', 'community_social');
                $rowobj->customdata = json_encode($tmp);

                $rowobj->timeread = time();
                $DB->update_record('notifications', $rowobj);
            }

            // Send mail to user.
            $userto = $DB->get_record('user', ['id' => $obj->usersendid]);
            $userfrom = $CFG->noreplyaddress;
            $subject = get_string('infomessageforteacher', 'community_social');

            $requestuser = $DB->get_record('user', ['id' => $obj->userid]);

            $a = new \stdClass();
            $a->userName = $requestuser->firstname . ' ' . $requestuser->lastname;
            $a->courseNames = '';

            $sharedcoursesids = explode(',', $obj->social_shared_courses_ids);
            foreach ($sharedcoursesids as $courseid) {
                $row = $DB->get_record('community_social_shrd_crss', ['id' => $courseid]);
                $course = get_course($row->courseid);
                $a->courseNames .= $course->fullname . ' ';
            }

            $bodyhtml = get_string('coursepombimdeclineforuser', 'community_social', $a);
            email_to_user($userto, $userfrom, $subject, '', $bodyhtml);

            // Save Moodle Log.
            $eventdata = [
                    'userid' => $obj->userid,
                    'targetuserid' => $obj->usersendid
            ];
            \community_social\event\decline_colleague::create_event($USER->id, $eventdata)->trigger();
        }

        return [];
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function remove_teacher_request_parameters() {
        return new external_function_parameters(
                array(
                        'courseid' => new external_value(PARAM_INT, 'course id', VALUE_DEFAULT, null),
                        'userid' => new external_value(PARAM_INT, 'user id', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function remove_teacher_request_returns() {
        return new external_single_structure(
                array(
                        'data' => new external_value(PARAM_RAW, 'json data'),
                        'header' => new external_value(PARAM_RAW, 'header popup'),
                )
        );
    }

    /**
     * Remove teacher request
     *
     * @param int $courseid
     * @param int $userid
     * @return array
     */
    public static function remove_teacher_request($courseid, $userid) {
        global $DB;

        $sharedobj = $DB->get_record("community_social_shrd_crss", ['id' => $courseid]);
        $course = get_course($sharedobj->courseid);

        $social = new \community_social\social();
        $user = $social->getSingleDataUser($userid);

        $data = [];
        $data['courseid'] = $courseid;
        $data['userid'] = $userid;
        $data['course_name'] = $course->fullname;
        $data['user_firstname'] = $user->firstname;
        $data['user_lastname'] = $user->lastname;

        $arrcontent = [
                'data' => json_encode($data),
                'header' => get_string('removepeerteacher', 'community_social')
        ];

        return $arrcontent;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function popup_migrate_public_course_parameters() {
        return new external_function_parameters(
                array(
                        'userid' => new external_value(PARAM_INT, 'userid', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function popup_migrate_public_course_returns() {
        return new external_single_structure(
                array(
                        'data' => new external_value(PARAM_RAW, 'json data'),
                        'header' => new external_value(PARAM_RAW, 'header popup'),
                )
        );
    }

    /**
     * Get courses for pombim
     *
     * @param int $userid
     * @return array
     */
    public static function popup_migrate_public_course($userid) {
        global $USER;

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $allcourses = \community_social\funcs::get_users_courses($userid);

        $social = new \community_social\social();
        $data = $social->getSingleDataUser($userid);

        // Prepare courses pombim.
        $ifcopy = false;
        $pombim = [];
        $exclude = [];
        foreach ($data->courses_pombim as $item) {
            $pombim[] = ['id' => $item->id, 'name' => $item->fullname];
            $exclude[] = $item->id;

            if ($item->ifcopy == 1) {
                $ifcopy = true;
            }
        }

        // Prepare courses for migrate.
        $migrate = [];
        foreach ($allcourses as $id) {

            if (in_array($id, $exclude)) {
                continue;
            }

            $course = get_course($id);
            $migrate[] = ['id' => $course->id, 'name' => $course->fullname];
        }

        $data = [
                'pombim' => $pombim,
                'migrate' => $migrate,
                'migrate_enable' => count($pombim) && count($migrate),
                'ifcopy' => $ifcopy
            ];

        $arrcontent = [
                'data' => json_encode($data),
                'header' => get_string('migratepubliccourses', 'community_social')
        ];

        return $arrcontent;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function migrate_public_course_parameters() {
        return new external_function_parameters(
                array(
                        'userid' => new external_value(PARAM_INT, 'userid', VALUE_DEFAULT, null),
                        'oldcourseid' => new external_value(PARAM_INT, 'public course id', VALUE_DEFAULT, null),
                        'newcourseid' => new external_value(PARAM_INT, 'my course id', VALUE_DEFAULT, null),
                        'ifcopy' => new external_value(PARAM_BOOL, 'if copy', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function migrate_public_course_returns() {
        return new external_single_structure(
                array()
        );
    }

    /**
     * Get courses for pombim
     *
     * @param int $userid
     * @return array
     */
    public static function migrate_public_course($userid, $oldcourseid, $newcourseid, $ifcopy) {
        global $USER;

        $context = context_user::instance($userid);
        self::validate_context($context);

        \community_social\funcs::migrate_course($oldcourseid, $newcourseid, $ifcopy);

        return array();
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function lazy_load_parameters() {
        return new external_function_parameters(
                array(
                        'teacher_tab' => new external_value(PARAM_INT, 'teacher_tab', VALUE_DEFAULT, null),
                        'search' => new external_value(PARAM_TEXT, 'search', VALUE_DEFAULT, null),
                        'loaded_cards' => new external_value(PARAM_INT, 'loaded_cards', VALUE_DEFAULT, null),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function lazy_load_returns() {
        return new external_single_structure(
                array(
                        'content' => new external_value(PARAM_RAW, 'result html'),
                        'loaded_cards' => new external_value(PARAM_INT, 'loaded_cards'),
                )
        );
    }

    /**
     * Get cards lazy load.
     *
     * @return array
     */
    public static function lazy_load($teachertab, $search, $loadedcards) {
        global $OUTPUT;

        $context = context_system::instance();
        self::validate_context($context);

        $html = '';
        $social = new \community_social\social();

        foreach ($social->data_lazy_loading($teachertab, $search, $loadedcards) as $data) {
            $html .= $OUTPUT->render_from_template('community_social/teachers/card', $data);
            $loadedcards++;
        }

        $arrcontent = [
                'content' => $html,
                'loaded_cards' => $loadedcards,
        ];

        return $arrcontent;
    }

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function share_corse_pombim_parameters() {
        return new external_function_parameters(
                array(
                        'courseid' => new external_value(PARAM_INT, 'courseid', VALUE_DEFAULT, null),
                        'ifcopy' => new external_value(PARAM_BOOL, 'ifcopy', VALUE_DEFAULT, false),
                )
        );
    }

    /**
     * Returns result
     *
     * @return external_single_structure
     */
    public static function share_corse_pombim_returns() {
        return new external_single_structure(
                array(
                        'status' => new external_value(PARAM_RAW, 'status'),
                )
        );
    }

    /**
     * Get cards lazy load.
     *
     * @return array
     */
    public static function share_corse_pombim($courseid, $ifcopy) {
        global $DB, $USER;

        $context = context_system::instance();
        self::validate_context($context);

        if ($row = $DB->get_record('community_social_shrd_crss', ['userid' => $USER->id, 'courseid' => $courseid])) {
            $row->userid = $USER->id;
            $row->courseid = $courseid;
            $row->ifcopy = $ifcopy ? 1 : 0;
            $row->timemodified = time();
            $DB->update_record('community_social_shrd_crss', $row);
        } else {
            $dataobject = new \stdClass();
            $dataobject->userid = $USER->id;
            $dataobject->courseid = $courseid;
            $dataobject->ifcopy = $ifcopy ? 1 : 0;
            $dataobject->timecreated = time();
            $dataobject->timemodified = time();
            $DB->insert_record('community_social_shrd_crss', $dataobject);
        }

        // Recache user.
        $social = new \community_social\social();
        $social->refreshUser($USER->id);

        $arrcontent = [
                'status' => '',
        ];

        return $arrcontent;
    }
}
