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
 * Plugin general functions are defined here.
 *
 * @package     quiz_competencyoverview
 * @copyright   2018 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/report/reportlib.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once($CFG->dirroot . '/mod/quiz/report/competencyoverview/classes/questionsoverview/report.php');
require_once($CFG->dirroot . '/mod/quiz/report/competencyoverview/classes/question_competency.php');
require_once($CFG->dirroot . '/mod/quiz/report/competencyoverview/report.php');

/**
 * Get user courses
 * @return array
 */
function quiz_competencyoverview_get_courses() {
    global $DB, $USER, $PAGE;

    $mycourses = enrol_get_my_courses('*', 'id DESC');

    // Sort courses by last access of current user.
    $lastaccesscourses = $DB->get_records('user_lastaccess', array('userid' => $USER->id), 'timeaccess DESC');

    foreach ($lastaccesscourses as $c) {
        if (isset($mycourses[$c->courseid])) {
            $mycourses[$c->courseid]->lastaccess = $c->timeaccess;
        }
    }

    // Sort by user's lastaccess to course.
    usort($mycourses, function ($a, $b) {
        return $b->lastaccess - $a->lastaccess;
    });

    $result = array();
    foreach ($mycourses as $course) {
        if (!has_capability('moodle/course:update', context_course::instance($course->id), $USER->id)) {
            continue;
        }
        $tmp = array();
        $tmp['id'] = $course->id;
        $tmp['fullname'] = $course->fullname;
        $tmp['shortname'] = $course->shortname;
        $result[] = $tmp;
    }

    return $result;
}

/**
 * Get user activities
 * @return array
 */
function quiz_competencyoverview_get_activities($courseid) {
    global $DB, $USER;

    $myactivities = enrol_get_my_courses('*', 'id DESC');

    // Sort activities by last access of current user.
    $lastaccessactivities = $DB->get_records('user_lastaccess', array('userid' => $USER->id), 'timeaccess DESC');

    foreach ($lastaccessactivities as $c) {
        if (isset($myactivities[$c->courseid])) {
            $myactivities[$c->courseid]->lastaccess = $c->timeaccess;
        }
    }

    // Sort by user's lastaccess to course.
    usort($myactivities, function ($a, $b) {
        return $b->lastaccess - $a->lastaccess;
    });

    $result = array();
    foreach ($myactivities as $course) {
        if (!has_capability('moodle/course:update', context_course::instance($course->id), $USER->id)) {
            continue;
        }
        $tmp = array();
        $tmp['id'] = $course->id;
        $tmp['fullname'] = $course->fullname;
        $tmp['shortname'] = $course->shortname;
        $result[] = $tmp;
    }

    return $result;
}

/**
 * Get user items
 * @return array
 */
function quiz_competencyoverview_get_items($skills) {
    global $OUTPUT, $PAGE, $DB, $CFG;

    $numgroups = 3;
    $numitemspergroup = 3;

    $allgroups = [];
    $PAGE->set_context(context_system::instance());
    require_sesskey();

    $obj = new \community_oer\activity_oer;
    $activities = $obj->query()->get();
    $founded = [];
    $cmdata = [];
    $qdata = [];
    foreach ($activities as $key => $activity) {
        if (!empty($activity->compmcompetencyids)) {
            $cmdata = explode(',', $activity->compmcompetencyids);
        }
        if (!empty($activity->compqcompetencyids)) {
            $qdata = explode(',', $activity->compqcompetencyids);
        }

        foreach ($skills as $skill) {
            if (in_array($skill, $cmdata) || in_array($skill, $qdata)) {
                $founded[] = $activity;
                break;
            }
        }
    }

    $sorteditems = quiz_competencyoverview_group_items($founded);

    $groupeditems = [];
    $groupcounter = 0;

    foreach ($skills as $keyskill => $skillid) {
        $group = [];
        $itemcounter = 0;

        // Sort by skill, but only if both $a and $b have the skill.
        uasort($sorteditems, function ($a, $b) use ($skillid) {
            $compScoreA = $a->compscore[$skillid] ?? 0; // Default to 0 if the skill doesn't exist.
            $compScoreB = $b->compscore[$skillid] ?? 0;

            return $compScoreB <=> $compScoreA; // Sort in descending order.
        });

        foreach ($sorteditems as $keysa => $sa) {
            if (array_key_exists($skillid, $sa->compscore)) {
                $group[] = $sa;
                unset($sorteditems[$keysa]);
                $itemcounter++;
                if ($itemcounter == $numitemspergroup) {
                    break;
                }
            }
        }
        $groupeditems[$skillid] = $group;
        $groupcounter++;
        if ($groupcounter == $numgroups) {
            break;
        }
    }

    // Groupeditems.
    foreach ($groupeditems as $key => $group) {
        $items = [];
        foreach ($group as $item) {
            $block = [];

            $compitem = quiz_competencyoverview_get_item($item->cmid);

            $data = new stdClass;
            $data->activity_id = $item->cmid;
            $data->course_id = $item->courseid;
            $data->mod_name = $item->mod_name;
            $data->item = $compitem;

            // Wrap item, add choose button.
            $wrappeditem = $OUTPUT->render_from_template('quiz_competencyoverview/itemwrapper', $data);

            $block['id'] = $item->cmid;
            $block['item'] = $wrappeditem;

            $items[] = $block;
        }

        // Skillname.
        $skillname = $DB->get_record('competency', ['id' => $key]);
        $allgroups[] = [$skillname->shortname, $items];
    }

    return $allgroups;
}

/**
 * Get questions by competency table
 * @return string
 */
function quiz_competencyoverview_get_questions_by_competency_table($compid, $cmid, $quizid, $courseid, $qset, $lastaccess, $actualusers) {
    global $OUTPUT, $PAGE, $DB, $CFG;

    $PAGE->set_context(context_system::instance());

    $embedhtml = '';

    if (!$cm = get_coursemodule_from_id('quiz', $cmid)) {
        throw new \moodle_exception('invalidcoursemodule');
    }
    if (!$course = $DB->get_record('course', array('id' => $courseid))) {
        throw new \moodle_exception('coursemisconf');
    }
    if (!$quiz = $DB->get_record('quiz', array('id' => $quizid))) {
        throw new \moodle_exception('invalidcoursemodule');
    }

    $report = new quiz_questionsoverview_report();
    $report->lastaccess = $lastaccess;
    $report->actualusers = $actualusers;
    $embedhtml = $report->get_html_table($compid, $quiz, $cm, $course, $qset);

    return $embedhtml;
}

/**
 * Get user items
 */
function quiz_competencyoverview_group_items(array $items) {
    global $DB;

    $goupeditems = [];

    foreach ($items as $item) {

        $cm = $DB->get_record('course_modules', array('id' => $item->cmid));

        // Base score of activity.
        $competency = core_competency\api::list_course_module_competencies_in_course_module($item->cmid);
        $compscore = [];
        foreach ($competency as $key => $comp) {
            $compscore[$comp->get('competencyid')] = 1;
        }

        // For quiz type add quiestions score.
        $questions = [];
        $qcompetencies = [];
        if ($item->mod_type == 'quiz' || $item->mod_type == 'activequiz') {
            $quiz = $DB->get_record('quiz', array('id' => $cm->instance));
            $questions = quiz_report_get_significant_questions($quiz);
            $qcompetencies = quiz_competencyoverview_get_competencies_by_questions($questions);

            foreach ($qcompetencies as $key => $qcomp) {
                $compqscore = count($qcomp);
                if (isset($compscore[$key])) {
                    $compscore[$key] = $compscore[$key] + $compqscore;
                } else {
                    $compscore[$key] = $compqscore;
                }
            }
        }

        $item->compscore = $compscore;
        $goupeditems[] = $item;
    }

    return $goupeditems;
}

function quiz_competencyoverview_get_item($cmid, $show = true) {
    global $OUTPUT, $PAGE, $CFG;

    $PAGE->set_context(context_system::instance());
    require_sesskey();

    $items = '';

    if ($cmid) {
        $activity = new \community_oer\activity_oer;
        if ($element = $activity->single_cmid_render_data($cmid)) {
            // Activity data.
            $data = [];
            $element->showbutton = $show;
            $data['blocks'][0] = $element;
            $html = $OUTPUT->render_from_template('quiz_competencyoverview/blockoercatalog', $data);
            $items = $html;
        }
    }

    return $items;
}

function quiz_competencyoverview_get_targetsections($currentcourseid) {
    global $DB, $USER;

    $mycourses = enrol_get_my_courses('*', 'id DESC');

    // Sort courses by last access of current user.
    $lastaccesscourses = $DB->get_records('user_lastaccess', array('userid' => $USER->id), 'timeaccess DESC');

    foreach ($lastaccesscourses as $c) {
        if (isset($targetsections[$c->courseid])) {
            $targetsections[$c->courseid]->lastaccess = $c->timeaccess;
        }
    }

    // Sort by user's lastaccess to course.
    usort($targetsections, function ($a, $b) {
        return $b->lastaccess - $a->lastaccess;
    });

    $result = array();
    foreach ($targetsections as $course) {
        if (!has_capability('moodle/course:update', context_course::instance($course->id), $USER->id)) {
            continue;
        }
        $tmp = array();
        $tmp['id'] = $course->id;
        $tmp['fullname'] = $course->fullname;
        $tmp['shortname'] = $course->shortname;
        $result[] = $tmp;
    }

    return $result;
}

function quiz_competencyoverview_get_competencies_by_questions($questions) {
    global $DB;

    $competencies = [];
    foreach ($questions as $question) {
        $competency = list_question_competencies_in_question($question->id);

        if (!$competency) {
            continue;
        }

        foreach ($competency as $comp) {
            if (isset($competencies[$comp->get('competencyid')])) {
                array_push($competencies[$comp->get('competencyid')], $comp->get('qid'));
            } else {
                $competencies[$comp->get('competencyid')] = [$comp->get('qid')];
            }
        }
    }

    return $competencies;
}

function quiz_competencyoverview_message_to_students($metadata, $newactivityid) {
    global $DB, $CFG, $USER, $PAGE;

    $PAGE->set_context(context_system::instance());

    $metadata = (object) $metadata;

    $conditions = (array) $metadata->conditions;
    $conditions['activityid'] = $newactivityid;
    $newaa = $DB->get_record('quiz_competencyoverview_aa', $conditions);

    if (!$newaa) {
        $newaa = $DB->insert_record('quiz_competencyoverview_aa', $conditions);
    }
    $availabilityjson = $DB->get_field(
        'course_modules',
        'availability',
        ['id' => $newactivityid]
    );

    $availability = json_decode($availabilityjson);

    if (!empty($availability->c)) {
        $rules = $availability->c;
    } else {
        $rules = [];
    }

    // Send message.
    foreach (explode(',', $metadata->students) as $key => $userto) {

        // Get email for userto.
        $user = $DB->get_record('user', ['id' => $userto]);
        // Prepare rules for restrict for each user.
        $cond = \availability_profile\condition::get_json(false, 'email', 'isequalto', $user->email);

        if (!in_array($cond, $rules)) {
            $rules[] = $cond;
        }

        $sql = '
        SELECT *
        FROM {course_modules} cm
        LEFT JOIN {modules} m ON(cm.module = m.id)
        WHERE cm.id = ?
        ';
        $module = $DB->get_record_sql($sql, array($newactivityid));

        $a = new stdClass;
        $a->link = $CFG->wwwroot . '/mod/' . $module->name . '/view.php?id=' . $newactivityid;

        $fullmessage = get_string('fullmessagehtml_to_student', 'quiz_competencyoverview', $a) . '<br>' . $metadata->message;
        $smallmessage = get_string('shortmessage_to_student', 'quiz_competencyoverview');

        $admins = get_admins();
        $userfrom = array_shift($admins);

        $usertoobj = $DB->get_record('user', ['id' => $userto]);

        $messageid = message_post_message($userfrom, $usertoobj, $fullmessage, FORMAT_HTML);

    }

    // Restrict access by email.
    $restriction = \core_availability\tree::get_root_json(
        $rules,
        '|',
        false
    );
    $DB->set_field(
        'course_modules',
        'availability',
        json_encode($restriction),
        ['id' => $newactivityid]
    );

    $cm = $DB->get_record('course_modules', ['id' => $newactivityid]);
    rebuild_course_cache($cm->course, true);

    return;
}

/**
 * Get user courses
 * @return array
 */
function quiz_competencyoverview_get_init_params($quizid, $cmid, $courseid, $lastaccess) {
    global $DB, $USER, $PAGE;

    $PAGE->set_context(context_system::instance());

    if (!$cm = get_coursemodule_from_id('quiz', $cmid)) {
        throw new \moodle_exception('invalidcoursemodule');
    }
    if (!$course = $DB->get_record('course', array('id' => $courseid))) {
        throw new \moodle_exception('coursemisconf');
    }
    if (!$quiz = $DB->get_record('quiz', array('id' => $quizid))) {
        throw new \moodle_exception('invalidcoursemodule');
    }

    $report = new quiz_competencyoverview_report();
    $report->lastaccess = $lastaccess;
    $params = $report->get_init_params_report($quiz, $cm, $course);

    return $params;
}

/**
 * List all the competencies linked to a question.
 *
 * @param int $qid The question ID.
 * @return array[competency] Array of competency records.
 */
function list_question_competencies_in_question($qid) {
    // static::require_enabled();

    // TODO: add proper security checks to see if user have access view competencies.
    /*
    $cm = $cmorid;
    if (!is_object($cmorid)) {
    $cm = get_coursemodule_from_id('', $cmorid, 0, true, MUST_EXIST);
    }

    // Check the user have access to the course module.
    self::validate_course_module($cm);
    $context = context_module::instance($cm->id);

    $capabilities = array('moodle/competency:coursecompetencyview', 'moodle/competency:coursecompetencymanage');
    if (!has_any_capability($capabilities, $context)) {
    throw new required_capability_exception($context, 'moodle/competency:coursecompetencyview', 'nopermissions', '');
    }
     */
    $result = array();

    //$cmclist = course_module_competency::list_course_module_competencies($cm->id);
    $qclist = quiz_competencyoverview\question_competency::list_question_competencies($qid);
    foreach ($qclist as $id => $cmc) {
        //array_push($result, $cmc);
        $result[] = $cmc;
    }

    return $result;
}

/**
 * Remove a competency from this question.
 *
 * @param int $qid The id of the question
 * @param int $competencyid The id of the competency
 * @return bool
 */
function remove_competency_from_question($qid, $competencyid) {
    // static::require_enabled();
    // quiz_competencyoverview\question_competency

    // TODO: add proper security checks to see if user have access view competencies.
    /*
    $cm = $cmorid;
    if (!is_object($cmorid)) {
    $cm = get_coursemodule_from_id('', $cmorid, 0, true, MUST_EXIST);
    }
    // Check the user have access to the course module.
    self::validate_course_module($cm);

    // First we do a permissions check.
    $context = context_module::instance($cm->id);

    require_capability('moodle/competency:coursecompetencymanage', $context);
     */

    // TODO: following code looks redundant?
    //$record = new stdClass();
    //$record->qid = $qid;
    //$record->competencyid = $competencyid;

    //$competency = new competency($competencyid);
    $exists = quiz_competencyoverview\question_competency::get_record(array('qid' => $qid, 'competencyid' => $competencyid));
    if ($exists) {
        return $exists->delete();
    }
    return false;
}

/**
 * Add a competency to this question.
 *
 * @param int $qid The id of the question
 * @param int $competencyid The id of the competency
 * @return bool
 */
function add_competency_to_question($qid, $competencyid) {
    // static::require_enabled(ß);

    // TODO: add proper security checks to see if user have access view competencies.
    /*
    $cm = $cmorid;
    if (!is_object($cmorid)) {
    $cm = get_coursemodule_from_id('', $cmorid, 0, true, MUST_EXIST);
    }

    // Check the user have access to the course module.
    self::validate_course_module($cm);

    // First we do a permissions check.
    $context = context_module::instance($cm->id);

    require_capability('moodle/competency:coursecompetencymanage', $context);
     */

    // TODO: fix this, get course from qid.
    // Check that the competency belongs to the course.
    //$exists = question_competency::get_records(array('qid' => $qid, 'competencyid' => $competencyid));
    //if (!$exists) {
    //    throw new coding_exception('Cannot add a competency to a question if it does not belong to the course');
    //}

    $record = new stdClass();
    $record->qid = $qid;
    $record->competencyid = $competencyid;

    $questioncompetency = new quiz_competencyoverview\question_competency();
    $exists = $questioncompetency::get_records(array('qid' => $qid, 'competencyid' => $competencyid));
    if (!$exists) {
        $questioncompetency->from_record($record);
        if ($questioncompetency->create()) {
            return true;
        }
    }
    return false;
}

/**
 * Update ruleoutcome value for a question competency.
 *
 * @param int|quiz_competencyoverview\question_competency $questioncompetencyorid The question_competency, or its ID.
 * @param int $ruleoutcome The value of ruleoutcome.
 * @return bool True on success.
 */
function set_question_competency_ruleoutcome($questioncompetencyorid, $ruleoutcome) {
    // static::require_enabled();
    $questioncompetency = $questioncompetencyorid;
    if (!is_object($questioncompetency)) {
        $questioncompetency = new quiz_competencyoverview\question_competency($questioncompetencyorid);
    }

    /*
    $cm = get_coursemodule_from_id('', $questioncompetency->get('qid'), 0, true, MUST_EXIST);

    self::validate_course_module($cm);
    $context = context_module::instance($cm->id);

    require_capability('moodle/competency:coursecompetencymanage', $context);
     */
    $questioncompetency->set('ruleoutcome', $ruleoutcome);
    return $questioncompetency->update();
}
