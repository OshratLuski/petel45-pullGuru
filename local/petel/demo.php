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
 * Demo script autoenrols and logs in.
 *
 * @package    local_petel
 * @copyright  2022 Weizmann institute of science, Israel.
 * @author     2022 Devlion Ltd <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
require_once($CFG->libdir . '/enrollib.php');


$key = required_param('key', PARAM_ALPHANUMEXT);
$cmid = optional_param('cmid', 0, PARAM_INT);

if (!get_config('local_petel', 'enabledemo')) {
    die();
}

$syscontext = \context_system::instance();
$PAGE->set_context($syscontext);

$recaptchav2enable = isset($CFG->recaptchav2enable) ? $CFG->recaptchav2enable : get_config('local_petel', 'recaptchav2enable');
$recaptchav3enable = isset($CFG->recaptchav3enable) ? $CFG->recaptchav3enable : get_config('local_petel', 'recaptchav3enable');
$recaptchav3demoenable = isset($CFG->recaptchav3demoenable) ? $CFG->recaptchav3demoenable : get_config('local_petel', 'recaptchav3demoenable');

if ($recaptchav2enable && !$recaptchav3demoenable) {
    $state = 'v2';
} else {
    $state = 'v3';
}

if ($state == 'v2') {
    $form = new \local_petel\forms\demo_captcha_v2(null, ['key' => $key, 'cmid' => $cmid]);

    if ($formdata = $form->get_data()) {

        $redirecturl = demo_check_page($cmid, $key);
        redirect($redirecturl);

        die();
    } else {
        $baseurl = new \moodle_url('/local/petel/demo.php');
        $PAGE->set_url($baseurl);

        echo $OUTPUT->header();
        $form->display();
        echo $OUTPUT->footer();

        die();
    }
}

if ($state == 'v3') {

    $recaptchav3url = isset($CFG->recaptchav3url) ? $CFG->recaptchav3url : get_config('local_petel', 'recaptchav3url');
    $recaptchav3sitekey = isset($CFG->recaptchav3sitekey) ? $CFG->recaptchav3sitekey : get_config('local_petel', 'recaptchav3sitekey');
    $recaptchav3privatekey = isset($CFG->recaptchav3privatekey) ? $CFG->recaptchav3privatekey : get_config('local_petel', 'recaptchav3privatekey');

    if (!$recaptchav3url || !$recaptchav3sitekey || !$recaptchav3privatekey) {
        throw new \moodle_exception('Please configure reCAPTCHA site key and secret key');
    }

    $form = new \local_petel\forms\demo_captcha_v3(null, [
            'key' => $key,
            'cmid' => $cmid,
            'recaptchaurl' => $recaptchav3url,
            'recaptchasitekey' => $recaptchav3sitekey,
    ]);

    $baseurl = new \moodle_url('/local/petel/demo.php');
    $PAGE->set_url($baseurl);

    if ($formdata = $form->get_data()) {

        $response = file_get_contents($recaptchav3url . '/siteverify' . '?secret=' . $recaptchav3privatekey . '&response=' . $formdata->token);
        $responseKeys = json_decode($response, true);

        $recaptchascore = isset($CFG->recaptchav3score) && !empty($CFG->recaptchav3score) ? $CFG->recaptchav3score : 0.5;

        if (!$responseKeys["success"] || $responseKeys["score"] < $recaptchascore) {
            die(get_string('configrecaptchav3failed', 'local_petel'));
        }

        $redirecturl = demo_check_page($cmid, $key);
        redirect($redirecturl);

        die();
    } else {
        echo $OUTPUT->header();
        $form->display();
        echo $OUTPUT->footer();

        die();
    }
}

function demo_check_page($cmid, $key) {
    global $DB, $CFG, $USER;

    if (!$instance = $DB->get_record('enrol', ['password' => $key])) {
        throw new \moodle_exception('errordemonokey', 'local_petel');
    }

    if (!$enrol = enrol_get_plugin($instance->enrol)) {
        throw new \moodle_exception('errordemonoenrol', 'local_petel');
    }

    $bulkuserprefix = $CFG->local_petel_prefix_bulk_user ?? \local_petel\task\demo_users_cleanup_task::DEFAULT_BULK_USER_PREFIX;
    $enrolledparams = ['username' => $DB->sql_like_escape($bulkuserprefix) . '%'];

    $enrolleduserids = array_keys($DB->get_records_sql(
            "SELECT u.id, u.username FROM {user} u LEFT JOIN {user_enrolments} ue ON (u.id = ue.userid)
                WHERE " . $DB->sql_like('u.username', ':username', false, false) . " 
                AND u.deleted = 0 AND ue.id IS NOT NULL"
            , $enrolledparams));

    $sql = '';
    if ($enrolleduserids) {
        list($sql, $params) = $DB->get_in_or_equal($enrolleduserids, SQL_PARAMS_NAMED, 'userid', false);
        $sql = " AND id $sql";
    }

    $params['username'] = $DB->sql_like_escape($bulkuserprefix) . '%';

    $sql = 'select * from {user} where deleted = 0 ' . $sql . ' AND ' . $DB->sql_like('username', ':username', false, false) .
            ' order by username limit 1';
    if (!$user =
            $DB->get_record_sql($sql, $params)) {
        throw new \moodle_exception('errordemocoursefull', 'local_petel');
    }

    // Log user in.
    \core\session\manager::set_user($user);
    complete_user_login($user);

    // Check function exists.
    $methodname = 'can_' . $instance->enrol . '_enrol';

    if (!is_callable([$enrol, $methodname], false, $callablename)) {
        throw new \moodle_exception('errordemonoenrolmethod', 'local_petel');
    }

    // Now we check if user is able to enrol.
    if (!$callablename) {
        throw new \moodle_exception('errordemocoursefull', 'local_petel');
    }

    // Now we try to enrol.
    $timestart = time();
    if ($instance->enrolperiod) {
        $timeend = $timestart + $instance->enrolperiod;
    } else {
        $timeend = 0;
    }

    $roleid = get_config('local_petel', 'demorole') ?: $instance->roleid;

    $enrol->enrol_user($instance, $USER->id, $roleid, $timestart, $timeend, null, false);

    $context = context_course::instance($instance->courseid);
    if (!is_enrolled($context, $user)) {
        throw new \moodle_exception('errordemoenrol', 'local_petel');
    }

    if ($cmid) {
        require_once($CFG->dirroot . '/question/editlib.php');
        list($modrec, $cmrec) = get_module_from_cmid($cmid);

        $redirecturl = new moodle_url('/mod/' . $cmrec->modname . '/view.php', ['id' => $cmid]);
    } else {
        $redirecturl = new moodle_url('/course/view.php', ['id' => $instance->courseid]);
    }

    return $redirecturl;
}