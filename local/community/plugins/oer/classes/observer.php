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
 * @package    community_oer
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class community_oer_observer {

    /**
     * Observer for \core\event\course_category_created event.
     *
     * @param \core\event\course_category_created $event
     * @return void
     */
    public static function course_category_created(\core\event\course_category_created $event) {

        // Structure data.
        \community_oer\main_oer::recache_main_structure_elements();
        list($categories, $courses, $activities) = \community_oer\main_oer::get_main_structure_elements();

        if (in_array($event->objectid, $categories)) {
            \community_oer\main_oer::purge_structure();
        }
    }

    /**
     * Observer for \core\event\course_category_updated event.
     *
     * @param \core\event\course_category_updated $event
     * @return void
     */
    public static function course_category_updated(\core\event\course_category_updated $event) {

        // Structure data.
        \community_oer\main_oer::recache_main_structure_elements();
        list($categories, $courses, $activities) = \community_oer\main_oer::get_main_structure_elements();

        if (in_array($event->objectid, $categories)) {
            \community_oer\main_oer::purge_structure();
        }
    }

    /**
     * Observer for \core\event\course_category_updated event.
     *
     * @param \core\event\course_category_deleted $event
     * @return void
     */
    public static function course_category_deleted(\core\event\course_category_deleted $event) {

        // Structure data.
        \community_oer\main_oer::recache_main_structure_elements();
        list($categories, $courses, $activities) = \community_oer\main_oer::get_main_structure_elements();

        if (in_array($event->objectid, $categories)) {
            \community_oer\main_oer::purge_structure();
        }
    }

    /**
     * Observer for \core\event\course_created event.
     *
     * @param \core\event\course_created $event
     * @return void
     */
    public static function course_created(\core\event\course_created $event) {

        // Structure data.
        \community_oer\main_oer::recache_main_structure_elements();
        list($categories, $courses, $activities) = \community_oer\main_oer::get_main_structure_elements();

        if (in_array($event->objectid, $courses)) {
            \community_oer\main_oer::purge_structure();
        }

        // Course module.
        $csubject = \local_metadata\mcontext::course()->get($event->objectid, 'csubject');
        if (!empty($csubject)) {
            \local_metadata\mcontext::course()->save($event->objectid, 'cversion', date("YmdHi"));
        }

        $course = new \community_oer\course_oer();
        $course->update_observer($event->objectid);
    }

    /**
     * Observer for \core\event\course_updated event.
     *
     * @param \core\event\course_updated $event
     * @return void
     */
    public static function course_updated(\core\event\course_updated $event) {
        global $DB;

        // Structure data.
        \community_oer\main_oer::recache_main_structure_elements();
        list($categories, $courses, $activities) = \community_oer\main_oer::get_main_structure_elements();

        if (in_array($event->objectid, $courses)) {
            \community_oer\main_oer::purge_structure();
        }

        // Course module.
        $csubject = \local_metadata\mcontext::course()->get($event->objectid, 'csubject');
        if (!empty($csubject)) {
            \local_metadata\mcontext::course()->save($event->objectid, 'cversion', date("YmdHi"));
        }

        $course = new \community_oer\course_oer();
        $course->update_observer($event->objectid);
    }

    /**
     * Observer for \core\event\course_updated event.
     *
     * @param \core\event\course_updated $event
     * @return void
     */
    public static function course_deleted(\core\event\course_deleted $event) {
        global $DB;

        // Structure data.
        \community_oer\main_oer::purge_structure();

        // Activity module.
        $DB->delete_records('community_oer_activity', ['courseid' => $event->objectid]);

        $activity = new \community_oer\activity_oer;
        $activity->recalculate_data_in_cache();

        // Question module.
        $DB->delete_records('community_oer_question', ['courseid' => $event->objectid]);

        $question = new \community_oer\question_oer;
        $question->recalculate_data_in_cache();

        // Sequence module.
        $DB->delete_records('community_oer_sequence', ['courseid' => $event->objectid]);

        $sequence = new \community_oer\sequence_oer();
        $sequence->recalculate_data_in_cache();

        // Course module.
        $DB->delete_records('community_oer_course', ['courseid' => $event->objectid]);
        $DB->delete_records('community_oer_course', ['cid' => $event->objectid]);

        $course = new \community_oer\course_oer();
        $course->recalculate_data_in_cache();
    }

    public static function update_metadata(\local_metadata\event\update_metadata $event) {
        global $DB;

        if ($event->contextlevel == CONTEXT_COURSECAT) {

            // Structure data.
            \community_oer\main_oer::recache_main_structure_elements();
            list($categories, $courses, $activities) = \community_oer\main_oer::get_main_structure_elements();

            if (in_array($event->objectid, $categories)) {
                \community_oer\main_oer::purge_structure();

                // Activity.
                $activity = new \community_oer\activity_oer;
                foreach ($DB->get_records('community_oer_activity', ['catid' => $event->objectid]) as $item) {
                    $activity->activity_recalculate_in_db($item->cmid);
                }

                $activity->recalculate_data_in_cache();
            }
        }

        if ($event->contextlevel == CONTEXT_COURSE) {

            // Structure data.
            \community_oer\main_oer::recache_main_structure_elements();
            list($categories, $courses, $activities) = \community_oer\main_oer::get_main_structure_elements();

            if (in_array($event->objectid, $courses)) {
                \community_oer\main_oer::purge_structure();

                // Activity module.
                $activity = new \community_oer\activity_oer;
                foreach ($DB->get_records('community_oer_activity', ['courseid' => $event->objectid]) as $item) {
                    $activity->activity_recalculate_in_db($item->cmid);
                }

                $activity->recalculate_data_in_cache();
            }

            // Course module.
            // Course enrol.
            $task = new \community_oer\task\adhoc_oer_course_enrol();
            $task->set_custom_data(
                    ['cid' => $event->objectid]
            );
            \core\task\manager::queue_adhoc_task($task);

            $csubject = \local_metadata\mcontext::course()->get($event->objectid, 'csubject');
            if (!empty($csubject)) {
                \local_metadata\mcontext::course()->save($event->objectid, 'cversion', date("YmdHi"));
            }

            $course = new \community_oer\course_oer();
            $course->update_observer($event->objectid);
        }

        if ($event->contextlevel == CONTEXT_MODULE) {

            // Activity module.
            \community_oer\main_oer::recache_main_structure_elements();
            list($categories, $courses, $activities) = \community_oer\main_oer::get_main_structure_elements();

            if (in_array($event->objectid, $activities)) {
                $activity = new \community_oer\activity_oer;
                $activity->activity_recalculate_in_db($event->objectid);
                $activity->recalculate_data_in_cache();
            }

            // Sequence module.
            $sequence = new \community_oer\sequence_oer();
            list($sequences, $activities) = $sequence->get_main_sequence_elements();

            foreach ($activities as $seqid => $cmids) {
                if (in_array($event->objectid, $cmids)) {
                    \local_metadata\mcontext::section()->save($seqid, 'sversion', date("YmdHi"));

                    if ($sequence->sequence_recalculate_in_db($seqid) != false) {
                        $sequence->recalculate_data_in_cache();
                    }
                }
            }
        }

        // Metadata.
        if ($event->contextlevel == 10) {

            // Question module.
            $question = new \community_oer\question_oer;
            \local_metadata\mcontext::question()->save($event->objectid, 'qversion', date("YmdHi"));
            if ($question->question_recalculate_in_db($event->objectid) != false) {
                $question->recalculate_data_in_cache();
            } else {
                \local_metadata\mcontext::question()->save($event->objectid, 'qversion', '');
            }

            // Sequence module.
            $sequence = new \community_oer\sequence_oer();
            list($sequences, $activities) = $sequence->get_main_sequence_elements();
            if (in_array($event->objectid, $sequences)) {
                \local_metadata\mcontext::section()->save($event->objectid, 'sversion', date("YmdHi"));

                if ($sequence->sequence_recalculate_in_db($event->objectid) != false) {
                    $sequence->recalculate_data_in_cache();
                }
            }
        }
    }

    public static function question_to_catalog_copy(\community_sharequestion\event\question_to_catalog_copy $event) {

        // Question module.
        $question = new \community_oer\question_oer;
        \local_metadata\mcontext::question()->save($event->other['qid'], 'qversion', date("YmdHi"));
        if ($question->question_recalculate_in_db($event->other['qid']) != false) {
            $question->recalculate_data_in_cache();
        } else {
            \local_metadata\mcontext::question()->save($event->other['qid'], 'qversion', '');
        }
    }

    /**
     * Observer for \core\event\course_module_created event.
     *
     * @param \core\event\course_module_created $event
     * @return void
     */
    public static function course_module_created(\core\event\course_module_created $event) {
        global $DB;

        // Activity module.
        \community_oer\main_oer::recache_main_structure_elements();
        list($categories, $courses, $activities) = \community_oer\main_oer::get_main_structure_elements();

        if (in_array($event->objectid, $activities)) {
            //local_metadata\mcontext::module()->save($event->objectid, 'version', date("YmdHi"));

            $activity = new \community_oer\activity_oer;
            $activity->activity_recalculate_in_db($event->objectid);
            $activity->recalculate_data_in_cache();
        }

        // Sequence module.
        $sequence = new \community_oer\sequence_oer();
        list($sequences, $activities) = $sequence->get_main_sequence_elements();

        foreach ($activities as $seqid => $cmids) {
            if (in_array($event->objectid, $cmids)) {
                \local_metadata\mcontext::section()->save($seqid, 'sversion', date("YmdHi"));

                if ($sequence->sequence_recalculate_in_db($seqid) != false) {
                    $sequence->recalculate_data_in_cache();
                }
            }
        }

        // Course module.
        $row = $DB->get_record('course_modules', ['id' => $event->objectid]);
        if (!empty($row)) {
            $course = new \community_oer\course_oer();
            $course->update_observer($row->course);
        }
    }

    /**
     * Observer for \core\event\course_module_updated event.
     *
     * @param \core\event\course_module_updated $event
     * @return void
     */
    public static function course_module_updated(\core\event\course_module_updated $event) {
        global $DB;

        // Activity module.
        \community_oer\main_oer::recache_main_structure_elements();
        list($categories, $courses, $activities) = \community_oer\main_oer::get_main_structure_elements();

        if (in_array($event->objectid, $activities)) {

            // From unvisible to visible.
            $row = $DB->get_record('course_modules', ['id' => $event->objectid]);
            $oldstatevisible = local_metadata\mcontext::module()->get($event->objectid, 'visible');
            if ($row->visible == 1 && $oldstatevisible == 0) {
                local_metadata\mcontext::module()->save($event->objectid, 'version', date("YmdHi"));
            }

            $activity = new \community_oer\activity_oer;
            $activity->activity_recalculate_in_db($event->objectid);
            $activity->recalculate_data_in_cache();
        }

        // Sequence module.
        $sequence = new \community_oer\sequence_oer();
        list($sequences, $activities) = $sequence->get_main_sequence_elements();

        foreach ($activities as $seqid => $cmids) {
            if (in_array($event->objectid, $cmids)) {
                \local_metadata\mcontext::section()->save($seqid, 'sversion', date("YmdHi"));

                if ($sequence->sequence_recalculate_in_db($seqid) != false) {
                    $sequence->recalculate_data_in_cache();
                }
            }
        }

        // Course module.
        $row = $DB->get_record('course_modules', ['id' => $event->objectid]);
        if (!empty($row)) {
            $course = new \community_oer\course_oer();
            $course->update_observer($row->course);
        }
    }

    /**
     * Observer for \core\event\course_module_deleted event.
     *
     * @param \core\event\course_module_deleted $event
     * @return void
     */
    public static function course_module_deleted(\core\event\course_module_deleted $event) {
        global $DB;

        // Activity module.
        $DB->delete_records('community_oer_activity', ['cmid' => $event->objectid]);

        $activity = new \community_oer\activity_oer;
        $activity->recalculate_data_in_cache();

        // Sequence module.
        $sequence = new \community_oer\sequence_oer();
        list($sequences, $activities) = $sequence->get_main_sequence_elements();

        foreach ($activities as $seqid => $cmids) {
            if (in_array($event->objectid, $cmids)) {
                $DB->delete_records('community_oer_sequence', ['seqid' => $seqid]);
                $sequence->recalculate_data_in_cache();
            }
        }

        // Course module.
        $row = $DB->get_record('course_modules', ['id' => $event->objectid]);
        if (!empty($row)) {
            $course = new \community_oer\course_oer();
            $course->update_observer($row->course);
        }
    }

    /**
     * Observer for \core\event\question_created event.
     *
     * @param \core\event\question_created $event
     * @return void
     */
    public static function question_created(\core\event\question_created $event) {
        global $DB;

        $obj = $DB->get_record('question_versions', ['questionid' => $event->objectid]);
        if ($oldversion = $obj->version - 1) {
            $prevq = $DB->get_record('question_versions', ['version' => $oldversion, 'questionbankentryid' => $obj->questionbankentryid]);
            $DB->delete_records('community_oer_question', ['qid' => $prevq->questionid]);
        }

        // Question module.
        $question = new \community_oer\question_oer;
        \local_metadata\mcontext::question()->save($event->objectid, 'qversion', date("YmdHi"));
        if ($question->question_recalculate_in_db($event->objectid) != false) {
            $question->recalculate_data_in_cache();
        } else {
            \local_metadata\mcontext::question()->save($event->objectid, 'qversion', '');
        }
    }

    /**
     * Observer for \core\event\question_updated event.
     *
     * @param \core\event\question_updated $event
     * @return void
     */
    public static function question_updated(\core\event\question_updated $event) {

        // Question module.
        $question = new \community_oer\question_oer;
        \local_metadata\mcontext::question()->save($event->objectid, 'qversion', date("YmdHi"));
        if ($question->question_recalculate_in_db($event->objectid) != false) {
            $question->recalculate_data_in_cache();
        } else {
            \local_metadata\mcontext::question()->save($event->objectid, 'qversion', '');
        }
    }

    /**
     * Observer for \core\event\question_deleted event.
     *
     * @param \core\event\question_deleted $event
     * @return void
     */
    public static function question_deleted(\core\event\question_deleted $event) {
        global $DB;

        // Question module.
        $DB->delete_records('community_oer_question', ['qid' => $event->objectid]);

        $question = new \community_oer\question_oer;
        $question->recalculate_data_in_cache();
    }

    /**
     * Observer for \core\event\question_moved event.
     *
     * @param \core\event\question_moved $event
     * @return void
     */
    public static function question_moved(\core\event\question_moved $event) {
        global $DB;

        // Question module.
        $question = new \community_oer\question_oer;
        \local_metadata\mcontext::question()->save($event->objectid, 'qversion', date("YmdHi"));
        if ($question->question_recalculate_in_db($event->objectid) != false) {
            $question->recalculate_data_in_cache();
        } else {
            \local_metadata\mcontext::question()->save($event->objectid, 'qversion', '');
        }

        if ($row = $DB->get_record('community_oer_question', ['qid' => $event->objectid])) {
            $row->recache = 1;
            $DB->update_record('community_oer_question', $row);
        };
    }

    /**
     * Observer for \community_sharewith\event\activity_from_bank_download event.
     *
     * @param \community_sharewith\event\activity_from_bank_download $event
     * @return void
     */
    public static function activity_from_bank_download(\community_sharewith\event\activity_from_bank_download $event) {
        global $DB, $PAGE;

        $PAGE->theme->force_svg_use(1);

        $instanceid = $event->other['instanceid'];

        // Cache data module activity.
        $activity = new \community_oer\activity_oer;
        $activity->activity_recalculate_in_db($instanceid);

        $activity->recalculate_data_in_cache();
    }

    /**
     * Observer for \community_sharequestion\event\question_to_quiz_copy event.
     *
     * @param \community_sharequestion\event\question_to_quiz_copy $event
     * @return void
     */
    public static function question_to_quiz_copy(\community_sharequestion\event\question_to_quiz_copy $event) {

        // Question module.
        foreach (explode(',', $event->other['sourcequestionids']) as $qid) {
            $question = new \community_oer\question_oer;
            \local_metadata\mcontext::question()->save($qid, 'qversion', date("YmdHi"));
            if ($question->question_recalculate_in_db($qid) != false) {
                $question->recalculate_data_in_cache();
            } else {
                \local_metadata\mcontext::question()->save($qid, 'qversion', '');
            }
        }
    }

    /**
     * Observer for \community_sharequestion\event\question_to_category_copy event.
     *
     * @param \community_sharequestion\event\question_to_category_copy $event
     * @return void
     */
    public static function question_to_category_copy(\community_sharequestion\event\question_to_category_copy $event) {

        // Question module.
        foreach (explode(',', $event->other['sourcequestionids']) as $qid) {
            $question = new \community_oer\question_oer;
            \local_metadata\mcontext::question()->save($qid, 'qversion', date("YmdHi"));
            if ($question->question_recalculate_in_db($qid) != false) {
                $question->recalculate_data_in_cache();
            } else {
                \local_metadata\mcontext::question()->save($qid, 'qversion', '');
            }
        }
    }

    /**
     * Observer for \core\event\course_section_created event.
     *
     * @param \core\event\course_section_created $event
     * @return void
     */
    public static function course_section_created(\core\event\course_section_created $event) {
        global $DB;

        // Course module.
        $row = $DB->get_record('course_sections', ['id' => $event->objectid]);
        if (!empty($row)) {
            $course = new \community_oer\course_oer();
            $course->update_observer($row->course);
        }
    }

    /**
     * Observer for \core\event\course_section_updated event.
     *
     * @param \core\event\course_section_updated $event
     * @return void
     */
    public static function course_section_updated(\core\event\course_section_updated $event) {
        global $DB;

        // Sequence module.
        $sequence = new \community_oer\sequence_oer();
        list($sequences, $activities) = $sequence->get_main_sequence_elements();
        if (in_array($event->objectid, $sequences)) {
            \local_metadata\mcontext::section()->save($event->objectid, 'sversion', date("YmdHi"));

            if ($sequence->sequence_recalculate_in_db($event->objectid) != false) {
                $sequence->recalculate_data_in_cache();
            }
        }

        // Course module.
        $row = $DB->get_record('course_sections', ['id' => $event->objectid]);
        if (!empty($row)) {
            $course = new \community_oer\course_oer();
            $course->update_observer($row->course);
        }
    }

    /**
     * Observer for \core\event\course_section_deleted event.
     *
     * @param \core\event\course_section_deleted $event
     * @return void
     */
    public static function course_section_deleted(\core\event\course_section_deleted $event) {
        global $DB;

        // Course module.
        $row = $DB->get_record('course_sections', ['id' => $event->objectid]);
        if (!empty($row)) {
            $course = new \community_oer\course_oer();
            $course->update_observer($row->course);
        }
    }

    /**
     * Observer for \community_oer\event\module_move event.
     *
     * @param \community_oer\event\module_move $event
     * @return void
     */
    public static function module_move(\community_oer\event\module_move $event) {

        $objectid = $event->other['cmid'];
        $fromsectionid = $event->other['fromsectionid'];

        // Activity module.
        \community_oer\main_oer::recache_main_structure_elements();
        list($categories, $courses, $activities) = \community_oer\main_oer::get_main_structure_elements();

        if (in_array($objectid, $activities)) {
            $activity = new \community_oer\activity_oer;
            $activity->activity_recalculate_in_db($objectid);
            $activity->recalculate_data_in_cache();
        }

        // Sequence module.
        $sequence = new \community_oer\sequence_oer();
        list($sequences, $activities) = $sequence->get_main_sequence_elements();

        foreach ($activities as $seqid => $cmids) {
            if (in_array($objectid, $cmids)) {
                if ($sequence->sequence_recalculate_in_db($seqid) != false) {
                    $sequence->recalculate_data_in_cache();
                }
            }
        }

        if (in_array($fromsectionid, $sequences)) {
            if ($sequence->sequence_recalculate_in_db($fromsectionid) != false) {
                $sequence->recalculate_data_in_cache();
            }
        }

    }

    /**
     * Observer for \community_oer\event\module_move event.
     *
     * @param \community_oer\event\resort_course $event
     * @return void
     */
    public static function resort_course(\community_oer\event\resort_course $event) {

        $catid = isset($event->other['categoryid']) ? $event->other['categoryid'] : 0;
        $courseid = isset($event->other['courseid']) ? $event->other['courseid'] : 0;

        list($categories, $courses, $activities) = \community_oer\main_oer::get_main_structure_elements();

        if ($catid > 0 && in_array($catid, $categories)) {
            \community_oer\main_oer::set_oercacheversion();
            \community_oer\main_oer::recache_main_structure_elements();
        }

        if ($courseid > 0 && in_array($courseid, $courses)) {
            \community_oer\main_oer::set_oercacheversion();
            \community_oer\main_oer::recache_main_structure_elements();
        }
    }

    public static function resort_category(\community_oer\event\resort_category $event) {

        $catid = isset($event->other['categoryid']) ? $event->other['categoryid'] : 0;

        list($categories, $courses, $activities) = \community_oer\main_oer::get_main_structure_elements();

        if ($catid > 0 && in_array($catid, $categories)) {
            \community_oer\main_oer::set_oercacheversion();
            \community_oer\main_oer::recache_main_structure_elements();
        }

        if ($catid == 0) {
            \community_oer\main_oer::set_oercacheversion();
            \community_oer\main_oer::recache_main_structure_elements();
        }
    }
}
