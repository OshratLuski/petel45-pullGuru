<?php
require_once(__DIR__ . '/../../config.php');
require_login();

define('NEW_PASSWORD', '123456');

// get user id from url or from global USER if not exist
$userid = required_param('userid', PARAM_INT);    // User id; -1 if creating new user.
$courseid = optional_param('courseid', $COURSE->id,PARAM_INT);    // Course id; -1 if creating new user.
// get the user whose password we're going to change from the DB
$user = $DB->get_record('user', array('id' => $userid));
$authplugin = get_auth_plugin($user->auth);
$confirm = optional_param('confirm', false, PARAM_BOOL);
$layout = optional_param('layout', 'incourse', PARAM_TEXT);

$pageurl = '/report/roster/resetpassword.php';
$returnurl = new moodle_url('/report/roster/index.php', array('id' => $courseid));
$strdeletecheck = get_string('resetstudentpassword', 'report_roster');

// Set up page - this part should always show, whether confirmed or not
$PAGE->set_context(context_course::instance($COURSE->id));
$PAGE->set_url(new moodle_url($pageurl));
$PAGE->navbar->add($strdeletecheck);
$PAGE->set_title($COURSE->shortname . ': ' . get_string('roster', 'report_roster'));
$PAGE->set_heading($COURSE->fullname);
$PAGE->set_pagelayout($layout);

if (!confirm_sesskey()) {
    redirect($returnurl);
}

// Check if current user teacher on course or admin.
// Admins.
$admins = array();
foreach(get_admins() as $item){
    $admins[] = $item->id;
}

// If current user admin.
$isadmin = (in_array($USER->id, $admins)) ? true : false;

// If current user teacher.
$context = context_course::instance($courseid);
$editroles = array('teacher', 'editingteacher');
$isteacher = false;
foreach(get_user_roles($context, $USER->id) as $item){
    if(in_array($item->shortname, $editroles)){
        $isteacher = true;
        break;
    }
}

if(!$isadmin && !$isteacher){
    $returnurl = new moodle_url('/user/index.php', array('id' => $courseid));
    redirect($returnurl);
}

if(!$isadmin && $isteacher){
    $canresetpass = false;
    foreach(get_user_roles($context, $userid) as $item){
        if($item->shortname == 'student'){
            $canresetpass = true;
        }
    }

    foreach(get_user_roles($context, $userid) as $item){
        if(in_array($item->shortname, array('manager', 'editingteacher', 'teacher'))){
            $canresetpass = false;
            break;
        }
    }

    if(in_array($userid, $admins)){
        $canresetpass = false;
    }

    if(!$canresetpass){
        $returnurl = new moodle_url('/user/index.php', array('id' => $courseid));
        redirect($returnurl);
    }
}

if (!$confirm) {
    $reseturl = new moodle_url($pageurl, array('userid' => $userid, 'confirm' => true, 'courseid' => $courseid));
    $continueurl = $returnurl;
    $message = get_string('confirmmessage', 'report_roster', $user->username);
    echo $OUTPUT->header();
    echo $OUTPUT->confirm($message, $reseturl, $continueurl);
    echo $OUTPUT->footer();
    exit;
}

// give the user a new, fixed password
//if (!$authplugin->is_internal() and $authplugin->can_change_password()) {
//if (!$authplugin->user_update_password($user, $user->url)) { // user's profile URL field stores the original password. (yes. a security issue)
if (!$authplugin->user_update_password($user, NEW_PASSWORD)) { // user's profile URL field stores the original password. (yes. a security issue)
    print_object($authplugin);
    // Do not stop here, we need to finish user creation.
    debugging(get_string('cannotupdatepasswordonextauth', '', '', $user->auth), DEBUG_NONE);
}
//}

// require the user to change their password on next login
set_user_preference('auth_forcepasswordchange', 1, $user);

// show the username and new password
echo $OUTPUT->header();
echo html_writer::tag('div', get_string('passwordwasreset', 'report_roster',
    array('username' => $user->username, 'password' => NEW_PASSWORD)));
echo $OUTPUT->continue_button(new moodle_url('/course/view.php',array('id'=>$courseid)));
echo $OUTPUT->footer();

