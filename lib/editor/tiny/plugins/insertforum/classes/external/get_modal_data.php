<?php
namespace tiny_insertforum\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_multiple_structure;
use core_external\external_value;
use \context_system;
use \context_course;
use \required_capability_exception;
use core\exception\coding_exception;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/group/lib.php');

class get_modal_data extends external_api {

    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID'),
        ]);
    }

    public static function execute($courseid) {
        global $COURSE;

        function get_my_forums_by_contextid() {
            $forums = [];
            global $DB, $PAGE;

            $rawforums = $DB->get_records_sql("SELECT cm.id AS id,
                                             h.name
                                        FROM {course_modules} cm,
                                             {course_sections} cw,
                                             {modules} md,
                                             {forum} h
                                       WHERE cm.course = ?
                                         AND cm.instance = h.id
                                         AND cm.section = cw.id
                                         AND md.name = 'forum'
                                         AND md.id = cm.module
                                     ", array($PAGE->course->id));

            $modinfo = get_fast_modinfo($PAGE->course, NULL);
            if (empty($modinfo->instances['forum'])) {
                $forums = $rawforums;
            } else {
                // Lets try to order these bad boys
                foreach ($modinfo->instances['forum'] as $cm) {
                    if (!$cm->uservisible || !isset($rawforums[$cm->id])) {
                        continue; // Not visible or not found
                    }
                    if (!empty($cm->extra)) {
                        $rawforums[$cm->id]->extra = $cm->extra;
                    }
                    $forums[] = $rawforums[$cm->id];
                }
            }
            return $forums;
        }

        $params = self::validate_parameters(self::execute_parameters(), ['courseid' => $courseid]);

        $context = context_course::instance($courseid);
        self::validate_context($context);

        require_capability('moodle/course:manageactivities', $context);

        $COURSE = get_course($courseid); // Update global COURSE

        $list_forums = [];
        $insertforums = get_my_forums_by_contextid($context->id); // May need contextid as param
        foreach ($insertforums as $forum) {
            $list_forums[] = ['id' => $forum->id, 'text' => $forum->name];
        }

        $list_groups = [['id' => 0, 'name' => get_string('nogroups', 'tiny_insertforum')]];
        $course_groups = groups_get_all_groups($courseid);
        foreach ($course_groups as $group) {
            $list_groups[] = ['id' => $group->id, 'name' => $group->name];
        }

        $list_groupings = [['id' => 0, 'name' => get_string('nogroupings', 'tiny_insertforum')]];
        $course_groupings = groups_get_all_groupings($courseid);
        foreach ($course_groupings as $grouping) {
            $list_groupings[] = ['id' => $grouping->id, 'name' => $grouping->name];
        }

        return [
            'forums' => $list_forums,
            'groups' => $list_groups,
            'groupings' => $list_groupings,
        ];
    }

    public static function execute_returns() {
        return new external_single_structure([
            'forums' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'Forum ID'),
                    'text' => new external_value(PARAM_TEXT, 'Forum name')
                ])
            ),
            'groups' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'Group ID'),
                    'name' => new external_value(PARAM_TEXT, 'Group name')
                ])
            ),
            'groupings' => new external_multiple_structure(
                new external_single_structure([
                    'id' => new external_value(PARAM_INT, 'Grouping ID'),
                    'name' => new external_value(PARAM_TEXT, 'Grouping name')
                ])
            ),
        ]);
    }
}
