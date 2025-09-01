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
 * External functions backported.
 *
 * @package    local_redmine
 * @copyright  devlion.co
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot.'/local/redmine/locallib.php');
require_once($CFG->dirroot . '/local/redmine/vendor/autoload.php');

class local_redmine_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function support_request_parameters() {
        return new external_function_parameters(
                array(
                        'moreInfo' => new external_value(PARAM_TEXT, 'id of course'),
                        'questionType' => new external_value(PARAM_TEXT, 'question type (radio-button value)'),
                        'uploadInfo' => new external_value(PARAM_TEXT, 'upload info field', VALUE_DEFAULT, ''),
                        'userBrowserName' => new external_value(PARAM_TEXT, 'user browser name', VALUE_DEFAULT, ''),
                        'userBrowserVersion' => new external_value(PARAM_TEXT, 'user browser version', VALUE_DEFAULT, ''),
                        'userIP' => new external_value(PARAM_TEXT, 'user IP', VALUE_DEFAULT, ''),
                        'resolution' => new external_value(PARAM_TEXT, 'resolution', VALUE_DEFAULT, ''),
                        'pageurl' => new external_value(PARAM_TEXT, 'pageurl', VALUE_DEFAULT, ''),
                )
        );
    }
    /**
     * Returns welcome message
     * @param string $moreinfo
     * @param string $question
     * @param string $uploadinfo
     * @param string $userbrowser
     * @param string $userip
     * @param string $resolution
     * @param string $pageurl
     * @return string
     */
    public static function support_request($moreinfo, $question, $uploadinfo, $userbrowsername, $userbrowserversion, $userip,
            $resolution, $pageurl) {
        global $USER, $DB, $CFG, $PAGE;

        $systemcontext = context_system::instance();
        $PAGE->set_context($systemcontext);

        if (get_config('local_redmine', 'redminestatus')) {
            $client = new \Redmine\Client\NativeCurlClient(
                    get_config('local_redmine', 'redmineurl'),
                    get_config('local_redmine', 'redmineusername'),
                    get_config('local_redmine', 'redminepassword'));

            if (!empty($CFG->proxyhost)) {
                $client->setCurlOption(CURLOPT_PROXY, $CFG->proxyhost.':'.$CFG->proxyport);
            }

            switch ($question) {
                case 'error':
                    $questiontype = get_string('i_want_to_report_a_bug', 'local_redmine');
                    $trackerid = 1;
                    $assignedtoid = get_config('local_redmine', 'redminereporterid');
                    break;
                case 'contenterror':
                    $questiontype = get_string('i_want_to_report_a_contentbug', 'local_redmine');
                    $trackerid = 8;
                    $assignedtoid = get_config('local_redmine', 'redmine_leadoercatalog');
                    break;
                case 'question':
                    $questiontype = get_string('i_want_to_ask_a_question', 'local_redmine');
                    $trackerid = 3;
                    $assignedtoid = get_config('local_redmine', 'redminereporterid');
                    break;
                case 'suggest_improvement':
                    $questiontype = get_string('i_want_to_suggest_improvement', 'local_redmine');
                    $trackerid = 6;
                    $assignedtoid = get_config('local_redmine', 'redmine_technopedagogical');
                    break;
                case 'pedagogical_help':
                    $questiontype = get_string('i_want_to_get_pedagogical_help', 'local_redmine');
                    $trackerid = 3;
                    $assignedtoid = get_config('local_redmine', 'redmine_technopedagogical');
                    break;
            }
            $a = new stdClass();
            $a->username = $USER->username;
            $userprofileurl = new moodle_url('/user/profile.php?id=' . $USER->id);
            $a->name = clean_filename($USER->firstname . ' ' . $USER->lastname) . ' ' . $userprofileurl;
            $a->email = $USER->email;

            // Figure out teachers mentor by looking at parent categories idnumber and matching it with mentors idnumber.
            // NOTE: At this time, only works on PHYSICS instance.
            //$mentors = $DB->get_records_sql(
            //        'SELECT u.firstname, u.lastname, u.email
            //        FROM {course_categories} cc
            //        JOIN {context} ctx ON ctx.instanceid = cc.parent AND ctx.contextlevel = 40
            //        JOIN {role_assignments} ra ON ra.contextid = ctx.id
            //        JOIN {user} u ON u.id = ra.userid
            //        WHERE cc.idnumber IN (SELECT u.idnumber FROM {user} u WHERE u.id = ?)
            //    ', [$USER->id]); // Lets hope we are in a course.
            //$mentoremails = "\n";
            //if (count($mentors) > 0 && count($mentors) < 3) {
            //    foreach ($mentors as $mentor) {
            //        $mentoremails .= "\n Mentor: $mentor->firstname $mentor->lastname < $mentor->email >";
            //    }
            //} else {
            //    $mentoremails = get_config('local_redmine', 'allmightymentor');
            //}

            $a->moreinfo = $moreinfo;
            $a->question = $questiontype;
            $a->userbrowser = $userbrowsername.' v.'.$userbrowserversion;
            $a->userip = $userip;
            $a->screenshot = "";
            $a->uploadinfo = $uploadinfo;
            $a->resolution = $resolution;
            $a->pageurl = $pageurl;
            $a->digest = mb_substr($moreinfo, 0, 85) . '...';

            $description = get_string('redmine_description', 'local_redmine', $a);
            $instanceprefix = $CFG->rm_instance_prefix ?? ''; // Helps distinguish request from PROD/DEV
            $subject = $instanceprefix.' '.get_string('redmine_subject', 'local_redmine', $a);
            $urlcomponents = parse_url($pageurl);

            if (isset($urlcomponents['query'])) {
                parse_str($urlcomponents['query'], $params);

                $cmid = isset($params['cmid']) ? $params['cmid'] : 0;
                $id = isset($params['id']) ? $params['id'] : 0;
            } else {
                $cmid = $id = 0;
            }

            if ($cmid) {
                // Guess parent module url from preview attempt.
                $moduletype = $DB->get_record_sql('SELECT m.name
                                    FROM {course_modules} cm
                                    JOIN {modules} m ON m.id = cm.module
                                    WHERE cm.id=' . $cmid);
                $moduleurl = $CFG->wwwroot . '/mod/' . $moduletype->name . '/view.php?id=' . $cmid;
                $description .= ' [Guess] Parent module: ' . $moduleurl . PHP_EOL;
            }

            if ($id) {
                // Try to guess the OER catalog module that was the source for this module
                $oermodule = $DB->get_record_sql('SELECT m.name \'type\', lm.data \'oer_cmid\'
                                                FROM {local_metadata} lm
                                                 JOIN {local_metadata_field} lmf ON lm.fieldid = lmf.id
                                                 JOIN {course_modules} cm ON cm.id = lm.instanceid
                                                 JOIN {modules} m ON m.id = cm.module
                                                WHERE lmf.shortname = \'ID\' and cm.id = ' . $id);
                $moduleurl = $CFG->wwwroot.'/mod/'.$oermodule->type.'/view.php?id='.$oermodule->oer_cmid;
                $description .= ' [Guess] OER catalog module: '.$moduleurl. PHP_EOL;
            }

            switch ($CFG->instancename) {
                case 'physics':
                    $stgroup = 'פיזיקה';
                    break;
                case 'chemistry':
                    $stgroup = 'כימיה';
                    break;
                case 'biology':
                    $stgroup = 'ביולוגיה';
                    break;
                case 'math':
                    $stgroup = 'מתמטיקה';
                    break;
                case 'sciences':
                    $stgroup = 'מדעים חטב';
                    break;
                case 'computerscience':
                    $stgroup = 'מדעי המחשב';
                    break;
                case 'feinberg':
                    $stgroup = 'פיינברג';
                    break;
                default:
                    $stgroup = 'משותף';
                    break;
            }
            $watchers[] = ($question === 'error') ? get_config('local_redmine', 'redminewatcherbugid') : '';
            $projectid = (isset($CFG->redmine_project_id)) ? $CFG->redmine_project_id : 'petel';
            $issuedata = [
                    'project_id' => $projectid,
                    'subject' => $subject,
                    'description' => $description,
                    'assigned_to_id' => $assignedtoid,
                    'watcher_user_ids' => $watchers,
                    'tracker_id' => $trackerid,
                    'custom_fields' => [['id'=> 4, 'value' => $stgroup , 'name' => 'קבוצה מערכתית']],
                //'category_id' => 17, // IS פיתוח משאבים בפיזיקה
            ];

            $eventparam = array(
                    'context' => \context_system::instance(),
                    'other' => array(
                            'subject' => $subject,
                            'issue' => $issuedata
                    ),
                    'relateduserid' => 1
            );
            \local_redmine\event\support_request_audit::create($eventparam)->trigger();

            try {
                $newissuexml = $client->getApi('issue')->create($issuedata);

                // Add url to chat.
                $url = $CFG->wwwroot.'/local/redmine/index.php?id='.$newissuexml->id;
                $message = '"'.get_string('teacherresponse', 'local_redmine').'":'.$url;

                $client->getApi('issue')->addNoteToIssue($newissuexml->id, $message);

            } catch (moodle_exception $e) {
                // Report to admin (nadavkav) errors creating RM issues.
                $adminer = get_admin();

                $supporterror = new stdClass();
                $supporterror->useridfrom = $adminer->id;
                $nadavkav = $DB->get_record('user', ['username' => 'nadavkav']);
                $supporterror->useridto = $nadavkav->id; // TODO: change to a CFG
                $supporterror->subject = 'Error creating RM support issue';
                $supporterror->fullmessage = 'Support request by userid: '.$USER->id.' was not created successfully. ';
                $supporterror->fullmessage .= s($e->getMessage()).'<br />'.s($e->debuginfo);
                $supporterror->component = 'moodle';
                $supporterror->eventtype = 'supportmessage';
                $supporterror->timecreated = time();
                $supporterror->customdata = json_encode($issuedata);
                $notificationid = $DB->insert_record('notifications', $supporterror);
                $notify = new stdClass();
                $notify->notificationid = $notificationid;
                $DB->insert_record('message_petel_notifications', $notify);
                return 'Error trying to create RM support issue. admin was notified.';
            }
            $issueid = (string)$newissuexml->id;

            // Send notification to user about sucessful issue creation.
            $adminer = get_admin();

            $time = time();
            $subject = get_string('supportconfirmsmall', 'local_redmine');
            $fullmessage = get_string('supportconfirmbody', 'local_redmine', $issueid) ;
            $supportmoreinfo = get_string('supportmoreinfo', 'local_redmine',
                    $CFG->wwwroot.'/local/redmine/search_issues.php') ;

            $objinsert = new stdClass();
            $objinsert->useridfrom = $adminer->id;
            $objinsert->useridto = $USER->id;
            $objinsert->subject = $subject;
            $objinsert->fullmessage = $fullmessage."\n".$supportmoreinfo;
            $objinsert->fullmessageformat = 2;
            $objinsert->fullmessagehtml = '';
            $objinsert->smallmessage = $subject. "\n ID: " . $issueid;
            $objinsert->component = 'moodle';
            $objinsert->eventtype = 'supportmessage';
            $objinsert->timecreated = $time;
            $objinsert->customdata = json_encode(array_merge($issuedata, ['issueid' => $issueid]));

            $notificationid = $DB->insert_record('notifications', $objinsert);

            $success = email_to_user($USER, $adminer, get_string('supportconfirmsubject', 'local_redmine', $questiontype),
                    $objinsert->fullmessage, $objinsert->fullmessage, '', '', true, $CFG->noreplyaddress);

            $objinsert = new stdClass();
            $objinsert->notificationid = $notificationid;
            $DB->insert_record('message_petel_notifications', $objinsert);

            return $issueid;
        } else {
            return 'Disabled!';
        }
    }
    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function support_request_returns() {
        return new external_value(PARAM_RAW, 'Answer to the front');
    }


    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_support_activities_parameters() {
        return new external_function_parameters(
                array(
                        'query' => new external_value(PARAM_TEXT, 'query'),
                )
        );
    }
    /**
     * Returns get_support_activities
     * @return string
     */
    public static function get_support_activities($query) {
        global $DB, $CFG;

        $returnactivities = [];
        $list             = '';
        $list             = preg_split("/[\s,]+/i", strtolower($query));
        $list             = array_unique($list);
        $cllist           = [];
        $wplist           = [];

        foreach ($list as $key => $value) {
            $value = trim($value);
            $cllist[] = $value;
            $wplist[] = $value;

            // HE trick.
            if (strpos($value, 'ל') === 0 || strpos($value, 'ה') === 0) {
                $trimmedvalue = substr($value, 2);
                $cllist[] = $trimmedvalue;
            }
        }

        // Sort cllist by length.
        function sortByLength($a,$b){
            return strlen($b)-strlen($a);
        }

        usort($cllist,'sortByLength');

        $courseid   = get_config('local_redmine', 'supportcourse');
        if(!$courseid){
            return json_encode([]);
        }

        $modinfo    = get_fast_modinfo($courseid);
        $activities = [];
        foreach ($modinfo->get_cms() as $cm) {

            try {
                // If activity not visible.
                if($cm->visible != 1) continue;

                $hitcount  = 0;
                $keyewords = preg_split("/[\s,]+/i", strtolower($cm->name));
                $keyewords = array_unique($keyewords);
                $hits      = array();
                $name      = $cm->name;
                $placeholders = [];
                $plh = '###PLH###';

                // Search by name.
                foreach ($cllist as $ka => $va) {
                    foreach ($keyewords as $kb => $vb) {
                        if (strpos($vb, $va) !== false) {
                            $hits[] = $va;
                        }
                    }
                }

                // Search in community_oer description teacherremarks.
                $data = \local_metadata\mcontext::module()->get($cm->id, 'teacherremarks');
                if(!empty($data)) {
                    $str = strip_tags($data);
                    $keyewordsdesc = preg_split("/[\s,]+/i", strtolower($str));

                    foreach ($cllist as $ka => $va) {
                        foreach ($keyewordsdesc as $kb => $vb) {
                            if (strpos($vb, $va) !== false) {
                                $hits[] = $va;
                            }
                        }
                    }
                }

                // Search in community_oer description intro.
                $cmitem = $DB->get_record($cm->modname, array('id' => $cm->instance));
                if(!empty($cmitem->intro)){
                    $str = strip_tags($cmitem->intro);
                    $keyewordsintro = preg_split("/[\s,]+/i", strtolower($str));

                    foreach ($cllist as $ka => $va) {
                        foreach ($keyewordsintro as $kb => $vb) {
                            if (strpos($vb, $va) !== false) {
                                $hits[] = $va;
                            }
                        }
                    }
                }

                // Search in דפי תוכן בחוצצים.
                if($cm->modname == 'tab'){
                    $str = '';
                    $arrtc = $DB->get_records('tab_content', array('tabid' => $cm->instance));
                    foreach($arrtc as $item){
                        $str .= $item->tabcontent.' ';
                    }

                    if(!empty($str)){
                        $str = strip_tags($str);
                        $keyewordstab = preg_split("/[\s,]+/i", strtolower($str));

                        foreach ($cllist as $ka => $va) {
                            foreach ($keyewordstab as $kb => $vb) {
                                if (strpos($vb, $va) !== false) {
                                    $hits[] = $va;
                                }
                            }
                        }
                    }
                }

                $hitcount = count($hits);
                if ($hitcount != 0) {
                    $activities[] = [
                            'id'         => $cm->id,
                            'shortname'  => $name,
                            'keyewords'  => $keyewords,
                            'hitcount'   => $hitcount,
                            'modname'    => $cm->modname,
                            'section'    => $cm->section,
                            'sectionnum' => $cm->sectionnum,
                    ];
                }

                if (count($activities) >= 7) {
                    break;
                }
            } catch(\Exception $e) {

            }
        }

        function sorthits($a, $b) {
            return $a['hitcount'] < $b['hitcount'];
        }
        usort($activities, "sorthits");
        foreach ($activities as $key => $value) {
            $item               = new stdClass();
            $item->title        = $value['shortname'];
            $item->link        = $CFG->wwwroot.'/local/community/plugins/oer/activityshare.php?id='.$value['id'];
            $returnactivities[] = $item;
        }

        // Get hits from WP Api.
        include_once ($CFG->libdir . '/filelib.php');

        $curl = new curl();
        $options = array(
                'CURLOPT_FRESH_CONNECT' => true,
                'CURLOPT_RETURNTRANSFER' => true,
                'CURLOPT_HEADER' => 0,
                'CURLOPT_CONNECTTIMEOUT' => 2,
                'CURLOPT_SSL_VERIFYPEER' => false,
                'CURLOPT_SSL_VERIFYHOST' => false,
                'CURLOPT_NOPROXY' => 'stwww1.weizmann.ac.il',
        );

        $locations = [
                'https://stwww1.weizmann.ac.il/petel/wp-json/wp/v2/instructions',
                'https://stwww1.weizmann.ac.il/petel/wp-json/wp/v2/faq'
        ];

        $redminesearchwords = get_config('local_redmine', 'redminesearchwords');
        $redmineshowresults = get_config('local_redmine', 'redmineshowresults');

        if($redminesearchwords > 0 && $redmineshowresults > 0){
            $searchwords = [];
            $count = 0;
            foreach(array_filter($wplist) as $word){
                if(mb_strlen($word) > 1 && $count < $redminesearchwords){
                    $searchwords[] = $word;
                    $count++;
                }
            }

            $posts = [];
            $count = 0;
            foreach ($locations as $location) {
                foreach ($searchwords as $word) {
                    if($count < $redmineshowresults) {
                        $out = $curl->get($location, ['search' => trim($word)], $options);

                        if (empty($curl->error)) {
                            foreach (json_decode($out) as $post) {
                                if($count < $redmineshowresults) {
                                    $posts[$post->id] = ['title' => $post->title->rendered, 'link' => $post->link];
                                    $count++;
                                }
                            }
                        }else{
                            break;
                        }
                    }
                }
            }

            $returnactivities = array_merge($returnactivities, $posts);
        }

        return json_encode($returnactivities);
    }
    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_support_activities_returns() {
        return new external_value(PARAM_RAW, 'Support activities');
    }


    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function support_student_request_parameters() {
        return new external_function_parameters(
                array(
                        'moreInfo' => new external_value(PARAM_TEXT, 'id of course'),
                        'uploadInfo' => new external_value(PARAM_TEXT, 'upload info field', VALUE_DEFAULT, ''),
                        'userBrowserName' => new external_value(PARAM_TEXT, 'user browser name', VALUE_DEFAULT, ''),
                        'userBrowserVersion' => new external_value(PARAM_TEXT, 'user browser version', VALUE_DEFAULT, ''),
                        'userIP' => new external_value(PARAM_TEXT, 'user IP', VALUE_DEFAULT, ''),
                        'screenshot' => new external_value(PARAM_TEXT, 'screenshot in base64', VALUE_DEFAULT, ''),
                        'resolution' => new external_value(PARAM_TEXT, 'resolution', VALUE_DEFAULT, ''),
                        'pageurl' => new external_value(PARAM_TEXT, 'pageurl', VALUE_DEFAULT, ''),
                        'courseid' => new external_value(PARAM_INT, 'courseid', VALUE_DEFAULT, 0),
                )
        );
    }
    /**
     * Returns welcome message
     * @param string $moreinfo
     * @param string $uploadinfo
     * @param string $userbrowser
     * @param string $userip
     * @param string $screenshot
     * @param string $resolution
     * @param string $pageurl
     * @param int $courseid
     * @return string
     */
    public static function support_student_request($moreinfo, $uploadinfo, $userbrowsername, $userbrowserversion, $userip, $screenshot,
            $resolution, $pageurl, $courseid) {
        global $USER, $DB, $COURSE, $CFG, $PAGE;

        $context = context_system::instance();
        $PAGE->set_context($context);
        $time = time();

        // Screenshot.
        preg_match('/^data:image\/(\w+);base64,/', $screenshot, $type);
        $screenshot = substr($screenshot, strpos($screenshot, ',') + 1);
        $type       = strtolower($type[1]); // jpg, png, gif
        $filename   = 'screenshot' . $time . '.' . $type;
        if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
            throw new \Exception('invalid image type');
        }
        $screenshot = str_replace(' ', '+', $screenshot);
        $screenshot = base64_decode($screenshot);
        if ($screenshot === false) {
            return json_encode(['url' => '']);
        }
        $fs       = get_file_storage();
        $fileinfo = array(
                'contextid' => $context->id,
                'component' => 'local_redmine',
                'filearea'  => 'screenshot',
                'itemid'    => 0,
                'filepath'  => '/',
                'filename'  => $filename,
        );
        $storedfile     = $fs->create_file_from_string($fileinfo, $screenshot);
        $storedfilepath = $storedfile->copy_content_to_temp();

        // Email and notifications to teachers.
        $result   = $DB->get_records_sql("SELECT id FROM {role} WHERE shortname='editingteacher' OR shortname='teacher'");
        $context  = context_course::instance($courseid);
        $teachers = array();
        foreach ($result as $item) {
            $teachers = array_merge($teachers, get_role_users($item->id, $context));
        }

        foreach ($teachers as $teacher) {
            // Email.
            $a                     = new stdClass();
            $a->username           = $USER->username;
            $userprofileurl        = new moodle_url('/user/profile.php?id=' . $USER->id);
            $a->name               = clean_filename($USER->firstname . ' ' . $USER->lastname);
            $a->userprofileurl     = $userprofileurl->out();
            $a->teacher_name       = clean_filename($teacher->firstname . ' ' . $teacher->lastname);
            $a->email              = $USER->email;
            $a->moreinfo           = $moreinfo;
            $a->userbrowser        = $userbrowsername.' v.'.$userbrowserversion;
            $a->userip             = $userip;
            $a->screenshot         = "";
            $a->uploadinfo         = $uploadinfo;
            $a->resolution         = $resolution;
            $a->pageurl            = $pageurl;
            $a->digest             = mb_substr($moreinfo, 0, 59) . '...';
            $emailtoteachermessage = get_string('supportstudent_description', 'local_redmine', $a);
            $subject               = get_string('support_subject', 'local_redmine', $a);
            $emailtoteacher        = email_to_user($teacher, $USER, $subject, html_to_text($emailtoteachermessage), $emailtoteachermessage, $storedfilepath, $filename, true, $CFG->noreplyaddress);

            // Notification.
            $notiftoteachermessage        = get_string('supportstudent_description_notification', 'local_redmine', $a);
            $objinsert                    = new stdClass();
            $objinsert->useridfrom        = $USER->id;
            $objinsert->useridto          = $teacher->id;
            $objinsert->subject           = $subject;
            $objinsert->userprofileurl    = $userprofileurl->out();
            $objinsert->fullmessage       = $notiftoteachermessage;
            $objinsert->fullmessageformat = FORMAT_HTML;
            $objinsert->fullmessagehtml   = $notiftoteachermessage;
            $objinsert->smallmessage      = $notiftoteachermessage;
            $objinsert->component         = 'moodle';
            $objinsert->eventtype         = 'supportmessage';
            $objinsert->timecreated       = $time;
            $objinsert->contexturl        = $pageurl;
            $notificationid               = $DB->insert_record('notifications', $objinsert);
            $objinsert                    = new stdClass();
            $objinsert->notificationid    = $notificationid;
            $DB->insert_record('message_petel_notifications', $objinsert);
        }

        return '';
    }
    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function support_student_request_returns() {
        return new external_value(PARAM_RAW, 'Answer to the front');
    }


    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_active_issues_parameters() {
        return new external_function_parameters(
                array(
                        'search' => new external_value(PARAM_RAW, 'Search', VALUE_DEFAULT, ''),
                        'filter' => new external_value(PARAM_INT, 'Filter', VALUE_DEFAULT, 1),
                        'sort_col' => new external_value(PARAM_RAW, 'Sort column name', VALUE_DEFAULT, ''),
                        'sort_dir' => new external_value(PARAM_RAW, 'Sort column direction', VALUE_DEFAULT, ''),
                        'page' => new external_value(PARAM_INT, 'Page', VALUE_DEFAULT, 1),
                )
        );
    }
    /**
     * @return string result of submittion
     */
    public static function get_active_issues($search, $filter, $sort_col, $sort_dir, $page) {

        $context = \context_system::instance();
        self::validate_context($context);

        $params = self::validate_parameters(self::get_active_issues_parameters(),
                array(
                        'search' => $search,
                        'filter' => (int) $filter,
                        'sort_col' => $sort_col,
                        'sort_dir' => $sort_dir,
                        'page' => $page,
                )
        );

        return \local_redmine::buildTableIssuesForUser('opened', 50, 'created_on', $params);
    }
    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_active_issues_returns() {
        return new external_value(PARAM_RAW, 'History array');
    }


    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_history_issues_parameters() {
        return new external_function_parameters(
            array(
                    'search' => new external_value(PARAM_RAW, 'Search', VALUE_DEFAULT, ''),
                    'filter' => new external_value(PARAM_INT, 'Filter', VALUE_DEFAULT, 1),
                    'sort_col' => new external_value(PARAM_RAW, 'Sort column name', VALUE_DEFAULT, ''),
                    'sort_dir' => new external_value(PARAM_RAW, 'Sort column direction', VALUE_DEFAULT, ''),
                    'page' => new external_value(PARAM_INT, 'Page', VALUE_DEFAULT, 1),
            )
        );
    }
    /**
     * @return string result of submittion
     */
    public static function get_history_issues($search, $filter, $sort_col, $sort_dir, $page) {

        $context = \context_system::instance();
        self::validate_context($context);

        $params = self::validate_parameters(self::get_history_issues_parameters(),
            array(
                'search' => $search,
                'filter' => (int) $filter,
                'sort_col' => $sort_col,
                'sort_dir' => $sort_dir,
                'page' => $page,
            )
        );

        return \local_redmine::buildTableIssuesForUser('closed', 10, 'closed_on', $params);
    }
    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_history_issues_returns() {
        return new external_value(PARAM_RAW, 'History array');
    }


    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_chat_page_parameters() {
        return new external_function_parameters(
                array(
                        'id' => new external_value(PARAM_INT, 'Id issue'),
                )
        );
    }
    /**
     * @return string result of submittion
     */
    public static function get_chat_page($id) {
        global $CFG, $DB;

        $context = \context_system::instance();
        self::validate_context($context);

        $params = self::validate_parameters(self::get_chat_page_parameters(),
                array(
                        'id' => $id,
                )
        );

        $client = new \Redmine\Client\NativeCurlClient(
                get_config('local_redmine', 'redmineurl'),
                get_config('local_redmine', 'redmineusername'),
                get_config('local_redmine', 'redminepassword'));

        if (!empty($CFG->proxyhost)) {
            $client->setCurlOption(CURLOPT_PROXY, $CFG->proxyhost.':'.$CFG->proxyport);
        }

        // Get data of issue.
        $result = [];
        $result['id'] = $id;

        // Get from opened issues.
        $result['status_opened'] = true;
        $params = ['issue_id' => $id, 'status_id' => 'opened'];
        $data = $client->getApi('issue')->all($params);

        // Or get from closed issues.
        if(empty($data['issues'])){
            $params = ['issue_id' => $id, 'status_id' => 'closed'];
            $data = $client->getApi('issue')->all($params);
            $result['status_opened'] = false;
        }

        if (empty($data['issues'])) {
            throw new \moodle_exception('No matching id in Redmine');
        }

        if(isset($data['issues'][0])){
            $issue = $data['issues'][0];

            // If user is author of issue.
            $isauthor= false;
            if (strpos($issue['description'], \local_redmine::userQuery()) !== false) {
                $isauthor = true;
            }

            $result['isauthor'] = $isauthor;

            // Get author name.
            $authorstring = trim(\local_redmine::stringBetweenTwoWords($issue['description'], 'שם*:', '*'));

            if(count(preg_split('/\n|\r/',$authorstring)) == 1){
                $pattern = '@(http(s)?://)?(([a-zA-Z])([-\w]+\.)+([^\s\.]+[^\s]*)+[^,.\s])@';

                // Name of autor.
                $authorname = preg_replace($pattern, '', $authorstring);

                // Link to user.
                preg_match($pattern, $authorstring, $matches);
                if(isset($matches[0]) && filter_var($matches[0], FILTER_VALIDATE_URL)){
                    $authorname = '<a href="'.$matches[0].'" target="_blank">'.$authorname.'</a>';
                }

                $result['authorname'] = $authorname;
            }

            // Get type.
            //$type = \local_redmine::stringBetweenTwoWords($issue['description'], 'סוג*:', '*');
            $type = isset($issue['tracker']['name']) ? $issue['tracker']['name'] :
                    \local_redmine::stringBetweenTwoWords($issue['description'], 'סוג*:', '*');

            if(count(preg_split('/\n|\r/',$type)) == 1){
                $result['type'] = $type;
            }

            $result['status'] = \local_redmine::getStatusName($issue['status']['id']);

            // Page url.
            preg_match_all('#\bPageUrl: https?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $issue['description'], $match);
            if(isset($match[0][0])){
                preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $match[0][0], $match2);
                if(isset($match2[0][0])){
                    $result['url'] = $match2[0][0];
                }
            }

            if(isset($issue['created_on'])){
                $datetime = str_replace(['T', 'Z'], [' ', ''], $issue['created_on']);
                $dtime = DateTime::createFromFormat('Y-m-d H:i:s', $datetime);
                $createdtimestamp = $dtime->getTimestamp();

                $result['created_on'] = date('d.m.Y', $createdtimestamp);
            }else{
                $result['created_on'] = '';
            }

            if(isset($issue['updated_on'])){
                $datetime = str_replace(['T', 'Z'], [' ', ''], $issue['updated_on']);
                $dtime = DateTime::createFromFormat('Y-m-d H:i:s', $datetime);
                $updatetimestamp = $dtime->getTimestamp();

                $result['updated_on'] = date('d.m.Y', $updatetimestamp);
            }else{
                $result['updated_on'] = '';
            }

            // Alert note.
            $row['alert_note_enable'] = false;
            if($issue['status']['id'] == 21) {

                $query = "
                    SELECT *
                    FROM {local_redmine_chat}
                    WHERE issueid = ?
                    ORDER BY timecreated DESC
                    LIMIT 1                
                ";
                $lastmessage = $DB->get_record_sql($query, [$issue['id']]);

                if(isset($lastmessage->timecreated) && !empty($lastmessage->timecreated)) {
                    $lasttimestamp = $lastmessage->timecreated;
                }else{
                    $lasttimestamp = $createdtimestamp;
                }

                $daysbefore = ceil((time() - $lasttimestamp) / (60 * 60 * 24));
                $delta = 10 - $daysbefore;

                if ($delta > 0) {
                    $result['alert_note_enable'] = true;

                    $a = new \StdClass();
                    $a->days = $delta;
                    $result['alert_note'] = get_string('alertnote', 'local_redmine', $a);
                }
            }

            // Url to redmine issue.
            if (is_siteadmin()) {
                $result['redmine_url_enable'] = true;
                $result['redmine_url'] = get_config('local_redmine', 'redmineurl') . '/issues/' . $id;
            }else{
                $result['redmine_url_enable'] = false;
                $result['redmine_url'] = '';
            }

        }

        return json_encode($result);
    }
    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_chat_page_returns() {
        return new external_value(PARAM_RAW, 'Single issue array');
    }


    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_chat_messages_parameters() {
        return new external_function_parameters(
                array(
                        'id' => new external_value(PARAM_INT, 'Id issue'),
                )
        );
    }
    /**
     * @return string result of submittion
     */
    public static function get_chat_messages($id) {
        global $DB, $USER, $CFG;

        $context = \context_system::instance();
        self::validate_context($context);

        $params = self::validate_parameters(self::get_chat_messages_parameters(),
                array(
                        'id' => $id,
                )
        );

        $result = [];

        $currentdate = date('d.m.Y', time());

        $sql = "
            SELECT *
            FROM {local_redmine_chat}
            WHERE issueid = ?
            ORDER BY timecreated ASC
        ";
        $params = [$id];

        $tmp = [];
        foreach($DB->get_records_sql($sql, $params) as $message){

            $date = date('d.m.Y', $message->timecreated);
            if($date == $currentdate) $date = get_string('chatnow', 'local_redmine');

            $mess = [];
            $mess['my_message'] = ($message->userid == $USER->id) ? true : false;

            $user = $DB->get_record('user', ['id' => $message->userid]);
            $mess['username'] = $user->firstname.' '.$user->lastname;

            // Convert url to link.
            $pattern = '@(http(s)?://)?(([a-zA-Z])([-\w]+\.)+([^\s\.]+[^\s]*)+[^,.\s])@';
            $message->message = preg_replace($pattern, '<a href="http$2://$3">$0</a>', $message->message);

            $mess['message'] = format_text($message->message);
            $mess['time'] = date('H:i', $message->timecreated);

            // File.
            $mess['files'] = [];
            $contextuser = context_user::instance($message->userid);

            $fs = get_file_storage();
            $files = $fs->get_area_files($contextuser->id, 'local_redmine', 'chat', $message->id);
            foreach ($files as $file) {
                if ($file->is_valid_image()) {
                    $imageurl = $CFG->wwwroot . '/pluginfile.php/' . $file->get_contextid() .
                            '/' . $file->get_component() .
                            '/' . $file->get_filearea() .
                            '/' . $file->get_itemid() .
                            $file->get_filepath() .
                            $file->get_filename();

                    $mess['files'][] = [
                            'url' => $imageurl,
                            'filename' => $file->get_filename(),
                            //'thumbnail' => $file->generate_image_thumbnail(100,100)
                    ];
                }
            }

            $tmp[$date][] = $mess;
        }

        $chat = [];
        foreach($tmp as $date => $items){
            $tmp2 = [];
            $tmp2['date'] = $date;
            $tmp2['data'] = $items;

            $chat[] = $tmp2;
        }

        $result['messages'] = $chat;

        // Problem note.
        $client = new \Redmine\Client\NativeCurlClient(
                get_config('local_redmine', 'redmineurl'),
                get_config('local_redmine', 'redmineusername'),
                get_config('local_redmine', 'redminepassword'));

        if (!empty($CFG->proxyhost)) {
            $client->setCurlOption(CURLOPT_PROXY, $CFG->proxyhost.':'.$CFG->proxyport);
        }

        // Get from opened issues.
        $params = ['issue_id' => $id, 'status_id' => 'opened'];
        $data = $client->getApi('issue')->all($params);

        // Or get from closed issues.
        if(empty($data['issues'])){
            $params = ['issue_id' => $id, 'status_id' => 'closed'];
            $data = $client->getApi('issue')->all($params);
        }

        $problemnote = '';
        if(isset($data['issues'][0]['description'])){
            $description = $data['issues'][0]['description'];

            // Find in description.
            if (strpos($description, 'Mentor') !== false) {
                $problemnote = \local_redmine::stringBetweenTwoWords($description, '*תוכלו לספר לנו עוד?*', 'Mentor');
            }else{
                if (strpos($description, 'IP') !== false) {
                    $problemnote = \local_redmine::stringBetweenTwoWords($description, '*תוכלו לספר לנו עוד?*', 'IP');
                }
            }
        }

        $result['problem_note_enable'] = !empty(trim($problemnote)) ? true : false;
        $result['problem_note'] = format_text($problemnote);

        return json_encode($result);
    }
    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_chat_messages_returns() {
        return new external_value(PARAM_RAW, 'messages array');
    }


    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function send_chat_message_parameters() {
        return new external_function_parameters(
                array(
                        'id' => new external_value(PARAM_INT, 'Id issue'),
                        'message' => new external_value(PARAM_TEXT, 'message'),
                        'filename' => new external_value(PARAM_TEXT, 'filename'),
                        'filetype' => new external_value(PARAM_TEXT, 'filetype'),
                        'filecontent' => new external_value(PARAM_TEXT, 'filecontent'),
                )
        );
    }
    /**
     * @return string result of submittion
     */
    public static function send_chat_message($issueid, $message, $filename, $filetype, $filecontent) {
        global $USER, $DB, $CFG;

        $context = \context_system::instance();
        self::validate_context($context);

        $params = self::validate_parameters(self::send_chat_message_parameters(),
                array(
                        'id' => $issueid,
                        'message' => $message,
                        'filename' => $filename,
                        'filetype' => $filetype,
                        'filecontent' => $filecontent,
                )
        );

        // Insert new message to DB.
        $chatid = $DB->insert_record('local_redmine_chat', [
                'issueid' => $issueid,
                'userid' => $USER->id,
                'message' => $message,
                'timecreated' => time(),
                'timemodified' => time(),
        ]);

        // Create file.
        if(!empty($filecontent) && !empty($filename)){
            $fs = get_file_storage();
            $usercontext = \context_user::instance($USER->id);

            $filerecord = array(
                    'contextid' => $usercontext->id,
                    'component' => 'local_redmine',
                    'filearea'  => 'chat',
                    'itemid'    => $chatid,
                    'filepath'  => '/',
                    'filename'  => $filename,
            );

            $arr = explode(',', $filecontent);
            $str = isset($arr[1]) ? $arr[1] : $arr[0];
            $filecontent = base64_decode($str);

            $fs->create_file_from_string($filerecord, $filecontent);
        }

        // Send to redmine.
        $client = new \Redmine\Client\NativeCurlClient(
                get_config('local_redmine', 'redmineurl'),
                get_config('local_redmine', 'redmineusername'),
                get_config('local_redmine', 'redminepassword'));

        if (!empty($CFG->proxyhost)) {
            $client->setCurlOption(CURLOPT_PROXY, $CFG->proxyhost.':'.$CFG->proxyport);
        }

        $str = get_string('responsefrom', 'local_redmine').': *'.$USER->firstname.' '.$USER->lastname.'*';
        $message = $str.PHP_EOL.PHP_EOL.$message;

        if(!empty($filecontent) && !empty($filename)) {
            $filename = str_replace(' ','_', $filename);

            $upload = json_decode($client->getApi('attachment')->upload($filecontent));
            $client->getApi('issue')->attach($issueid, [
                    'token' => $upload->upload->token,
                    'filename' => $filename,
                    'description' => '',
                    'content_type' => $filetype,
            ]);

            $message .= PHP_EOL.PHP_EOL.'!'.$filename.'!';
        }

        // Add url to chat.
        $url = $CFG->wwwroot.'/local/redmine/index.php?id='.$issueid;
        $message .= PHP_EOL.PHP_EOL.
            '"'.get_string('teacherresponse', 'local_redmine').'":'.$url;

        $client->getApi('issue')->addNoteToIssue($issueid, $message);

        // Change status.
        $isauthor= false;

        // Get from opened issues.
        $data = $client->getApi('issue')->all(['issue_id' => $issueid, 'status_id' => 'opened']);

        // Or get from closed issues.
        if (empty($data['issues'])) {
            $data = $client->getApi('issue')->all(['issue_id' => $issueid, 'status_id' => 'closed']);
        }

        if (isset($data['issues'][0])) {
            $issue = $data['issues'][0];

            if (strpos($issue['description'], \local_redmine::userQuery()) !== false) {
                $isauthor = true;
            }
        }

        if(!empty(get_config('local_redmine', 'redmineadminusername')) && !empty(get_config('local_redmine', 'redmineadminpassword'))) {
            $adminclient = new \Redmine\Client\NativeCurlClient(
                    get_config('local_redmine', 'redmineurl'),
                    get_config('local_redmine', 'redmineadminusername'),
                    get_config('local_redmine', 'redmineadminpassword'));

            if (!empty($CFG->proxyhost)) {
                $adminclient->setCurlOption(CURLOPT_PROXY, $CFG->proxyhost . ':' . $CFG->proxyport);
            }

            if ($isauthor) {
                $adminclient->getApi('issue')->setIssueStatus($issueid, 'ממתין להתייחסות');
            } else {
                $adminclient->getApi('issue')->setIssueStatus($issueid, 'ממתין להתייחסות מורה');
            }
        }

        // Send email.
        $data = $client->getApi('issue')->all(['issue_id' => $issueid, 'status_id' => 'opened']);

        // Or get from closed issues.
        if(empty($data['issues'])){
            $data = $client->getApi('issue')->all(['issue_id' => $issueid, 'status_id' => 'closed']);
        }

        if (!empty($data['issues']) && isset($data['issues'][0])) {
            $issue = $data['issues'][0];

            $email = trim(\local_redmine::stringBetweenTwoWords($issue['description'], 'דואל*:', '*'));
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $touser = new \stdClass();
                $touser->email = $email;
                $touser->firstname = '';
                $touser->lastname = '';
                $touser->maildisplay = true;
                $touser->mailformat = 1;
                $touser->id = -99;
                $touser->firstnamephonetic = '';
                $touser->lastnamephonetic = '';
                $touser->middlename = '';
                $touser->alternatename = '';

                $fromuser = get_admin();

                $subject = get_string('mailsubject', 'local_redmine');

                email_to_user($touser, $fromuser, $subject, $message, $message);
            }
        }

        return $chatid;
    }
    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function send_chat_message_returns() {
        return new external_value(PARAM_INT, 'Chat message id');
    }

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function issues_counter_user_parameters() {
        return new external_function_parameters(
                array(
                )
        );
    }
    /**
     * @return string result of submittion
     */
    public static function issues_counter_user() {
        global $USER, $CFG, $DB;

        $result = ['counter' => 0];

        // Prevent redundant exception popup.
        if ( !isloggedin() || get_user_preferences('auth_forcepasswordchange') || $USER->policyagreed === '0' ) {
            return json_encode($result);
        }

        // Check user preferences.
        if (isset($CFG->rm_issue_interval_check) && is_numeric($CFG->rm_issue_interval_check) && $CFG->rm_issue_interval_check > 0) {

            $rmtime = get_user_preferences('redmine_issues_time', -1);
            $rmcounter = get_user_preferences('redmine_issues_counter', -1);

            if ($rmtime != -1 && $rmcounter != -1) {
                if (time() <= $rmtime + $CFG->rm_issue_interval_check * 60) {
                    $result = ['counter' => $rmcounter];
                    return json_encode($result);
                }
            }
        }

        $context = \context_system::instance();
        self::validate_context($context);

        $params = self::validate_parameters(self::issues_counter_user_parameters(),
                array()
        );

        if (get_config('local_redmine', 'redminestatus') && \local_petel\funcs::user_admin_or_teacher()) {

            $client = new \Redmine\Client\NativeCurlClient(
                    get_config('local_redmine', 'redmineurl'),
                    get_config('local_redmine', 'redmineusername'),
                    get_config('local_redmine', 'redminepassword'));

            if (!empty($CFG->proxyhost)) {
                $client->setCurlOption(CURLOPT_PROXY, $CFG->proxyhost.':'.$CFG->proxyport);
            }

            // Get issue id by user.
            $searchdata = $client->getApi('search')->search(\local_redmine::userQuery(), ['offset' => 0, 'limit' => 100]);

            // Search.
            $ids = [];
            foreach($searchdata['results'] as $issue){

                // Check instance.
                if(isset($CFG->instancename) && !empty($CFG->instancename)) {
                    if (strpos($issue['description'], $CFG->instancename) == false) {
                        continue;
                    }
                }

                $ids[] = $issue['id'];
            }

            // If empty issue ids.
            if(!empty($ids)){
                $params = [
                        'issue_id' => implode(',', array_unique($ids)),
                        'status_id' => 'opened',
                        'limit' => 100,
                ];

                $counter = 0;
                $data = $client->getApi('issue')->all($params);
                foreach($data['issues'] as $issue){
                    if($issue['status']['id'] == 21) {
                        $datetime = str_replace(['T', 'Z'], [' ', ''], $issue['created_on']);
                        $dtime = \DateTime::createFromFormat('Y-m-d H:i:s', $datetime);
                        $createdtimestamp = $dtime->getTimestamp();

                        $query = "
                            SELECT *
                            FROM {local_redmine_chat}
                            WHERE issueid = ?
                            ORDER BY timecreated DESC
                            LIMIT 1                
                        ";
                        $lastmessage = $DB->get_record_sql($query, [$issue['id']]);

                        if(isset($lastmessage->timecreated) && !empty($lastmessage->timecreated)) {
                            $lasttimestamp = $lastmessage->timecreated;
                        }else{
                            $lasttimestamp = $createdtimestamp;
                        }

                        $daysbefore = ceil((time() - $lasttimestamp) / (60 * 60 * 24));
                        $delta = 10 - $daysbefore;

                        if ($delta > 0) {
                            $counter++;
                        }
                    }
                }

                $result['counter'] = $counter;
            }
        }

        // Set user preference.
        set_user_preference('redmine_issues_time', time());
        set_user_preference('redmine_issues_counter', $result['counter']);

        return json_encode($result);
    }
    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function issues_counter_user_returns() {
        return new external_value(PARAM_RAW, 'Json result');
    }
}