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
 * @package    community_social
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace community_social;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/format/lib.php');

class social {

    public $cache;
    public $key;
    public $maxuserspage;

    public function __construct() {
        global $DB;

        $maxuserspage = get_config('community_social', 'maxuserspage');
        if (!is_numeric($maxuserspage)){
            $maxuserspage = 20;
        }

        $this->maxuserspage = $maxuserspage;

        $this->cache = \cache::make_from_params(\cache_store::MODE_APPLICATION, 'social_cache', 'data');
        $this->key = 'data';

        $count = $DB->count_records('community_social_usr_dtls');
        if ($count > count($this->get_data_from_cache())) {
            $this->recalculate_data_in_cache();
        }
    }

    public function recalculate_data_in_cache() {
        global $DB;

        $result = [];
        foreach ($DB->get_records('community_social_usr_dtls') as $item) {
            $data = json_decode($item->data);
            $result[$data->uniqueid] = $data;
        }

        $this->cache->purge();
        $this->cache->set($this->key, $result);
    }

    public function social_recalculate_in_db($userid) {
        global $DB;

        if ($data = $this->build_social_data($userid)) {

            $row = $DB->get_record('community_social_usr_dtls', ['userid' => $userid]);
            if ($row) {
                $row->data = json_encode($data);
                $row->timecreated = time();
                $row->timemodified = time();

                $DB->update_record('community_social_usr_dtls', $row);
            } else {
                $obj = new \StdClass();
                $obj->userid = $data->userid;
                $obj->data = json_encode($data);
                $obj->timecreated = time();
                $obj->timemodified = time();

                $DB->insert_record('community_social_usr_dtls', $obj);
            }
        }
    }

    public function refreshUser($userid) {
        $this->social_recalculate_in_db($userid);
        $this->recalculate_data_in_cache();
    }

    public static function get_relevant_userid($userid) {
        global $USER;
        if ($userid == null || $userid == 0) {
            $userid = $USER->id;
        }
        return $userid;
    }

    public function build_social_data($userid) {
        global $DB, $CFG;

        // If present in user_preferences.
        if (!$DB->get_records('user_preferences', ['name' => 'community_social_enable', 'userid' => $userid])) {
            return false;
        }

        $data = $DB->get_record("user", ['id' => $userid]);
        if (!$data) {
            return false;
        }

        $data->userid = $data->id;
        $data->uniqueid = $data->id . time();

        $data->fullname = $data->firstname . ' ' . $data->lastname;

        // Active.
        $data->active = get_user_preferences('community_social_enable', 0, $userid);

        // Build user.

        // User image.
        $data->image_url = $CFG->wwwroot . '/user/pix.php/' . $userid . '/f1.jpg';

        //$url = $CFG->wwwroot . '/user/pix.php/' . $userid . '/f1.jpg';
        //$type = pathinfo($url, PATHINFO_EXTENSION);
        //$content = file_get_contents($url);
        //$data->image_url = 'data:image/' . $type . ';base64,' . base64_encode($content);

        // Get school.
        $data->school = '';
        if ($school = $DB->get_record('user_info_field', array('shortname' => 'school'))) {
            if ($res = $DB->get_record('user_info_data', array('fieldid' => $school->id, 'userid' => $userid))) {
                $data->school = $res->data;
            }
        }

        // Get url profile.
        $data->profile_url = (new \moodle_url('/local/community/plugins/social/profile.php', ['id' => $userid]))->out();

        // Link to massage.
        $data->url_to_message = (new \moodle_url('/message/index.php', ['id' => $userid]))->out();;

        // Get colleagues.
        $sql = "
            SELECT 
                lsc.id, 
                lssc.courseid,
                lsc.social_shared_courses_id,
                lsc.userid,
                u.firstname,
                u.lastname
            FROM {community_social_shrd_crss} lssc
            LEFT JOIN {community_social_collegues} lsc ON (lssc.id = lsc.social_shared_courses_id)
            LEFT JOIN {user} u ON (lsc.userid = u.id)
            WHERE lssc.userid = ? AND lsc.approved = 1            
        ";

        $colleagues = $arr = [];
        foreach ($DB->get_records_sql($sql, [$userid]) as $key => $row) {
            if ($this->if_user_active($row->userid)) {
                $arr[$key] = $row;
                $colleagues[] = $row->userid;
            }
        }

        $colleagues = array_unique($colleagues);

        $data->colleagues = $arr;
        $data->colleagues_count = count($colleagues);

        // Get folowers.
        $folowers = [];
        foreach ($DB->get_records('community_social_followers', ['followuserid' => $userid, 'isactive' => 1]) as $row) {
            if ($this->if_user_active($row->userid)) {
                $folowers[] = $row->userid;
            }
        }

        $folowers = array_unique($folowers);

        $data->followers = $folowers;
        $data->followers_count = count($folowers);

        // Get followed.
        $followed = [];
        foreach ($DB->get_records('community_social_followers', ['userid' => $userid, 'isactive' => 1]) as $row) {
            if ($this->if_user_active($row->followuserid)) {
                $followed[] = $row->followuserid;
            }
        }

        $followed = array_unique($followed);

        $data->followed = $followed;
        $data->followed_count = count($followed);

        // Get last access.
        // PTL-2773 Improve performance of SQL WHERE.
        //$sql = "
        //    SELECT *
        //    FROM {logstore_standard_log}
        //    WHERE eventname = '\\community_social\\event\\social_view' AND userid = ?
        //    ORDER BY id DESC
        //";
        //
        //$obj = $DB->get_records_sql($sql, array($userid));
        //$obj = array_values($obj);
        //
        //if (!empty($obj)) {
        //    $data->last_access = $obj[0]->timecreated;
        //}

        // Hack .
        $data->last_access = time();

        // Get user Badges.
        require_once($CFG->dirroot . '/badges/renderer.php');

        if ($badgeissued = $DB->get_records('badge_issued', ['userid' => $userid])) {
            foreach ($badgeissued as $badge) {
                $userbadge = new \core_badges\output\issued_badge($badge->uniquehash);
                $data->badges = ['badge' => [
                        'image' => $userbadge->badgeclass['image'],
                        'name' => $userbadge->badgeclass['name'],
                        'url' => $CFG->wwwroot . '/badges/badge.php?hash=' . $badge->uniquehash
                ]
                ];
            }
        }

        // Get course pombim.
        $rescourses = [];
        foreach ($DB->get_records('community_social_shrd_crss', ['userid' => $userid]) as $item) {

            try {
                $tmp = get_course($item->courseid);
            } catch (\moodle_exception $e) {
                continue;
            }

            $sectionnames = [];
            $format = \course_get_format($item->courseid);
            foreach ($DB->get_records('course_sections', ['course' => $item->courseid]) as $section) {
                if ($section->section != 0) {
                    $sectionnames[] = $format->get_section_name($section->section);
                }
            }

            $tmp->sectionlist = $sectionnames;
            $tmp->summary = is_string($tmp->summary) ? strip_tags($tmp->summary) : '';
            $tmp->imageurl = \community_social\funcs::get_course_image($item->courseid);
            $tmp->ifcopy = $item->ifcopy ? true : false;

            // User.
            $tmp->user_image = $CFG->wwwroot . '/user/pix.php/' . $item->userid . '/f1.jpg';
            $tmp->created_date = date('d.m.Y', $item->timecreated);

            $userpombim = $DB->get_record('user', ['id' => $item->userid]);
            $tmp->user_name = $userpombim->firstname . ' ' . $userpombim->lastname;

            // Course shared by users and count used course.
            $sql = "
                SELECT *
                FROM {community_sharecourse_shr}
                WHERE courseid = ? AND type = 'coursecopy'            
            ";

            $coursesharedusers = [];
            foreach ($DB->get_records_sql($sql, [$item->courseid]) as $obj) {
                $coursesharedusers[] = $obj->useridfrom;
            }

            $tmp->course_shared_users = $coursesharedusers;
            $tmp->course_shared_users_count = count($coursesharedusers);

            $rescourses[] = $tmp;
        }

        $data->courses_pombim = $rescourses;
        $data->count_courses_pombim = count($rescourses);

        // Get courses learn stat.
        $data = $this->get_courses_learn_stat($data, $userid);

        // Peered courses.
        $data->peeredcourses = $this->get_user_shared_courses($userid);

        // Oercatalog activities.
        $activity = new \community_oer\activity_oer;
        $resactivities = $activity->query()->compareArrayField('users', 'userid', $data->userid)->compare('visible', '1')
                ->groupBy('cmid')->groupBy('mod_name');

        $resactivities = $activity->calculate_data_online($resactivities, 'social');
        $resactivities = array_values($resactivities->get());

        $data->oercatalog_activities = $resactivities;
        $data->count_oercatalog_activities = count($resactivities);

        // Users used my oercatalog activity.
        $actids = [];
        foreach ($resactivities as $item2) {
            $actids[] = $item2->cmid;
        }

        if (!empty($actids)) {
            $sql = "SELECT * FROM {community_oer_log} WHERE activityid IN (" . implode(',', $actids) .
                    ") GROUP BY userid";
            $tmp = $DB->get_records_sql($sql);

            $data->users_used_my_oercatalog_count = count($tmp);
        } else {
            $data->users_used_my_oercatalog_count = 0;
        }

        // Prepare oer courses block.
        $course = new \community_oer\course_oer;
        $elements = $course->query()->compareArrayField('users', 'userid', $data->userid)
                ->compare('visible', '1')->groupBy('cid');
        $elements = $course->calculate_data_online($elements, 'social');

        $oercourses = [];
        foreach ($elements->get() as $item2) {
            $oercourses[$item2->cid] = $item2;
        }

        $data->oercatalog_courses = array_values($oercourses);
        $data->oercatalog_courses_count = count($oercourses);

        $data->all_courses_count = $data->oercatalog_courses_count + $data->count_courses_pombim;

        $data->shared_items_count = $data->oercatalog_courses_count + $data->count_oercatalog_activities + $data->count_courses_pombim;

        return $data;
    }

    public function calculate_data_online($obj) {
        global $USER, $DB;

        $cache = get_config('community_social', 'cache_viewed_oercourses');
        $cache = json_decode($cache, true);

        foreach ($obj->data as $key => $item) {
            $obj->data[$key]->editable = $this->if_editable_profile($item->userid);
            $obj->data[$key]->if_followers = $this->if_user_followers($item->userid);
            $obj->data[$key]->if_colleagues = $this->if_user_colleagues($item->userid);

            // TODO Disabled for speed.
            //$obj->data[$key]->if_followed = $this->if_user_followed($item->userid);

            // User_settings_link.
            $obj->data[$key]->user_settings_link = $this->prepare_user_settings_link($item->userid);

            // Calculate courses pombim online.
            $obj->data[$key] = $this->get_courses_pombim_online($obj->data[$key], $item->userid);

            // Calculate courses oer catalog online.
            $oercourses = $item->oercatalog_courses;
            foreach ($oercourses as $key2 => $course) {

                // Get social collegues.
                $course->collegues = [];
                $allsocialusersids = null;
                if (isset($cache[$course->cid])) {
                    foreach ($cache[$course->cid] as $uid) {

                        // Get all social users.
                        if ($allsocialusersids == null) {
                            $sql = "
                                SELECT csu.userid as id, 
                                       csu.userid, 
                                       u.firstname, 
                                       u.lastname        
                                FROM {community_social_usr_dtls} csu
                                LEFT JOIN {user} u ON (csu.userid = u.id)
                            ";
                            $allsocialusersids = $DB->get_records_sql($sql);
                        }

                        if (isset($allsocialusersids[$uid]) && $USER->id != $uid) {
                            $f = new \StdClass();
                            $f->firstname = $allsocialusersids[$uid]->firstname;
                            $f->lastname = $allsocialusersids[$uid]->lastname;

                            $course->collegues[] = $f;
                        }
                    }
                }

                $course->social_collegues_enable = !empty($course->collegues) ? true : false;

                $oercourses[$key2] = $course;
            }

            $obj->data[$key]->oercatalog_courses = $oercourses;
        }

        return $obj;
    }

    public function get_data_from_cache() {
        if (($datacache = $this->cache->get($this->key)) === false) {
            $datacache = [];
        }

        return $datacache;
    }

    // Query in cache.
    public function query($data = -1) {
        if ($data == -1) {
            $data = $this->get_data_from_cache();
        }

        $query = new \community_oer\object_query($data, 'uniqueid');
        return $query;
    }

    public function getSingleDataUser($userid) {
        global $DB;

        if (!$raw = $DB->get_record('community_social_usr_dtls', ['userid' => $userid])) {
            $this->refreshUser($userid);
            $raw = $DB->get_record('community_social_usr_dtls', ['userid' => $userid]);
        }

        $obj = $this->query([json_decode($raw->data)])->compare('userid', $userid)->compare('active', '1');
        $obj = $this->calculate_data_online($obj);
        $data = $obj->get();
        $data = reset($data);

        return $data;
    }

    public function data_list_teachers($data, $numtab = 0, $search = '') {

        $tabs = $this->get_tabs($search);

        // Set active tab.
        foreach ($tabs as $key => $item) {
            $tabs[$key]['tab_active'] = ($item['tab_id'] == $numtab) ? 'active' : '';
        }

        // Default tabid if wrong tabid.
        if (!isset($tabs[$numtab])) {
            $numtab = 0;
        }

        // Tabs.
        $data->teachers_tabs = $tabs;

        $currenttab = $tabs[$numtab];

        // Get first {maxuserspage} users.
        if ($this->maxuserspage) {
            $count = 0;
            $tmp = [];
            foreach ($currenttab['tab_data'] as $item) {
                if ($count < $this->maxuserspage) {
                    $tmp[] = $item;
                }

                $count++;
            }

            $currenttab['tab_data'] = $tmp;
        }

        $data->teachers_current_tab = $currenttab;
        $data->teachers_tab_date_empty = count($currenttab['tab_data']) > 0 ? false : true;
        $data->load_users_onpage = $this->maxuserspage;

        return $data;
    }

    public function data_lazy_loading($numtab, $search, $loadedcards) {

        if (!$this->maxuserspage) {
            return [];
        }

        $tabs = $this->get_tabs($search);
        $currenttab = $tabs[$numtab];

        // Get range users.
        $data = [];
        foreach ($currenttab['tab_data'] as $key => $item) {
            if ($key >= $loadedcards && $key < ($loadedcards + $this->maxuserspage)) {
                $data[] = $item;
            }
        }

        return $data;
    }

    public function if_user_followers($userid, $foruser = null) {
        global $DB, $USER;

        if ($foruser == null) {
            $foruser = $USER->id;
        }

        if ($userid == $foruser) {
            return false;
        }

        $dataforuser = null;
        if ($raw = $DB->get_record('community_social_usr_dtls', ['userid' => $userid])) {
            $obj = $this->query([json_decode($raw->data)])->compare('userid', $userid)->inArray('followers', $foruser)
                    ->compare('active', '1')->get();
            $dataforuser = reset($obj);
        }

        if (!empty($dataforuser)) {
            return true;
        }

        return false;

        //return $DB->count_records('community_social_followers',
        //        ['userid' => $foruser, 'followuserid' => $userid, 'isactive' => 1]);
    }

    public function if_user_followed($userid, $foruser = null) {
        global $DB, $USER;

        if ($foruser == null) {
            $foruser = $USER->id;
        }

        if ($userid == $foruser) {
            return false;
        }

        $dataforuser = null;
        if ($raw = $DB->get_record('community_social_usr_dtls', ['userid' => $userid])) {
            $obj = $this->query([json_decode($raw->data)])->compare('userid', $userid)->inArray('followed', $foruser)
                    ->compare('active', '1')->get();
            $dataforuser = reset($obj);
        }

        if (!empty($dataforuser)) {
            return true;
        }

        return false;

        //return $DB->count_records('community_social_followers',
        //        array('userid' => $userid, 'followuserid' => $foruser, 'isactive' => 1));
    }

    public function if_user_colleagues($userid, $foruser = null) {
        global $DB, $USER;

        if ($foruser == null) {
            $foruser = $USER->id;
        }

        if ($userid == $foruser) {
            return false;
        }

        // Normal state.
        $dataforuser = null;
        if ($raw = $DB->get_record('community_social_usr_dtls', ['userid' => $foruser])) {
            $obj = $this->query([json_decode($raw->data)])->compare('userid', $foruser)->compare('active', '1')->get();
            $dataforuser = reset($obj);
        }

        if (!empty($dataforuser)) {
            foreach ($dataforuser->colleagues as $item) {
                if ($item->userid == $userid) {
                    return true;
                }
            }
        }

        // Revert state.
        $tmp = $foruser;
        $foruser = $userid;
        $userid = $tmp;

        $dataforuser = null;
        if ($raw = $DB->get_record('community_social_usr_dtls', ['userid' => $foruser])) {
            $obj = $this->query([json_decode($raw->data)])->compare('userid', $foruser)->compare('active', '1')->get();
            $dataforuser = reset($obj);
        }

        if (!empty($dataforuser)) {
            foreach ($dataforuser->colleagues as $item) {
                if ($item->userid == $userid) {
                    //echo '<pre>';print_r($dataforuser);exit;
                    return true;
                }
            }
        }

        //$sql = "SELECT lsc.userid
        //        FROM {community_social_shrd_crss} lssc
        //        LEFT JOIN {community_social_collegues} lsc ON(lssc.id=lsc.social_shared_courses_id)
        //        WHERE lssc.userid=? AND lsc.userid=? AND lsc.approved=1
        //        GROUP BY lsc.userid";
        //
        //$tmp = $DB->get_records_sql($sql, array($foruser, $userid));
        //
        //if (count($tmp) > 0) {
        //    return true;
        //}

        return false;
    }

    public function if_editable_profile($userid) {
        global $USER;

        if ($userid && $userid == $USER->id) {
            return true;
        }
        return false;
    }

    private function prepare_user_settings_link($userid) {
        global $USER;

        // Check if admin.
        $admins = get_admins();
        $isadmin = false;

        foreach ($admins as $admin) {
            if ($USER->id == $admin->id) {
                $isadmin = true;
                break;
            }
        }

        if ($isadmin) {
            return new \moodle_url('/user/profile.php', ['id' => $userid]);
        }

        return false;
    }

    private function get_courses_pombim_online($obj, $userid) {
        global $USER;

        $rescourses = [];
        $countcollegues = 0;
        foreach ($obj->courses_pombim as $item) {

            $collegues = $this->get_course_collegues($obj, $userid, $item->id);
            $countcollegues += $collegues['collegues_counter'];

            $item->collegues = $collegues['collegues'];
            $item->if_can_see_url = $collegues['if_can_see_url'];

            if ($userid == $USER->id) {
                $url = new \moodle_url('/course/view.php', ['id' => $item->id]);
                $item->courseurl = $url->out();
            } else {
                $url = new \moodle_url('/local/community/plugins/social/enrol_course_collegues.php', [
                        'id' => $item->id,
                        'userid' => $userid
                ]);
                $item->courseurl = $url->out();
            }

            $status = $this->get_status_request($userid, $item->id);

            switch ($status) {
                case '0':
                    $item->button_send_request = false;
                    $item->button_wait_for_answer = true;
                    $item->button_request_decline = false;
                    break;
                case '2':
                    $item->button_send_request = false;
                    $item->button_wait_for_answer = false;
                    $item->button_request_decline = true;
                    break;
                case 'empty':
                    $item->button_send_request = true;
                    $item->button_wait_for_answer = false;
                    $item->button_request_decline = false;
                    break;
                default:
                    $item->button_send_request = false;
                    $item->button_wait_for_answer = false;
                    $item->button_request_decline = false;
            }

            // PTL-8544.
            $item->button_send_request = false;

            $item->course_shared = in_array($USER->id, $item->course_shared_users);

            $rescourses[] = $item;
        }

        $buttoncoursespombimenable = !$this->if_editable_profile($userid) && !empty($rescourses) ? true : false;

        $obj->courses_pombim = $rescourses;
        $obj->count_courses_pombim = count($rescourses);
        $obj->count_collegues = $countcollegues;
        $obj->firstname_pombim = $obj->firstname;
        $obj->button_courses_pombim_enable = $buttoncoursespombimenable;

        return $obj;
    }

    private function get_courses_learn_stat($obj, $userid) {
        global $DB, $CFG;

        // Get activities by user.
        $sql = "
            SELECT ol.activityid, 
                   ol.courseid, 
                   cm.module, 
                   m.name AS mod_type, 
                   cm.instance, 
                   ol.timemodified, 
                   cm.section, 
                   cs.name AS section_name, 
                   cs.section AS num_section
            FROM {community_oer_log} ol
            LEFT JOIN {course_modules} cm ON(ol.activityid=cm.id)
            LEFT JOIN {modules} m ON(cm.module=m.id)
            LEFT JOIN {course_sections} cs ON(cm.section=cs.id)
            WHERE ol.userid=?
            GROUP BY cm.section
            ORDER BY ol.timemodified DESC
            LIMIT 5
        ";
        $activities = $DB->get_records_sql($sql, array($userid));

        foreach ($activities as $item) {
            $urltoactivity = '';
            if (!empty($item->mod_type)) {
                $sql = "
                    SELECT *
                    FROM {" . $item->mod_type . "}
                    WHERE id=?
                ";
                $activity = $DB->get_record_sql($sql, [$item->instance]);
                $item->activity_name = $activity->name;

                // Prepare url to activity.
                switch ($item->mod_type) {
                    case "quiz":
                        $urltoactivity = $CFG->wwwroot . '/mod/' . $item->mod_type . '/startattempt.php?cmid=' . $item->activityid .
                                '&sesskey=' . sesskey();
                        break;
                    case "questionnaire":
                        $urltoactivity = $CFG->wwwroot . '/mod/' . $item->mod_type . '/preview.php?id=' . $item->activityid;
                        break;
                    default:
                        $urltoactivity = $CFG->wwwroot . '/mod/' . $item->mod_type . '/view.php?id=' . $item->activityid;

                }
            }
            $item->activity_url = $urltoactivity;

            // Prepare sections name.
            if (empty($item->section_name) && $item->num_section != 0) {
                $item->section_name = get_string('nameemptysection', 'community_social') . ' ' . $item->num_section;
            }
        }

        $activities = array_values($activities);

        $obj->activities_learn_stat = $activities;
        $obj->count_learn_stat = count($activities);

        return $obj;
    }

    private function get_status_request($userid, $courseid) {
        global $DB, $USER;

        $sharedcourse = $DB->get_record('community_social_shrd_crss', array('userid' => $userid, 'courseid' => $courseid));
        if (!empty($sharedcourse)) {

            $sql = "
                SELECT *
                FROM (SELECT *
                FROM {community_social_requests}
                ORDER BY id DESC) AS fg
                WHERE usersendid=? AND userid=? AND social_shared_courses_ids=?
                GROUP BY social_shared_courses_ids
            ";

            if ($request = $DB->get_record_sql($sql, array($USER->id, $userid, $sharedcourse->id))) {

                if ($request->status == 1) {
                    $collegue = $DB->get_record('community_social_collegues',
                            array('userid' => $USER->id, 'social_shared_courses_id' => $sharedcourse->id));
                    if ($collegue->approved == 0) {
                        return 'empty';
                    }
                }

                return $request->status;
            } else {
                return 'empty';
            }
        }

        return 'error';
    }

    private function get_course_collegues($obj, $userid, $courseid) {
        global $USER;

        $collegues = [];
        foreach ($obj->colleagues as $item) {
            if ($item->courseid == $courseid) {
                $collegues[] = $item;
            }
        }

        $result = [];
        $collegues = array_values($collegues);

        // Add flag if can see url to course.
        $flagseeurlincourse = $this->if_editable_profile($userid) ? true : false;

        // Add flag if can delete.
        foreach ($collegues as $item) {
            if ($this->if_editable_profile($userid)) {
                $item->if_can_delete = true;
                $flagseeurlincourse = true;
            } else {
                if ($item->userid == $USER->id) {
                    $item->if_can_delete = true;
                    $flagseeurlincourse = true;
                } else {
                    $item->if_can_delete = false;
                }
            }
        }
        if (is_siteadmin()) {
            $flagseeurlincourse = true;
        }

        // PTL-8544.
        $flagseeurlincourse = true;

        $result['collegues'] = $collegues;
        $result['collegues_counter'] = count($collegues);
        $result['if_can_see_url'] = $flagseeurlincourse;

        return $result;
    }

    private function get_user_shared_courses($userid) {
        global $DB, $USER;

        $courses = [];

        $sql = "
            SELECT lssc.courseid
            FROM {community_social_shrd_crss} lssc
            LEFT JOIN {community_social_collegues} lsc ON (lssc.id = lsc.social_shared_courses_id)
            INNER JOIN {course} cc ON (cc.id=lssc.courseid)
            WHERE lsc.userid = ? AND lssc.userid = ? AND lsc.approved = 1
        ";
        $sharedcourses = $DB->get_records_sql($sql, array($USER->id, $userid));

        foreach ($sharedcourses as $id => $c) {
            $courses[$id] = get_course($id);
            $courses[$id]->courseimage = \community_social\funcs::get_course_image($id);
        }

        $courses = array_values($courses);

        return $courses;
    }

    private function if_user_active($userid) {
        global $DB;

        if ($DB->get_record("user", ['id' => $userid]) && get_user_preferences('community_social_enable', 0, $userid) == 1) {
            return true;
        }

        return false;
    }

    private function get_tabs($search = '') {
        global $USER;

        $obj = $this->query()->compare('active', '1')->notIn('userid', $USER->id);

        $search = trim($search);

        if (!empty($search)) {
            $obj = $this->query($obj->get());
            $obj = $obj->like('firstname', $search)->orLike('lastname', $search)->orLike('fullname', $search);
        }

        $obj = $obj->multiOrder([
                'colleagues_count' => 'desc',
                'followers_count' => 'desc',
                'shared_items_count' => 'desc'
        ]);

        $relevantusers = [];
        foreach ($this->calculate_data_online($obj)->get() as $item) {
            $item->type_card_normal = false;
            $item->type_card_empty = false;
            $item->type_card_collegue = false;

            $relevantusers[] = $item;
        }

        // All teachers.
        $dataallteachers = [];
        foreach ($relevantusers as $item) {
            $obj = $item;
            if ($obj->colleagues_count > 0 || $obj->followers_count > 0 || $obj->shared_items_count > 0) {
                $obj->type_card_normal = true;
            } else {
                $obj->type_card_empty = true;
            }

            $dataallteachers[] = $obj;
        }

        // Colleague teachers.
        $datateacherswhereyoucollegue = [];
        foreach ($relevantusers as $item) {
            $obj = $item;
            if ($obj->if_colleagues) {
                if ($obj->colleagues_count > 0 || $obj->followers_count > 0 || $obj->shared_items_count > 0) {
                    $obj->type_card_normal = true;
                    //$item->type_card_collegue = true;
                } else {
                    $obj->type_card_empty = true;
                }

                $datateacherswhereyoucollegue[] = $obj;
            }
        }

        // Follow teachers.
        $datateachersfollowers = [];
        foreach ($relevantusers as $item) {
            $obj = $item;
            if ($obj->if_followers) {
                if ($obj->colleagues_count > 0 || $obj->followers_count > 0 || $obj->shared_items_count > 0) {
                    $obj->type_card_normal = true;
                } else {
                    $obj->type_card_empty = true;
                }

                $datateachersfollowers[] = $obj;
            }
        }

        $tabs = array(
                0 => array(
                        'tab_id' => 0,
                        'tab_name' => get_string('allteachers', 'community_social'),
                        'tab_data' => $dataallteachers,
                        'tab_count' => count($dataallteachers),
                        'tab_count_active' => false,
                        'tab_active' => '',
                        'tab_search' => $search
                ),
                1 => array(
                        'tab_id' => 1,
                        'tab_name' => get_string('colleagueteacherstab', 'community_social'),
                        'tab_data' => $datateacherswhereyoucollegue,
                        'tab_count' => count($datateacherswhereyoucollegue),
                        'tab_count_active' => true,
                        'tab_active' => '',
                        'tab_search' => $search
                ),
                2 => array(
                        'tab_id' => 2,
                        'tab_name' => get_string('followteachers', 'community_social'),
                        'tab_data' => $datateachersfollowers,
                        'tab_count' => count($datateachersfollowers),
                        'tab_count_active' => true,
                        'tab_active' => '',
                        'tab_search' => $search
                ),
        );

        // Prepare cohorts tabs.
        $counttabs = count($tabs);
        $cohorts = \community_social\funcs::get_cohorts_by_user();
        $cohorts = \community_social\funcs::cohorts_via_settings($cohorts);

        foreach ($cohorts as $cohort) {
            $res = [];
            foreach ($relevantusers as $item) {
                $obj = $item;
                if (\community_social\funcs::has_permission($obj->userid) &&
                        \community_social\funcs::if_user_in_cohort($cohort->id, $obj->userid)) {

                    if ($obj->colleagues_count > 0 || $obj->followers_count > 0 || $obj->shared_items_count > 0) {
                        $obj->type_card_normal = true;
                    } else {
                        $obj->type_card_empty = true;
                    }

                    $res[] = $obj;
                }
            }

            $tabs[$counttabs] = array(
                    'tab_id' => $counttabs,
                    'tab_name' => $cohort->name,
                    'tab_data' => $res,
                    'tab_count' => count($res),
                    'tab_count_active' => true,
                    'tab_active' => '',
                    'tab_search' => $search
            );
            $counttabs++;
        }


        return $tabs;
    }
}
