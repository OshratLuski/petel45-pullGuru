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
 * @package    community_oer
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace community_oer;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/format/lib.php');

class activity_oer {

    public $cache;
    public $key;
    public $revision;

    public function __construct() {
        global $DB;

        $this->cache = \cache::make_from_params(\cache_store::MODE_APPLICATION, 'oer_activity_cache', 'activities');
        $this->revision = \community_oer\main_oer::get_oercacheversion();
        $this->key = 'data' . $this->revision;

        $count = $DB->count_records('community_oer_activity');
        if ($count > count($this->get_activities_from_cache())) {
            $this->recalculate_data_in_cache();
        }
    }

    public function structure_cache() {
        return \cache::make_from_params(\cache_store::MODE_APPLICATION, 'oer_cache', 'activity_structure');
    }

    public function structure_activity_catalog() {
        $cache = $this->structure_cache();
        $key = 'structure' . $this->revision;

        if (($result = $cache->get($key)) === false) {
            $result = \community_oer\main_oer::structure_main_catalog();
            $cache->set($key, $result);
            return $result;
        } else {
            return $result;
        }
    }

    public function get_courses_and_sections_for_current_user() {
        global $DB, $USER;

        $mycourses = enrol_get_my_courses('*', 'id DESC');
        foreach ($mycourses as $key => $item) {
            $mycourses[$key]->lastaccess = 0;
        }

        // Sort courses by last access of current user.
        $lastaccesscourses = $DB->get_records('user_lastaccess', array('userid' => $USER->id), 'timeaccess DESC');
        foreach ($lastaccesscourses as $c) {
            if (isset($mycourses[$c->courseid])) {
                $mycourses[$c->courseid]->lastaccess = $c->timeaccess;
            }
        }
        // Sort by user's lastaccess to course.
        usort($mycourses, function($a, $b) {
            return $b->lastaccess - $a->lastaccess;
        });

        $structure = $this->structure_activity_catalog();

        foreach ($structure as $item) {
            foreach ($item['courses'] as $course) {
                $coursesmaagar[] = $course->id;
            }
        }

        $result = array();
        $rolespermitted = array('editingteacher', 'juniorteacher');
        foreach ($mycourses as $course) {

            $context = \context_course::instance($course->id);
            $roles = get_user_roles($context, $USER->id, true);
            $flagpermission = false;
            foreach ($roles as $role) {
                if (in_array($role->shortname, $rolespermitted)) {
                    $flagpermission = true;
                }
            }

            if (!in_array($course->id, $coursesmaagar) && $flagpermission) {
                $tmpcourse = new \StdClass;
                $tmpcourse->courseid = $course->id;
                $tmpcourse->coursename = $course->fullname;

                $sql = "SELECT cs.*
                        FROM {course} c
                        LEFT JOIN {course_sections} cs ON c.id=cs.course
                        WHERE cs.section!=0 AND c.id=?";
                $sections = $DB->get_records_sql($sql, array($course->id));
                $sections = array_values($sections);

                $tmpsections = [];
                foreach ($sections as $section) {
                    $sectionname = course_get_format($course)->get_section_name($section->section);
                    $tmpsections[] = ['sectionid' => $section->id, 'sectionname' => $sectionname];
                }

                if (!empty($tmpsections)) {
                    $tmpcourse->sections = $tmpsections;
                    $result['courses'][] = $tmpcourse;
                }
            }
        }

        return $result;
    }

    private function build_activity_data($cmid) {
        global $DB, $CFG, $OUTPUT;

        $query = "
            SELECT 
                cm.id AS cmid,
                cm.course AS courseid,
                c.category AS catid,
                cm.instance AS instance,
                cm.added AS cm_created,  	
                cm.visible AS visible,
                cm.visibleold AS visibleold,
                cm.visibleoncoursepage AS visibleoncoursepage,
                cm.module AS modid,
                m.name AS mod_type,
                
                cs.id AS sectionid,  
                cs.name AS section_name,  
                cs.summary AS section_summary,  
                cs.sequence AS section_sequence,  
                cs.section AS section_order,
                
                #GROUP_CONCAT(DISTINCT t.rawname SEPARATOR ',') AS tags,
                GROUP_CONCAT(DISTINCT compm.competencyid SEPARATOR ',') AS compmcompetencyids,
                GROUP_CONCAT(DISTINCT compq.competencyid SEPARATOR ',') AS compqcompetencyids            
                
            FROM {course_modules} cm
            LEFT JOIN {course} c ON(cm.course = c.id)
            LEFT JOIN {modules} m ON(cm.module = m.id)
            LEFT JOIN {course_sections} cs ON cm.section = cs.id
            LEFT JOIN {tag_instance} ti ON (ti.itemid = cm.id)
            LEFT JOIN {tag} t ON (ti.tagid = t.id)
                
            LEFT JOIN {competency_modulecomp} compm ON (cm.id = compm.cmid)
            LEFT JOIN {quiz_slots} quizslots ON cm.instance = (quizslots.quizid)
            LEFT JOIN {question_references} qr ON qr.itemid = quizslots.id
            LEFT JOIN {competency_questioncomp} compq ON (qr.questionbankentryid = compq.qid)            
            
            WHERE cm.deletioninprogress != 1 AND cm.id = ?;
        ";

        $obj = $DB->get_record_sql($query, [$cmid]);
        if (empty($obj->cmid) || !($obj->instance > 0)) {
            return [];
        }

        // Mod details.
        $tmp = $DB->get_record($obj->mod_type, ['id' => $obj->instance]);
        $obj->mod_name = isset($tmp->name) ? $tmp->name : '';
        $obj->mod_intro = isset($tmp->intro) ? $tmp->intro : '';

        // Forum.
        if ($obj->mod_type == 'forum' && $tmp->type == 'news') {
            return [];
        }

        // Build tags.
        $tags = [];
        $context = \context_module::instance($obj->cmid);
        foreach (\core_tag_tag::get_item_tags('core', 'course_modules', $obj->cmid) as $tag) {
            if ($tag->taginstancecontextid == $context->id) {
                $tags[] = trim($tag->name);
            }
        }

        $obj->tags = $tags;

        // Check tag "hide_from_oer_catalog".
        if (in_array('hide_from_oer_catalog', $tags)) {
            return [];
        }

        // Change visible by visibleoncoursepage.
        if (!$obj->visibleoncoursepage) {
            $obj->visible = 0;
        }

        // Metadata details.
        $category = $DB->get_record('local_metadata_category', ['contextlevel' => CONTEXT_MODULE], '*', IGNORE_MULTIPLE);

        if (!empty($category)) {
            $query = "
                SELECT id, shortname
                FROM {local_metadata_field}
                WHERE contextlevel = ? AND categoryid = ?
                ORDER BY sortorder ASC
            ";

            $fields = $DB->get_records_sql($query, [CONTEXT_MODULE, $category->id]);
            foreach ($fields as $field) {
                $name = 'metadata_' . $field->shortname;
                $obj->$name = \local_metadata\mcontext::module()->get($cmid, $field->shortname);
            }
        }

        // Activity image.
        $metadataimageactivity = isset($obj->metadata_imageactivity) ? $obj->metadata_imageactivity : '';
        $context = \context_module::instance($obj->cmid);
        $obj->imageactivity = \community_oer\main_oer::create_url_image($metadataimageactivity, $context, $obj->courseid);

        // User details.
        $obj->users = [];
        $username = '';
        if (isset($obj->metadata_userid) && !empty($obj->metadata_userid)) {
            foreach (explode(',', $obj->metadata_userid) as $userid) {
                if ($user = $DB->get_record('user', ['id' => trim($userid)])) {
                    $tmp = new \StdClass();
                    $tmp->userid = $user->id;
                    $tmp->user_fname = $user->firstname;
                    $tmp->user_lname = $user->lastname;
                    $tmp->user_image = $CFG->wwwroot . '/user/pix.php/' . $user->id . '/f1.jpg';

                    $obj->users[] = $tmp;
                    $username .= ' ' . implode(' ', [$tmp->user_fname, $tmp->user_lname]);
                }
            }
        } else {
            // Default user.
            $tmp = new \StdClass();
            $tmp->userid = 0;
            $tmp->user_fname = get_string('defaultuser', 'community_oer');
            $tmp->user_lname = '';
            $tmp->user_image = $OUTPUT->image_url('avatar', 'community_oer')->out(false);

            $obj->users[] = $tmp;
            $username .= implode(' ', [$tmp->user_fname, $tmp->user_lname]);
        }

        $obj->username = $username;

        // Activity url.
        $mid = \local_metadata\mcontext::module()->get($obj->cmid, 'ID');
        $obj->urlactivity = $CFG->wwwroot . '/local/community/plugins/oer/activityshare.php?id=' . $mid;

        // Format date create.
        $obj->cm_created_format = date('d.m.Y', $obj->cm_created);

        $obj->cm_updated = $obj->cm_created;
        $obj->cm_updated_format = $obj->cm_created_format;
        if (isset($obj->metadata_version) && !empty($obj->metadata_version)) {

            $dtime = \DateTime::createFromFormat("YmdHi", $obj->metadata_version);

            if ($dtime instanceof \DateTime) {
                $timestamp = $dtime->getTimestamp();
                $obj->cm_updated = $timestamp;
                $obj->cm_updated_format = date('d.m.Y', $timestamp);
            }
        }

        // Format info metadata.
        $metadatadurationactivity = isset($obj->metadata_durationactivity) ? $obj->metadata_durationactivity : '';
        $metadatalevelactivity = isset($obj->metadata_levelactivity) ? $obj->metadata_levelactivity : '';
        $arr = array_filter([
                self::funcs()::get_metadata_value(trim($metadatadurationactivity)),
                self::funcs()::get_metadata_value(trim($metadatalevelactivity))
        ]);
        $obj->info_metadata = count($arr) ? '(' . implode(', ', $arr) . ')' : '';

        // Activity shared by users and count used activity.
        $sql = "
                SELECT id, userid, IF(id > 0, 1, NULL) AS activity_shared
                FROM {community_oer_log}
                WHERE activityid = ?
                GROUP BY activityid, userid
            ";

        $activitysharedusers = [];
        foreach ($DB->get_records_sql($sql, [$cmid]) as $item) {
            $activitysharedusers[] = $item->userid;
        }

        $obj->activity_shared_users = json_encode($activitysharedusers);

        // PTL-7663.
        $usage = self::funcs()::usage_calculation($obj->mod_type, $obj->metadata_ID);
        $obj->count_student_response = $usage->responses;
        $obj->count_used_activity = $usage->uniqueteacher;

        // Default data for calculate online.
        $obj->count_comments = 0;
        $obj->url_comments = '';

        // Create order.
        $counter = 1;
        foreach (explode(',', $obj->section_sequence) as $cmid) {
            if ($obj->cmid == $cmid) {
                break;
            }

            $counter++;
        }

        $obj->order = $obj->courseid . $obj->section_order . $counter;

        // Default uniqueid.
        $obj->uniqueid = time() . $obj->cmid . $obj->courseid . $obj->catid;

        // Virtual sections.
        $linksectionids = \local_metadata\mcontext::module()->get($obj->cmid, 'linksectionids');

        // Prepare array sections.
        $arrsections = [$obj->sectionid];
        foreach (explode(',', $linksectionids) as $psectionid) {
            $arrsections[] = trim($psectionid);
        }

        $arrsections = array_unique($arrsections);
        $arrsections = array_filter($arrsections);

        $res = [];
        foreach ($arrsections as $psectionid) {

            $sectionobj = clone $obj;

            if ($psection = $DB->get_record('course_sections', ['id' => $psectionid])) {
                $pcourse = $DB->get_record('course', ['id' => $psection->course]);

                $sectionobj->courseid = $pcourse->id;
                $sectionobj->catid = $pcourse->category;
                $sectionobj->sectionid = $psection->id;
                $sectionobj->section_name = $psection->name;
                $sectionobj->section_summary = $psection->summary;
                $sectionobj->section_sequence = $psection->sequence;
                $sectionobj->section_order = $psection->section;

                $sectionobj->uniqueid =
                        time() . $sectionobj->cmid . $sectionobj->courseid . $sectionobj->catid . $sectionobj->sectionid;

                // Create order.
                $counter = 1;
                foreach (explode(',', $sectionobj->section_sequence) as $cmid) {
                    if ($sectionobj->cmid == $cmid) {
                        break;
                    }

                    $counter++;
                }

                $sectionobj->order = $sectionobj->courseid . $sectionobj->section_order . $counter;
            }

            $res[] = $sectionobj;
        }

        return $res;
    }

    public function activity_recalculate_in_db($cmid) {
        global $DB;

        $cm = $DB->get_record('course_modules', ['id' => $cmid]);
        if (!empty($cm)) {
            $section = $DB->get_record('course_sections', ['id' => $cm->section]);
            if (!empty($section)) {
                $modinfo = get_fast_modinfo($cm->course);
                $current = $modinfo->get_section_info($section->section)->getIterator()->getArrayCopy();

                if (isset($current['parent']) && $current['parent'] != 0) {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }

        $DB->delete_records('community_oer_activity', ['cmid' => $cmid]);

        foreach ($this->build_activity_data($cmid) as $data) {
            $obj = new \StdClass();
            $obj->uniqueid = $data->uniqueid;
            $obj->cmid = $data->cmid;
            $obj->catid = $data->catid;
            $obj->courseid = $data->courseid;
            $obj->sectionid = $data->sectionid;
            $obj->recache = 0;
            $obj->data = json_encode($data);
            $obj->timecreated = time();
            $obj->timemodified = time();

            $DB->insert_record('community_oer_activity', $obj);
        }

        return true;
    }

    public function recalculate_all_activities_in_db_cache() {
        global $DB;

        raise_memory_limit(MEMORY_UNLIMITED);

        $DB->execute("TRUNCATE TABLE {community_oer_activity}");

        foreach ($this->structure_activity_catalog() as $item) {
            foreach ($item['courses'] as $course) {
                $activities = $DB->get_records('course_modules', ['course' => $course->id]);
                foreach ($activities as $cm) {
                    $this->activity_recalculate_in_db($cm->id);
                }
            }
        }

        $this->recalculate_data_in_cache();

        return true;
    }

    public function build_standart_info_for_page() {
        $result = [];

        $result['dashboard'] = 'activity';

        // Side panel menu.
        $result['aside_menu'] = self::funcs()::build_menu();

        // Filters.
        $result['filters'] = self::funcs()::build_filters();

        // Sorting.
        $sorting = \community_oer\activity_help::sorting_elements();

        // Set default.
        $default = get_config('community_oer', 'default_sort_activity');
        foreach ($sorting as $key => $item) {
            $sorting[$key]['selected'] = ($item['value'] == $default) ? true : false;
        }
        $result['sorting'] = $sorting;

        return $result;
    }

    public function calculate_data_online($obj, $referer) {
        global $DB, $USER, $OUTPUT, $CFG;

        foreach ($obj->data as $key => $item) {

            // Activity shared for user.
            $activitysharedusers = json_decode($item->activity_shared_users);
            $obj->data[$key]->activity_shared = in_array($USER->id, $activitysharedusers) ? true : false;

            $obj->data[$key]->referer = $referer;

            // Activity mod name.
            $obj->data[$key]->modname = self::funcs()::get_mod_name($item->mod_type);

            // Activity mod icon.
            $obj->data[$key]->modicon = $OUTPUT->pix_icon('icon', '', $item->mod_type, array('class' => 'oer-icon-modtype'));

            // Url to social profile.
            foreach ($obj->data[$key]->users as $key2 => $item2) {
                $urltosocialprofile = "javascript:void(0)";
                $urltosocialtarget = false;
                if (isset($item2->userid) && get_user_preferences('community_social_enable', '', $item2->userid)) {
                    $urltosocialprofile = new \moodle_url('/local/community/plugins/social/profile.php', ['id' => $item2->userid]);
                    $urltosocialprofile = $urltosocialprofile->out();
                    $urltosocialtarget = true;
                }
                $obj->data[$key]->users[$key2]->urltosocialprofile = $urltosocialprofile;
                $obj->data[$key]->users[$key2]->urltosocialtarget = $urltosocialtarget;
            }

            // Mid activity url.
            if (isset($item->metadata_ID) && !empty($item->metadata_ID) && is_number($item->metadata_ID) &&
                    $DB->get_record('course_modules', ['id' => $item->metadata_ID])) {

                $obj->data[$key]->urlactivitymid =
                        $CFG->wwwroot . '/local/community/plugins/oer/activityshare.php?id=' . $item->metadata_ID;
            } else {
                $obj->data[$key]->urlactivitymid = $obj->data[$key]->urlactivity;
            }

            // Translate mid url.
            $obj->data[$key]->translatedenable = false;
            $obj->data[$key]->urltranslateduser = false;

            // Viewing hebrew + link basedon.
            if (isset($item->metadata_translatemid) && !empty($item->metadata_translatemid) &&
                    is_number($item->metadata_translatemid) &&
                    $DB->get_record('course_modules', ['id' => $item->metadata_translatemid])) {

                $obj->data[$key]->translatedenable = true;
                $obj->data[$key]->translatedlabel = get_string('translationbasedon', 'community_oer');

                $obj->data[$key]->urltranslatemid =
                        $CFG->wwwroot . '/local/community/plugins/oer/activityshare.php?id=' . $item->metadata_translatemid;
            } else {
                // Viewing arabic + link to hebrew + user translated by.
                if (isset($item->metadata_sourcemid) && !empty($item->metadata_sourcemid) &&
                        is_number($item->metadata_sourcemid) &&
                        $DB->get_record('course_modules', ['id' => $item->metadata_sourcemid])) {

                    $obj->data[$key]->translatedenable = true;
                    $obj->data[$key]->translatedlabel = get_string('originalactivity', 'community_oer');

                    $obj->data[$key]->urltranslatemid =
                            $CFG->wwwroot . '/local/community/plugins/oer/activityshare.php?id=' . $item->metadata_sourcemid;

                    $translatedby = trim($item->metadata_translatedby);
                    if (!empty($translatedby)) {
                        $arrtmp = [];
                        foreach (explode(',', $translatedby) as $userid) {
                            if ($row = $DB->get_record('user', ['id' => trim($userid)])) {

                                $userlink = new \moodle_url('/user/profile.php?id=', ['id' => $row->id]);
                                $username = $row->firstname . ' ' . $row->lastname;

                                $arrtmp[] = \html_writer::tag('a', $username, [
                                        'href' => $userlink->out(false),
                                        'target' => '_blank',
                                        'class' => 'urltranslate mb-1'
                                ]);
                            }
                        }

                        $obj->data[$key]->translatedeusershtml = implode(', ', $arrtmp);
                        $obj->data[$key]->translatedeusersenable = !empty($arrtmp) ? true : false;
                    }
                }
            }

            $obj->data[$key]->showteachercomments = false;

            // Count reviews to activity and activity rating.
            $reviews = $DB->get_records('community_oer_reviews', array('objid' => $item->cmid, 'reviewtype' => 'activity'));
            $obj->data[$key]->countreview = count($reviews);

            $comments = $DB->get_records('comments', array('contextid' => $item->cmid));
            $obj->data[$key]->countcomments = count($comments);

            $obj->data[$key]->activityrating = false;
            $rating1 = false;
            $rating2 = false;

            $minreviewcount = get_config('community_oer', 'minreviewcount');

            if (count($reviews) > 2) {
                $rate = array();
                $rate['pos'] = array_filter((array) $reviews, function($a) {
                    return ($a->recommendation == 5 || $a->recommendation == 4);
                });
                $rate['neg'] = array_filter((array) $reviews, function($a) {
                    return ($a->recommendation == 1 || $a->recommendation == 2);
                });
                if (count($rate['pos']) >= 1.2 * count($rate['neg'])) {
                    $rating1 = true;
                }

                if ($minreviewcount > 0 && count($rate['pos']) < $minreviewcount) {
                    $rating1 = false;
                }
            }

            if (count($reviews) > 2) {
                $rate = array();
                $rate['pos'] = array_filter((array) $reviews, function($a) {
                    return ($a->recommendation == 11);
                });
                $rate['neg'] = array_filter((array) $reviews, function($a) {
                    return ($a->recommendation == 10);
                });

                $divider = count($rate['pos']) + count($rate['neg']);
                if ($divider) {
                    $calculate = count($rate['pos']) / $divider;
                } else {
                    $calculate = 0;
                }

                if (empty(get_config('community_oer', 'reviewrating')) || get_config('community_oer', 'reviewrating') == 0) {
                    $const = 0;
                } else {
                    $const = get_config('community_oer', 'reviewrating') / 100;
                }

                if ($const != 0 && $calculate >= $const) {
                    $rating2 = true;
                }

                if ($minreviewcount > 0 && count($rate['pos']) < $minreviewcount) {
                    $rating2 = false;
                }
            }

            $obj->data[$key]->activityrating = $rating1 || $rating2;

            // Display new activity.
            $obj->data[$key]->display_whats_new = false;

            $whatnew = $DB->get_record('community_oer_wht_new', [
                    'itemid' => $item->cmid,
                    'itemtype' => 'activity',
                    'useridfollow' => $USER->id
            ]);

            if (time() < strtotime("+1 month", $item->cm_created) && $obj->data[$key]->activity_shared == false &&
                    empty($whatnew)) {
                $obj->data[$key]->display_whats_new = true;
            }

            // Enable first - display_whats_new , second activityrating.
            if ($obj->data[$key]->display_whats_new == true) {
                $obj->data[$key]->activityrating = false;
            }

            if (!$item->visible) {
                $obj->data[$key]->not_visible = true;
                $obj->data[$key]->display_whats_new = false;
                $obj->data[$key]->activityrating = false;
            } else {
                $obj->data[$key]->not_visible = false;
            }

            // PTL-6060 Run filters through metadata_teacherremarks.
            $filtered = format_text($obj->data[$key]->metadata_teacherremarks, FORMAT_HTML);

            // If present image, change url.
            $filtered = str_replace($CFG->wwwroot.'/brokenfile.php#', $CFG->wwwroot.'/draftfile.php', $filtered);
            $obj->data[$key]->metadata_teacherremarks = $filtered;

            // Show/hide teacherremarks.
            $obj->data[$key]->trimteacherremarks = false;

            if (isset($CFG->trimbuttonchars) && $CFG->trimbuttonchars && isset($CFG->trimteacherremarks)
                && $CFG->trimteacherremarks) {

                $strip = strip_tags($obj->data[$key]->metadata_teacherremarks);
                $teacherremarkssize = mb_strlen($strip);

                if ($teacherremarkssize > $CFG->trimbuttonchars) {
                    $obj->data[$key]->trimteacherremarks = true;
                }
            }

            // Analyze metadata_certificatestamp.
            $obj->data[$key]->stamp_teachers_offer =
            $obj->data[$key]->stamp_tested_by_petel = $obj->data[$key]->stamp_tested_by_teachers = false;

            $certificatestamp = self::funcs()::get_metadata_parameter(trim($obj->data[$key]->metadata_certificatestamp));

            switch ($certificatestamp) {
                case 'teachers_offer':
                    $obj->data[$key]->stamp_teachers_offer = true;
                    $stampdesc = get_string('teachers_offer', 'community_oer');
                    break;

                case 'tested_by_petel':
                    $obj->data[$key]->stamp_tested_by_petel = true;
                    $stampdesc = get_string('tested_by_petel', 'community_oer');
                    break;

                case 'tested_by_teachers':
                    $obj->data[$key]->stamp_tested_by_teachers = true;
                    $stampdesc = get_string('tested_by_teachers', 'community_oer');
                    break;
                default:
                    $stampdesc = '';
            }

            // Prepare image title.
            $a = new \StdClass();
            $a->modname = $obj->data[$key]->modname;
            $a->cm_name = $obj->data[$key]->mod_name;
            $a->stamp = $stampdesc;

            if (!empty($stampdesc)) {
                $obj->data[$key]->image_title = get_string('image_title_with_stamp', 'community_oer', $a);
            } else {
                $obj->data[$key]->image_title = get_string('image_title_without_stamp', 'community_oer', $a);
            }

            // Prepare metadata_sourceurl.
            if (isset($item->metadata_sourceurl) && !empty($item->metadata_sourceurl)) {

                // Explode words.
                $w = [];
                foreach (explode(' ', $item->metadata_sourceurl) as $word) {
                    if (!empty($word)) {
                        $w[] = $word;
                    }
                }

                // Convert url to link.
                foreach ($w as $num => $word) {
                    $match = [];
                    preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $word, $match);

                    if (isset($match[0]) && !empty($match[0])) {
                        foreach ($match[0] as $url) {
                            $newurl = '<a href="' . $url . '" target="_blank">' . get_string('link', 'community_oer') . '</a>';
                            $word = str_replace($url, $newurl, $word);
                        }

                        $w[$num] = $word;
                    }
                }

                $maxlengthrow = 20;
                $row = '';
                $string = '<span class="tooltip-inner-span">' . get_string('base_on_activity', 'community_oer') . '</span>';
                $countchars = 0;
                foreach ($w as $word) {
                    $countchars += mb_strlen(strip_links($word)) + 1;

                    if ($countchars >= $maxlengthrow) {
                        $row .= ' ' . $word;
                        $string .= '<span class="tooltip-inner-span">' . $row . '</span>';
                        $row = '';
                        $countchars = 0;
                    } else {
                        $row .= ' ' . $word;
                    }
                }

                if (!empty($row)) {
                    $string .= '<span class="tooltip-inner-span">' . $row . '</span>';
                }

                $obj->data[$key]->metadata_sourceurl = $string;
            }

            // Local diagnostic.
            if ($obj->data[$key]->mod_type == 'quiz') {
                $obj->data[$key]->diagnostic = false;
                $allowedtags = get_config('local_diagnostic','allowedtags');
                $allowedtags = array_filter(array_map('trim', explode(',', $allowedtags)));

                if (!empty($allowedtags) && array_intersect($obj->data[$key]->tags, $allowedtags)) {
                    $obj->data[$key]->diagnostic = true;
                }

            }
        }

        return $obj;
    }

    public function single_cmid_render_data($cmid, $referer = 'oercatalog') {

        $obj = $this->query(-1)->compare('cmid', $cmid)->groupBy('cmid');
        $obj = $this->calculate_data_online($obj, $referer);
        $data = $obj->get();

        if (!empty($data)) {
            return reset($data);
        }

        return false;
    }

    public function get_total_elements($type, $value, $data) {

        switch ($type) {
            case 'category':
                $newcache = $this->query()->compare('catid', $value)->get();
                break;
            case 'course':
                $newcache = $this->query()->compare('courseid', $value)->get();
                break;
            case 'section':
                $newcache = $this->query()->compare('sectionid', $value)->get();
                break;
        }

        $obj = $this->query($newcache)->compare('visible', '1');

        // Search.
        $flag = false;
        $obj = $this->query($obj->get());
        foreach ($data as $item) {
            if ($item->area == 'pillsearch') {
                if (!empty($item->value)) {
                    $obj = ($flag) ? $obj->orLikeLower('mod_name', trim($item->value)) :
                            $obj->likeLower('mod_name', trim($item->value));

                    $flag = true;

                    $obj = ($flag) ? $obj->orLikeLower('metadata_teacherremarks', trim($item->value)) :
                            $obj->likeLower('metadata_teacherremarks', trim($item->value));
                    $obj = ($flag) ? $obj->orLikeLower('username', trim($item->value)) :
                            $obj->likeLower('username', trim($item->value));
                }
            }
        }

        return $obj->groupBy('mod_name')->count();
    }

    // Cache rebuild.
    public function recalculate_data_in_cache() {
        global $DB;

        $result = [];
        foreach ($DB->get_records('community_oer_activity') as $item) {
            $result[$item->uniqueid] = json_decode($item->data);
        }

        $this->cache->purge();
        $this->cache->set($this->key, $result);

        return true;
    }

    // Get activities from cache.
    public function get_activities_from_cache() {
        if (($datacache = $this->cache->get($this->key)) === false) {
            $datacache = [];
        }

        return $datacache;
    }

    // Query in cache.
    public function query($data = -1) {
        if ($data == -1) {
            $data = $this->get_activities_from_cache();
        }

        $query = new \community_oer\object_query($data, 'uniqueid');
        return $query;
    }

    public static function funcs() {
        return new \community_oer\activity_help();
    }
}

class activity_help {

    public static function build_menu() {
        $activity = new \community_oer\activity_oer;
        $structure = $activity->structure_activity_catalog();
        foreach ($structure as $cat) {
            foreach ($cat['courses'] as $key => $course) {
                $sections = [];

                $modinfo = get_fast_modinfo($course->id);

                foreach ($modinfo->get_section_info_all() as $item) {
                    $data = $item->getIterator()->getArrayCopy();

                    if (!$data['visible']) {
                        continue;
                    }

                    if (!isset($data['parent'])) {
                        $data['parent'] = 0;
                    }

                    if ($data['sectionnum'] != 0 && $data['visible'] == 1 && $data['parent'] == 0) {

                        // If no activities in section.
                        //$cms = $activity->query()->compare('sectionid', $data['id'])->compare('visible', '1')->get();
                        //if (!empty($cms)) {
                        //    $sectionname = course_get_format($course->id)->get_section_name($data['sectionnum']);
                        //    $sections[] = ['sectionid' => $data['id'], 'section_name' => $sectionname];
                        //}

                        $sectionname = course_get_format($course->id)->get_section_name($data['sectionnum']);
                        $sections[] = ['sectionid' => $data['id'], 'section_name' => $sectionname];
                    }
                }

                $cat['courses'][$key]->sections = $sections;
            }
        }

        return $structure;
    }

    public static function get_mod_name($mod) {

        return get_string('pluginname', 'mod_' . $mod);

        // Not needed.
        switch ($mod) {
            case 'quiz':
                $string = get_string('modquiz', 'community_oer');
                break;
            case 'assign':
                $string = get_string('modassign', 'community_oer');
                break;
            case 'questionnaire':
                $string = get_string('modquestionnaire', 'community_oer');
                break;
            case 'data':
                $string = get_string('moddata', 'community_oer');
                break;
            case 'glossary':
                $string = get_string('modglossary', 'community_oer');
                break;
            case 'lesson':
                $string = get_string('modlesson', 'community_oer');
                break;
            case 'hvp':
                $string = get_string('modhvp', 'community_oer');
                break;
            case 'game':
                $string = get_string('modgame', 'community_oer');
                break;
            case 'workshop':
                $string = get_string('modworkshop', 'community_oer');
                break;
            case 'resource':
                $string = get_string('modresource', 'community_oer');
                break;
            case 'url':
                $string = get_string('modurl', 'community_oer');
                break;
            case 'page':
                $string = get_string('modpage', 'community_oer');
                break;

            default:
                $string = get_string('pluginname', 'mod_' . $mod);
        }

        return $string;
    }

    public static function get_metadata_value($string) {
        if (!empty($string)) {
            $arr = explode('|', $string);

            return $arr[0];
        }

        return $string;
    }

    public static function get_metadata_parameter($string) {
        if (!empty($string)) {
            $arr = explode('|', $string);

            if (isset($arr[1]) && !empty($arr[1])) {
                return $arr[1];
            } else {
                return $arr[0];
            }
        }

        return $string;
    }

    public static function build_filters() {
        $result = [];

        list($filtertypes, $filtersingle, $filtermore) = self::prepare_data_for_filters();

        $group = 1400;

        // Filter mod_types.
        if (!empty($filtertypes)) {

            $columns = [];
            foreach ($filtertypes as $item) {

                if ($item['type'] != 'mod_type') {
                    continue;
                }

                $arr['title'] = '';
                $arr['data'][] = [
                        'uniqueid' => 'filter-' . $item['value'], 'value' => $item['value'],
                        'print_value' => self::get_mod_name($item['value']),
                        'field' => 'mod_type', 'search' => 'equal', 'group' => '1000'
                ];

                if (count($arr['data']) == 4) {
                    $columns[] = $arr;
                    $arr['data'] = [];
                }
            }

            $columns[] = $arr;

             /*Add anoter types.
                        $columns[] = [
                            'title' => '',
                            'data' => [
                                0 => [
                                    'uniqueid' => 'filter-rest',
                                    'value' => $modtypes,
                                    'print_value' => get_string('modother', 'community_oer'),
                                    'field' => 'mod_type', 'search' => 'notIn', 'group' => '1000'
                                ],
                            ]
                        ];*/

            $result[] = [
                    'nav_title' => get_string('filter1title', 'community_oer'),
                    'nav_id' => 'filternav1',
                    'columns' => $columns
            ];

        }

        // Filter singles.
        if (!empty($filtersingle)) {
            $columns = [];
            foreach ($filtersingle as $item) {

                if ($item['type'] != 'metadata') {
                    continue;
                }

                $result[] = [
                        'nav_title' => $item['name'],
                        'nav_id' => 'filternav' . $group,
                        'columns' => [
                                'title' => '',
                                'data' => self::rebuild_filter_metadata(
                                        \community_oer\main_oer::build_metadata_parameters(CONTEXT_MODULE, $item['value']),
                                        'metadata_' . $item['value'], $group
                                )
                        ]
                ];

                $group += 10;
            }
        }

        // Filter more.
        if (!empty($filtermore)) {
            $columns = [];

            foreach ($filtermore as $item) {
                if ($item['type'] == 'metadata') {
                    $columns[] = [
                            'title' => \community_oer\main_oer::get_metadata_name(CONTEXT_MODULE, $item['value']),
                            'data' => self::rebuild_filter_metadata(
                                    \community_oer\main_oer::build_metadata_parameters(CONTEXT_MODULE, $item['value']),
                                    'metadata_' . $item['value'], $group
                            )
                    ];

                    $group += 10;
                }
            }

            $arr = [];
            foreach ($filtermore as $item) {
                if ($item['type'] == 'mod_type') {

                    $arr['title'] = '';
                    $arr['data'][] = [
                            'uniqueid' => 'filter-' . $item['value'], 'value' => $item['value'],
                            'print_value' => self::get_mod_name($item['value']),
                            'field' => 'mod_type', 'search' => 'equal', 'group' => '10000'
                    ];

                    if (count($arr['data']) == 4) {
                        $columns[] = $arr;
                        $arr['data'] = [];
                    }
                }
            }

            $columns[] = $arr;

            // Grouper.
            $allowedtags = get_config('local_diagnostic','allowedtags');
            $allowedtags = array_filter(array_map('trim', explode(',', $allowedtags)));

            $columns[] = [
                    'title' => get_string('grouper', 'community_oer'),
                    'data' => [
                        'uniqueid' => 'metadata_grouper0',
                        'label' => get_string('selectgrouper', 'community_oer'),
                        'value' => $allowedtags,
                        'field' => 'tags',
                        'search' => 'inArray',
                        'print_value' => get_string('selectgrouper', 'community_oer'),
                        'group' => $group,
                    ]
            ];

            $fullwidth = count($columns) > 3 ? true : false;
            $result[] = [
                    'nav_title' => get_string('filter3title', 'community_oer'),
                    'nav_id' => 'filternav4',
                    'columns' => $columns,
                    'fullwidth' => $fullwidth
            ];
        }

        return $result;
    }

    public static function prepare_data_for_filters() {
        global $DB;

        $filtertypes = [];
        $filtersingle = [];
        $filtermore = [];

        // Filter modtypes.
        $modtypes = get_config('community_oer', 'filter_modtypes');
        if (!empty($modtypes)) {

            $modtypes = (array) json_decode($modtypes);

            foreach ($modtypes as $value => $key) {
                switch ($key) {
                    case PROFILE_VISIBLE_PRIVATE:
                        $filtertypes[] = ['type' => 'mod_type', 'name' => '', 'value' => $value];
                        break;
                    case PROFILE_VISIBLE_ALL:
                        $filtermore[] = ['type' => 'mod_type', 'name' => '', 'value' => $value];
                        break;
                }
            }
        }

        // Build filters from metadata.
        $query = "
            SELECT * 
            FROM {local_metadata_field}
            WHERE contextlevel = ? AND datatype IN ('menu', 'multimenu', 'multiselect')
            ORDER BY sortorder ASC
        ";

        foreach ($DB->get_records_sql($query, [CONTEXT_MODULE]) as $item) {
            switch ($item->visible) {
                case PROFILE_VISIBLE_PRIVATE:
                    $filtersingle[] = ['type' => 'metadata', 'name' => $item->name, 'value' => $item->shortname];
                    break;
                case PROFILE_VISIBLE_ALL:
                    $filtermore[] = ['type' => 'metadata', 'name' => $item->name, 'value' => $item->shortname];
                    break;
            }
        }

        return [$filtertypes, $filtersingle, $filtermore];
    }

    public static function rebuild_filter_metadata($data, $field, $group) {
        foreach ($data as $key => $item) {
            $data[$key]['uniqueid'] = $field . $key;
            $data[$key]['field'] = $field;
            $data[$key]['search'] = isset($item['search']) ? $item['search'] : 'like';
            $data[$key]['print_value'] = isset($item['label']) ? $item['label'] : $item['value'];
            $data[$key]['group'] = $group;
        }

        return $data;
    }

    public static function check_metadata_id($cmid) {
        $value = \local_metadata\mcontext::module()->get($cmid, 'ID');
        if (empty($value)) {
            return \local_metadata\mcontext::module()->save($cmid, 'ID', $cmid);
        }

        return false;
    }

    public static function whats_new_update_counter($cmid, $type = 'activity', $userid = null) {
        global $DB, $USER;

        if ($userid == null) {
            $userid = $USER->id;
        }

        $obj = $DB->get_record('community_oer_wht_new', [
                'itemid' => $cmid,
                'itemtype' => $type,
                'useridfollow' => $userid
        ]);

        if (empty($obj)) {
            $ins = new \StdClass();
            $ins->itemid = $cmid;
            $ins->itemtype = $type;
            $ins->useridfollow = $userid;
            $ins->useridfollowed = 0;
            $ins->counter = 1;
            $ins->timecreated = time();

            $DB->insert_record('community_oer_wht_new', $ins);
        } else {
            $obj->counter = $obj->counter + 1;
            $DB->update_record('community_oer_wht_new', $obj);
        }

        return true;
    }

    public static function sorting_elements() {
        return [
                0 => ['name' => get_string('sorting1', 'community_oer'), 'value' => '1'],
                1 => ['name' => get_string('sorting2', 'community_oer'), 'value' => '2'],
                2 => ['name' => get_string('sorting3', 'community_oer'), 'value' => '3'],
                3 => ['name' => get_string('sorting4', 'community_oer'), 'value' => '4'],
                4 => ['name' => get_string('sorting5', 'community_oer'), 'value' => '5'],
                5 => ['name' => get_string('sorting6', 'community_oer'), 'value' => '6'],
        ];
    }

    public static function usage_calculation($mode, $mid) {
        global $DB, $CFG;

        $return = new \stdClass();
        $return->responses = $return->uniqueteacher = 0;

        if (in_array($mode, array("assign", "quiz", "questionnaire")) && is_numeric($mid)) {
            $grwhere = '';
            $grparams = [];
            if ($mode == 'quiz') {
                list($grwhere, $grparams) = $DB->get_in_or_equal(explode(',', $CFG->gradebookroles), SQL_PARAMS_NAMED, 'grbr');
            }

            $sql = "                    
                    SELECT 
                        m.instanceid,
                        cm.instance,
                        (SELECT  cc.name 
                        FROM {course} c 
                        JOIN {course_categories} cc ON cc.id = c.category  WHERE c.id = cm.course) AS category,
    
                        CASE mo.name
                        WHEN 'assign' THEN (SELECT COUNT(*) FROM {assign_submission} asg WHERE asg.assignment = cm.instance AND cm.module = 1 AND asg.status = 'submitted') 
                        WHEN 'quiz' THEN (
                                 SELECT COUNT(DISTINCT qa.id) FROM {quiz_attempts} qa
                                 JOIN {role_assignments} ra ON (qa.userid = ra.userid)
                                     WHERE qa.quiz = cm.instance
                                     AND ra.roleid $grwhere
                                     AND qa.state = 'finished'
                                     AND qa.preview = 0
                                 )
                        WHEN 'questionnaire' THEN (SELECT COUNT(*) FROM {questionnaire_response} q 
                                                WHERE cm.instance = q.questionnaireid AND cm.module = 24 AND q.complete='y')
                        END AS responses
                            
                        FROM {local_metadata} as m
                        JOIN {local_metadata_field} mf ON m.fieldid = mf.id AND mf.contextlevel = 70 and mf.shortname = 'ID'
                        JOIN {course_modules} cm on cm.id = m.instanceid 
                        JOIN {modules} mo ON mo.id = cm.module
                        WHERE m.data = " . $mid . "
                        ORDER BY m.instanceid
                        ";

            $copies = $DB->get_records_sql($sql, $grparams);

            $minresponses = get_config('community_oer', 'min_student_response');
            $teachers = [];
            foreach ($copies as $copy) {
                if (isset($copy->responses)) {
                    $return->responses += $copy->responses;
                    if ($copy->responses >= $minresponses) {
                        if (!in_array($copy->category, $teachers)) {
                            $teachers[] = $copy->category;
                        }
                    }
                }
            }

            $return->uniqueteacher = count($teachers);
        }

        return $return;
    }
}
