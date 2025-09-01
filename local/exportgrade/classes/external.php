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
 * @package local_exportgrade
 * @author Evgeniy Voevodin
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2021 Devlion.co
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/modinfolib.php');
require_once($CFG->libdir . '/../login/lib.php');
require_once(__DIR__ . '/../lib.php');

/**
 * @package local_exportgrade
 * @author Devlion.co
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2020 Devlion.co
 */
class local_exportgrade_external extends external_api {
    private static $idnumberpad = 9;

    private static $response = ['result' => 0,
            'message' => '',
            'subject' => '',
            'learninggroup_id' => '',
            'activities' => [],
            'smsenabled' => false
    ];

    public static function learninggroup_activities_parameters() {
        return new external_function_parameters(
                [
                        'year' => new external_value(PARAM_INT, 'year'),
                        'school_symbol' => new external_value(PARAM_INT, 'school symbol'),
                        'learninggroup_id' => new external_value(PARAM_INT, 'Learning Group'),
                        'teacher_idnumber' => new external_value(PARAM_INT, 'Teacher ID'),
                ]
        );
    }

    public static function learninggroup_activities($year, $school_symbol, $learninggroup_id, $teacher_idnumber) {
        $debug = local_exportgrade_init_debug();
        $params = self::validate_parameters(self::learninggroup_activities_parameters(),
                ['year' => $year, 'school_symbol' => $school_symbol, 'learninggroup_id' => $learninggroup_id,
                        'teacher_idnumber' => $teacher_idnumber]);
        $webservices = local_exportgrade_get_config_webservices();

        if ($debug) {
            mtrace('webservices: ' . count($webservices));
        }
        foreach ($webservices as $webservice) {
            if (!empty($webservice['webserviceurl']) || !empty($webservice['webservicetoken'])) {
                $url = $webservice['webserviceurl'] . "/webservice/rest/server.php";
                $post = [];
                $post['wstoken'] = $webservice['webservicetoken'];
                $post['wsfunction'] = 'local_exportgrades_find_activities';
                $post['moodlewsrestformat'] = 'json';

                $post = array_merge($post, $params);
                try {
                    $curl = new curl($url);
                    $post = http_build_query($post, '', '&');
                    $resp = $curl->post($url, $post);
                    $resp = json_decode($resp);

                    if (!empty($resp->activities)) {
                        $resp->smsenabled = get_config('local_exportgrade', 'smsverification');
                        return $resp;
                    }

                    if (!empty($resp->errorcode)) {
                        if ($debug) {
                            mtrace("learninggroup_activities no courses for idnumber: " . $teacher_idnumber . " school_symbol: " .
                                    $school_symbol . " learninggroup_id:" . $learninggroup_id . " year: " . $year);
                        }
                        $resp = self::$response;
                        $resp['message'] = get_string('noresults', 'local_exportgrade');
                    }
                } catch (\Exception $e) {
                    if ($debug) {
                        mtrace('error to get token: ' . print_r($e->getMessage()) . " " . print_r($resp, 1));
                    }
                    $resp = self::$response;
                }
            } else {
                if ($debug) {
                    mtrace('webservice is not defined: ' . print_r($webservice, 1));
                }
            }
        }

        if (!empty($resp->result) && !empty($resp->activities) && get_config('local_exportgrade', 'smsverification')) {
            self::sendvalidationsms($learninggroup_id, $resp->user);
            $resp->smsenabled = 1;
        }

        if (empty($resp->activities)) {
            $resp = self::$response;
            $resp['message'] = get_string('noresults', 'local_exportgrade');
        }

        return $resp;
    }

    public static function learninggroup_activities_returns() {
        return new external_single_structure(
                [
                        'result' => new external_value(PARAM_BOOL, 'Result'),
                        'message' => new external_value(PARAM_RAW, 'Message'),
                        'subject' => new external_value(PARAM_RAW, 'Subject'),
                        'smsenabled' => new external_value(PARAM_BOOL, 'Sms Enabled'),
                        'learninggroup_id' => new external_value(PARAM_RAW, 'Learninggroup Id'),
                        'activities' => new external_multiple_structure(
                                new external_single_structure (
                                        [
                                                'cmid' => new external_value(PARAM_INT, 'Message'),
                                                'fullname' => new external_value(PARAM_RAW, 'Message')
                                        ]))
                ]
        );
    }

    public static function find_activities_parameters() {
        return new external_function_parameters(
                [
                        'year' => new external_value(PARAM_INT, 'year'),
                        'school_symbol' => new external_value(PARAM_INT, 'school symbol'),
                        'learninggroup_id' => new external_value(PARAM_INT, 'Learning Group'),
                        'teacher_idnumber' => new external_value(PARAM_INT, 'Teacher ID'),
                ]
        );
    }

    public static function find_activities($year, $school_symbol, $learninggroup_id, $teacher_idnumber) {
        global $CFG;
        $debug = local_exportgrade_init_debug();
        $params = self::validate_parameters(self::find_activities_parameters(),
                ['year' => $year, 'school_symbol' => $school_symbol, 'learninggroup_id' => $learninggroup_id,
                        'teacher_idnumber' => $teacher_idnumber]);
        $year = $params['year'];
        $school_symbol = $params['school_symbol'];
        $learninggroup_id = $params['learninggroup_id'];
        $teacher_idnumber = $params['teacher_idnumber'];

        $activities = [];
        if (!$user = self::finduser($teacher_idnumber)) {
            $return = self::$response;
            $return['result'] = 0;
            $return['message'] = get_string('notvaliduser', 'local_exportgrad');
            if ($debug) {
                mtrace('user with idnumber ' . $teacher_idnumber . " is not exists");
            }
            return $return;
        }

        $courses = self::get_matched_courses($user, $year, $school_symbol, $learninggroup_id);
        if (empty($courses)) {
            $return = self::$response;
            $return['result'] = 0;
            $return['message'] = get_string('notmatchedcourses', 'local_exportgrade');
            if ($debug) {
                mtrace(" no courses for idnumber: " . $teacher_idnumber . " school_symbol: " . $school_symbol .
                        " learninggroup_id:" . $learninggroup_id . " year: " . $year);
            }
            return $return;
        }

        foreach ($courses as $course) {
            $course = get_course($course);
            $courseinfo = new course_modinfo($course, $user->id);
            foreach ($courseinfo->get_cms() as $mod) {
                if (!self::module_has_grades($mod->id)) {
                    continue;
                }
                if (in_array($mod->modname, ['quiz', 'assign', 'vpl'])) {
                    $activities[] = ['cmid' => $mod->id, 'fullname' => $mod->name];
                }
            }
        }

        $return = [];
        $return['result'] = 1;
        $return['message'] = "";
        $return['user'] = ['idnumber' => $user->idnumber,
                'fullname' => fullname($user),
                'phone1' => $user->phone1,
                'phone2' => $user->phone2
        ];
        $return['subject'] = isset($CFG->instancename) ? $CFG->instancename : '';
        $return['learninggroup_id'] = $learninggroup_id;
        $return['activities'] = $activities;
        return $return;
    }

    public static function find_activities_returns() {
        return new external_single_structure(
                [
                        'result' => new external_value(PARAM_BOOL, 'Result'),
                        'message' => new external_value(PARAM_RAW, 'Message'),
                        'subject' => new external_value(PARAM_RAW, 'Subject'),
                        'learninggroup_id' => new external_value(PARAM_RAW, 'Learninggroup Id'),
                        'activities' => new external_multiple_structure(
                                new external_single_structure (
                                        [
                                                'cmid' => new external_value(PARAM_INT, 'Message'),
                                                'fullname' => new external_value(PARAM_RAW, 'Message')
                                        ])),
                        'user' => new external_single_structure (
                                [
                                        'idnumber' => new external_value(PARAM_RAW, 'idnumber'),
                                        'fullname' => new external_value(PARAM_RAW, 'fullname'),
                                        'phone1' => new external_value(PARAM_RAW, 'phone1'),
                                        'phone2' => new external_value(PARAM_RAW, 'phone2')
                                ]),
                ]
        );
    }

    public static function learninggroup_grades_parameters() {
        return new external_function_parameters(
                [
                        'smscode' => new external_value(PARAM_ALPHANUMEXT, 'Smms Code'),
                        'subject' => new external_value(PARAM_ALPHANUMEXT, 'Environment'),
                        'learninggroup_id' => new external_value(PARAM_INT, 'Learning Group'),
                        'cmid' => new external_value(PARAM_INT, 'CM ID'),
                ]
        );
    }

    public static function learninggroup_grades($smscode, $subject, $learninggroup_id, $cmid) {
        $debug = local_exportgrade_init_debug();
        $params = self::validate_parameters(self::learninggroup_grades_parameters(),
                ['smscode' => $smscode, 'subject' => $subject, 'learninggroup_id' => $learninggroup_id, 'cmid' => $cmid]);

        if (get_config('local_exportgrade', 'smsverification')) {
            if (!self::validatecode($learninggroup_id, $smscode)) {
                $return = [];
                $return['result'] = 0;
                $return['message'] = "SMS Not Valid";
                $return['subject'] = '';
                $return['learninggroup_id'] = '';
                $return['students'] = [];
                if ($debug) {
                    mtrace(" SMS Not Valid with smscode: " . $smscode);
                }
                return $return;
            }
        }

        $webservice = local_exportgrade_get_config_webservices_by_instance($subject);
        if (!empty($webservice['webserviceurl']) || !empty($webservice['webservicetoken'])) {

            $url = $webservice['webserviceurl'] . "/webservice/rest/server.php";
            $post = [];
            $post['wstoken'] = $webservice['webservicetoken'];
            $post['wsfunction'] = 'local_exportgrades_get_grades';
            $post['moodlewsrestformat'] = 'json';
            unset($params['smscode']);
            $post = array_merge($post, $params);
            try {
                $curl = new curl($url);
                $post = http_build_query($post, '', '&');
                $resp = $curl->post($url, $post);
                $resp = json_decode($resp);
            } catch (\Exception $e) {
                if ($debug) {
                    mtrace('error to get token: ' . print_r($e->getMessage()) . " " . print_r($resp, 1));
                }
            }
        } else {
            if ($debug) {
                mtrace('webservice is not defined: ' . print_r($webservice, 1));
            }
        }

        $return = [];
        $return['result'] = 1;
        $return['message'] = "";
        $return['subject'] = $subject;
        $return['learninggroup_id'] = $learninggroup_id;
        $return['students'] = $resp->students;
        return $return;
    }

    public static function learninggroup_grades_returns() {
        return new external_single_structure(
                [
                        'result' => new external_value(PARAM_BOOL, 'Result'),
                        'message' => new external_value(PARAM_RAW, 'Message'),
                        'subject' => new external_value(PARAM_RAW, 'Subject'),
                        'learninggroup_id' => new external_value(PARAM_RAW, 'Learninggroup Id'),
                        'students' => new external_multiple_structure(
                                new external_single_structure (
                                        [
                                                'useridnumber' => new external_value(PARAM_RAW, 'Message'),
                                                'grade' => new external_value(PARAM_RAW, 'Message')
                                        ]))
                ]
        );
    }

    public static function get_grades_parameters() {
        return new external_function_parameters(
                [
                        'subject' => new external_value(PARAM_ALPHANUMEXT, 'Environment'),
                        'learninggroup_id' => new external_value(PARAM_INT, 'Learning Group'),
                        'cmid' => new external_value(PARAM_INT, 'CM ID'),
                ]
        );
    }

    public static function get_grades($subject, $learninggroup_id, $cmid) {
        $params = self::validate_parameters(self::get_grades_parameters(),
                ['subject' => $subject, 'learninggroup_id' => $learninggroup_id, 'cmid' => $cmid]);

        if (!self::validatelearninggroup($learninggroup_id, $cmid)) {
            $return = [];
            $return['result'] = 0;
            $return['message'] = "Wrong Learning group";
            $return['subject'] = $subject;
            $return['learninggroup_id'] = $learninggroup_id;
            $return['students'] = [];
            return $return;
        }

        $cmid = $params['cmid'];
        $students = self::get_grades_by_cmid($cmid);
        $return = [];
        $return['result'] = 1;
        $return['message'] = "";
        $return['subject'] = $subject;
        $return['learninggroup_id'] = $learninggroup_id;
        $return['students'] = $students;
        return $return;
    }

    public static function get_grades_returns() {
        return new external_single_structure(
                [
                        'result' => new external_value(PARAM_BOOL, 'Result'),
                        'message' => new external_value(PARAM_RAW, 'Message'),
                        'subject' => new external_value(PARAM_RAW, 'Subject'),
                        'learninggroup_id' => new external_value(PARAM_RAW, 'Learninggroup Id'),
                        'students' => new external_multiple_structure(
                                new external_single_structure (
                                        [
                                                'useridnumber' => new external_value(PARAM_RAW, 'Message'),
                                                'grade' => new external_value(PARAM_RAW, 'Message')
                                        ]))
                ]
        );
    }

    public static function sendvalidationsms($learninggroup_id, $user) {
        $resetrecord = new stdClass;
        $resetrecord->smscode = self::generatecode($learninggroup_id);
        if (self::send_sms_to_user($user, $resetrecord)) {
            return true;
        };
        return false;
    }

    public static function finduser($idnumber) {
        global $DB;

        $idnumber = str_pad($idnumber, self::$idnumberpad, 0, STR_PAD_LEFT);
        return $DB->get_record('user', ['idnumber' => $idnumber, 'suspended' => 0, 'deleted' => 0]);
    }

    public static function get_matched_courses($user, $year, $school_symbol, $learninggroup_id) {
        global $DB;
        $sql = 'SELECT cd.id, c.id as courseid, cd.fieldid, cd.value, cf.shortname
                FROM {customfield_data} cd
                JOIN {customfield_field} cf ON (cd.fieldid=cf.id)
                JOIN {course} c ON c.id = cd.instanceid
                JOIN {context} con ON  con.instanceid = c.id  AND con.contextlevel = 50
                JOIN {role_assignments} ra ON  ra.contextid =con.id
                JOIN {role} r ON ra.roleid = r.id
                WHERE r.shortname = ? AND ra.userid = ?';

        $result = $DB->get_records_sql($sql, ['editingteacher', $user->id]);
        if ($result) {
            $courses = [];
            foreach ($result as $field) {
                $courses[$field->courseid][$field->shortname] = $field->value;
            }
            $result = $courses;
        }

        $return = [];
        foreach ($result as $courseid => $course) {
            $lgroupscourses = explode(',', $course['learninggroup_id']);
            if ($course['year'] == $year && $course['school_symbol'] == $school_symbol &&
                    in_array($learninggroup_id, $lgroupscourses)) {
                $return[] = $courseid;
            }
        }

        return $return;
    }

    public static function get_grades_by_cmid($cmid) {
        global $DB;
        $sql = "SELECT gg.id,gg.userid AS userid, gg.finalgrade, u.idnumber
                FROM {grade_grades} gg
                LEFT JOIN {grade_items} gi ON (gg.itemid = gi.id)
                INNER JOIN {course} c ON(c.id = gi.courseid)
                INNER JOIN {course_modules} cm ON (cm.course = c.id AND cm.instance = gi.iteminstance)
                INNER JOIN {user} u ON (gg.userid = u.id)
                WHERE cm.id=? and cm.deletioninprogress = 0";
        $results = $DB->get_records_sql($sql, array($cmid));
        $data = [];
        foreach ($results as $result) {
            if ($result->idnumber) {
                $data[] = ['useridnumber' => $result->idnumber, 'grade' => $result->finalgrade];
            }
        }
        return $data;
    }

    public static function generatecode($learninggroup_id) {
        $smscode = get_config('local_exportgrade', 'smscode');
        if (!empty($smscode)) {
            $smscode = json_decode($smscode, 1);
        } else {
            $smscode = [];
        }
        $smscode[$learninggroup_id]['smscode'] = mt_rand(1111, 9999);
        $smscode[$learninggroup_id]['timemodified'] = time();

        set_config('smscode', json_encode($smscode), 'local_exportgrade');
        return $smscode[$learninggroup_id]['smscode'];
    }

    public static function validatecode($learninggroup_id, $code) {
        self::clearecode();
        $smscode = get_config('local_exportgrade', 'smscode');
        if (!empty($smscode)) {
            $smscode = json_decode($smscode, 1);
        }
        if (isset($smscode[$learninggroup_id]['smscode']) && $code == $smscode[$learninggroup_id]['smscode']) {
            return true;
        }
        return false;
    }

    public static function clearecode() {
        $smscode = get_config('local_exportgrade', 'smscode');
        if (!empty($smscode)) {
            $smscode = json_decode($smscode, 1);
            foreach ($smscode as $key => $item) {
                if (time() - $smscode[$key]['timemodified'] > 60 * 5) {
                    unset($smscode[$key]);
                }
            }
            set_config('smscode', json_encode($smscode), 'local_exportgrade');
        }
    }

    public static function validatelearninggroup($learninggroup_id, $cmid) {
        global $DB;

        list($course, $cm) = get_course_and_cm_from_cmid($cmid);
        $sql = 'SELECT c.id, cd.value
                FROM {customfield_data} cd
                JOIN {customfield_field} cf ON (cd.fieldid=cf.id)
                JOIN {course} c ON c.id = cd.instanceid
                JOIN {context} con ON  con.instanceid = c.id  AND con.contextlevel = 50
                WHERE  cf.shortname = ? AND c.id =? LIMIT 1';
        $course = $DB->get_record_sql($sql, ['learninggroup_id', $course->id]);
        if (empty($course)) {
            return false;
        } else {
            $groups = $course->value ? explode(',', $course->value) : [];
            if (!in_array($learninggroup_id, $groups)) {
                return false;
            }
        }
        return true;
    }

    public static function module_has_grades($cmid) {
        global $DB;
        $sql = "SELECT gg.id
                FROM {grade_grades} gg
                LEFT JOIN {grade_items} gi ON (gg.itemid = gi.id)
                INNER JOIN {course} c ON(c.id = gi.courseid)
                INNER JOIN {course_modules} cm ON (cm.course = c.id AND cm.instance = gi.iteminstance)
                INNER JOIN {user} u ON (gg.userid = u.id)
                WHERE cm.id=? and cm.deletioninprogress = 0 and gg.finalgrade is not null";
        $results = $DB->get_records_sql($sql, array($cmid));
        return $results;
    }

    private static function send_sms_to_user($user, $resetrecord) {
        global $CFG;

        if (!isset($CFG->smsapicode)) {
            return false;
        }

        if (isset($CFG->smsphonefrom) && !empty($CFG->smsphonefrom)) {
            $fromNumber = $CFG->smsphonefrom;
        } else {
            $fromNumber = '+972520000000';
        }
        // In case cellular phone is stored in phone2 (and not in phone1)
        if (empty($user->phone1) && !empty($user->phone2)) {
            $usercellphone = $user->phone2;
        } else if (!empty($user->phone1)) { // get cellular phone number from phone1
            $usercellphone = $user->phone1;
        }
        $toNumber = '+972' . substr($usercellphone, 1);

        $endpoint = 'https://ssl-vp.com/rest/v1/Messages?sendNow=true';
        $header = array('Content-type: application/json', 'Authorization: ' . $CFG->smsapicode);
        $params = array('toMembersByCell' => array($toNumber),
                'body' => get_string('textforsmscode', 'theme_petel') . ' ' . $resetrecord->smscode,
                'fromNumber' => $fromNumber);

        $curl = new \curl(array('debug' => false));
        $curl->setHeader($header);
        $response = $curl->post($endpoint, json_encode($params));
        $curlerrno = $curl->get_errno();
        if (!empty($curlerrno)) {
            return false;
        }
        $curlinfo = $curl->get_info();
        if (empty($curlinfo['http_code']) || $curlinfo['http_code'] != 200) {
            return false;
        }

        return true;
    }
}
