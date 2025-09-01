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
 * @package    community_sharesequence
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace community_sharesequence;

defined('MOODLE_INTERNAL') || die();

class funcs {

    // Old function "community_sharesequense_if_section_not_empty".
    private static function if_section_not_empty($course, $sectionnum) {

        $sec = false;
        $act = false;

        $modinfo = get_fast_modinfo($course);

        $current = $modinfo->get_section_info($sectionnum)->getIterator()->getArrayCopy();
        if (!empty($current['sequence'])) {
            $sec = true;
        }

        if (course_get_format($course)->get_format() == 'flexsections') {
            foreach ($modinfo->get_section_info_all() as $item) {
                $data = $item->getIterator()->getArrayCopy();

                if ($data['parent'] == $sectionnum && $data['visible'] == 1) {
                    $act = true;
                    break;
                }
            }
        } else {
            $act = false;
        }

        return $sec || $act;
    }

    // Old function "community_sharesequense_get_main_sections".
    public static function get_main_sections($course) {

        $result = [];
        $modinfo = get_fast_modinfo($course);
        foreach ($modinfo->get_section_info_all() as $item) {
            $data = $item->getIterator()->getArrayCopy();

            if (!isset($data['parent'])) {
                $data['parent'] = 0;
            }

            if ($data['parent'] == 0 && $data['visible'] == 1 && $data['sectionnum'] != 0) {
                if (self::if_section_not_empty($course, $data['sectionnum'])) {
                    $data['name'] = course_get_format($course)->get_section_name($data['sectionnum']);
                    $result[] = $data;
                }
            }
        }

        return $result;
    }

    // Old function "community_sharesequense_get_structure_section".
    public static function get_structure_section($sectionid) {
        global $DB, $OUTPUT;

        $section = $DB->get_record('course_sections', ['id' => $sectionid]);
        try {
            $course = get_course($section->course);
        } catch (\Exception $e) {
            return false;
        }

        $modinfo = get_fast_modinfo($course);

        $activities = [];

        // Labels.
        foreach ($DB->get_records('course_modules',
                ['course' => $section->course, 'section' => $section->id, 'deletioninprogress' => 0]) as $cm) {
            try {
                $act = $modinfo->get_cm($cm->id);
                if ($act->modname == 'label') {

                    $name = $act->name;
                    if ($cm->visible != 1) {
                        $name .= ' ('.get_string('hidden', 'community_sharesequence').')';
                    }

                    $activities[] = [
                            'cmid' => $act->id,
                            'name' => $name,
                            'modname' => $act->modname,
                            'instance' => $act->instance,
                            'modicon' => $OUTPUT->pix_icon('icon', '', $act->modname, array('class' => ''))
                    ];
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Other types.
        foreach ($DB->get_records('course_modules',
                ['course' => $section->course, 'section' => $section->id, 'deletioninprogress' => 0]) as $cm) {
            try {
                $act = $modinfo->get_cm($cm->id);
                if ($act->modname != 'label') {

                    $name = $act->name;
                    if ($cm->visible != 1) {
                        $name .= ' ('.get_string('hidden', 'community_sharesequence').')';
                    }

                    $activities[] = [
                            'cmid' => $act->id,
                            'name' => $name,
                            'modname' => $act->modname,
                            'instance' => $act->instance,
                            'modicon' => $OUTPUT->pix_icon('icon', '', $act->modname, array('class' => ''))
                    ];
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        $sections = [];
        foreach ($modinfo->get_section_info_all() as $item) {
            $data = $item->getIterator()->getArrayCopy();

            if ($section->section != 0 && $data['visible'] == 1 && $data['parent'] == $section->section) {
                if (self::if_section_not_empty($course, $data['sectionnum'])) {
                    $data['name'] = course_get_format($course)->get_section_name($data['sectionnum']);
                    $sections[] = $data;
                }
            }
        }

        return [$activities, $sections];
    }

    // Old function "community_sharesequense_create_sub_section".
    public static function create_sub_section($sectionid, $sectionname) {
        global $DB, $CFG;

        require_once($CFG->dirroot . '/course/format/lib.php');

        $section = $DB->get_record('course_sections', ['id' => $sectionid]);
        try {
            $course = get_course($section->course);
        } catch (\Exception $e) {
            return false;
        }

        $sections = get_fast_modinfo($course)->get_section_info_all();
        $sectionnums = array_keys($sections);
        $sectionnum = array_pop($sectionnums) + 1;
        course_create_sections_if_missing($course, $sectionnum);

        $newsection = $DB->get_record('course_sections', ['course' => $course->id, 'section' => $sectionnum]);

        // Change name and visible.
        $DB->update_record('course_sections', ['id' => $newsection->id, 'name' => $sectionname, 'visible' => 0]);

        // Set parent to new section.
        $DB->insert_record('course_format_options', [
                'courseid' => $course->id,
                'format' => 'flexsections',
                'sectionid' => $newsection->id,
                'name' => 'parent',
                'value' => $section->section
        ]);

        $DB->insert_record('course_format_options', [
                'courseid' => $course->id,
                'format' => 'flexsections',
                'sectionid' => $newsection->id,
                'name' => 'visibleold',
                'value' => 1
        ]);

        $DB->insert_record('course_format_options', [
                'courseid' => $course->id,
                'format' => 'flexsections',
                'sectionid' => $newsection->id,
                'name' => 'collapsed',
                'value' => 0
        ]);

        rebuild_course_cache($course->id);

        return $newsection;
    }

    // Old function "community_sharesequense_add_task".
    public static function add_task($type, $userid, $sectionid, $activities, $metadata = []) {
        global $DB;

        $obj = new \stdClass();
        $obj->type = $type;
        $obj->userid = $userid;
        $obj->sectionid = $sectionid;
        $obj->activities = json_encode($activities);
        $obj->metadata = json_encode($metadata);
        $obj->status = 0;
        $obj->timemodified = time();

        return $DB->insert_record('community_sharesequence_task', $obj);
    }

    // Old function "community_sharesequense_get_notpresent_availability".
    public static function get_notpresent_availability($object, &$arrcmids) {
        global $DB;

        $obj = clone($object);

        if (isset($obj->op) && isset($obj->c)) {
            foreach ($obj->c as $key => $rule) {
                self::get_notpresent_availability($rule, $arrcmids);
            }
        }

        if (isset($obj->type)) {
            switch ($obj->type) {
                case 'completion':
                    $arrcmids[] = $obj->cm;

                    break;
                case 'grade':

                    if ($gradeold = $DB->get_record('grade_items', ['id' => $obj->id])) {
                        $module = $DB->get_record('modules', array('name' => $gradeold->itemmodule));
                        $cm = $DB->get_record('course_modules', [
                                'module' => $module->id,
                                'course' => $gradeold->courseid,
                                'instance' => $gradeold->iteminstance,
                        ]);

                        $arrcmids[] = $cm->id;
                    }

                    break;
                case 'sequence':
                    $arrcmids[] = $obj->cm;

                    break;
                case 'quizquestion':
                    $quiz = $DB->get_record('quiz', ['id' => $obj->quizid]);
                    $module = $DB->get_record('modules', array('name' => 'quiz'));
                    $cm = $DB->get_record('course_modules', [
                            'module' => $module->id,
                            'course' => $quiz->course,
                            'instance' => $obj->quizid,
                    ]);

                    $arrcmids[] = $cm->id;

                    break;
            }
        }

        return '';
    }
}
