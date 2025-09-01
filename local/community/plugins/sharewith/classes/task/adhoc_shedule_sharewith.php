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
 * Local plugin "sandbox" - Task definition
 *
 * @package    community_sharewith
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace community_sharewith\task;

use context_course;
use context_module;
use stdClass;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../lib.php');
require_once($CFG->dirroot . '/local/community/plugins/sharewith/classes/classFunctionHelp.php');

/**
 * The local_sandbox restore courses task class.
 *
 * @package    community_sharewith
 * @copyright  2014 Alexander Bias, Ulm University <alexander.bias@uni-ulm.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class adhoc_shedule_sharewith extends \core\task\adhoc_task {

    /**
     * Return localised task name.
     *
     * @return string
     */
    public function get_component() {
        return 'community_sharewith';
    }

    /**
     * Execute adhoc task
     *
     * @return boolean
     */
    public function execute() {
        global $CFG;

        $lockkey = 'sharewith_cron';
        $lockfactory = \core\lock\lock_config::get_lock_factory('community_sharewith_task');
        $lock = $lockfactory->get_lock($lockkey, 0);

        if ($lock !== false) {
            $this->run_cron_sharewith();
            $lock->release();
        }
    }

    /**
     * run_cron_sharewith
     */
    public function run_cron_sharewith() {
        global $CFG, $DB, $USER;

        require_once($CFG->dirroot . '/local/community/plugins/sharewith/classes/duplicate.php');

        $obj = $DB->get_records('community_sharewith_task', array('status' => 0));

        // End working.
        foreach ($obj as $item) {
            $item->status = 2;
            $DB->update_record('community_sharewith_task', $item);
        }

        foreach ($obj as $item) {
            try {
                switch ($item->type) {
                    case 'coursecopy':

                        $tc = get_course($item->sourcecourseid);

                        $category = $DB->get_record('course_categories', array('id' => $tc->category));

                        $fullname = $tc->fullname . ' ' . $category->name . ' ' . get_string('word_copy', 'community_sharewith');

                        $shortnamedefault = $tc->shortname . '-' . $tc->category;
                        $shortname = $this->create_relevant_shortname($shortnamedefault);

                        $adminid = isset($CFG->adminid) ? $CFG->adminid : 2;

                        // Copy course.
                        $newcourse =
                                \duplicate::duplicate_course($adminid, $tc->id, $fullname, $shortname, $item->categoryid);

                        // Copy metadata and update cID.
                        \local_metadata\mcontext::course()->copy_all_metadata($tc->id, $newcourse['id']);
                        \local_metadata\mcontext::course()->save($newcourse['id'], 'cID', $tc->id);

                        // Change startdate and enddate in new course.
                        $startdate = time();
                        $diff = $tc->enddate - $tc->startdate;
                        $enddate = ($diff > 0) ? $startdate + $diff : 0;

                        $obj = $DB->get_record('course', ['id' => $newcourse['id']]);
                        $obj->startdate = $startdate;
                        $obj->enddate = $enddate;

                        $DB->update_record('course', $obj);

                        // Set user to course.
                        if (!is_siteadmin($item->sourceuserid)) {
                            $namerole = 'editingteacher';
                            $role = $DB->get_record('role', array('shortname' => $namerole));
                            if (!empty($role)) {
                                enrol_try_internal_enrol($newcourse['id'], $item->sourceuserid, $role->id);
                            }
                        }

                        $roles = array();
                        $context = \context_course::instance($tc->id);
                        if ($userroles = get_user_roles($context, $item->sourceuserid)) {
                            foreach ($userroles as $role) {
                                $roles[] = $role->shortname;
                            }
                        }

                        $usertype = 'teacher';
                        if (in_array('teachercolleague', $roles)) {
                            $usertype = 'teachercolleague';
                        }

                        $eventdata = array(
                                'userid' => $item->sourceuserid,
                                'courseid' => $tc->id,
                                'categoryid' => $tc->category,
                                'targetcourseid' => $newcourse['id'],
                                'usertype' => $usertype,
                        );

                        \community_sharewith\event\course_copy::create_event($newcourse['id'], $eventdata)->trigger();

                        // Send mail.
                        $this->send_mail_to_teacher($item, $newcourse);

                        // Send notification.
                        $this->send_notification_to_teacher($item, $newcourse);

                        break;

                    case 'activitycopy':

                        $metadataobj = !empty($item->metadata) ? json_decode($item->metadata) : null;
                        $messagetype = isset($metadataobj->notification) ? $metadataobj->notification : "";
                        $warningselect = isset($metadataobj->metadata->warningselect) ? $metadataobj->metadata->warningselect : 0;

                        $item->disable_update_meta_history = $warningselect == 1 ? true : false;

                        $lib = new \duplicate();
                        $lib->set_obj_duplicate($item);
                        $lib->set_copy_type($messagetype);

                        if (isset($metadataobj->ifglossary) && $metadataobj->ifglossary == 1) {
                            $lib->enable_glossary_copy_users();
                        }

                        if (isset($metadataobj->ifdatabase) && $metadataobj->ifdatabase == 1) {
                            $lib->enable_database_copy_users();
                        }

                        $newactivities = array();
                        $sequence = (isset($metadataobj->activitysequence) && $metadataobj->activitysequence) ?
                                json_decode($metadataobj->activitysequence) : [];
                        $newcmids =
                                $lib->duplicate_activity($item->sourceactivityid, $item->courseid, $item->sectionid, $newactivities,
                                        $sequence);

                        // Mod learningmap.
                        foreach ($newcmids as $newcmid) {
                            $lib->copy_single_mod_learningmap($item->sourceactivityid, $newcmid, $item->courseid, $item->sectionid);
                        }

                        // Callback in metadata.
                        // Requred 'callbackfunc'.
                        // Optional 'callbackpath'.
                        // Metadata object metadataobj will sent to callback func.
                        // Example:
                        // {
                        //   "callbackpath": "/mod/quiz/report/competencyoverview/locallib.php",
                        //   "callbackfunc": "quiz_competencyoverview_message_to_students",
                        //   "message": "Some message",
                        //   "students": "3,4"
                        // }.

                        $callbackfunc = isset($metadataobj->callbackfunc) ? $metadataobj->callbackfunc : false;
                        if (isset($metadataobj->callbackpath)) {
                            require_once($CFG->dirroot . $metadataobj->callbackpath);
                        }

                        // Update field added in course_modules.
                        foreach ($newcmids as $newcmid) {

                            if (function_exists($callbackfunc)) {
                                call_user_func_array($callbackfunc, [$metadataobj, $newcmid]);
                            }

                            $newrow = $DB->get_record('course_modules', array('id' => $newcmid));
                            $newrow->added = time();
                            $DB->update_record('course_modules', $newrow);

                            // Add competencies.
                            if (isset($metadataobj->newactivitycompetencies) && $metadataobj->newactivitycompetencies != '') {
                                $newactivitycompetencies = explode(',', $metadataobj->newactivitycompetencies);
                                foreach ($newactivitycompetencies as $key => $compid) {
                                    $competresult = \core_competency\api::add_competency_to_course_module($newcmid, $compid);
                                }
                            }
                        }

                        /*
                         * PTL-927 Do not sent system notification and emails to teachers
                         *          when they initiate an activity copy or share.
                         */
                        // Send mail.
                        //$this->send_mail_to_teacher($item, $newcmid);

                        // Send notification.
                        //$this->send_notification_to_teacher($item, $newcmid);

                        switch ($messagetype) {
                            case "banksharing":

                                foreach ($newcmids as $newcmid) {

                                    // UPDATE linksectionids.
                                    \local_metadata\mcontext::module()
                                            ->save($newcmid, 'linksectionids', $metadataobj->linksectionids);

                                    // Update metadata.
                                    if (isset($metadataobj->metadata) && !empty($metadataobj->metadata)) {
                                        $func = new \functionHelp();
                                        $func->update_metadata_cron((array) $metadataobj->metadata, $item->sourceuserid, $newcmid);
                                    }

                                    // UPDATE MID IN METADATA.
                                    switch ($warningselect) {

                                        // Translate activity.
                                        case 1:
                                            \local_metadata\mcontext::module()->save($newcmid, 'ID', $newcmid);

                                            \local_metadata\mcontext::module()->save($newcmid, 'translatedby', $item->userid);

                                            $msourcemid = \local_metadata\mcontext::module()->get($item->sourceactivityid, 'ID');
                                            \local_metadata\mcontext::module()->save($newcmid, 'sourcemid', $msourcemid);

                                            \local_metadata\mcontext::module()->save($msourcemid, 'translatemid', $newcmid);

                                            \local_metadata\mcontext::module()->saveEmpty($newcmid, 'translatemid');

                                            $muserid = \local_metadata\mcontext::module()->get($item->sourceactivityid, 'userid');
                                            \local_metadata\mcontext::module()->save($newcmid, 'userid', $muserid);

                                            $activity = new \community_oer\activity_oer;
                                            $activity->activity_recalculate_in_db($newcmid);
                                            $activity->activity_recalculate_in_db($msourcemid);

                                            $activity->recalculate_data_in_cache();

                                            break;

                                        // Repair or improvement of the activity.
                                        case 2:
                                            \community_sharewith\funcs::send_mail_toadmin_about_duplicate_mid($item->sourceactivityid,
                                                    $newcmid);
                                            break;

                                        // A new pedagogical activity.
                                        case 3:
                                            \local_metadata\mcontext::module()->save($newcmid, 'ID', $newcmid);
                                            break;
                                        default:
                                            \local_metadata\mcontext::module()->save($newcmid, 'ID', $newcmid);
                                    }


                                    // Deactivate activities.
                                    set_coursemodule_visible($newcmid, 0);

                                    $eventdata = array(
                                            'userid' => $item->sourceuserid,
                                            'instanceid' => $item->sourceactivityid,
                                            'targetuserid' => $item->userid,
                                            'targetinstanceid' => $newcmid,
                                            'targetcourseid' => $item->courseid,
                                            'targetsectionid' => $item->sectionid,
                                    );

                                    // PTL-4202.
                                    if ($cm = $DB->get_record('course_modules', ['id' => $newcmid])) {
                                        if ($module = $DB->get_record('modules', ['id' => $cm->module])) {
                                            if ($extra = $DB->get_record($module->name, ['id' => $cm->instance])) {

                                                $cm->availability = null;
                                                $cm->completion = 0;
                                                $cm->completiongradeitemnumber = null;
                                                $cm->completionview = 0;
                                                $cm->completionexpected = 0;

                                                $DB->update_record('course_modules', $cm);

                                                switch ($module->name) {
                                                    case 'quiz':
                                                        $extra->timeopen = 0;
                                                        $extra->timeclose = 0;
                                                        break;
                                                    case 'assign':
                                                        $extra->duedate = 0;
                                                        $extra->allowsubmissionsfromdate = 0;
                                                        $extra->cutoffdate = 0;
                                                        $extra->gradingduedate = 0;
                                                        break;
                                                    case 'questionnaire':
                                                        $extra->opendate = 0;
                                                        $extra->closedate = 0;
                                                        break;
                                                }

                                                $DB->update_record($module->name, $extra);
                                            }
                                        }
                                    }

                                    // PTL-5822.
                                    if ($cm = $DB->get_record('course_modules', ['id' => $newcmid])) {

                                        switch (get_config('community_sharewith', 'visibilitytype')) {
                                            case 1:
                                                $cm->visible = 1;
                                                $cm->visibleold = 1;
                                                $cm->visibleoncoursepage = 1;
                                                break;
                                            case 2:
                                                $cm->visible = 0;
                                                $cm->visibleold = 0;
                                                $cm->visibleoncoursepage = 1;
                                                break;
                                            case 3:
                                                $cm->visible = 0;
                                                $cm->visibleold = 1;
                                                $cm->visibleoncoursepage = 0;
                                                break;
                                        }

                                        $DB->update_record('course_modules', $cm);

                                        // Rebuild cm and course cache.
                                        \course_modinfo::purge_course_module_cache($cm->course, $cm->id);
                                        rebuild_course_cache($cm->course, true, true);
                                    }

                                    \community_sharewith\event\activity_to_bank_copy::create_event($item->courseid, $eventdata)
                                            ->trigger();

                                    \community_sharewith\funcs::send_mail_toadmin_about_banksharing($item->sourceactivityid, $newcmid);
                                }
                                break;

                            case "bankdownload":

                                $targetsection = $DB->get_record('course_sections', ['id' => $item->sectionid]);

                                foreach ($newcmids as $newcmid) {

                                    // Deactivate activities.
                                    set_coursemodule_visible($newcmid, 0);

                                    // Save to oer catalog log.
                                    $arrinsert = array(
                                            'userid' => $item->sourceuserid,
                                            'activityid' => $item->sourceactivityid,
                                            'courseid' => $item->courseid,

                                        // TODO: should be $item->sectionid.
                                            'sectionid' => $targetsection->section,

                                        // TODO: we should add newsectionnum instead of sectionid.
                                        //'newsectionnum' => $targetsection->section,

                                            'newactivityid' => $newcmid,
                                            'timemodified' => time()
                                    );
                                    $DB->insert_record('community_oer_log', $arrinsert);

                                    // Save Moodle Log oercata log.
                                    if (!empty($metadataobj) && isset($metadataobj->referer)) {

                                        $eventdata = array(
                                                'userid' => $item->sourceuserid,
                                                'instanceid' => $item->sourceactivityid,
                                                'targetuserid' => $item->userid,
                                                'targetinstanceid' => $newcmid,
                                                'targetcourseid' => $item->courseid,
                                                'targetsectionid' => $item->sectionid,
                                                'referer' => $metadataobj->referer,
                                        );

                                        \community_sharewith\event\activity_from_bank_download::create_event($item->courseid,
                                                $eventdata)
                                                ->trigger();
                                    }
                                }

                                break;

                            case "copytohimself":

                                foreach ($newcmids as $newcmid) {
                                    $eventdata = array(
                                            'userid' => $item->sourceuserid,
                                            'instanceid' => $item->sourceactivityid,
                                            'targetuserid' => $item->userid,
                                            'targetinstanceid' => $newcmid,
                                            'targetcourseid' => $item->courseid,
                                            'targetsectionid' => $item->sectionid,
                                    );

                                    \community_sharewith\event\activity_copy::create_event($item->courseid, $eventdata)->trigger();
                                }
                                break;

                            case "copytoanotherteacher":

                                foreach ($newcmids as $newcmid) {
                                    $eventdata = array(
                                            'userid' => $item->sourceuserid,
                                            'instanceid' => $item->sourceactivityid,
                                            'targetuserid' => $item->userid,
                                            'targetinstanceid' => $newcmid,
                                            'targetcourseid' => $item->courseid,
                                            'targetsectionid' => $item->sectionid,
                                    );

                                    \community_sharewith\event\sent_activity_copy::create_event($item->courseid, $eventdata)
                                            ->trigger();
                                }
                                break;

                        }
                        break;

                    case 'sectioncopy':

                        // Subsections.
                        require_once($CFG->dirroot . '/course/format/lib.php');

                        $metadataobj = !empty($item->metadata) ? json_decode($item->metadata) : null;
                        $copysub = isset($metadataobj->copysub) ? $metadataobj->copysub : false;
                        $targetcourseformat = course_get_format($item->courseid)->get_format();

                        $lib = new \duplicate();
                        $lib->set_obj_duplicate($item);
                        $sectioncount = 0;
                        if ($copysub) {
                            $subsectionstree = \community_sharewith\funcs::get_subsections_tree($item->sourcesectionid);
                            $keys = [];
                            $newsection = $lib->duplicate_section($item->sourcesectionid, $item->courseid);
                            $sectioncount += 1;
                            $mainsection = $newsection->section;
                            foreach ($subsectionstree as $keysubs => $subs) {
                                if (count($keys) != 0) {
                                    $mainsection = $keys[$keysubs];
                                }
                                foreach ($subs as $keysub => $sub) {
                                    $newsubsection = $lib->duplicate_section($sub->id, $item->courseid);
                                    $sectioncount += 1;
                                    $keys[$keysub] = $newsubsection->section;

                                    // Restore sctructure.
                                    if ($targetcourseformat == 'flexsections') {
                                        $option = new stdClass();
                                        $option->courseid = $item->courseid;
                                        $option->format = $targetcourseformat;
                                        $option->sectionid = $newsubsection->id;
                                        $option->name = 'parent';
                                        $option->value = $mainsection;
                                        $DB->insert_record('course_format_options', $option);
                                    }
                                }
                            }
                        } else {
                            $newsection = $lib->duplicate_section($item->sourcesectionid, $item->courseid);
                            $sectioncount += 1;
                        }

                        // Update sID metadata.
                        \local_metadata\mcontext::section()->save($newsection->id, 'sID', $item->sourcesectionid);

                        // Send mail.
                        $this->send_mail_to_teacher($item, $newsection);

                        // Send notification.
                        $this->send_notification_to_teacher($item, $newsection);

                        $roles = array();
                        $context = \context_course::instance($item->courseid);
                        if ($userroles = get_user_roles($context, $item->sourceuserid)) {
                            foreach ($userroles as $role) {
                                $roles[] = $role->shortname;
                            }
                        }

                        $usertype = 'teacher';
                        if (in_array('teachercolleague', $roles)) {
                            $usertype = 'teachercolleague';
                        }

                        $eventdata = array(
                                'userid' => $item->sourceuserid,
                                'courseid' => $item->courseid,
                                'sectionid' => $item->sourcesectionid,
                                'targetuserid' => $item->userid,
                                'targetcourseid' => $item->courseid,
                                'targetsectionid' => $item->sectionid,
                                'usertype' => $usertype,
                        );

                        \community_sharewith\event\section_copy::create_event($item->courseid, $eventdata)->trigger();

                        break;
                }

                $item->status = 1;
                $DB->update_record('community_sharewith_task', $item);

            } catch (\Exception $e) {
                $item->error = $e->getMessage();
                $DB->update_record('community_sharewith_task', $item);
            }
        }
    }

    public function create_relevant_shortname($shortname) {
        global $CFG, $DB;

        $i = 1;
        do {
            $arr = $DB->get_records('course', array('shortname' => $shortname));
            if (!empty($arr)) {
                $shortname .= $i;
                $i++;
            } else {
                break;
            }
        } while (1);

        return $shortname;
    }

    public function send_mail_to_teacher($duplicatejob, $target) {
        global $DB, $OUTPUT, $CFG;
        $metadataobj = !empty($duplicatejob) ? json_decode($duplicatejob->metadata) : null;
        $messagetype = isset($metadataobj->notification) ? $metadataobj->notification : "";
        $supportuser = \core_user::get_support_user();

        switch ($duplicatejob->type) {
            case 'coursecopy':

                $touser = $DB->get_record('user', array('id' => $duplicatejob->userid));
                $newcourse = get_course($target['id']);

                $message = '';
                $subject = get_string('mail_subject_to_teacher_course', 'community_sharewith');

                // Render html.
                $templatecontext = array();
                $a = new \stdClass;
                $a->user_fname = $touser->firstname;
                $a->user_lname = $touser->lastname;
                $a->coursename = $newcourse->fullname;
                $a->url = $CFG->wwwroot . "/course/view.php?id=" . $target['id'];
                $templatecontext['url'] = $a->url;
                $templatecontext['notification_course_to_teacher'] =
                        get_string('notification_course_to_teacher', 'community_sharewith', $a);
                $messagehtml = $OUTPUT->render_from_template(
                        'community_sharewith/mails/mail_course_to_teacher', $templatecontext);

                email_to_user($touser, $supportuser, $subject, $message, $messagehtml);

                break;

            case 'activitycopy':

                $touser = $DB->get_record('user', array('id' => $duplicatejob->userid));
                $module = $DB->get_record('modules', ['id' => $target->module]);
                $message = '';
                $subject = get_string('mail_subject_to_teacher_activity', 'community_sharewith');

                $modinfo = get_fast_modinfo($duplicatejob->courseid);
                $cm = $modinfo->cms[$target->id];
                $activityname = $cm->name;
                $section = $DB->get_record('course_sections', array('id' => $duplicatejob->sectionid));
                $sectionname = $section->name;
                $course = $DB->get_record('course', array('id' => $duplicatejob->courseid));
                $coursename = $course->fullname;

                // Render html.
                $templatecontext = array();
                $a = new \stdClass;
                $a->user_fname = $touser->firstname;
                $a->user_lname = $touser->lastname;
                $a->coursename = $coursename;
                $a->activityname = $activityname;
                $a->sectionname = $sectionname;
                $templatecontext['url'] = $CFG->wwwroot . "/mod/" . $module->name . "/view.php?id=" . $target->id;
                switch ($messagetype) {
                    case "banksharing":
                        $templatecontext['notification_activity_to_banksharing'] =
                                get_string('notification_activity_to_banksharing', 'community_sharewith', $a);
                        $messagehtml = $OUTPUT->render_from_template(
                                'community_sharewith/notifications/notification_activity_to_banksharing',
                                $templatecontext);
                        break;
                    case "bankdownload":
                        $templatecontext['notification_activity_to_bankdownload'] =
                                get_string('notification_activity_to_bankdownload', 'community_sharewith', $a);
                        $messagehtml = $OUTPUT->render_from_template(
                                'community_sharewith/notifications/notification_activity_to_bankdownload',
                                $templatecontext);
                        break;
                    default:
                        $templatecontext['notification_activity_to_teacher'] =
                                get_string('notification_activity_to_teacher', 'community_sharewith', $a);
                        $messagehtml = $OUTPUT->render_from_template(
                                'community_sharewith/notifications/notification_activity_to_teacher',
                                $templatecontext);
                }

                email_to_user($touser, $supportuser, $subject, $message, $messagehtml);

                break;

            case 'sectioncopy':

                $touser = $DB->get_record('user', array('id' => $duplicatejob->userid));

                $message = '';
                $subject = get_string('mail_subject_to_teacher_activity', 'community_sharewith');

                $section = $DB->get_record('course_sections', array('id' => $duplicatejob->sourcesectionid));
                $course = $DB->get_record('course', array('id' => $duplicatejob->courseid));

                $sectionname = !empty($section->name) ? $section->name :
                        (get_string('sectionname', 'format_' . $course->format) . " " . $section->section);
                $coursename = $course->fullname;

                // Render html.
                $templatecontext = array();
                $a = new \stdClass;
                $a->user_fname = $touser->firstname;
                $a->user_lname = $touser->lastname;
                $a->coursename = $coursename;
                $a->sectionname = $sectionname;
                $templatecontext['url'] = $CFG->wwwroot . "/course/view.php?id=" . $target->course . "#section-" . $target->section;

                $templatecontext['notification_section_to_teacher'] =
                        get_string('notification_section_to_teacher', 'community_sharewith', $a);
                $messagehtml = $OUTPUT->render_from_template('community_sharewith/mails/mail_section_to_teacher', $templatecontext);

                email_to_user($touser, $supportuser, $subject, $message, $messagehtml);

                break;
        }
    }

    public function send_notification_to_teacher($duplicatejob, $target) {
        global $DB, $OUTPUT, $CFG;

        $metadataobj = !empty($duplicatejob) ? json_decode($duplicatejob->metadata) : null;
        $messagetype = isset($metadataobj->notification) ? $metadataobj->notification : "";
        $supportuser = \core_user::get_support_user();

        switch ($duplicatejob->type) {
            case 'coursecopy':

                $touser = $DB->get_record('user', array('id' => $duplicatejob->userid));
                $newcourse = get_course($target['id']);

                $subject = get_string('mail_subject_to_teacher_course', 'community_sharewith');

                // Render html.
                $templatecontext = array();
                $a = new \stdClass;
                $a->user_fname = $touser->firstname;
                $a->user_lname = $touser->lastname;
                $a->coursename = $newcourse->fullname;
                $url = new \moodle_url('/course/view.php', array("id" => $target['id']));
                $a->url = $url->out();
                $templatecontext['notification_course_to_teacher'] =
                        get_string('notification_course_to_teacher', 'community_sharewith', $a);
                $messagehtml = $OUTPUT->render_from_template(
                        'community_sharewith/notifications/notification_course_to_teacher', $templatecontext);
                $objinsert = new \stdClass();
                $objinsert->useridfrom = $supportuser->id;
                $objinsert->useridto = $touser->id;

                $objinsert->subject = $subject;
                $objinsert->fullmessage = $subject;
                $objinsert->fullmessageformat = 2;
                $objinsert->fullmessagehtml = $messagehtml;
                $objinsert->smallmessage = $subject;
                $objinsert->component = 'community_sharewith';
                $objinsert->eventtype = 'copy_course';
                $objinsert->timecreated = time();
                $objinsert->customdata = json_encode(array());

                $notificationid = $DB->insert_record('notifications', $objinsert);

                $objinsert = new \stdClass();
                $objinsert->notificationid = $notificationid;
                $DB->insert_record('message_petel_notifications', $objinsert);

                break;

            case 'activitycopy':

                $touser = $DB->get_record('user', array('id' => $duplicatejob->userid));
                $module = $DB->get_record('modules', ['id' => $target->module]);
                $subject = get_string('mail_subject_to_teacher_activity', 'community_sharewith');

                $modinfo = get_fast_modinfo($duplicatejob->courseid);
                $cm = $modinfo->cms[$target->id];
                $activityname = $cm->name;
                $section = $DB->get_record('course_sections', array('id' => $duplicatejob->sectionid));
                $course = $DB->get_record('course', array('id' => $duplicatejob->courseid));
                $sectionname = !empty($section->name) ? $section->name :
                        (get_string('sectionname', 'format_' . $course->format) . " " . $section->section);
                $coursename = $course->fullname;

                $templatecontext = array();
                $a = new \stdClass;
                $a->user_fname = $touser->firstname;
                $a->user_lname = $touser->lastname;
                $a->coursename = $coursename;
                $a->activityname = $activityname;
                $a->sectionname = $sectionname;
                $templatecontext['url'] = new \moodle_url("/mod/" . $module->name . "/view.php", array('id' => $target->id));

                switch ($messagetype) {
                    case "banksharing":
                        $templatecontext['notification_activity_to_banksharing'] =
                                get_string('notification_activity_to_banksharing', 'community_sharewith', $a);
                        $messagehtml = $OUTPUT->render_from_template(
                                'community_sharewith/notifications/notification_activity_to_banksharing',
                                $templatecontext);
                        break;
                    case "bankdownload":
                        $templatecontext['notification_activity_to_bankdownload'] =
                                get_string('notification_activity_to_bankdownload', 'community_sharewith', $a);
                        $messagehtml = $OUTPUT->render_from_template(
                                'community_sharewith/notifications/notification_activity_to_bankdownload',
                                $templatecontext);
                        break;
                    default:
                        $templatecontext['notification_activity_to_teacher'] =
                                get_string('notification_activity_to_teacher', 'community_sharewith', $a);
                        $messagehtml = $OUTPUT->render_from_template(
                                'community_sharewith/notifications/notification_activity_to_teacher',
                                $templatecontext);
                }

                $objinsert = new \stdClass();
                $objinsert->useridfrom = $supportuser->id;
                $objinsert->useridto = $touser->id;

                $objinsert->subject = $subject;
                $objinsert->fullmessage = $subject;
                $objinsert->fullmessageformat = 2;
                $objinsert->fullmessagehtml = $messagehtml;
                $objinsert->smallmessage = $subject;
                $objinsert->component = 'community_sharewith';
                $objinsert->eventtype = 'copy_activity';
                $objinsert->timecreated = time();
                $objinsert->customdata = json_encode(array());

                $notificationid = $DB->insert_record('notifications', $objinsert);

                $objinsert = new \stdClass();
                $objinsert->notificationid = $notificationid;
                $DB->insert_record('message_petel_notifications', $objinsert);

                break;

            case 'sectioncopy':

                $touser = $DB->get_record('user', array('id' => $duplicatejob->userid));

                $message = '';
                $subject = get_string('mail_subject_to_teacher_activity', 'community_sharewith');

                $section = $DB->get_record('course_sections', array('id' => $duplicatejob->sourcesectionid));
                $course = $DB->get_record('course', array('id' => $duplicatejob->courseid));

                $sectionname = !empty($section->name) ? $section->name :
                        (get_string('sectionname', 'format_' . $course->format) . " " . $section->section);
                $coursename = $course->fullname;

                // Render html.
                $templatecontext = array();
                $a = new \stdClass;
                $a->user_fname = $touser->firstname;
                $a->user_lname = $touser->lastname;
                $a->coursename = $coursename;
                $a->sectionname = $sectionname;
                $templatecontext['url'] = $CFG->wwwroot . "/course/view.php?id=" . $target->course . "#section-" . $target->section;
                $templatecontext['notification_section_to_teacher'] =
                        get_string('notification_section_to_teacher', 'community_sharewith', $a);
                $messagehtml =
                        $OUTPUT->render_from_template('community_sharewith/notifications/notification_section_to_teacher',
                                $templatecontext);

                $objinsert = new \stdClass();
                $objinsert->useridfrom = $supportuser->id;
                $objinsert->useridto = $touser->id;

                $objinsert->subject = $subject;
                $objinsert->fullmessage = $subject;
                $objinsert->fullmessageformat = 2;
                $objinsert->fullmessagehtml = $messagehtml;
                $objinsert->smallmessage = $subject;
                $objinsert->component = 'community_sharewith';
                $objinsert->eventtype = 'copy_section';
                $objinsert->timecreated = time();
                $objinsert->customdata = json_encode(array());

                $notificationid = $DB->insert_record('notifications', $objinsert);

                $objinsert = new \stdClass();
                $objinsert->notificationid = $notificationid;
                $DB->insert_record('message_petel_notifications', $objinsert);

                break;
        }
    }
}
