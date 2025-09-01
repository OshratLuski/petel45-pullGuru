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
 * @package    community_sharequestion
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace community_sharequestion\task;

use context_course;
use context_module;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../lib.php');
require_once($CFG->dirroot . '/lib/questionlib.php');

/**
 * The local_sandbox restore courses task class.
 *
 * @package    community_sharequestion
 * @copyright  2014 Alexander Bias, Ulm University <alexander.bias@uni-ulm.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class adhoc_sharequestion extends \core\task\adhoc_task {

    /**
     * Return localised task name.
     *
     * @return string
     */
    public function get_component() {
        return 'community_sharequestion';
    }

    /**
     * Execute adhoc task
     *
     * @return boolean
     */
    public function execute() {

        $lockkey = 'sharequestion_cron' . time();
        $lockfactory = \core\lock\lock_config::get_lock_factory('community_sharequestion_task');
        $lock = $lockfactory->get_lock($lockkey, 0);

        if ($lock !== false) {
            $this->run_cron_sharequestion();
            $lock->release();
        }
    }

    public function run_cron_sharequestion() {
        global $DB, $USER, $CFG;

        $obj = $DB->get_records('community_sharequestion_task', array('status' => 0));

        // End working.
        foreach ($obj as $item) {
            $item->status = 2;
            $DB->update_record('community_sharequestion_task', $item);
        }

        $copiedquizids = [];
        $copiedcategoryids = [];

        foreach ($obj as $item) {
            switch ($item->type) {
                case 'copy_to_quiz':
                    $USER = get_admin();

                    if (!empty($item->targetuserid)) {
                        $copiedquizids[$item->targetuserid][] = $item->targetcmid;
                    }

                    try {
                        $context = \context_module::instance($item->targetcmid);
                        $category = question_make_default_categories([$context]);

                        $newquestionid =
                                \community_sharequestion\duplicate_question::duplicate_single_question($item->sourcequestionid,
                                        $category->id);
                        \community_sharequestion\duplicate_question::copy_question_metadata($item->sourcequestionid,
                                $newquestionid);
                        \community_sharequestion\duplicate_question::add_question_to_quiz($item->targetcmid, $newquestionid);

                        $item->status = 1;
                        $DB->update_record('community_sharequestion_task', $item);

                    } catch (\Exception $e) {
                        $item->error = $e->getMessage();
                        $DB->update_record('community_sharequestion_task', $item);
                    }
                    break;

                case 'copy_to_category':
                    $USER = get_admin();

                    if (!empty($item->targetuserid)) {
                        $copiedcategoryids[$item->targetuserid][] = $item->targetcatid;
                    }

                    try {
                        $newquestionid =
                                \community_sharequestion\duplicate_question::duplicate_single_question($item->sourcequestionid,
                                        $item->targetcatid);
                        \community_sharequestion\duplicate_question::copy_question_metadata($item->sourcequestionid,
                                $newquestionid);

                        $item->status = 1;
                        $DB->update_record('community_sharequestion_task', $item);

                    } catch (\Exception $e) {
                        $item->error = $e->getMessage();
                        $DB->update_record('community_sharequestion_task', $item);
                    }
                    break;

                case 'upload_to_catalog':
                    require_once($CFG->dirroot . '/course/lib.php');
                    require_once($CFG->dirroot . '/lib/questionlib.php');

                    $USER = get_admin();

                    try {
                        // Create category.
                        $section = $DB->get_record('course_sections', ['id' => $item->targetsectionid]);
                        $categoryname = get_section_name($section->course, $section->section);
                        $categoryname = shorten_text($categoryname, 255);

                        $context = \context_course::instance($section->course);
                        $topcategory = question_get_top_category($context->id, true);

                        $idnumber = 'CSID-' . $item->targetsectionid;

                        $category = $DB->get_record('question_categories', [
                                'contextid' => $context->id,
                                'parent' => $topcategory->id,
                                'idnumber' => $idnumber,
                            //'name' => $categoryname
                        ]);

                        if (empty($category)) {
                            $category = new \stdClass();
                            $contextname = $context->get_context_name(false, true);

                            // Max length of name field is 255.
                            $category->name = $categoryname;
                            $category->info = get_string('defaultinfofor', 'question', $contextname);
                            $category->contextid = $context->id;
                            $category->parent = $topcategory->id;
                            // By default, all categories get this number, and are sorted alphabetically.
                            $category->sortorder = 999;
                            $category->stamp = make_unique_id_code();
                            $category->idnumber = $idnumber;
                            $category->id = $DB->insert_record('question_categories', $category);
                        }

                        $metadata = json_decode($item->metadata);
                        $newquestionid =
                                \community_sharequestion\duplicate_question::duplicate_single_question($item->sourcequestionid,
                                        $category->id, false);
                        \community_sharequestion\duplicate_question::add_metadata_to_question($newquestionid,
                                $item->sourcequestionid, $metadata);

                        // Add competencies.
                        if (isset($metadata->competencies) && !empty($metadata->competencies)) {
                            $competencies = explode(',', $metadata->competencies);
                            foreach ($competencies as $compid) {
                                $competresult = \core_competency\api::add_competency_to_question($newquestionid, $compid);
                            }
                        }

                        $item->status = 1;
                        $DB->update_record('community_sharequestion_task', $item);

                        $eventdata = array(
                                'userid' => $item->targetuserid,
                                'qid' => $newquestionid,
                                'sourcequestionids' => $newquestionid,
                                'targetsectionid' => $item->targetsectionid,
                        );

                        \community_sharequestion\event\question_to_catalog_copy::create_event($eventdata)->trigger();

                    } catch (\Exception $e) {
                        $item->error = $e->getMessage();
                        $DB->update_record('community_sharequestion_task', $item);
                    }
                    break;
            }
        }

        // Send notification to user about sucessful question copied to quiz.
        //        foreach($copiedquizids as $userid => $array){
        //            foreach(array_unique($array) as $cmid){
        //                $adminer = get_admin();
        //
        //                $cm = $DB->get_record('course_modules', ['id' => $cmid]);
        //                if(!empty($cm)){
        //                    $quiz = $DB->get_record('quiz', ['id' => $cm->instance]);
        //
        //                    $a = new \stdClass();
        //                    $a->name = $quiz->name;
        //                    $a->url = $CFG->wwwroot.'/mod/quiz/edit.php?cmid='.$cmid;
        //                    $htmlmessage = get_string('notificationmessage', 'community_sharequestion', $a);
        //                    $smallmessage = $htmlmessage;
        //
        //                    $customdata = array();
        //                    $customdata['custom'] = true;
        //                    $customdata['custom_html_only'] = true;
        //
        //                    $objinsert = new \stdClass();
        //                    $objinsert->useridfrom = $adminer->id;
        //                    $objinsert->useridto = $userid;
        //                    $objinsert->subject = $smallmessage;
        //                    $objinsert->fullmessage = $smallmessage;
        //                    $objinsert->fullmessageformat = 2;
        //                    $objinsert->fullmessagehtml = $htmlmessage;
        //                    $objinsert->smallmessage = $smallmessage;
        //                    $objinsert->component = 'community_sharequestion';
        //                    $objinsert->eventtype = 'custom_html_only';
        //                    $objinsert->timecreated = time();
        //                    $objinsert->customdata = json_encode($customdata);
        //
        //                    $notificationid = $DB->insert_record('notifications', $objinsert);
        //
        //                    $objinsert = new \stdClass();
        //                    $objinsert->notificationid = $notificationid;
        //                    $DB->insert_record('message_petel_notifications', $objinsert);
        //                }
        //            }
        //        }
    }
}
