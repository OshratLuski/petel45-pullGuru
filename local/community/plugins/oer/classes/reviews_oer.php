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
 * @package    community_oer
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace community_oer;

use \stdClass;

class reviews_oer {

    public function __construct() {
    }

    /**
     * Update stack of requests. Check if an user has a new activity copied one week ago
     * and at least five students have enrolled (finished activity)
     *
     */
    public static function update_stack($userid) {
        global $DB, $CFG;

        $sql = "
            SELECT ol.id as id, ol.activityid as activityid, ol.newactivityid as newactivityid
            FROM {community_oer_log} ol
            LEFT JOIN {course_modules} cm ON cm.id = ol.activityid
            LEFT JOIN {modules} m ON m.id = cm.module
            WHERE ol.userid = ?
                AND m.name IN ('quiz','questionnaire','hvp','assign')
                AND (ol.timemodified 
                    BETWEEN UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 2 WEEK)) 
                    AND UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 0 WEEK)))
                AND ol.activityid NOT IN (
                    SELECT ol2.activityid
                    FROM {community_oer_log} ol2
                    WHERE ol2.id IN (
                        SELECT orr2.logid
                        FROM {community_oerctlg_rvw_rqsts} orr2
                        WHERE orr2.userid = ? AND orr2.state IN (2,3)
                    )
                )
        ";
        $options = array($userid, $userid);
        $newlogs = $DB->get_records_sql($sql, $options);
        if ($CFG->debugdeveloper) {
            mtrace("UserID= $userid have new log reviews = " . count($newlogs));
        }
        $sql = "
            SELECT *
            FROM {community_oerctlg_rvw_rqsts} orr
            LEFT JOIN {community_oer_log} ol ON orr.logid = ol.id
            WHERE orr.userid = ? AND orr.state NOT IN (2,3)
        ";
        $currentrequests = $DB->get_records_sql($sql, array($userid));
        $currentlogs = array_unique(array_map(function($a) {
            return $a->logid;
        }, $currentrequests));
        $currentact = array_unique(array_map(function($a) {
            return $a->activityid;
        }, $currentrequests));

        if (count($newlogs)) {
            $coursestudents = array();
            $activityviewed = get_config('community_oer', 'activityviewed');
            foreach ($newlogs as $log) {
                $stillexistsold = $DB->count_records('course_modules', array('id' => $log->activityid));
                $stillexistsnew = $DB->count_records('course_modules', array('id' => $log->newactivityid));
                if ($stillexistsold && $stillexistsnew) {
                    if (isset($coursestudents[$log->newactivityid])) {
                        $currentcourseviewed = $coursestudents[$log->newactivityid];
                    } else {
                        $sqlm = "SELECT m.name
                                FROM {course_modules} cm
                                LEFT JOIN {modules} m ON m.id = cm.module
                                WHERE cm.id = ?";
                        $cm = $DB->get_record_sql($sqlm, array($log->newactivityid));

                        // Used by students in the last 6 weeks.
                        if (in_array($cm->name, ['hvp', 'quiz', 'questionnaire'])) {
                            $sql2 = "SELECT count(distinct userid) as studentsviewed
                                    FROM {logstore_standard_log}
                                    WHERE contextinstanceid = ?
                                    AND action = 'submitted' 
                                    AND (timecreated 
                                        BETWEEN UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 6 WEEK)) 
                                        AND UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 0 WEEK)))";
                        } else if (in_array($cm->name, ['assign'])) {
                            $sql2 = "SELECT count(distinct userid) as studentsviewed
                                    FROM {logstore_standard_log}
                                    WHERE contextinstanceid = ?
                                    AND action = 'created' AND target = 'submission' 
                                    AND (timecreated 
                                        BETWEEN UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 6 WEEK)) 
                                        AND UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 0 WEEK)))";
                        }
                        $viewed = $DB->get_record_sql($sql2, array($log->newactivityid));
                        $currentcourseviewed = $viewed->studentsviewed;
                        $coursestudents[$log->newactivityid] = $currentcourseviewed;
                    }
                    if ($CFG->debugdeveloper) {
                        mtrace("   Students submitted: $currentcourseviewed for {$log->newactivityid}");
                    }
                    if ((int) $currentcourseviewed >= (int) $activityviewed) {
                        $exist = array_search($log->id, $currentlogs);
                        if (!$exist) {
                            // State = 0 - new, 1 - started, 2 - viewed, 3 - rejected, 4 || 99 - skip to show.
                            $state = 0;

                            /*PTL-6032 We stop limiting teacher review feedback count.
                            $limitation = self::check_if_user_has_limit();
                            if ($limitation['reviewsnumber'] >= 5) {
                                $state = 4; // Set state 4, record the review request into DB, but skip to show it.
                            }*/

                            $request = array(
                                    'userid' => $userid,
                                    'logid' => $log->id,
                                    'state' => $state,
                                    'views' => 0,
                                    'cohort' => 0,
                                    'timecreated' => time(),
                                    'timemodified' => time()
                            );
                            if (!in_array($log->activityid, $currentact)) {
                                // Dynamic allocate user (teacher) cohort research group.
                                // Split 50% teachers in group a and 50% in group b.

                                if (isset($CFG->eladresearch_cohort_a)
                                        && isset($CFG->eladresearch_cohort_b)) {
                                    if ($CFG->debugdeveloper) {
                                        mtrace("Checking cohorts...");
                                    }
                                    $cohortamembers = $DB->get_records_sql(
                                            "SELECT mcm.userid 
                                            FROM {cohort_members} mcm
                                            JOIN {cohort} mc ON mc.id = mcm.cohortid
                                            WHERE mc.idnumber=? ", [$CFG->eladresearch_cohort_a]);

                                    $cohortaid = $DB->get_record_sql("SELECT id FROM {cohort} WHERE idnumber=? ",
                                            [$CFG->eladresearch_cohort_a]);

                                    $cohortbmembers = $DB->get_records_sql(
                                            "SELECT mcm.userid 
                                            FROM {cohort_members} mcm
                                            JOIN {cohort} mc ON mc.id = mcm.cohortid
                                            WHERE mc.idnumber=? ", [$CFG->eladresearch_cohort_b]);

                                    $cohortbid = $DB->get_record_sql("SELECT id FROM {cohort} WHERE idnumber=? ",
                                            [$CFG->eladresearch_cohort_b]);

                                    if (count($cohortamembers) >= count($cohortbmembers)) {
                                        $request['cohort'] = (int) $cohortbid->id;
                                    } else {
                                        $request['cohort'] = (int) $cohortaid->id;
                                    }

                                    if ($CFG->debugdeveloper) {
                                        mtrace(' dynamic split ' . $userid . ' into cohort ' . $request['cohort']);
                                    }

                                    // If already in one of the research cohorts, use that cohort.
                                    $iscohortmember = $DB->get_record_sql(
                                            "SELECT mc.id 'cid'
                                        FROM {cohort_members} mcm
                                        JOIN {cohort} mc ON mc.id = mcm.cohortid WHERE mcm.userid=? AND mc.idnumber IN (?,?)",
                                            [$userid, $CFG->eladresearch_cohort_a, $CFG->eladresearch_cohort_b]);

                                    if ($iscohortmember) {
                                        $request['cohort'] = (int) $iscohortmember->cid;

                                        if ($CFG->debugdeveloper) {
                                            mtrace("Userid $userid already a cohort {$iscohortmember->cid} member");
                                        }
                                    }
                                }

                                // Update cohort too.
                                $cohortmember = [
                                        'cohortid' => (int) $request['cohort'],
                                        'userid' => $userid,
                                        'timeadded' => time()];
                                try {
                                    $DB->insert_record('cohort_members', $cohortmember);
                                } catch (\Exception $e) {
                                    mtrace_exception($e);
                                    //throw new \moodle_exception('error');
                                }

                                $DB->insert_record('community_oerctlg_rvw_rqsts', $request);
                                $currentact[] = $log->activityid;
                                if ($CFG->debugdeveloper) {
                                    mtrace('OK ' . $log->activityid);
                                }
                            }
                        } else {
                            unset($currentlogs[$exist]);
                        }
                    }
                }
            }
        }

        /*if (count($currentlogs)) {
            $sql = "DELETE FROM {community_oerctlg_rvw_rqsts}
                        WHERE logid IN (" . implode(',', $currentlogs) . ")
                            AND userid = ?
                            AND state NOT IN (2,3)";

            $DB->execute($sql, array($userid));
        }*/

        if ($CFG->debugdeveloper) {
            mtrace('Current log reviews = ' . count($currentlogs));
        }
    }

    /*
     * Check and process popups
     */
    public static function popup_engine($courseid = null, $activity = null) {
        global $DB, $USER, $PAGE;

        // Find if we have any popups to show. status = 0 - new, 1 - started, 2 - viewed, 3 - rejected, 4 - skip to
        // show.
        $sql = "
            SELECT *, orr.id as requestid
            FROM {community_oerctlg_rvw_rqsts} orr
            LEFT JOIN {community_oer_log} ol ON ol.id = orr.logid
            WHERE orr.userid = ? AND orr.state IN (0,1)";
        $params = [$USER->id];

        if ($courseid) {
            $sql .= " AND ol.courseid = ? 
            ";
            $params[] = $courseid;
        }

        if ($activity) {
            $sql .= " AND ol.newactivityid = ? 
            ";
            $activityid = $PAGE->cm->id;
            $params[] = $activityid;
        }

        $sql .= " ORDER BY orr.state DESC, orr.timecreated ASC
            LIMIT 1
        ";

        $requests = $DB->get_records_sql($sql, $params);

        $limitation = array(
                'limiteduser' => false,
                'allowed' => true,
                'reviewsnumber' => 0,
        );

        $popupdata = false;
        while (count($requests) && !$popupdata) {
            $request = array_shift($requests);
            $newactivityexist = $DB->count_records('course_modules', array('id' => $request->newactivityid));
            $activitydata = self::get_activity($request->activityid);

            $popupdata = new stdClass();
            $popupdata->metadata_id = $activitydata->metadata_id;
            $popupdata->activity_id = $activitydata->activity_id;
            $popupdata->mod_name = $activitydata->mod_name;
            $popupdata->image = $activitydata->image;
            $popupdata->url = $activitydata->url;

            if (isset($popupdata->activity_id) && $popupdata->activity_id && $newactivityexist) {
                $popupdata->requestid = $request->requestid;
                $popupdata->views = $request->views;
                $popupdata->allowed = $limitation['allowed'];
            } else {
                // Delete copied activity not present.
                $DB->delete_records('community_oerctlg_rvw_rqsts', array('id' => $request->requestid));

                $popupdata->allowed = false;
            }

            $popupdata->limiteduser = $limitation['limiteduser'];
            $popupdata->reviewsnumber = $limitation['reviewsnumber'];
            $popupdata->reviewsnumberleft = (int) (5 - $limitation['reviewsnumber']);
            $popupdata->reviewspc = $limitation['reviewsnumber'] * 20;
            $popupdata->showprogress = (bool) !$courseid;

            $progressitems = [];
            if ($popupdata->showprogress) {
                for ($s = 0; $s <= 5; $s++) {
                    $progressitems[$s] = (object) array(
                            'step' => $s,
                            'class' => ($s <= $popupdata->reviewsnumber) ? ($s < $popupdata->reviewsnumber ? 'passed' : 'active') :
                                    '',
                    );
                }
            }

            $popupdata->progressitems = array_values($progressitems);
            $rejectreviewbutton = get_config('community_oer', 'rejectreviewbutton');
            $popupdata->firstname = $USER->firstname;

            if (isset($popupdata->views)) {
                $popupdata->rejectReview = $popupdata->views >= $rejectreviewbutton ? true : false;
            } else {
                $popupdata->rejectReview = false;
            }
        }
        return $popupdata;
    }

    public static function check_if_user_has_limit() {
        global $CFG, $USER, $DB;
        $response = array(
                'limiteduser' => false,
                'allowed' => true,
                'reviewsnumber' => 0,
        );
        $reviewcohortid = get_config('community_oer', 'reviewcohort');
        if (isset($reviewcohortid) && $reviewcohortid != -1) {
            require_once($CFG->dirroot . '/cohort/lib.php');
            if (cohort_is_member($reviewcohortid, $USER->id)) {
                $response['limiteduser'] = true;
                $reviewstrdate = get_config('community_oer', 'reviewscountstartdate');
                $reviewdate = $reviewstrdate ? strtotime($reviewstrdate) : 0;
                $select = "userid = ? AND timecreated > ? AND reviewtype = 'activity'";
                $response['reviewsnumber'] = $DB->count_records_select('community_oer_reviews', $select, array
                ($USER->id, $reviewdate));
                if ($response['reviewsnumber'] >= 5) {
                    $response['allowed'] = false;
                }
            }
        }
        return $response;
    }

    public static function remind_me_later($requestid) {
        // No action yet.

        return '';
    }

    public static function reject_review($requestid) {
        global $DB, $USER;
        $request = $DB->get_record('community_oerctlg_rvw_rqsts', array('id' => $requestid, 'userid' => $USER->id));
        if ($request) {
            // Was 3.
            $request->state = 99;
            $DB->update_record('community_oerctlg_rvw_rqsts', $request);
            return true;
        }
        return false;
    }

    public static function count_review_oncourse($courseid) {
        global $DB, $USER;
        if ($courseid === 1) {
            return 0;
        }
        // Find if we have any popups to show. status = 0 - new, 1 - started, views no more than 3.
        $sql = "SELECT COUNT(orr.id), orr.id as requestid
                FROM {community_oerctlg_rvw_rqsts} orr
                LEFT JOIN {community_oer_log} ol ON ol.id = orr.logid
                WHERE orr.userid = ? AND orr.state IN (0,1) AND ol.courseid = ? 
                ORDER BY orr.state DESC, orr.timecreated ASC
            ";
        return $DB->count_records_sql($sql, array($USER->id, $courseid));
    }

    public static function add_review_ajax($params, $files) {
        global $DB, $USER;
        $result = false;
        $objcountreviews = 0;

        if ($params['requestid']) {
            $request = $DB->get_record('community_oerctlg_rvw_rqsts', array('id' => $params['requestid'], 'userid' => $USER->id));
            $shared = $DB->get_record('community_oer_log', array('id' => $request->logid, 'userid' => $USER->id));

            $review = array(
                    'userid' => $USER->id,
                    'logid' => $request->logid,
                    'reviewtype' => $params['reviewtype'],
                    'objid' => $shared->activityid,
                    'recommendation' => $params['recommendation'],
                    'feedback' => $params['reviewdata'],
                    'timecreated' => time(),
                    'timemodified' => time(),
            );
            $result = $DB->insert_record('community_oer_reviews', $review);
            if ($result) {
                $request->state = 2;
                $res2 = $DB->update_record('community_oerctlg_rvw_rqsts', $request);
            }

            if (($params['errorreporting'] && $params['errorreporting'] != 'undefined') || $params['issuedescr'] || count($files)) {

                $error = array(
                        'userid' => $USER->id,
                        'logid' => $request->logid,
                        'errortype' => $params['errorreporting'],
                        'errortext' => $params['issuedescr'],
                        'timecreated' => time(),
                        'timemodified' => time(),
                );
                $res3 = $DB->insert_record('community_oer_errors', $error);

                $errorimages = array();
                if ($res3 && count($files)) {
                    foreach ($files as $file) {
                        if (!empty($file['tmp_name'])) {
                            $filecontent = file_get_contents($file['tmp_name']);
                            $filename = $file['name'];

                            $fs = get_file_storage();
                            $draftitemid = file_get_unused_draft_itemid();

                            // Prepare file record object.
                            $fileinfo = array(
                                    'contextid' => $shared->activityid,
                                    'component' => 'community_oercatalog',
                                    'filearea' => 'image',
                                    'itemid' => $draftitemid,
                                    'filepath' => '/',
                                    'userid' => $USER->id,
                                    'source' => $filename,
                                    'filename' => $filename
                            );

                            // Create file containing content.
                            $fs->create_file_from_string($fileinfo, $filecontent);

                            $record = $DB->get_record_sql(
                                    'SELECT * FROM {files} WHERE contextid=? AND itemid=? AND filesize!=0 AND source IS NOT NULL',
                                    [$shared->activityid, $draftitemid]);

                            if (!empty($record->filename)) {
                                $url = $draftitemid;
                            } else {
                                $url = '';
                            }

                            if ($url) {
                                $errorimage = array(
                                        'errorid' => $res3,
                                        'imagepath' => $url,
                                );
                                $res4 = $DB->insert_record('community_oer_er_imgs', $errorimage);

                                $errorimages[] = array(
                                        'filename' => $fileinfo['filename'],
                                        'description' => '',
                                        'content_type' => $record->mimetype,
                                        'content' => $filecontent,
                                );
                            }
                        }
                    }
                }

                $activity = self::get_activity($shared->activityid, false);

                $error['activityname'] = $activity->mod_name;
                $error['activityurl'] = $activity->url;
                $error['errorimages'] = $errorimages;
                $result = self::send_error($error);

            }
        } else if ($params['objid'] && !$params['requestid']) {
            $review = array(
                    'userid' => $USER->id,
                    'logid' => 0,
                    'reviewtype' => $params['reviewtype'],
                    'objid' => (int) $params['objid'],
                    'recommendation' => $params['recommendation'],
                    'feedback' => $params['reviewdata'],
                    'timecreated' => time(),
                    'timemodified' => time(),
            );
            $result = $DB->insert_record('community_oer_reviews', $review);

            $objcountreviews = $DB->count_records('community_oer_reviews', [
                    'reviewtype' => $params['reviewtype'],
                    'objid' => (int) $params['objid'],
            ]);
        }
        $countreview = self::count_review_oncourse($params['courseid']);

        // Send messages to participiants.
        self::send_message_to_participiants($params['objid'], null, $params['reviewtype']);

        return array('result' => $result, 'countreview' => $countreview, 'objcountreviews' => $objcountreviews);
    }

    public static function show_review($objid, $type) {
        global $DB, $USER, $PAGE;

        $PAGE->set_context(\context_system::instance());

        $response = [];
        $sql = "
              SELECT orr.*, from_unixtime(orr.timecreated, '%d.%m.%Y') as reviewdate,  u.id as uid, u.firstname as firstname, u.lastname as lastname, u.picture as userpicture
              FROM {community_oer_reviews} orr
              LEFT JOIN {user} u ON u.id = orr.userid
              WHERE orr.objid = :objid AND orr.reviewtype = :type
              ORDER BY orr.timecreated DESC
            ";

        $rawreviews = $DB->get_records_sql($sql, array('objid' => $objid, 'type' => $type));

        $reviews = [];
        $fs = get_file_storage();
        if (count($rawreviews)) {
            foreach ($rawreviews as $review) {
                $sql = "SELECT orc.*, from_unixtime(orc.timecreated, '%d.%m.%Y') as commentdate, orc.timecreated as created, orc.timemodified as modified, u.id as uid, u.firstname as firstname, u.lastname as lastname, u.picture as userpicture
                        FROM {community_oerctlg_rvw_cmmnts} orc
                        LEFT JOIN {user} u ON u.id = orc.userid
                        WHERE orc.reviewid = :reviewid
                      ";
                $rawcomments = $DB->get_records_sql($sql, array('reviewid' => $review->id));
                $comments = [];

                if (count($rawcomments)) {
                    foreach ($rawcomments as $comment) {
                        if ($usercomment = $DB->get_record('user', ['id' => $review->userid])) {
                            $userpicture = new \user_picture($usercomment);
                            $userpicture->size = 1; // Size f1.
                            $imageurl = $userpicture->get_url($PAGE)->out(false);
                        } else {
                            $imageurl = '';
                        }

                        $comments[] = array(
                                'commentid' => $comment->id,
                                'commentdate' => $comment->commentdate,
                                'firstname' => $comment->firstname,
                                'lastname' => $comment->lastname,
                                'userpicture' => $imageurl,
                                'comment' => $comment->comment,
                                'edited' => (int) $comment->modified - (int) $comment->created,
                                'showbuttons' => ($comment->uid == $USER->id) || is_siteadmin(),
                        );
                    }
                }

                $feedback = json_decode($review->feedback);

                if ($userreview = $DB->get_record('user', ['id' => $review->userid])) {
                    $userpicture = new \user_picture($userreview);
                    $userpicture->size = 1; // Size f1.
                    $imageurl = $userpicture->get_url($PAGE)->out(false);
                } else {
                    $imageurl = '';
                }

                $sql = "
                    SELECT ra.*
                    FROM {role_assignments} ra
                    LEFT JOIN {role} r ON (r.id = ra.roleid)
                    WHERE r.shortname = 'manager' AND ra.contextid = ? AND ra.userid = ?                
                ";

                $context = \context_coursecat::instance(\community_oer\main_oer::get_oer_category());
                $row = $DB->get_record_sql($sql, [$context->id, $USER->id]);

                $showdeletebutton = $row ? true : false;
                $showdeletebutton = is_siteadmin() || $showdeletebutton;

                $reviews[] = array(
                        'reviewid' => $review->id,
                        'recommendation' => $review->recommendation,
                        'reviewdate' => $review->reviewdate,
                        'firstname' => $review->firstname,
                        'lastname' => $review->lastname,
                        'userpicture' => $imageurl,
                        'objby' => isset($feedback->activityby) ? self::get_timing($feedback->activityby) : '',
                        'classroom' => isset($feedback->classroom) ? true : false,
                        'home' => isset($feedback->home) ? true : false,
                        'phone' => isset($feedback->phone) ? true : false,
                        'pc' => isset($feedback->pc) ? true : false,
                        'howtouse' => isset($feedback->classroom) && isset($feedback->home) && isset($feedback->phone) &&
                        isset($feedback->pc) ? true : false,

                        'descr' => isset($feedback->descr) ? $feedback->descr : '',
                        'descr1' => isset($feedback->descr1) ? $feedback->descr1 : '',
                        'descr2' => isset($feedback->descr2) ? $feedback->descr2 : '',
                        'descr3' => isset($feedback->descr3) ? $feedback->descr3 : '',
                        'comments' => $comments,
                        'totalcomments' => count($comments),
                        'showdeletebutton' => $showdeletebutton
                );
            }
        }

        $userpicture = new \user_picture($USER);
        $userpicture->size = 1; // Size f1.
        $imageurl = $userpicture->get_url($PAGE)->out(false);

        $response['objid'] = $objid;
        $response['objtype'] = $type;
        $response['reviews'] = array_values($reviews);
        $response['userfirstname'] = $USER->firstname;
        $response['userlastname'] = $USER->lastname;
        $response['avatar'] = $imageurl;
        $response['header'] = '';
        $response['objurl'] = '';
        $response['objlinkname'] = '';
        $response['objcreated'] = '';
        $response['author'] = '';
        $response['messageurl'] = '';

        switch ($type) {
            case 'activity':
                $activity = new \community_oer\activity_oer;

                $obj = $activity->single_cmid_render_data($objid);
                if (!empty($obj) && !empty($obj->users)) {
                    $user = reset($obj->users);

                    $messageurl = ($user->userid && is_siteadmin()) ? (new \moodle_url('/message/index.php', ['user2'
                    => $user->userid]))->out() : "";

                    $a = new \StdClass();
                    $a->count = count($rawreviews);
                    $a->activityName = $obj->mod_name;
                    $response['header'] = get_string('responses_to_activity', 'community_oer', $a);
                    $response['objurl'] = $obj->urlactivity;
                    $response['objlinkname'] = get_string('open_activity', 'community_oer');
                    $response['objcreated'] = $obj->cm_created_format;
                    $response['author'] = $user->user_fname.' '.$user->user_lname;
                    $response['messageurl'] = $messageurl;
                }
                break;
            case 'course':
                $course = new \community_oer\course_oer;
                $data = $course->query()->compare('cid', $objid)->get();

                $obj = reset($data);
                if (!empty($obj) && !empty($obj->users)) {
                    $user = reset($obj->users);

                    $messageurl = ($user->userid && is_siteadmin()) ? (new \moodle_url('/message/index.php', ['user2'
                    => $user->userid]))->out() : "";

                    $a = new \StdClass();
                    $a->count = count($rawreviews);
                    $a->courseName = $obj->fullname;
                    $response['header'] = get_string('responses_to_course', 'community_oer', $a);
                    $response['objurl'] = (new \moodle_url('/course/view.php', ['id' => $obj->cid]))->out(false);
                    $response['objlinkname'] = get_string('open_course', 'community_oer');
                    $response['objcreated'] = gmdate("d.m.Y", $obj->metadata_cshared_at);
                    $response['author'] = $user->user_fname.' '.$user->user_lname;
                    $response['messageurl'] = $messageurl;
                }
                break;
            case 'question':
                $question = new \community_oer\question_oer;
                $data = $question->query()->compare('qid', $objid)->get();

                $obj = reset($data);
                if (!empty($obj)) {
                    $messageurl = ($obj->userid && is_siteadmin()) ? (new \moodle_url('/message/index.php', ['user2'
                    => $obj->userid]))->out() : "";

                    $a = new \StdClass();
                    $a->count = count($rawreviews);
                    $a->questionName = $obj->qname_text;
                    $response['header'] = get_string('responses_to_question', 'community_oer', $a);
                    $response['objurl'] = (new \moodle_url('/question/preview.php', ['id' => $obj->qid]))->out(false);
                    $response['objlinkname'] = get_string('open_question', 'community_oer');
                    $response['objcreated'] = gmdate("d.m.Y", $obj->qtimecreated);
                    $response['author'] = $obj->username;
                    $response['messageurl'] = $messageurl;
                }
                break;
            case 'sequence':
                $sequence = new \community_oer\sequence_oer;
                $data = $sequence->query()->compare('seqid', $objid)->get();

                $obj = reset($data);
                if (!empty($obj) && !empty($obj->tab_data_sequence->users)) {
                    $user = reset($obj->tab_data_sequence->users);

                    $messageurl = ($user->userid && is_siteadmin()) ?
                        (new \moodle_url('/message/index.php', ['user2' => $user->userid]))->out() : "";

                    $a = new \StdClass();
                    $a->count = count($rawreviews);
                    $a->sequenceName = $obj->seqname;
                    $response['header'] = get_string('responses_to_sequence', 'community_oer', $a);

                    $response['objurl'] =
                            (new \moodle_url('/course/view.php',
                                    ['id' => $obj->courseid, 'section' => $obj->section]))->out(false);
                    $response['objlinkname'] = get_string('open_sequence', 'community_oer');
                    $response['objcreated'] = '';
                    $response['author'] = $user->user_fname.' '.$user->user_lname;
                    $response['messageurl'] = $messageurl;

                }
                break;
        }

        // Event data.
        $eventdata = array(
                'type' => $type,
                'userid' => $USER->id,
                'objid' => $objid,
        );
        \community_oer\event\oer_reviews_open::create_event($eventdata)->trigger();

        return json_encode($response);
    }

    public static function send_comment($params) {
        global $DB, $USER;

        $res = false;
        if ($params['reviewid'] && trim($params['comment'])) {
            $comment = array(
                    'userid' => $USER->id,
                    'reviewid' => $params['reviewid'],
                    'comment' => $params['comment'],
                    'timecreated' => time(),
                    'timemodified' => time()
            );
            $res = $DB->insert_record('community_oerctlg_rvw_cmmnts', $comment);

            // Send messages to participiants.
            self::send_message_to_participiants(null, $res);
        }

        // Event data.
        $eventdata = array(
                'userid' => $USER->id,
                'reviewid' => $params['reviewid'],
                'comment' => $params['comment'],
        );
        \community_oer\event\oer_reviews_addcomment::create_event($eventdata)->trigger();

        return $res ? $res : 0;
    }

    public static function edit_comment($params) {
        global $DB, $USER;

        $res = false;
        if ($params['commentid'] && trim($params['comment'])) {
            $comment = $DB->get_record('community_oerctlg_rvw_cmmnts', array('id' => $params['commentid'], 'userid' => $USER->id));
            if ($comment && $comment->comment != $params['comment']) {
                $comment->comment = $params['comment'];
                $comment->timemodified = time();
                $res = $DB->update_record('community_oerctlg_rvw_cmmnts', $comment);

                // Send messages to participiants.
                self::send_message_to_participiants(null, $params['commentid']);
            } else {
                $res = true;
            }
        }

        // Event data.
        $eventdata = array(
                'userid' => $USER->id,
                'commentid' => $params['commentid'],
                'comment' => $params['comment'],
        );
        \community_oer\event\oer_reviews_editcomment::create_event($eventdata)->trigger();

        return $res ? 1 : 0;
    }

    public static function add_view_to_request($requestid) {
        global $DB, $USER;

        $request = $DB->get_record('community_oerctlg_rvw_rqsts', array('id' => $requestid, 'userid' => $USER->id));
        if ($request) {
            $request->views += 1;
            $request->state = 1;
            $DB->update_record('community_oerctlg_rvw_rqsts', $request);
        }
    }

    public static function delete_review($reviewid) {
        global $DB, $USER;

        $res = $DB->delete_records('community_oer_reviews', array('id' => $reviewid));
        $DB->delete_records('community_oerctlg_rvw_cmmnts', array('reviewid' => $reviewid));

        // Event data.
        $eventdata = array(
                'userid' => $USER->id,
                'reviewid' => $reviewid,
        );
        \community_oer\event\oer_reviews_deletemessage::create_event($eventdata)->trigger();

        return $res ? 1 : 0;
    }

    public static function delete_comment($commentid) {
        global $DB, $USER;

        $res = $DB->delete_records('community_oerctlg_rvw_cmmnts', array('id' => $commentid, 'userid' => $USER->id));

        // Event data.
        $eventdata = array(
                'userid' => $USER->id,
                'commentid' => $commentid,
        );
        \community_oer\event\oer_reviews_deletecomment::create_event($eventdata)->trigger();

        return $res ? 1 : 0;
    }

    protected static function get_activity($act, $getimage = true) {
        global $DB, $USER, $CFG;

        $query = '
        SELECT DISTINCT name
        FROM {modules} m
        JOIN {course_modules} cm ON m.id = cm.module
        WHERE cm.id=' . $act;
        $namemods = $DB->get_records_sql($query);

        $query = 'SELECT id, shortname FROM {local_metadata_field} WHERE contextlevel=70';
        $namemetadata = $DB->get_records_sql($query);

        $query = '
        SELECT cm.id AS activity_id, cs.id AS section_id, cs.name AS section_name, cs.summary AS section_summary,
        cm.added AS activity_created, cm.module AS mod_id, cm.instance AS mod_instance, m.name AS mod_type,

        ';

        if (!empty($namemods)) {
            $query .= " CASE ";

            foreach ($namemods as $item) {
                $query .= " WHEN " . $item->name . "_r.name IS NOT NULL THEN " . $item->name . "_r.name ";
            }

            $query .= "
            ELSE ''
            END AS mod_name,

            CASE ";

            foreach ($namemods as $item) {
                $query .= " WHEN " . $item->name . "_r.intro IS NOT NULL THEN " . $item->name . "_r.intro ";
            }

            $query .= "
            ELSE ''
            END AS mod_intro,
            ";

        }

        // Get metadata parameters.
        if (!empty($namemetadata)) {
            foreach ($namemetadata as $item) {
                $query .= " metadata_" . $item->shortname . ".data AS metadata_" . $item->shortname . ", ";
            }
        }

        $query .= "
        metadata_imageactivity.data AS image_activity

        FROM {course_modules} cm

        JOIN {course_sections} cs ON cm.section=cs.id
        JOIN {modules} m ON m.id = cm.module
        ";

        // Get mods parameters.
        if (!empty($namemods)) {
            foreach ($namemods as $item) {
                $query .= " LEFT JOIN (SELECT id,course,NAME,intro,'" . $item->name . "' FROM {" . $item->name . "}) AS " .
                        $item->name . "_r ON (" . $item->name . "_r.id=cm.instance AND " . $item->name . "_r." . $item->name .
                        "=m.name)";
            }
        }

        // Get metadata parameters.
        if (!empty($namemetadata)) {
            foreach ($namemetadata as $item) {
                $query .= " LEFT JOIN {local_metadata} metadata_" . $item->shortname . " ON (metadata_" . $item->shortname .
                        ".instanceid=cm.id AND metadata_" . $item->shortname . ".fieldid=" . $item->id . ") ";
            }
        }

        $query .= "
        WHERE cm.id=" . $act;

        $activitiesall = $DB->get_records_sql($query);
        $activity = array_shift($activitiesall);

        if (isset($activity->activity_id) && $activity->activity_id) {
            if ($getimage == true) {
                $context = \context_module::instance($activity->activity_id);
                $activity->image = \community_oer\main_oer::create_url_image($activity->image_activity, $context);
            }

            // Prepare url to original activity.
            switch ($activity->mod_type) {
                case "quiz":
                    $activity->url =
                            $CFG->wwwroot . '/mod/' . $activity->mod_type . '/startattempt.php?cmid=' . $act . '&sesskey=' .
                            $USER->sesskey;
                    break;
                case "questionnaire":
                    $activity->url = $CFG->wwwroot . '/mod/' . $activity->mod_type . '/preview.php?id=' . $act;
                    break;
                default:
                    $activity->url = $CFG->wwwroot . '/mod/' . $activity->mod_type . '/view.php?id=' . $act;
            }
        }

        return $activity;
    }

    protected static function get_timing($id) {
        $timings = array(
                1 => get_string('preparation_for_topic', 'community_oer'),
                2 => get_string('topic_start', 'community_oer'),
                3 => get_string('during_subject', 'community_oer'),
                4 => get_string('when_topic_over', 'community_oer'),
                5 => get_string('inpreparation_for_test', 'community_oer'),
        );
        return $timings[(int) $id];
    }

    protected static function send_error($error) {
        global $CFG, $USER, $DB, $COURSE;

        if (get_config('local_redmine', 'redminestatus')) {

            require_once($CFG->dirroot . '/local/redmine/vendor/autoload.php');

            $client = new \Redmine\Client\NativeCurlClient(
                get_config('local_redmine', 'redmineurl'),
                get_config('local_redmine', 'redmineusername'),
                get_config('local_redmine', 'redminepassword'));

            if (!empty($CFG->proxyhost)) {
                $client->setCurlOption(CURLOPT_PROXY, $CFG->proxyhost.':'.$CFG->proxyport);
            }

            switch ($error['errortype']) {
                case 'bug':
                    $questiontype = get_string('i_want_to_report_a_bug', 'community_oer');
                    $trackerid = 1;
                    $assignedtoid = $CFG->redminereporterid;
                    break;
                case 'mistake':
                    $questiontype = get_string('i_want_to_report_a_contentbug', 'community_oer');
                    $trackerid = 8;
                    $assignedtoid = $CFG->redmine_leadoercatalog;
                    break;
            }

            $pageurl = $error['activityurl'];

            $a = new stdClass();
            $a->username = $USER->username;
            $userprofileurl = new \moodle_url('/user/profile.php?id=' . $USER->id);
            $a->name = clean_filename($USER->firstname . ' ' . $USER->lastname) . ' ' . $userprofileurl;
            $a->email = $USER->email;

            // Figure out teachers mentor by looking at parent categories and getting mentors idnumber.
            $mentors = $DB->get_records_sql(
                    'SELECT u.firstname, u.lastname, u.email
                FROM {course_categories} cc
                JOIN {context} ctx ON ctx.instanceid = cc.parent AND ctx.contextlevel = 40
                JOIN {role_assignments} ra ON ra.contextid = ctx.id
                JOIN {user} u ON u.id = ra.userid
                WHERE cc.idnumber IN (SELECT u.idnumber FROM {user} u WHERE u.id = ?)
            ', [$USER->id]);

            $mentoremails = "\n";
            if (count($mentors) > 0 && count($mentors) < 3) {
                foreach ($mentors as $mentor) {
                    $mentoremails .= "\n Mentor: $mentor->firstname $mentor->lastname < $mentor->email >";
                }
            } else {
                $mentoremails = $CFG->allmightymentor;
            }

            $a->moreinfo = $error['errortext'] . " " . $mentoremails;
            $a->question = $questiontype;
            $a->activityname = $error['activityname'];
            $a->pageurl = $pageurl;
            $a->digest = mb_substr($error['errortext'], 0, 85) . '...';

            $description = get_string('redmine_description', 'community_oer', $a);
            $subject = get_string('redmine_subject', 'community_oer', $a);

            $watchers[] = ($error['errortype'] === 'error') ? $CFG->redminewatcherbugid : '';

            $issue = [
                    'project_id' => 'petel',
                    'subject' => $subject,
                    'description' => $description,
                    'assigned_to_id' => $assignedtoid,
                    'watcher_user_ids' => $watchers,
                    'tracker_id' => $trackerid,
            ];

            $newissuexml = $client->getApi('issue')->create($issue);

            $redmineid = (isset($newissuexml->id['0'])) ? $newissuexml->id['0'] : '#0000';

            if (count($error['errorimages'])) {
                foreach ($error['errorimages'] as $image) {

                    $upload = json_decode($client->getApi('attachment')->upload($image['content']));
                    $client->getApi('issue')->attach($redmineid, [
                        'token' => $upload->upload->token,
                        'filename' => $image['filename'],
                        'description' => $image['description'],
                        'content_type' => $image['content_type'],
                    ]);
                }
            }

            // Send notification to user about sucessful issue creation.
            $adminer = \core_user::get_noreply_user();

            // Prepare the message.
            $eventdata = new \core\message\message();
            $eventdata->courseid = $COURSE->id;
            $eventdata->component = 'moodle';
            $eventdata->name = 'instantmessage';
            $eventdata->notification = 1;

            $eventdata->userfrom = $adminer;
            $eventdata->userto = $USER->id;
            $eventdata->subject = get_string('supportconfirmsubject', 'local_redmine', $questiontype);
            $eventdata->fullmessage = get_string('supportconfirmbody', 'local_redmine') . "\n ID: " . $redmineid;
            $eventdata->fullmessageformat = FORMAT_PLAIN;
            $eventdata->fullmessagehtml = '';

            $eventdata->smallmessage = get_string('supportconfirmsmall', 'local_redmine') . "\n ID: " . $redmineid;
            $eventdata->contexturl = $pageurl;
            $eventdata->contexturlname = get_string('supporturl', 'local_redmine');

            // Send it.
            return message_send($eventdata);
        } else {
            return 'Redmine Disabled!';
        }
    }

    // Send message to participiants.
    public static function send_message_to_participiants($objid = null, $commentid = null, $reviewtype = null) {
        global $DB, $CFG;

        $obj = new \StdClass();
        $users = [];

        if ($objid != null && $reviewtype != null) {
            foreach ($DB->get_records('community_oer_reviews', ['objid' => $objid, 'reviewtype' => $reviewtype]) as $item) {
                $users[] = $item->userid;
            }
        } else {
            if ($commentid != null) {
                $comment = $DB->get_record('community_oerctlg_rvw_cmmnts', ['id' => $commentid]);
                if (!empty($comment)) {
                    foreach ($DB->get_records('community_oerctlg_rvw_cmmnts', ['reviewid' => $comment->reviewid]) as $item) {
                        $users[] = $item->userid;
                    }

                    $review = $DB->get_record('community_oer_reviews', ['id' => $comment->reviewid]);
                    $users[] = $review->userid;

                    $reviewtype = $review->reviewtype;
                    $objid = $review->objid;
                }
            }
        }

        if (empty($users)) {
            return false;
        }

        switch ($reviewtype) {
            case 'activity':
                if ($cm = $DB->get_record('course_modules', ['id' => $objid])) {
                    $module = $DB->get_record('modules', ['id' => $cm->module]);
                    $act = $DB->get_record($module->name, ['id' => $cm->instance]);

                    $obj->name = $act->name;
                    $obj->url = $CFG->wwwroot . '/local/community/plugins/oer/activityshare.php?id=' . $cm->id . '&popup=1';
                }
                break;
            case 'course':
                if ($course = $DB->get_record('course', ['id' => $objid])) {
                    $obj->name = $course->fullname;
                    $obj->url = $CFG->wwwroot . '/course/view.php?id=' . $course->id;
                }
                break;
            case 'question':
                if ($question = $DB->get_record('question', ['id' => $objid])) {
                    $obj->name = $question->name;
                    $obj->url = '';
                }
                break;
            case 'sequence':
                if ($sequence = $DB->get_record('course_sections', ['id' => $objid])) {

                    require_once($CFG->dirroot . "/course/format/lib.php");

                    $format = course_get_format($sequence->course);
                    $sectionname = $format->get_section_name($sequence->section);

                    $obj->name = $sectionname;
                    $obj->url = '';
                }
                break;
        }

        if ($obj == null || empty($obj)) {
            return false;
        }

        $userfrom = get_admin();

        $a = new \stdClass();
        $a->name = $obj->name;
        $a->url = $obj->url;

        $htmlmessage = get_string('reviewnotificationmessage', 'community_oer', $a);
        $smallmessage = $htmlmessage;

        foreach (array_unique($users) as $useridto) {
            $customdata = array();
            $customdata['custom'] = true;
            $customdata['custom_html_only'] = true;

            $objinsert = new \stdClass();
            $objinsert->useridfrom = $userfrom->id;
            $objinsert->useridto = $useridto;
            $objinsert->subject = $smallmessage;
            $objinsert->fullmessage = $smallmessage;
            $objinsert->fullmessageformat = 2;
            $objinsert->fullmessagehtml = $htmlmessage;
            $objinsert->smallmessage = $smallmessage;
            $objinsert->component = 'community_oer';
            $objinsert->eventtype = 'custom_html_only';
            $objinsert->timecreated = time();
            $objinsert->customdata = json_encode($customdata);

            $notificationid = $DB->insert_record('notifications', $objinsert);

            $objinsert = new \stdClass();
            $objinsert->notificationid = $notificationid;
            $DB->insert_record('message_petel_notifications', $objinsert);
        }

        return true;
    }

    public static function send_remind($activityid, $remindtext, $ifsend) {
        global $DB;

        $remindtext = html_entity_decode($remindtext);

        if ($ifsend) {

            // Activity shared by users.
            $sql = "
                SELECT id, userid, activityid, courseid
                FROM {community_oer_log}
                WHERE activityid = ?
                GROUP BY courseid, userid
            ";

            $tmp = [];
            foreach ($DB->get_records_sql($sql, [$activityid]) as $item) {
                $tmp[$item->userid]['courses'][] = $item->courseid;
            }

            $data = [];
            foreach ($tmp as $userid => $item) {

                $data[$userid]['courses'] = [];

                // Check courses active/not active.
                foreach ($item['courses'] as $courseid) {
                    try {
                        $course = get_course($courseid);

                        // If active.
                        $time = time();
                        if ($course->startdate < $time && $course->enddate >= $time) {

                            // Prepare activities.
                            $cms = $DB->get_records('community_oer_log', [
                                    'userid' => $userid,
                                    'activityid' => $activityid,
                                    'courseid' => $courseid
                            ]);

                            $activities = [];
                            foreach ($cms as $obj) {
                                try {

                                    list($course, $cm) = get_course_and_cm_from_cmid($obj->newactivityid);
                                    if ($mod = $DB->get_record($cm->modname, ['id' => $cm->instance])) {
                                        $activities[] = [
                                                'id' => $cm->id,
                                                'name' => $mod->name,
                                                'cmlink' => new \moodle_url('/mod/'.$cm->modname.'/view.php', ['id' => $cm->id]),
                                        ];
                                    }
                                } catch (\Exception $e) {

                                }
                            }

                            $data[$userid]['courses'][] = [
                                    'id' => $course->id,
                                    'fullname' => $course->fullname,
                                    'courselink' => new \moodle_url('/course/view.php', ['id' => $course->id]),
                                    'activities' => $activities
                            ];
                        }
                    } catch (\Exception $e) {

                    }
                }
            }

            foreach ($data as $useridto => $item) {
                if (!empty($item['courses'])) {
                    $addon = get_string('active_courses', 'community_oer') . '</br>';

                    foreach ($item['courses'] as $course) {
                        foreach ($course['activities'] as $act) {
                            $addon .= $course['fullname'].' - '. '<a href="'.$act['cmlink'].'">'.$act['name'].'</a>' . '</br>';
                        }
                    }
                } else {
                    $addon = get_string('no_active_courses', 'community_oer');
                }

                $notificationtext = $remindtext . '<br>' . $addon;

                // Prepare notification.
                $customdata = array();
                $customdata['custom'] = true;
                $customdata['custom_html_only'] = true;

                $userfrom = get_admin();

                $objinsert = new \stdClass();
                $objinsert->useridfrom = $userfrom->id;
                $objinsert->useridto = $useridto;
                $objinsert->subject = $notificationtext;
                $objinsert->fullmessage = $notificationtext;
                $objinsert->fullmessageformat = 2;
                $objinsert->fullmessagehtml = $notificationtext;
                $objinsert->smallmessage = $notificationtext;
                $objinsert->component = 'community_oer';
                $objinsert->eventtype = 'custom_html_only';
                $objinsert->timecreated = time();
                $objinsert->customdata = json_encode($customdata);

                $notificationid = $DB->insert_record('notifications', $objinsert);

                $objinsert = new \stdClass();
                $objinsert->notificationid = $notificationid;
                $DB->insert_record('message_petel_notifications', $objinsert);
            }
        }

        // Update metadata version.
        \local_metadata\mcontext::module()->save($activityid, 'version', date("YmdHi"));

        // Update metadata versionhistory.
        $history = \local_metadata\mcontext::module()->get($activityid, 'versionhistory');
        $history = !empty($history) ? $history . '<br><br>' . $remindtext : $remindtext;
        \local_metadata\mcontext::module()->save($activityid, 'versionhistory', $history);

        $activityname = '';
        $cm = $DB->get_record('course_modules', ['id' => $activityid]);
        if ($cm) {
            $module = $DB->get_record('modules', ['id' => $cm->module]);
            $act = $DB->get_record($module->name, ['id' => $cm->instance]);
            $activityname = $act->name;
        }

        $activity = new \community_oer\activity_oer;
        $activity->activity_recalculate_in_db($activityid);
        $activity->recalculate_data_in_cache();

        return json_encode(['activityid' => $activityid, 'activityName' => $activityname]);
    }

    public static function check_version_of_oercatalog_activity($cmid) {
        global $CFG;

        if (!\community_oer\main_oer::check_if_user_admin_or_teacher()) {
            return [false, ''];
        }

        // Get oer cmid and version from metadata parameters.
        $mid = \local_metadata\mcontext::module()->get($cmid, 'ID');
        $version = \local_metadata\mcontext::module()->get($cmid, 'version');
        $version = intval(trim($version));

        $activity = new \community_oer\activity_oer;
        $obj = $activity->query(-1)->compare('metadata_ID', $mid)->groupBy('cmid');
        $values = array_values($obj->get());
        $data = array_shift($values);

        if (empty($data)) {
            return [false, ''];
        }

        $oerversion = intval(trim($data->metadata_version));

        if ($version < $oerversion) {

            $a = new \stdClass();
            $a->name = $data->mod_name;
            $a->url = $CFG->wwwroot . '/local/community/plugins/oer/activityshare.php?id=' . $mid;
            $a->version_history = $data->metadata_versionhistory;

            $description = get_string('cm_version_changed', 'community_oer', $a);
            return [true, $description];
        }

        return [false, ''];
    }
}
