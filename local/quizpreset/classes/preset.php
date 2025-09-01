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
 * Event observers supported by this module
 *
 * @package    local_quizpreset
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/quizpreset/classes/custom_types.php');
require_once($CFG->dirroot . '/local/quizpreset/classes/custom_types_default.php');

class preset {

    public static $instancename;

    public function __construct() {
    }

    public static function init() {
        global $CFG;

        // Set type of domain.
        if (!empty($CFG->instancename)
            && in_array($CFG->instancename, array('physics', 'chemistry', 'biology'))) {
                self::$instancename = $CFG->instancename;
        } else {
                self::$instancename = 'physics';
        }
    }

    private static function if_user_admin_or_teacher() {
        global $USER, $DB;

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
            return true;
        }

        // Check if teacher.
        $roles = $DB->get_records_sql("SELECT id FROM {role} WHERE shortname IN ('editingteacher', 'teacher', 'juniorteacher') ");

        $rolesids = array();
        foreach ($roles as $item) {
            $rolesids[] = $item->id;
        }

        if (isset($USER->access['ra']) && !empty($USER->access['ra'])) {
            foreach ($USER->access['ra'] as $item) {
                foreach ($item as $role) {
                    if (in_array($role, $rolesids)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Get buttons bar.
     *
     */
    public static function get_buttonsbar($cmid) {
        global $DB, $USER;

        self::init();
        $context = context_module::instance($cmid);

        // Number of attempts.
        $instance = $DB->get_record('course_modules', array('id' => $cmid));

        $query = "
						SELECT qa.*
						FROM {quiz_attempts} qa
						LEFT JOIN {user} u ON(u.id = qa.userid)
						JOIN {user_enrolments} ue_d ON ue_d.userid = u.id
						JOIN {enrol} e_d ON (e_d.id = ue_d.enrolid AND e_d.courseid = ?)
						INNER JOIN (
							SELECT userid, MAX(attempt) AS max_attempt
							  FROM {quiz_attempts}
							  WHERE quiz = ?
							  GROUP BY userid
						) a ON(a.userid = qa.userid AND a.max_attempt = qa.attempt)

						WHERE u.suspended = 0 AND qa.preview = 0 AND ue_d.status = 0 AND qa.quiz = ?
                        AND (
                            (ue_d.timestart = '0' AND ue_d.timeend = '0') OR
                            (ue_d.timestart = '0' AND ue_d.timeend > UNIX_TIMESTAMP()) OR
                            (ue_d.timeend = '0' AND ue_d.timestart < UNIX_TIMESTAMP()) OR
                            (ue_d.timeend > UNIX_TIMESTAMP() AND ue_d.timestart < UNIX_TIMESTAMP())
                            )
                        AND qa.state IN('finished', 'inprogress')
					";

        $params = [$instance->course, $instance->instance, $instance->instance];
        $numattempts = count($DB->get_records_sql($query, $params));

        $links = array();
        if (self::if_user_admin_or_teacher() && has_capability('mod/quiz:manage', $context)) {

            //$url1 = new moodle_url('/mod/quiz/startattempt.php', array('cmid' => $cmid, 'sesskey' => sesskey()));
            //$links[] = array(
            //        'url' => $url1->out(false),
            //        'title' => get_string('preview', 'local_quizpreset'),
            //);

            $url2 = new moodle_url('/mod/quiz/edit.php', array('cmid' => $cmid));

            $tmp = array(
                    'url' => $url2->out(false),
                    'title' => get_string('edit', 'local_quizpreset'),
            );

            if (class_exists('\community_oer\main_oer')) {
                $tmp['disable'] = \community_oer\main_oer::if_activity_in_research_mode($cmid);
            }

            $links[] = $tmp;

            // Number of attempts.
            if ($numattempts) {
                $url3 = new moodle_url('/mod/quiz/report.php', array('id' => $cmid, 'mode' => 'advancedoverview'));
                $links[] = array(
                        'url' => $url3->out(false),
                        'title' => get_string('numattempt', 'local_quizpreset', $numattempts),
                );
            }

            $url4 = new moodle_url('/mod/quiz/report.php', array('id' => $cmid, 'mode' => 'grading'));
            $links[] = array(
                    'url' => $url4->out(false),
                    'title' => get_string('manuallymarking', 'local_quizpreset'),
            );

            $arr5 = array('update' => $cmid, 'return' => 1);
            if (self::$instancename == 'chemistry') {
                $arr5['viewall'] = 1;
            }

            $url5 = new moodle_url('/course/modedit.php', $arr5);
            $links[] = array(
                    'url' => $url5->out(false),
                    'title' => get_string('settings', 'local_quizpreset'),
                    'disable' => false
            );

        } else {

            // Check if teachercolleague.
            if ($teachercolleague = $DB->get_record_sql("SELECT id FROM {role} WHERE shortname='teachercolleague'")) {

                $flag = false;
                if (isset($USER->access['ra']) && !empty($USER->access['ra'])) {
                    foreach ($USER->access['ra'] as $item) {
                        foreach ($item as $role) {
                            if ($role == $teachercolleague->id) {
                                $flag = true;
                            }
                        }
                    }
                }

                if ($flag) {
                    $url1 = new moodle_url('/mod/quiz/startattempt.php', array('cmid' => $cmid, 'sesskey' => sesskey()));
                    $links[] = array(
                            'url' => $url1->out(false),
                            'title' => get_string('preview', 'local_quizpreset'),
                    );

                    // Number of attempts.
                    if ($numattempts) {
                        $url3 = new moodle_url('/mod/quiz/report.php', array('id' => $cmid, 'mode' => 'advancedoverview'));
                        $links[] = array(
                                'url' => $url3->out(false),
                                'title' => get_string('numattempt', 'local_quizpreset', $numattempts),
                        );
                    }
                }
            }
        }

        return json_encode(array('links' => $links));
    }

    /**
     * Get page data.
     *
     */
    public static function get_pagedata($cmid, $defaulttype, $viewall, $pagestate, $urlparams) {

        self::init();
        $object = new \custom_types($cmid, $defaulttype, $viewall, $pagestate, $urlparams);

        $result = array();

        if ($pagestate != 'new' || $cmid != 0) {
            $context = context_module::instance($cmid);
            if (self::if_user_admin_or_teacher() && has_capability('mod/quiz:manage', $context)) {
                $isstudent = 0;
            } else {
                $isstudent = 1;
            }
        } else {
            $isstudent = 0;
        }

        $result['details'] = $object->get_details($isstudent);
        $result['expanded'] = $object->get_expanded();
        $result['global'] = $object->get_global_name();
        $result['values'] = $object->get_values();
        $result['selector'] = $object->get_selector($isstudent);

        return json_encode($result);
    }

    /**
     * Save data.
     *
     */
    public static function savedata($cmid, $pagestate, $type, $viewall) {
        global $DB, $USER;

        self::init();
        $result = array();

        $qp = new \stdClass();

        $qp->cmid = $cmid;
        $qp->userid = $USER->id;
        $qp->state = $pagestate;
        $qp->type = $type;
        $qp->viewall = $viewall;
        $qp->status = 0;
        $qp->timemodified = time();

        $DB->insert_record('local_quizpreset', $qp);

        return json_encode($result);
    }

    public static function fill_settings() {
        self::init();

        $config = get_config('local_quizpreset');

        // Was - if (!isset($config->numberoftypes) or $config->numberoftypes == 0).
        if (1) {

            $customtypes = new \custom_types_default(self::$instancename);
            $alltypes = $customtypes->get_types();

            $count = 0;
            foreach ($alltypes as $type) {
                // Collect data from custom_types.
                $customtypes->predefine_setting($type);

                $count++;

                $result = array();
                $result['expanded'] = $customtypes->get_expanded();
                $result['global'] = $customtypes->get_global_name();
                $result['values'] = $customtypes->get_values();

                switch (self::$instancename) {
                    case 'physics':
                        $result['typeDescribe'] = get_string('describe_physics_' . $type, 'local_quizpreset');
                        $result['typeName'] = get_string('name_physics_' . $type, 'local_quizpreset');
                        break;
                    case 'chemistry':
                        $result['typeDescribe'] = get_string('describe_chemistry_' . $type, 'local_quizpreset');
                        $result['typeName'] = get_string('name_chemistry_' . $type, 'local_quizpreset');
                        break;
                    case 'biology':
                        $result['typeDescribe'] = get_string('describe_biology_' . $type, 'local_quizpreset');
                        $result['typeName'] = get_string('name_biology_' . $type, 'local_quizpreset');
                        break;
                }

                $preset = new stdClass();

                $exposuregrades = ($type == 1) ? true : false;

                $preset->functionality = array(
                        'exposure_grades' => $exposuregrades,
                        'view_description' => true
                );

                $preset->sections = $result['expanded'];
                $preset->fields = $result['values'];

                // Fill settings.
                set_config('quiztypename_' . $count, $result['typeName'], 'local_quizpreset');
                set_config('quiztypedescription_' . $count, $result['typeDescribe'], 'local_quizpreset');
                set_config('quizpreset_' . $count, json_encode($preset), 'local_quizpreset');

            }

            set_config('state', 1, 'local_quizpreset');
            set_config('defaulttype', 1, 'local_quizpreset');
            set_config('numberoftypes', $count, 'local_quizpreset');
            set_config('defaultsettings', '', 'local_quizpreset');

        }
    }
}
