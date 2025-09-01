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

class sequence_oer {

    public $cache;
    public $key;
    public $revision;
    public $contextlevel;

    public function __construct() {
        global $DB;

        $this->cache = \cache::make_from_params(\cache_store::MODE_APPLICATION, 'oer_sequence_cache', 'sequences');
        $this->revision = \community_oer\main_oer::get_oercacheversion();
        $this->key = 'data' . $this->revision;
        $this->contextlevel = \local_metadata\mcontext::section()->get_contextid();

        $count = $DB->count_records('community_oer_sequence');
        if ($count > count($this->get_sequences_from_cache())) {
            $this->recalculate_data_in_cache();
        }
    }

    public function structure_cache() {
        return \cache::make_from_params(\cache_store::MODE_APPLICATION, 'oer_cache', 'sequences_structure');
    }

    public function structure_sequence_catalog() {
        $cache = $this->structure_cache();
        $key = 'structure' . $this->revision;;

        if (($result = $cache->get($key)) === false) {
            $result = \community_oer\main_oer::structure_main_catalog();
            $cache->set($key, $result);
            return $result;
        } else {
            return $result;
        }
    }

    public function get_main_sequence_elements() {
        global $DB;

        $sequences = [];
        $activities = [];

        foreach ($this->structure_sequence_catalog() as $item) {
            foreach ($item['courses'] as $course) {
                $sections = $DB->get_records('course_sections', ['course' => $course->id]);
                foreach ($sections as $section) {
                    if (!empty($section)) {
                        $modinfo = get_fast_modinfo($section->course);
                        $current = $modinfo->get_section_info($section->section)->getIterator()->getArrayCopy();

                        if (isset($current['parent']) && $current['parent'] != 0) {
                            $sequences[] = $current['id'];

                            // Activities details.
                            foreach (explode(',', $current['sequence']) as $cmid) {
                                $activities[$current['id']][] = $cmid;
                            }
                        }
                    }
                }
            }
        }

        return [$sequences, $activities];
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

        $structure = $this->structure_sequence_catalog();

        foreach ($structure as $item) {
            foreach ($item['courses'] as $course) {
                $coursesmaagar[] = $course->id;
            }
        }

        $result = array();
        $rolespermitted = array('editingteacher');
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

    private function build_sequence_data($seqid) {
        global $DB, $OUTPUT, $CFG;

        $section = $DB->get_record('course_sections', ['id' => $seqid]);
        $modinfo = get_fast_modinfo($section->course);
        $data = $modinfo->get_section_info($section->section)->getIterator()->getArrayCopy();
        $course = get_course($data['course']);

        $obj = new \StdClass();
        $obj->seqid = $seqid;
        $obj->catid = $course->category;
        $obj->courseid = $data['course'];

        $parentdata = $modinfo->get_section_info($data['parent'])->getIterator()->getArrayCopy();
        $obj->sectionid = $parentdata['id'];

        $obj->seqname = $data['name'];
        $obj->visible = $data['visible'];
        $obj->section = $data['sectionnum'];
        $obj->sequence = $data['sequence'];

        // Metadata details.
        $category = $DB->get_record('local_metadata_category', ['contextlevel' => $this->contextlevel], '*', IGNORE_MULTIPLE);

        if (!empty($category)) {
            $query = "
                SELECT id, shortname
                FROM {local_metadata_field}
                WHERE contextlevel = ? AND categoryid = ?
                ORDER BY sortorder ASC
            ";

            $fields = $DB->get_records_sql($query, [$this->contextlevel, $category->id]);
            foreach ($fields as $field) {
                $name = 'metadata_' . $field->shortname;
                $obj->$name = \local_metadata\mcontext::section()->get($seqid, $field->shortname);
            }
        }

        // User details.
        $obj->users = [];
        $username = '';
        if (isset($obj->metadata_suserid) && !empty($obj->metadata_suserid)) {
            foreach (explode(',', $obj->metadata_suserid) as $userid) {
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

        // Sequence image.
        $sequenceimage = isset($obj->metadata_simagesequence) ? $obj->metadata_simagesequence : '';
        $context = \context_system::instance();
        $obj->sequence_image = \community_oer\main_oer::create_url_image($sequenceimage, $context);

        // Sequence description.
        $sequencedescription = isset($obj->metadata_sequencedescription) ? $obj->metadata_sequencedescription : '';
        $obj->sequence_description = $sequencedescription;

        // Format date create.
        $obj->sequence_created_at = $obj->metadata_screated_at;
        $obj->sequence_created_at_format = is_number($obj->metadata_screated_at) ? date('d.m.Y', $obj->metadata_screated_at) : '';

        // Count used sequence.
        $sql = "
                SELECT *
                FROM {community_sharesequence_shr}
                WHERE seqid = ?
                GROUP BY useridfrom
            ";
        $rows = $DB->get_records_sql($sql, [$obj->seqid]);
        $obj->count_used_sequence = count($rows);

        // Sequence details.
        $tmp = clone $obj;
        $obj->tab_data_sequence = $tmp;

        // Activities details.
        $activities = [];
        foreach (explode(',', $data['sequence']) as $cmid) {
            if (!empty($cmid) && is_number($cmid)) {
                if ($actdata = $this->build_sequence_activity_data($cmid, $obj)) {
                    $activities[] = $actdata;
                }
            }
        }

        $obj->tabs_data_activities = $activities;

        return $obj;
    }

    private function build_sequence_activity_data($cmid, $sequence) {
        global $DB, $CFG, $OUTPUT;

        $query = "
            SELECT 
                cm.id AS cmid,
                cm.course AS courseid,
                c.category AS catid,
                cm.instance AS instance,
                cm.added AS cm_created,  	
                cm.visible AS visible,
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
            return false;
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
            return false;
        }

        $obj->uniqueid = time() . $obj->cmid . $obj->courseid . $obj->catid . $obj->sectionid;

        // Mod details.
        $tmp = $DB->get_record($obj->mod_type, ['id' => $obj->instance]);
        $obj->mod_name = isset($tmp->name) ? $tmp->name : '';
        $obj->mod_intro = isset($tmp->intro) ? $tmp->intro : '';

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
        if (!empty($metadataimageactivity)) {
            $context = \context_module::instance($obj->cmid);
            $obj->imageactivity = \community_oer\main_oer::create_url_image($metadataimageactivity, $context);
        } else {
            // Sequence image.
            $sequenceimage = isset($sequence->metadata_simagesequence) ? $sequence->metadata_simagesequence : '';
            $context = \context_system::instance();
            $obj->imageactivity = \community_oer\main_oer::create_url_image($sequenceimage, $context);
        }

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
        list($course, $cm) = get_course_and_cm_from_cmid($obj->cmid);
        $obj->urlactivity = isset($cm->url) ? $cm->url->out() : false;

        // Format date create.
        $obj->cm_created_format = date('d.m.Y', $obj->cm_created);

        // Format info metadata.
        $metadatadurationactivity = isset($obj->metadata_durationactivity) ? $obj->metadata_durationactivity : '';
        $metadatalevelactivity = isset($obj->metadata_levelactivity) ? $obj->metadata_levelactivity : '';
        $arr = array_filter([
                self::funcs()::get_metadata_value(trim($metadatadurationactivity)),
                self::funcs()::get_metadata_value(trim($metadatalevelactivity))
        ]);
        $obj->info_metadata = count($arr) ? '(' . implode(', ', $arr) . ')' : '';

        return $obj;
    }

    public function sequence_recalculate_in_db($seqid) {
        global $DB;

        $section = $DB->get_record('course_sections', ['id' => $seqid]);
        if (!empty($section)) {
            $modinfo = get_fast_modinfo($section->course);
            $current = $modinfo->get_section_info($section->section)->getIterator()->getArrayCopy();

            if (!isset($current['parent']) || $current['parent'] == 0) {
                return false;
            }
        } else {
            return false;
        }

        $DB->delete_records('community_oer_sequence', ['seqid' => $seqid]);

        $data = $this->build_sequence_data($seqid);
        if ($data == false) {
            return false;
        }

        $obj = new \StdClass();
        $obj->seqid = $data->seqid;
        $obj->catid = $data->catid;
        $obj->courseid = $data->courseid;
        $obj->sectionid = $data->sectionid;
        $obj->recache = 0;
        $obj->data = json_encode($data);
        $obj->timecreated = time();
        $obj->timemodified = time();

        $DB->insert_record('community_oer_sequence', $obj);

        return true;
    }

    public function recalculate_all_sequences_in_db_cache() {
        global $DB;

        raise_memory_limit(MEMORY_UNLIMITED);

        $DB->execute("TRUNCATE TABLE {community_oer_sequence}");

        foreach ($this->structure_sequence_catalog() as $item) {
            foreach ($item['courses'] as $course) {
                $sections = $DB->get_records('course_sections', ['course' => $course->id]);
                foreach ($sections as $item) {
                    $this->sequence_recalculate_in_db($item->id);
                }
            }
        }

        $this->recalculate_data_in_cache();

        return true;
    }

    public function build_standart_info_for_page() {
        $result = [];

        $result['dashboard'] = 'sequence';

        // Side panel menu.
        $result['aside_menu'] = self::funcs()::build_menu();

        // Filters.
        $result['filters'] = self::funcs()::build_filters();

        // Sorting.
        $sorting = \community_oer\sequence_help::sorting_elements();

        // Set default.
        $default = get_config('community_oer', 'default_sort_sequence');
        foreach ($sorting as $key => $item) {
            $sorting[$key]['selected'] = ($item['value'] == $default) ? true : false;
        }
        $result['sorting'] = $sorting;

        return $result;
    }

    public function calculate_data_online($obj) {
        global $OUTPUT, $DB, $USER;

        foreach ($obj->data as $key => $item) {

            // Tabs details.
            $tabs = [];

            $tabs[] = [
                    'tabid' => 'block_sequence_' . $item->tab_data_sequence->seqid,
                    'name' => get_string('sequence_describe', 'community_oer'),
                    'tabactive' => true,
            ];

            foreach ($item->tabs_data_activities as $k => $act) {
                $name = get_string('sequence_activity', 'community_oer') . ' ' . ($k + 1);
                $tabs[] = [
                        'tabid' => 'block_activity_' . $act->cmid,
                        'name' => $name,
                        'tabactive' => false,
                ];
            }

            $obj->data[$key]->tabs = $tabs;

            // Recalculate data.

            // Sequence.
            $obj->data[$key]->tab_data_sequence->seqactive = true;
            $obj->data[$key]->tab_data_sequence->tabid = 'block_sequence_' . $item->tab_data_sequence->seqid;

            // If user shared sequence.
            $count = $DB->count_records('community_sharesequence_shr', [
                    'type' => 'copy_to_course',
                    'seqid' => $item->seqid,
                    'useridfrom' => $USER->id,
            ]);

            $obj->data[$key]->sequence_shared = $count ? true : false;

            $reviews = $DB->get_records('community_oer_reviews', array('objid' => $item->seqid, 'reviewtype' => 'sequence'));
            $obj->data[$key]->countreview = count($reviews);

            // Url to social profile.
            foreach ($obj->data[$key]->users as $key2 => $item2) {
                $urltosocialprofile = "javascript:void(0)";
                $urltosocialtarget = false;
                if ($item2->userid && get_user_preferences('community_social_enable', '', $item2->userid)) {
                    $urltosocialprofile = new \moodle_url('/local/community/plugins/social/profile.php', ['id' => $item2->userid]);
                    $urltosocialprofile = $urltosocialprofile->out();
                    $urltosocialtarget = true;
                }
                $obj->data[$key]->tab_data_sequence->users[$key2]->urltosocialprofile = $urltosocialprofile;
                $obj->data[$key]->tab_data_sequence->users[$key2]->urltosocialtarget = $urltosocialtarget;
            }

            // Sequence link.
            $sequencelink = new \moodle_url('/local/community/plugins/oer/sequenceshare.php', ['id' => $item->seqid]);
            $obj->data[$key]->sequencelink = $sequencelink->out();

            // Prepare metadata_soriginality.
            if (isset($item->metadata_soriginality) && !empty($item->metadata_soriginality)) {

                // Explode words.
                $w = [];
                foreach (explode(' ', $item->metadata_soriginality) as $word) {
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

                $obj->data[$key]->metadata_soriginality = $string;
            }

            // Activities.
            foreach ($item->tabs_data_activities as $key2 => $act) {

                // Tab id.
                $obj->data[$key]->tabs_data_activities[$key2]->tabid = 'block_activity_' . $act->cmid;

                // If activity active.
                $obj->data[$key]->tabs_data_activities[$key2]->cmactive = false;

                // Activity mod name.
                $obj->data[$key]->tabs_data_activities[$key2]->modname = self::funcs()::get_mod_name($act->mod_type);

                // Activity mod icon.
                $obj->data[$key]->tabs_data_activities[$key2]->modicon =
                        $OUTPUT->pix_icon('icon', '', $act->mod_type, array('class' => 'oer-icon-modtype'));

                // Url to social profile.
                foreach ($act->users as $key3 => $item2) {
                    $urltosocialprofile = "javascript:void(0)";
                    $urltosocialtarget = false;
                    if ($item2->userid && get_user_preferences('community_social_enable', '', $item2->userid)) {
                        $urltosocialprofile =
                                new \moodle_url('/local/community/plugins/social/profile.php', ['id' => $item2->userid]);
                        $urltosocialprofile = $urltosocialprofile->out();
                        $urltosocialtarget = true;
                    }
                    $act->users[$key3]->urltosocialprofile = $urltosocialprofile;
                    $act->users[$key3]->urltosocialtarget = $urltosocialtarget;
                }

                $urltosocialprofile = "javascript:void(0)";
                $urltosocialtarget = false;
                if (isset($act->userid) && get_user_preferences('community_social_enable', '', $act->userid)) {
                    $urltosocialprofile = new \moodle_url('/local/community/plugins/social/profile.php', ['id' => $act->userid]);
                    $urltosocialprofile = $urltosocialprofile->out();
                    $urltosocialtarget = true;
                }
                $obj->data[$key]->tabs_data_activities[$key2]->urltosocialprofile = $urltosocialprofile;
                $obj->data[$key]->tabs_data_activities[$key2]->urltosocialtarget = $urltosocialtarget;
            }
        }

        return $obj;
    }

    public function single_cmid_render_data($cmid, $referer = 'oercatalog') {

        list($sequences, $activities) = $this->get_main_sequence_elements();

        foreach ($activities as $seqid => $cmids) {
            if (in_array($cmid, $cmids)) {

                $obj = $this->query(-1)->compare('seqid', $seqid);
                $obj = $this->calculate_data_online($obj);
                $data = $obj->get();

                foreach ($data[$seqid]->tabs_data_activities as $item) {
                    if ($item->cmid == $cmid) {
                        return $item;
                    }
                }
            }
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
        $flag2 = false;
        $obj = $this->query($obj->get());

        // Search in activities.
        $newcacheact = [];
        foreach ($obj->get() as $seq) {
            foreach ($seq->tabs_data_activities as $cm) {
                $cm->seqid = $seq->seqid;
                $newcacheact[$cm->uniqueid] = $cm;
            }
        }

        foreach ($data as $item) {
            if ($item->area == 'pillsearch') {

                $activity = new \community_oer\activity_oer;
                $act = $activity->query($newcacheact)->compare('visible', '1');

                if (!empty($item->value)) {
                    $obj = ($flag) ? $obj->orLikeLower('seqname', trim($item->value)) :
                            $obj->likeLower('seqname', trim($item->value));
                    $flag = true;
                    $obj = ($flag) ? $obj->orLikeLower('metadata_sequencedescription', trim($item->value)) :
                            $obj->likeLower('metadata_sequencedescription', trim($item->value));
                    $obj = ($flag) ? $obj->orLikeLower('username', trim($item->value)) :
                            $obj->likeLower('username', trim($item->value));

                    // Activities.
                    $act = ($flag2) ? $act->orLikeLower('mod_name', trim($item->value)) :
                            $act->likeLower('mod_name', trim($item->value));
                    $flag2 = true;
                    $act = ($flag2) ? $act->orLikeLower('metadata_teacherremarks', trim($item->value)) :
                            $act->likeLower('metadata_teacherremarks', trim($item->value));

                    foreach ($act->get() as $cm) {
                        $obj = ($flag) ? $obj->orCompare('seqid', trim($cm->seqid)) : $obj->compare('seqid', trim($cm->seqid));
                    }
                }
            }
        }

        return $obj->count();
    }

    // Cache rebuild.
    public function recalculate_data_in_cache() {
        global $DB;

        $result = [];
        foreach ($DB->get_records('community_oer_sequence') as $item) {
            $result[$item->seqid] = json_decode($item->data);
        }

        $this->cache->purge();
        $this->cache->set($this->key, $result);

        return true;
    }

    // Get sequences from cache.
    public function get_sequences_from_cache() {
        if (($datacache = $this->cache->get($this->key)) === false) {
            $datacache = [];
        }

        return $datacache;
    }

    // Query in cache.
    public function query($data = -1) {
        if ($data == -1) {
            $data = $this->get_sequences_from_cache();
        }

        $query = new \community_oer\object_query($data, 'seqid');
        return $query;
    }

    public static function funcs() {
        return new \community_oer\sequence_help();
    }
}

class sequence_help {

    public static $contextlevel;

    public function __construct() {
        self::$contextlevel = \local_metadata\mcontext::section()->get_contextid();
    }

    public static function build_menu() {
        $sequence = new \community_oer\sequence_oer();
        $structure = $sequence->structure_sequence_catalog();
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

                        // If no sequences in section.
                        //$sequences = $sequence->query()->compare('sectionid', $data['id'])->compare('visible', '1')->get();
                        //if (!empty($sequences)) {
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
                                        \community_oer\main_oer::build_metadata_parameters(self::$contextlevel, $item['value']),
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
                            'title' => \community_oer\main_oer::get_metadata_name(self::$contextlevel, $item['value']),
                            'data' => self::rebuild_filter_metadata(
                                    \community_oer\main_oer::build_metadata_parameters(self::$contextlevel, $item['value']),
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

        // Build filters from metadata.
        $query = "
            SELECT * 
            FROM {local_metadata_field}
            WHERE contextlevel = ? AND datatype IN ('menu', 'multimenu', 'multiselect')
            ORDER BY sortorder ASC
        ";

        foreach ($DB->get_records_sql($query, [self::$contextlevel]) as $item) {
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

    public static function sorting_elements() {
        return [
                0 => ['name' => get_string('sorting1', 'community_oer'), 'value' => '1'],
                1 => ['name' => get_string('sorting2', 'community_oer'), 'value' => '2'],
                2 => ['name' => get_string('sorting3', 'community_oer'), 'value' => '3'],
                3 => ['name' => get_string('sorting4', 'community_oer'), 'value' => '4'],
        ];
    }
}
