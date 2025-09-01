<?php

/**
 * toturials locallib.
 *
 * @package    local_toturials
 * @since      Moodle 3.9
 * @copyright  2022  Matan Berkovicth <matan.berkovitch@weizmann.ac.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


function local_update_activity_completed_observer (\core\event\course_module_completion_updated $event){

    $data = $event->get_data();
    $cmids_monitoring = get_config('local_tutorials','cmids_monitoring');
    $cmids_monitoring = explode(',',$cmids_monitoring);   // There can be several cmid separated by commas

    foreach($cmids_monitoring as $cmid) {

        // Check for monitoring cmid and then check for activity completion.
        if ($data['contextinstanceid'] == $cmid && is_completed($data['userid'],$cmid)){

            $user = \core_user::get_user($data['userid'], '*', MUST_EXIST);

            $payload = [];
            $payload['primaryUserId'] = $user->username ;
            $payload['courseId'] = $data['courseid'] ;
            $payload['testDate'] = date("Y-m-d",$data['timecreated']) ;

            // TODO: Add handling of TESTS/PROD esbOpsIn parameters.
            $json_data = '{
                            "esbOpsIn" : {
                                "serviceName" : "GC_INSERT_PASSED_MDL_TEST_HR",
                                "reqOrigin" : "MOODLE",
                                "reqOriginator" : "TEST",
                                "reqOriginApp" : "Sexual harassment"
                                },
                            "inputParameters" : {
                                "primaryUserId": "' . $payload['primaryUserId'] . '",
                                "courseId": "' . $payload['courseId'] . '",
                                "testDate": "' . $payload['testDate'] . '"
                                }
                          }';

            $esb_ws_url = get_config('local_tutorials','esb_ws_url');
            local_tutorials_update_activity_completed ($esb_ws_url , $json_data);
        }
    }
}

function is_completed ($userid, $cmid){
    global $DB;

    $result = $DB->get_record('course_modules_completion',
        ['userid' => $userid, 'coursemoduleid' => $cmid, 'completionstate' => '1']);
    return ($result) ? true : false;
}


function local_tutorials_update_activity_completed($url, $json_data) {
    global $CFG;

    $esb_ws_username = get_config('local_tutorials','esb_ws_username');
    $esb_ws_password = get_config('local_tutorials','esb_ws_password');
    $auth_basic = base64_encode($esb_ws_username.':'.$esb_ws_password);

    $curl_handle=curl_init();
    curl_setopt($curl_handle, CURLOPT_POST, 1);
    curl_setopt($curl_handle, CURLOPT_URL, $url);
    curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT,2);
    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER,1);
    curl_setopt($curl_handle, CURLOPT_PROXY, '');
    curl_setopt($curl_handle, CURLOPT_NOPROXY, $CFG->proxybypass);
    curl_setopt($curl_handle, CURLOPT_HTTPPROXYTUNNEL, false);
    curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl_handle, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Basic '.$auth_basic)
    );
    curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $json_data);

    $buffer = curl_exec($curl_handle);
    $code = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);

    curl_close($curl_handle);

    $data = json_decode($json_data, true);
    $contex = context_course::instance($data['inputParameters']['courseId']);
    $event = \local_tutorials\event\update_completed::create(array(
        'context' => $contex,
        'courseid' => $data['inputParameters']['courseId'],
        'other' => ['return code' => $code, 'return_output' => $buffer],
    ));
    $event->trigger();

    // Return a success if the static page was available (HTTP 200).
    if ($code >= 200 && $code < 300) {
        return 'success' ;
    }
    // Otherwise return a failure.
    else {
        return 'fail';
    }
}

/**
 * Get all enrolled users from a specific courseid
 * that did not complete a specific activity with cmid
 * and send them reminder email.
 *
 * Executes once a day by a schedule task.
 *
 * @return void
 * @throws dml_exception
 */
function send_reminders_to_uncompleted_activities() {
    global $DB, $CFG;

    $uncompleted_cmids = get_config('local_tutorials', 'watch_uncompleted_cmids');
    $intervals_array = [];
    $intervals = explode(',', trim(get_config('local_tutorials', 'intervals')));
    foreach(array_filter($intervals) as $day){
        if(!is_number(trim($day))){
            continue;
        }

        $intervals_array[] = trim($day);
    }

    foreach(explode(',', $uncompleted_cmids) as $cmid) {
        $enrolled_users_no_completion =
            "SELECT u.id 'userid', u.firstname 'firstname', u.lastname 'lastname', u.email 'email'
                    ,FROM_UNIXTIME(ue.timestart, '%d-%m-%Y') 'enrollment_start'
                    ,DATEDIFF(NOW(),FROM_UNIXTIME(ue.timestart)) 'days_enrolled'
                    ,m.name 'module_name'
                    ,IF((SELECT completionstate
                        FROM mdl_course_modules_completion
                        WHERE userid = u.id AND coursemoduleid = cm.id) = 1,
                        'completed', 'incomplete') 'completion_state'

                    FROM mdl_enrol e
                    JOIN mdl_user_enrolments ue ON ue.enrolid = e.id
                    JOIN mdl_course_modules cm ON cm.course = e.courseid
                    JOIN mdl_modules m ON cm.module = m.id
                    JOIN mdl_user u ON u.id = ue.userid
                    WHERE cm.id = ?
                      AND (e.enrol = 'manual' OR e.enrol = 'self')
                      AND e.status = 0";

        // Send email to each user after 2 weeks and 4 weeks, if not completed

        $send_emails = $DB->get_records_sql($enrolled_users_no_completion, [$cmid]);

        //print_r($send_emails);die;

        // Who is sending the email to the users.
        $from = new \stdClass();
        $from->id = 3;
        $from->firstname = 'Tutorials';
        $from->lastname = 'Admin';
        $from->email = $CFG->tutorials_noreplyaddress ?? 'learning@weizmann.ac.il'; //$CFG->noreplyaddress;
        $from->maildisplay = true;

        foreach($send_emails as $send_to_user) {

            echo "debug: $send_to_user->firstname $send_to_user->lastname ";

            // Prepare and send email to each user.
            $user = new \stdClass();
            $user->id = $send_to_user->userid;
            $user->firstname = $send_to_user->firstname;
            $user->lastname = $send_to_user->lastname;
            $user->email = $send_to_user->email;
            $user->mailformat = 1;

            $subject = null;
            $content = null;
            echo "(days_enrolled=$send_to_user->days_enrolled) ";
            if ($send_to_user->days_enrolled === '0'
                && $send_to_user->completion_state === 'incomplete' ) {

                echo " Initial email ";
                // First time email message. (when enrolled into course)
                $subject = get_config('local_tutorials', 'reminder_firstmail_subject');
                $reminder_firstmail_content = get_config('local_tutorials', 'reminder_firstmail_content');
                $reminder_firstmail_content = str_replace('{scormactivityurl}',
                    $CFG->wwwroot.'/mod/'.$send_to_user->module_name.'/view.php?id='.$cmid, $reminder_firstmail_content);
                $reminder_firstmail_content = str_replace('{fullname}',
                    $send_to_user->firstname.' '.$send_to_user->lastname, $reminder_firstmail_content);
                // SCORM tutorial activity expected completion date is 1 month after user enrollment.
                //echo "[enrolled=$send_to_user->enrollment_start]";
                $content = str_replace('{completiondate}',
                    date('d-m-Y', strtotime("+1 months", strtotime($send_to_user->enrollment_start))),
                    $reminder_firstmail_content);
            } else {
                // Reminder emails by intervals.
                if (in_array($send_to_user->days_enrolled, $intervals_array)
                    && $send_to_user->completion_state === 'incomplete') {

                    echo " Reminder email ";
                    // First time email message. (when enrolled into course)
                    $subject = get_config('local_tutorials', 'reminder_intervalsmail_subject_'.$send_to_user->days_enrolled);
                    $reminder_intervalsmail_content = get_config('local_tutorials', 'reminder_intervalsmail_content_'.$send_to_user->days_enrolled);
                    $reminder_intervalsmail_content = str_replace('{scormactivityurl}',
                        $CFG->wwwroot.'/mod/'.$send_to_user->module_name.'/view.php?id='.$cmid, $reminder_intervalsmail_content);
                    $reminder_intervalsmail_content = str_replace('{fullname}',
                        $send_to_user->firstname.' '.$send_to_user->lastname, $reminder_intervalsmail_content);
                    // SCORM tutorial activity expected completion date is 1 month after user enrollment.
                    //echo "[enrolled=$send_to_user->enrollment_start]";
                    $content = str_replace('{completiondate}',
                        date('d-m-Y', strtotime("+1 months", strtotime($send_to_user->enrollment_start))),
                        $reminder_intervalsmail_content);
                }
            }
            // Only send in case of enrollment or any of the intervals days.
            if ($subject && $content) {
                echo " >> send email to $send_to_user->firstname $send_to_user->lastname ";
                $CFG->noemailever = false;
                email_to_user($user, $from, $subject, $content, $content);
                // TODO: Add event for successful email sent
            }
            echo PHP_EOL;
        }
    }
}

function send_reminders_safety_course() {
    global $DB, $CFG;

    if(!get_config('local_tutorials','send_safety_reminders')) {
        return;
    }
    $enrolled_users_no_completion = "
            SELECT 
                u.id 'userid',
                ss.name as 'tutorial',
                u.email 'email',
                
                (SELECT IFNULL(CAST(MAX(CAST(m.value AS SIGNED)) AS SIGNED), 0)
                    FROM {scorm_scoes_track} AS m
                    WHERE 
                    m.element = 'cmi.core.score.raw' AND
                    m.userid = u.id AND
                    m.scormid = ss.id) AS 'Score'
                
                FROM
                    (select user2.* ,course.id 'courseid'
                    FROM {course} as course
                    JOIN {enrol} AS en ON en.courseid = course.id
                    JOIN {user_enrolments} AS ue ON ue.enrolid = en.id
                    JOIN {user} AS user2 ON ue.userid = user2.id
                    WHERE course.id = 241) as u 
                
                JOIN
                    (SELECT g.name, g.courseid, gm.userid
                    FROM {groups} AS g
                    JOIN {groups_members} AS gm ON gm.groupid = g.id) AS gr on gr.userid = u.id AND gr.courseid = u.courseid
                
                JOIN 
                    (SELECT id, name, SUBSTRING_INDEX(SUBSTRING_INDEX(name,')',1),'(',-1) as 'sname'
                    FROM {scorm}) 
                    AS ss ON ss.sname = gr.name
                
                LEFT JOIN
                    (SELECT g.value 'max_score', g.scormid, g.userid, id
                    FROM {scorm_scoes_track} AS g
                    WHERE g.element = 'cmi.core.score.raw') AS scorez ON scorez.userid = u.id and scorez.scormid = ss.id 
                
                GROUP BY u.email, ss.name
                HAVING  Score < 70";

    $send_emails = $DB->get_records_sql($enrolled_users_no_completion);

// Who is sending the email to the users.
    $from = new \stdClass();
    $from->id = 3;
    $from->firstname = 'Tutorials';
    $from->lastname = 'Admin';
    $from->email = $CFG->noreplyaddress;
    $from->maildisplay = true;

    $month = null;
    $currentday = date("j");
    if($currentday < 15){
        $month = date("F");
    }
    else{
        $month = date("F" ,strtotime("+1 month"));
    }

    foreach($send_emails as $send_to_user) {

        // Prepare and send email to each user
        $user = new \stdClass();
        $user->firstname = 'Tutorials';
        $user->lsatname = 'user';
        $user->id = $send_to_user->userid;
        $user->email = $send_to_user->email;
        $user->mailformat = 1;
        $tutorial = $send_to_user->tutorial;
        $subject = null;
        $content = null;

        switch($tutorial){
            case 'WIS-Chemical Safety for Chemists (CC)':
                $subject = 'Reminder: Finalizing of WIS-Chemical Safety for Chemists (CC) tutorial';
                $content = 'Dear employee,<br>
                        WIS-Chemical Safety for Chemists (CC) tutorial appear in the link below.<br>
                        Please sign in using your Weizmann user name and password and finalize the tutorial by '.$month.' 15th.<br>
                        https://tutorials.weizmann.ac.il/mod/scorm/view.php?id=996<br>
                        Best regards,<br>
                        The Tutorials Moodle Team';
                break;

            case 'WIS-Chemical Safety for Biologists (CB)':
                $subject = 'Reminder: Finalizing of WIS-Chemical Safety for Biologists (CB) tutorial';
                $content = 'Dear employee,<br>
                        WIS-Chemical Safety for Biologists (CB) tutorial appear in the link below.<br>
                        Please sign in using your Weizmann user name and password and finalize the tutorial by '.$month.' 15th.<br>
                        https://tutorials.weizmann.ac.il/mod/scorm/view.php?id=997<br>
                        Best regards,<br>
                        The Tutorials Moodle Team';
                break;

            case 'WIS-Biological Safety (BB)':
                $subject = 'Reminder: Finalizing of WIS-Biological Safety (BB) tutorial';
                $content = 'Dear employee,<br>
                        WIS-Biological Safety (BB) tutorial appear in the link below.<br>
                        Please sign in using your Weizmann user name and password and finalize the tutorial by '.$month.' 15th.<br>
                        https://tutorials.weizmann.ac.il/mod/scorm/view.php?id=998<br>
                        Best regards,<br>
                        The Tutorials Moodle Team';
                break;

            case 'WIS-Laser Safety (L)':
                $subject = 'Reminder: Finalizing of WIS-Laser Safety (L) tutorial';
                $content = 'Dear employee,<br>
                        WIS-Laser Safety (L) tutorial appear in the link below.<br>
                        Please sign in using your Weizmann user name and password and finalize the tutorial by '.$month.' 15th.<br>
                        https://tutorials.weizmann.ac.il/mod/scorm/view.php?id=999<br>
                        Best regards,<br>
                        The Tutorials Moodle Team';
                break;

//            case 'WIS-Radiation Safety Introduction (R0)':
//                $subject = 'Reminder: Finalizing of WIS-Radiation Safety Introduction (R0) tutorial';
//                $content = 'Dear employee,<br>
//                        WIS-Radiation Safety Introduction (R0) tutorial appear in the link below.<br>
//                        Please sign in using your Weizmann user name and password and finalize the tutorial by '.$month.' 15th.<br>
//                        https://tutorials.weizmann.ac.il/mod/scorm/view.php?id=1001<br>
//                        Best regards,<br>
//                        The Tutorials Moodle Team';
//                break;

            case 'WIS-Radiation Safety 1-Open Sources (R1)':
                $subject = 'Reminder: Finalizing of WIS-Radiation Safety 1-Open Sources (R1) tutorial';
                $content = 'Dear employee,<br>
                        WIS-Radiation Safety 1-Open Sources (R1) tutorial appear in the link below.<br>
                        Please sign in using your Weizmann user name and password and finalize the tutorial by '.$month.' 15th.<br>
                        https://tutorials.weizmann.ac.il/mod/scorm/view.php?id=1000<br>
                        Best regards,<br>
                        The Tutorials Moodle Team';
                break;

            case 'WIS-Radiation Safety 2-Sealed Sources (R2)':
                $subject = 'Reminder: Finalizing of WIS-Radiation Safety 2-Sealed Sources (R2) tutorial';
                $content = 'Dear employee,<br>
                        WIS-Radiation Safety 2-Sealed Sources (R2) tutorial appear in the link below.<br>
                        Please sign in using your Weizmann user name and password and finalize the tutorial by '.$month.' 15th.<br>
                        https://tutorials.weizmann.ac.il/mod/scorm/view.php?id=1002<br>
                        Best regards,<br>
                        The Tutorials Moodle Team';
                break;

            case 'WIS-Radiation Safety 3-Xray (R3)':
                $subject = 'Reminder: Finalizing of WIS-Radiation Safety 3-Xray (R3) tutorial';
                $content = 'Dear employee,<br>
                        WIS-Radiation Safety 3-Xray (R3) tutorial appear in the link below.<br>
                        Please sign in using your Weizmann user name and password and finalize the tutorial by '.$month.' 15th.<br>
                        https://tutorials.weizmann.ac.il/mod/scorm/view.php?id=1003<br>
                        Best regards,<br>
                        The Tutorials Moodle Team';
                break;
        }

        if ($subject && $content) {
            email_to_user($user, $from, $subject, $content, $content);
        }

        if (1) {
            $params = [
                'context' => context_course::instance(241),
                'courseid' => 241,
                'action' => 'send_reminders',
                'relateduserid' => $user->id,
                'other' => [
                    'tutorial' => $tutorial,
                ]
            ];

            $event = \local_tutorials\event\safety_course_send_reminders::create($params);
            $event->trigger();
        }


    }
}

function send_reminders_fire_prevention_course () {
    global $DB, $CFG;

    if(!get_config('local_tutorials','send_fire_prevention_reminders')) {
        return;
    }

    $date    = new DateTime();
    $newdate = $date->modify( '+ 7 days' );
    $finishdate = $newdate->format( 'd/m/y' );

    $cmids = array();
    $cmids['he'] = get_config('local_tutorials','fire_prevention_he_cmid');
    $cmids['en'] = get_config('local_tutorials','fire_prevention_en_cmid');
    $course = $DB->get_record('course_modules', array('id'=> $cmids['he']), 'course', MUST_EXIST);


    $enrolled_users_no_completion = " 
            SELECT ue.id 'userid',
                   u.email 'email',
                CAST(IFNULL((SELECT g.grade FROM {quiz_grades} AS g
                    WHERE g.userid = ue.userid AND g.quiz = 
                                  (SELECT instance
                                  FROM {course_modules}
                                  WHERE id = ". $cmids['he'] .")),0)AS SIGNED) AS 'grade_he',
                CAST(IFNULL((SELECT g.grade FROM {quiz_grades} AS g
                                WHERE g.userid = ue.userid AND g.quiz = 
                                (SELECT instance
                                FROM {course_modules}
                                WHERE id = ". $cmids['en'] .")),0)AS SIGNED) AS 'grade_en',
                (SELECT g.name
                 FROM {groups} AS g
                 JOIN {groups_members} AS gm ON gm.groupid = g.id
                 WHERE  gm.userid = u.id AND g.courseid = course.id) AS 'lang'

            FROM {course} AS course
                     JOIN {enrol} AS en ON en.courseid = course.id
                     JOIN {user_enrolments} AS ue ON ue.enrolid = en.id AND ue.status = 0
                    JOIN {user} AS u ON u.id = ue.userid 
            
            WHERE course.id = ". $course->course ."
            HAVING (grade_he != 100 AND grade_en != 100) ";

    $send_emails = $DB->get_records_sql($enrolled_users_no_completion);

// Who is sending the email to the users.
    $from = new \stdClass();
    $from->id = 3;
    $from->firstname = 'Tutorials';
    $from->lastname = 'Admin';
    $from->email = $CFG->noreplyaddress;
    $from->maildisplay = true;

    foreach($send_emails as $send_to_user) {

        $lang = $send_to_user->lang;
        // Prepare and send email to each user
        $user = new \stdClass();
        $user->firstname = 'Tutorials';
        $user->lsatname = 'user';
        $user->id = $send_to_user->userid;
        $user->email = $send_to_user->email;
        $user->mailformat = 1;
        $subject = null;
        $content = null;


        switch ($lang) {
            case 'עברית':
                $subject = 'תזכורת: השלמת קורס מניעת דלקות והתנהגות בשעת שרפה';
                $content = 'עובד יקר,<br>
                        על פי רישומינו, טרם השלמת את ביצוע הלומדה: מניעת דלקות והתנהגות בשעת שרפה.<br>
                        לאחר לחיצה על הקישור המופיע מטה, ההתחברות למערכת מתבצעת באמצעות שם משתמש וסיסמה מכוניים.<br>
                       יש לבצע את הלומדה עד לתאריך ' . $finishdate . '<br>
                        https://tutorials.weizmann.ac.il/course/view.php?id=' . $course->course . '<br>
                        בכבוד רב,<br>
                        צוות מודל הדרכות';
                break;

            case 'English':
                $subject = 'Reminder: Fire Prevention & How to Act in the Event of Fire';
                $content = 'Dear employee,<br>
                        Fire Prevention tutorial appear in the link below.<br>
                        Please sign in using your Weizmann user name and password and finalize the tutorial by ' . $finishdate . ' .<br>
                        https://tutorials.weizmann.ac.il/course/view.php?id=' . $course->course . '<br>
                        Best regards,<br>
                        The Tutorials Moodle Team';
                break;

            default:
                $subject = 'Reminder: Fire Prevention & How to Act in the Event of Fire';
                $content = 'Dear employee,<br>
                        Fire Prevention tutorial appear in the link below.<br>
                        Please sign in using your Weizmann user name and password and finalize the tutorial by ' . $finishdate . ' .<br>
                        https://tutorials.weizmann.ac.il/course/view.php?id=' . $course->course . '<br>
                        Best regards,<br>
                        The Tutorials Moodle Team<br><br><br>
                        
                        עובד יקר,<br>
                        על פי רישומינו, טרם השלמת את ביצוע הלומדה: מניעת דלקות והתנהגות בשעת שרפה.<br>
                        לאחר לחיצה על הקישור המופיע מטה, ההתחברות למערכת מתבצעת באמצעות שם משתמש וסיסמה מכוניים.<br>
                       יש לבצע את הלומדה עד לתאריך ' . $finishdate . ' 15th.<br>
                        https://tutorials.weizmann.ac.il/course/view.php?id=' . $course->course . '<br>
                        בכבוד רב,<br>
                        צוות מודל הדרכות
                        ';


        }

        if ($subject && $content) {
        email_to_user($user, $from, $subject, $content, $content);
        }
    }
}



