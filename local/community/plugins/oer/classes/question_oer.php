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

use local_community\plugininfo\community;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/format/lib.php');
require_once($CFG->dirroot . '/lib/questionlib.php');

class question_oer {

    public $cache;
    public $key;
    public $revision;
    public $contextlevel;

    public function __construct() {
        global $DB;

        $this->cache = \cache::make_from_params(\cache_store::MODE_APPLICATION, 'oer_question_cache', 'questions');
        $this->revision = \community_oer\main_oer::get_oercacheversion();
        $this->key = 'data' . $this->revision;
        $this->contextlevel = \local_metadata\mcontext::question()->get_contextid();

        $count = $DB->count_records('community_oer_question');
        if ($count > count($this->get_questions_from_cache())) {
            $this->recalculate_data_in_cache();
        }
    }

    public function structure_cache() {
        return \cache::make_from_params(\cache_store::MODE_APPLICATION, 'oer_cache', 'question_structure');
    }

    public function structure_question_catalog() {
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

    public function structure_question_categories() {
        global $DB;

        $sql = "
            SELECT * 
            FROM {question_categories}        
            WHERE idnumber IS NOT NULL;
        ";

        $structure = [];
        foreach ($DB->get_records_sql($sql) as $item) {

            $explode = explode('-', $item->idnumber);
            if (!isset($explode[1])) {
                continue;
            }

            $categorylist = question_categorylist($item->id);
            $categorylist = array_values($categorylist);

            $tmp['sectionid'] = $explode[1];
            $tmp['section_categoryid'] = $categorylist[0];
            $tmp['child_categories'] = [];

            if (count($categorylist) > 1) {
                foreach ($categorylist as $key => $catid) {
                    if ($key > 0) {
                        $tmp['child_categories'][] = $catid;
                    }
                }
            }

            $structure[] = $tmp;
        }

        $list = [];
        foreach ($structure as $item) {
            $list[$item['section_categoryid']] = $item['section_categoryid'];

            foreach ($item['child_categories'] as $childid) {
                $list[$childid] = $childid;
            }
        }

        $list = array_values($list);

        return [$structure, $list];
    }

    private function build_question_data($qid) {
        global $DB, $OUTPUT, $CFG;

        $query = "
            SELECT 
                q.id AS qid,                
                qbe.questioncategoryid AS qcatid,
                q.name AS qname,
                q.questiontext AS questiontext,
                q.parent AS qparent,
                q.qtype AS qtype,                
                qbe.idnumber AS qidnumber,
                q.timecreated AS qtimecreated,
                q.timemodified AS qtimemodified,
                #q.createdby AS qcreatedby,
                qbe.ownerid AS qcreatedby,
                q.modifiedby AS modifiedby,
                qc.name AS catname,
                qc.contextid AS coursecontextid,
                qc.info AS catinfo,
                qc.parent AS catparent,
                qc.idnumber AS catidnumber
                        
            FROM {question} q               
            JOIN {question_versions} qv ON qv.questionid = q.id
            JOIN {question_bank_entries} qbe ON qbe.id = qv.questionbankentryid
            JOIN {question_categories} qc ON qc.id = qbe.questioncategoryid
            WHERE q.id = ?;
        ";

        $obj = $DB->get_record_sql($query, [$qid]);
        if (empty($obj->qid)) {
            return [];
        }

        // Remove child question.
        if ($obj->qparent != 0) {
            return [];
        }

        // Prepare catid, courseid, sectionid.
        list($structure, $categorieslist) = $this->structure_question_categories();

        $sectionid = $qcategory = '';
        if (in_array($obj->qcatid, $categorieslist)) {

            foreach ($structure as $item) {
                if ($item['section_categoryid'] == $obj->qcatid) {
                    $sectionid = $item['sectionid'];
                    $qcategory = 0;

                    break;
                }

                foreach ($item['child_categories'] as $childid) {
                    if ($childid == $obj->qcatid) {
                        $sectionid = $item['sectionid'];
                        $qcategory = $childid;

                        break;
                    }
                }
            }
        }

        if (empty($sectionid) || !is_number($sectionid)) {
            return [];
        }

        $section = $DB->get_record('course_sections', ['id' => $sectionid]);
        if (empty($section)) {
            return [];
        }

        $courseid = $section->course;
        $course = $DB->get_record('course', ['id' => $courseid]);
        if (empty($course)) {
            return [];
        }

        $catid = $course->category;

        // Prepare qname_text.
        //$questiontext = trim(strip_tags($obj->questiontext));
        //$questiontext = str_replace('m&nbsp;', '', $questiontext);
        //$questiontext = str_replace('&nbsp;', '', $questiontext);

        // Check questiontext.
        //$flag = false;
        //if (in_array($obj->qtype, ['combined', 'multianswer'])) {
        //    $flag = true;
        //}
        //
        //if (strpos($questiontext, 'MULTICHOICE_S') !== false || strpos($questiontext, '[[') !== false ||
        //        strpos($questiontext, 'NUMERICAL') !== false) {
        //    $flag = true;
        //}
        //
        //if (empty($questiontext) || $flag) {
        //    $qnametext = $obj->qname;
        //} else {
        //    $qnametext = $questiontext;
        //}
        //
        //$words = [];
        //$countchars = 0;
        //foreach (explode(' ', $qnametext) as $word) {
        //    $countword = mb_strlen(trim($word), 'utf-8');
        //    if ($countword > 0) {
        //        $countchars += $countword;
        //
        //        if ($countchars < 50) {
        //            $words[] = $word;
        //        }
        //    }
        //}
        //
        //$obj->qname_text = implode(' ', $words);

        // PTL-11282.
        $obj->qname_text = $obj->qname;

        // Prepare relevant sectionid.
        $relevantsections = [];
        foreach ($this->structure_question_catalog() as $category) {
            foreach ($category['courses'] as $course) {
                foreach ($DB->get_records('course_sections', ['course' => $course->id]) as $section) {
                    $relevantsections[] = $section->id;
                }
            }
        }

        if (!in_array($sectionid, $relevantsections)) {
            return [];
        }

        $obj->catid = $catid;
        $obj->courseid = $courseid;
        $obj->sectionid = $sectionid;
        $obj->qcategory = $qcategory;

        // Default uniqueid.
        $obj->uniqueid = time() . $obj->qid . $obj->courseid . $obj->catid . $obj->sectionid;

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
            if (!empty($fields)) {
                foreach ($fields as $field) {
                    $name = 'metadata_' . $field->shortname;
                    $obj->$name = \local_metadata\mcontext::question()->get($qid, $field->shortname);
                }
            }
        }

        // Prepare metadata_qhidden.
        if (isset($obj->metadata_qhidden) && $obj->metadata_qhidden != 1) {
            $obj->metadata_qhidden = 0;
        }

        // User details.
        // Default user.
        $obj->userid = 0;
        $obj->user_fname = get_string('defaultuser', 'community_oer');
        $obj->user_lname = '';
        $obj->user_image = $OUTPUT->image_url('avatar', 'community_oer')->out(false);

        if (isset($obj->metadata_quserid) && !empty($obj->metadata_quserid)) {
            if ($user = $DB->get_record('user', ['id' => $obj->metadata_quserid])) {
                $obj->userid = $user->id;
                $obj->user_fname = $user->firstname;
                $obj->user_lname = $user->lastname;
                $obj->user_image = $CFG->wwwroot . '/user/pix.php/' . $user->id . '/f1.jpg';
            }
        }

        $obj->username = implode(' ', [$obj->user_fname, $obj->user_lname]);

        // Question details.
        $obj->qtypeimage = $OUTPUT->pix_icon("icon", 'qtype_' . $obj->qtype, 'qtype_' . $obj->qtype, array());

        // Format date create.
        $obj->qcreated_format = date('d/m/Y', $obj->qtimecreated);

        // Count used question.
        $sql = "
                SELECT id, useridfrom
                FROM {community_sharequestion_shr}
                WHERE type IN ('copy_to_quiz', 'copy_to_category') AND qid = ? 
                GROUP BY qid, useridfrom
            ";

        $res = $DB->get_records_sql($sql, [$obj->qid]);
        $obj->count_used_question = count($res);

        // Virtual sections.
        $qlinksectionids = \local_metadata\mcontext::question()->get($obj->qid, 'qlinksectionids');

        // Prepare array sections.
        $arrsections = [$obj->sectionid];
        foreach (explode(',', $qlinksectionids) as $psectionid) {
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

                $sectionobj->uniqueid =
                        time() . $sectionobj->qid . $sectionobj->courseid . $sectionobj->catid . $sectionobj->sectionid;
            }

            $res[] = $sectionobj;
        }

        return $res;
    }

    public function question_recalculate_in_db($qid) {
        global $DB;

        $DB->delete_records('community_oer_question', ['qid' => $qid]);

        foreach ($this->build_question_data($qid) as $data) {
            $obj = new \StdClass();
            $obj->uniqueid = $data->uniqueid;
            $obj->qid = $data->qid;
            $obj->catid = $data->catid;
            $obj->courseid = $data->courseid;
            $obj->sectionid = $data->sectionid;
            $obj->recache = 0;
            $obj->data = json_encode($data);
            $obj->timecreated = time();
            $obj->timemodified = time();

            $DB->insert_record('community_oer_question', $obj);
        }

        return true;
    }

    public function recalculate_all_questions_in_db_cache() {
        global $DB;

        raise_memory_limit(MEMORY_UNLIMITED);

        $DB->execute("TRUNCATE TABLE {community_oer_question}");

        list($structure, $categorieslist) = $this->structure_question_categories();

        foreach ($categorieslist as $catid) {

            $sql = "
                    SELECT q.id
                    FROM {question_bank_entries} qbe
                    JOIN {question_versions} qv ON qv.questionbankentryid = qbe.id AND qv.version = (
                        SELECT MAX(version) 
                        FROM {question_versions}
                        WHERE questionbankentryid = qbe.id AND status = :ready
                    )
                    JOIN {question} q ON q.id = qv.questionid                                                                    
                    WHERE qbe.questioncategoryid = :category";

            foreach ($DB->get_records_sql($sql, ['ready' => \core_question\local\bank\question_version_status::QUESTION_STATUS_READY,
                    'category' => $catid]) as $item) {
                $this->question_recalculate_in_db($item->id);
            }
        }

        $this->recalculate_data_in_cache();

        return true;
    }

    public function build_standart_info_for_page() {
        $result = [];

        $result['dashboard'] = 'question';

        // Side panel menu.
        $result['aside_menu'] = self::funcs()::build_menu();

        // Filters.
        $result['filters'] = self::funcs()::build_filters();

        // If enable button "hidden questions".
        $result['hidden_questions'] = self::funcs()::enable_views_hidden_questions();

        // Sorting.
        $sorting = \community_oer\question_help::sorting_elements();

        // Set default.
        $default = get_config('community_oer', 'default_sort_question');
        foreach ($sorting as $key => $item) {
            $sorting[$key]['selected'] = ($item['value'] == $default) ? true : false;
        }
        $result['sorting'] = $sorting;

        return $result;
    }

    public function calculate_data_online($obj) {
        global $CFG, $DB;

        foreach ($obj->data as $key => $item) {

            // Url to social profile.
            $urltosocialprofile = "javascript:void(0)";
            $urltosocialtarget = false;
            if (isset($item->userid) && get_user_preferences('community_social_enable', '', $item->userid)) {
                $urltosocialprofile = new \moodle_url('/local/community/plugins/social/profile.php', ['id' => $item->userid]);
                $urltosocialprofile = $urltosocialprofile->out();
                $urltosocialtarget = true;
            }
            $obj->data[$key]->urltosocialprofile = $urltosocialprofile;
            $obj->data[$key]->urltosocialtarget = $urltosocialtarget;

            // Metadata url.
            $obj->data[$key]->metadata_url =
                    $CFG->wwwroot . '/local/metadata/index.php?id=' . $item->qid . '&action=questiondata&contextlevel=' .
                    $this->contextlevel;

            // Enable views hidden questions.
            $obj->data[$key]->view_metadata_url = self::funcs()::enable_views_hidden_questions();

            // Question details.
            $obj->data[$key]->qtypename = self::funcs()::get_question_name($item->qtype);

            // Prepare metadata_qsource.
            if (isset($item->metadata_qsource) && !empty($item->metadata_qsource)) {

                // Explode words.
                $w = [];
                foreach (explode(' ', $item->metadata_qsource) as $word) {
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
                $string = '<span class="tooltip-inner-span">' . get_string('base_on_question', 'community_oer') . '</span>';
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

                $obj->data[$key]->metadata_qsource = $string;
            }

            // Prepare link to edit question.
            $context = \context_coursecat::instance($item->catid);
            if (has_capability('moodle/category:manage', $context)) {
                $obj->data[$key]->enable_edit_question = true;
                $obj->data[$key]->link_edit_question =
                        $CFG->wwwroot . '/question/bank/editquestion/question.php?courseid=' . $item->courseid . '&id=' . $item->qid;
            } else {
                $obj->data[$key]->enable_edit_question = false;
                $obj->data[$key]->link_edit_question = '';
            }

            // Prepare link to edit metadata question.
            $context = \context_coursecat::instance($item->catid);
            if (has_capability('moodle/category:manage', $context)) {
                $obj->data[$key]->enable_edit_question_metadata = true;

                $url = new \moodle_url('/local/metadata/index.php',
                        array(
                                'id' => $item->qid,
                                'action' => 'questiondata',
                                'contextlevel' => $this->contextlevel,
                                'returnurl' => ''
                        )
                );

                $obj->data[$key]->link_edit_question_metadata = $url->out();
            } else {
                $obj->data[$key]->enable_edit_question_metadata = false;
                $obj->data[$key]->link_edit_question_metadata = '';
            }

            // Count reviews to question.
            $reviews = $DB->get_records('community_oer_reviews', array('objid' => $item->qid, 'reviewtype' => 'question'));
            $obj->data[$key]->countreview = count($reviews);
        }

        return $obj;
    }

    public function single_qid_render_data($qid) {

        $obj = $this->query(-1)->compare('qid', $qid);
        $obj = $this->calculate_data_online($obj);
        $data = $obj->get();

        if (!empty($data) && isset($data[$qid])) {
            return $data[$qid];
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

        $obj = $this->query($newcache)->compare('metadata_qhidden', '0');

        // Search.
        $flag = false;
        $obj = $this->query($obj->get());
        foreach ($data as $item) {
            if ($item->area == 'pillsearch') {
                $obj = ($flag) ? $obj->orLikeLower('qname', $item->value) : $obj->likeLower('qname', $item->value);
                $flag = true;

                $obj = ($flag) ? $obj->orLikeLower('questiontext', $item->value) : $obj->likeLower('questiontext', $item->value);
            }
        }

        return $obj->count();
    }

    // Cache rebuild.
    public function recalculate_data_in_cache() {
        global $DB;

        raise_memory_limit(MEMORY_UNLIMITED);

        $result = [];
        foreach ($DB->get_records('community_oer_question') as $item) {
            $result[$item->uniqueid] = json_decode($item->data);
        }

        $this->cache->purge();
        $this->cache->set($this->key, $result);

        return true;
    }

    // Get questions from cache.
    public function get_questions_from_cache() {
        if (($datacache = $this->cache->get($this->key)) === false) {
            $datacache = [];
        }

        return $datacache;
    }

    // Query in cache.
    public function query($data = -1) {
        if ($data == -1) {
            $data = $this->get_questions_from_cache();
        }

        $query = new \community_oer\object_query($data, 'uniqueid');
        return $query;
    }

    public static function funcs() {
        return new \community_oer\question_help();
    }
}

class question_help {

    public static $contextlevel;

    public function __construct() {
        self::$contextlevel = \local_metadata\mcontext::question()->get_contextid();
    }

    public static function get_question_name($qtype) {

        $string = get_string('pluginname', 'qtype_' . $qtype);
        $arr = explode('(', $string);

        return $arr[0];
    }

    public static function build_menu() {
        global $DB;

        $question = new \community_oer\question_oer();
        $structure = $question->structure_question_catalog();
        list($structurecat, $categorieslist) = $question->structure_question_categories();
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
                        $sectionname = course_get_format($course->id)->get_section_name($data['sectionnum']);

                        // Get child category.
                        $childcategory = [];
                        foreach ($structurecat as $obj) {
                            if ($obj['sectionid'] == $data['id'] && !empty($obj['child_categories'])) {
                                foreach ($obj['child_categories'] as $childid) {
                                    $category = $DB->get_record('question_categories', ['id' => $childid]);
                                    $tmp['childcatid'] = $category->id;
                                    $tmp['childcatname'] = $category->name;
                                    $childcategory[] = $tmp;
                                }
                            }
                        }

                        // If no questions in section.
                        //$questions = $question->query()->compare('sectionid', $data['id'])->compare('metadata_qhidden', '0')->get();
                        //if (!empty($questions)) {
                        //    $sections[] = [
                        //            'sectionid' => $data['id'],
                        //            'section_name' => $sectionname,
                        //            'if_child_category_present' => !empty($childcategory) ? true : false,
                        //            'child_category' => $childcategory
                        //    ];
                        //}

                        $sections[] = [
                            'sectionid' => $data['id'],
                            'section_name' => $sectionname,
                            'if_child_category_present' => !empty($childcategory) ? true : false,
                            'child_category' => $childcategory
                        ];
                    }
                }

                $cat['courses'][$key]->sections = $sections;
            }
        }

        return $structure;
    }

    public static function build_filters() {
        $result = [];

        list($filtertypes, $filtersingle, $filtermore) = self::prepare_data_for_filters();

        $group = 1400;

        // Filter qtypes.
        if (!empty($filtertypes)) {

            $columns = [];
            foreach ($filtertypes as $item) {

                if ($item['type'] != 'qtype') {
                    continue;
                }

                $arr['title'] = '';
                $arr['data'][] = [
                        'uniqueid' => 'filter-' . $item['value'], 'value' => $item['value'],
                        'print_value' => self::get_question_name($item['value']),
                        'field' => 'qtype', 'search' => 'equal', 'group' => '1000'
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
                        'value' => $qtypes,
                        'print_value' => get_string('modother', 'community_oer'),
                        'field' => 'qtype', 'search' => 'notIn', 'group' => '1000'
                    ],
                ]
            ];*/

            $result[] = [
                    'nav_title' => get_string('filter1titlequestion', 'community_oer'),
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
                if ($item['type'] == 'qtype') {

                    $arr['title'] = '';
                    $arr['data'][] = [
                            'uniqueid' => 'filter-' . $item['value'], 'value' => $item['value'],
                            'print_value' => self::get_question_name($item['value']),
                            'field' => 'qtype', 'search' => 'equal', 'group' => '10000'
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

        // Filter qtypes.
        $qtypes = get_config('community_oer', 'filter_qtypes');
        if (!empty($qtypes)) {

            $qtypes = (array) json_decode($qtypes);

            foreach ($qtypes as $value => $key) {
                switch ($key) {
                    case PROFILE_VISIBLE_PRIVATE:
                        $filtertypes[] = ['type' => 'qtype', 'name' => '', 'value' => $value];
                        break;
                    case PROFILE_VISIBLE_ALL:
                        $filtermore[] = ['type' => 'qtype', 'name' => '', 'value' => $value];
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

    public static function enable_views_hidden_questions() {
        $flag = false;
        if (\community_oer\main_oer::get_oer_category() !== null) {
            $context = \context_coursecat::instance(\community_oer\main_oer::get_oer_category());
            if (has_capability('moodle/category:manage', $context)) {
                $flag = true;
            }
        }

        return $flag;
    }

    public static function sorting_elements() {
        return [
                0 => ['name' => get_string('sorting2', 'community_oer'), 'value' => '1'],
                1 => ['name' => get_string('sorting4', 'community_oer'), 'value' => '2'],
                2 => ['name' => get_string('sorting3', 'community_oer'), 'value' => '3'],
        ];
    }
}
