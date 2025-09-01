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
 * @package     community_sharequestion
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace community_sharequestion;

class copy_from_mycourses {

    private static function category_tree(&$tree, $contextid, $search, $parentcatid = 0, $level = 0) {
        global $DB;

        $printtree = false;
        $result = $DB->get_records('question_categories', ['parent' => $parentcatid, 'contextid' => $contextid]);

        if (!empty($result)) {
            // Level up.
            if ($parentcatid > 0) {
                $level++;
            }
        }

        foreach ($result as $row) {

            if ($printtree) {
                $i = 0;
                if ($i == 0) {
                    echo '<ul>';
                }
                echo '<li>' . $row->name . ' ' . $level;
            }

            $qtotal = count(self::query_questions_by_category($row->id, $search));

            if ($level - 1 >= 0 && $qtotal > 0) {
                $row->level = $level - 1;
                $row->qtotal = $qtotal;
                $tree[] = $row;
            }

            self::category_tree($tree, $contextid, $search, $row->id, $level);

            if ($printtree) {
                echo '</li>';
                $i++;
                if ($i > 0) {
                    echo '</ul>';
                }
            }
        }
    }

    private static function prepare_questions($result) {
        global $OUTPUT;

        foreach ($result as $key => $item) {

            // Question type name.
            $string = get_string('pluginname', 'qtype_' . $item->qtype);
            $arr = explode('(', $string);
            $result[$key]->qtype_name = $arr[0];

            // Question type image.
            $result[$key]->qtypeimage = $OUTPUT->pix_icon("icon", 'qtype_' . $item->qtype, 'qtype_' . $item->qtype, array());

            // Format date created and updated.
            $result[$key]->timecreated_format = date('d.m.Y', $item->timecreated);
            $result[$key]->timemodified_format = date('d.m.Y', $item->timemodified);

            // Prepare qname_text.
            $qnametext = trim(strip_tags($item->questiontext));
            $qnametext = str_replace('m&nbsp;', '', $qnametext);
            $qnametext = str_replace('&nbsp;', '', $qnametext);
            $words = [];
            $countchars = 0;
            foreach (explode(' ', $qnametext) as $word) {
                $countword = mb_strlen(trim($word), 'utf-8');
                if ($countword > 0) {
                    $countchars += $countword;

                    if ($countchars < 50) {
                        $words[] = $word;
                    }
                }
            }

            $result[$key]->qname_text = implode(' ', $words);

        }

        return array_values($result);
    }

    private static function query_questions_by_category($catid, $search = '') {
        global $DB;

        // Search.
        $search = trim($search);
        $like = !empty($search) ?
                " AND ( q.questiontext LIKE('%" . $search . "%') OR CONCAT(u.firstname, ' ', u.lastname) LIKE('%" . $search .
                "%') )" : '';

        $sql = "
                    SELECT 
                        q.id as qid, 
                        q.name, 
                        q.questiontext, 
                        q.qtype,      
                        CONCAT(u.firstname, ' ', u.lastname) as createdby,
                        q.timecreated,
                        q.timemodified,
                        qbe.idnumber, 
                        qbe.questioncategoryid AS category,
                        qv.id AS versionid, 
                        qv.version, 
                        qv.questionbankentryid
                    
                    FROM {question_bank_entries} qbe
                    JOIN {question_versions} qv ON qv.questionbankentryid = qbe.id AND qv.version = (
                        SELECT MAX(version) 
                        FROM {question_versions}
                        WHERE questionbankentryid = qbe.id AND status = :ready
                    )
                    JOIN {question} q ON q.id = qv.questionid
                    JOIN {user} u ON (q.createdby = u.id)
                                                
                    WHERE qbe.questioncategoryid = :category  ".$like;


        return $DB->get_records_sql($sql, ['ready' => \core_question\local\bank\question_version_status::QUESTION_STATUS_READY,
                        'category' => $catid]);
    }

    public static function get_courses_for_current_user($search) {
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

        list($categories, $coursesmaagar, $activities) = \community_oer\main_oer::get_main_structure_elements();

        $result = array();
        $flag = true;
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

                $tmpcourse->course_show = ($flag) ? true : false;

                $flag = false;
                $result[] = $tmpcourse;
            }
        }

        return $result;
    }

    public static function get_quiz_categories_by_course($courseid, $search = '') {
        global $DB;

        $result = [];
        $categories = [];

        $sql = "SELECT cm.*, q.name as quizname
                    FROM {course_modules} cm
                    LEFT JOIN {modules} m ON (cm.module = m.id)
                    LEFT JOIN {quiz} q ON (cm.instance = q.id)
                    WHERE m.name = 'quiz' AND cm.course = ?";
        $activities = $DB->get_records_sql($sql, array($courseid));

        // Category questions.
        foreach ($activities as $cm) {
            $context = \context_module::instance($cm->id);

            $tree = [];
            self::category_tree($tree, $context->id, $search);

            foreach ($tree as $key => $item) {
                $tree[$key]->quizname = $cm->quizname;
            }

            $categories = array_merge($categories, $tree);
        }

        foreach ($categories as $category) {
            $result[] = [
                    'catid' => $category->id,
                    'catname' => $category->quizname,
                    'qtotal' => $category->qtotal,
                    'level' => $category->level
            ];
        }

        return $result;
    }

    public static function get_bank_categories_by_course($courseid, $search = '') {

        $result = [];
        $context = \context_course::instance($courseid);

        $categories = [];
        self::category_tree($categories, $context->id, $search);

        foreach ($categories as $category) {
            $result[] = [
                    'catid' => $category->id,
                    'catname' => $category->name,
                    'qtotal' => $category->qtotal,
                    'level' => $category->level
            ];
        }

        return $result;
    }

    public static function get_questions_by_category($catid, $search = '') {

        $result = self::query_questions_by_category($catid, $search);
        return self::prepare_questions($result);
    }
}
