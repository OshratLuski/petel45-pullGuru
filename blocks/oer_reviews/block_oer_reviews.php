<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * oer_reviews block.
 *
 * @package    block_oer_reviews
 * @copyright  2020 Nadav Kavalerchik <nadav.kavalerchik@weizmann.ac.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_oer_reviews extends block_base {

    /**
     * Initializes class member variables.
     */
    public function init() {
        // Needed by Moodle to differentiate between blocks.
        $this->title = get_string('pluginname', 'block_oer_reviews');
    }

    /**
     * Returns the block contents.
     *
     * @return stdClass The block contents.
     */
    public function get_content() {
        global $DB, $OUTPUT, $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        if (!$this->is_user_real_teacher()) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();

        if (!empty($this->config->text)) {
            $this->content->text = $this->config->text;
        } else {
            $this->title = get_string('title', 'block_oer_reviews');

            $text = \core\output\html_writer::div(get_string('intro', 'block_oer_reviews'), 'intro', ['id' => 'fade-it']);

            // Scrolling.
            $text .= '<div class="p-3" style="">';

            $maxusers = trim(get_config('block_oer_reviews', 'items'));
            if (empty($maxusers) || !is_numeric($maxusers)) {
                $maxusers = 25;
            }

            $latestreviewers = [];

            // Get all ("good") reviews with Hebrew YES Default.
            if (!$this->is_user_orderbydate() && !$this->is_user_orderbyrelationship()) {
                $sql = "SELECT * FROM {community_oer_reviews} WHERE json_value(feedback, '$.question1') LIKE '%"
                        . get_string('yes') . "%' AND reviewtype = 'activity' ORDER BY timecreated DESC";
                $latestreviewers = $DB->get_records_sql($sql);
            }

            // Get all ("good") reviews with Hebrew YES order by date.
            if ($this->is_user_orderbydate()) {
                $sql = "SELECT * FROM {community_oer_reviews} WHERE json_value(feedback, '$.question1') LIKE '%"
                        . get_string('yes') . "%' AND reviewtype = 'activity' ORDER BY timecreated DESC";
                $latestreviewers = $DB->get_records_sql($sql);
            }

            // Get all ("good") reviews with Hebrew YES order by relationship.
            if ($this->is_user_orderbyrelationship()) {
                $usersfeedback = [];
                foreach ($DB->get_records('social_relationships', ['userid_watching' => $USER->id]) as $item) {
                    $usersfeedback[] = $item->userid_feedback;
                }

                if (!empty($usersfeedback)) {
                    $sql = "
                        SELECT sor.*, srs.points
                        FROM {community_oer_reviews} sor
                        LEFT JOIN {social_relationships} srs
                            ON (srs.userid_watching = " . $USER->id . " AND srs.userid_feedback = sor.userid)
                        WHERE json_value(sor.feedback, '$.question1') LIKE '%" . get_string('yes') . "%'
                            AND sor.reviewtype = 'activity'
                        ORDER BY srs.points DESC";
                    $latestreviewers = $DB->get_records_sql($sql);
                }
            }

            $counter = 0;
            foreach ($latestreviewers as $reviewer) {
                $user = $DB->get_record('user', ['id' => $reviewer->userid]);
                $userpicture = $OUTPUT->user_picture($user);
                $username = fullname($user);
                $feedback = json_decode($reviewer->feedback);
                $feedbacktooltip = '';

                if (!isset($feedback->descr)) {
                    continue;
                }

                $feedbacktooltip .= $feedback->descr;

                $mid = \local_metadata\mcontext::module()->get($reviewer->objid, 'ID');
                if (empty($mid)) {
                    $mid = $reviewer->objid;
                }

                $activityurl = new \moodle_url('/local/community/plugins/oer/activityshare.php?id=',
                        ['id' => $mid, 'source' => 'oerreviewsblock']);

                try {
                    list($course, $cm) = get_course_and_cm_from_cmid($reviewer->objid);
                    $cmname = $cm->name;
                } catch (Exception $e) {
                    $cmname = '';
                }

                $activity = html_writer::link(
                    $activityurl,
                    $cmname,
                    [
                        'aria-label' => $cmname . ' ' . strip_tags($feedbacktooltip),
                        'class' => 'oer_reviews-link text-body'
                    ]
                );

                $text .= html_writer::tag('div',
                        "<div class='row mb-1'>
                            <div class='col userinfo-wrapper d-flex align-items-start'>
                                $userpicture
                                <div class='username w-100 d-flex align-items-center ml-2 bold' style='height: 2rem;'>
                                $username
                                </div>
                            </div>
                        </div>
                        <div class='row mb-3'>
                            <div class='col d-flex flex-wrap align-items-start w-100'>
                                <div class='d-inline-flex align-items-center'
                                    data-toggle='tooltip' data-html='true' title='$feedbacktooltip'>
                                    $activity
                                    <i class='fa-light fa-circle-info text-primary ml-2'></i>
                                </div>
                            </div>
                        </div>
                        
                        ", ['class' => 'reviewer']);

                $counter++;

                if ($counter >= $maxusers) {
                    break;
                }
            }

            $text .= '</div>';

            $this->content->text = $text;
            $this->content->footer = '';
        }

        return $this->content;
    }

    /**
     * Defines configuration data.
     *
     * The function is called immediatly after init().
     */
    public function specialization() {

        // Load user defined title and make sure it's never empty.
        if (empty($this->config->title)) {
            $this->title = get_string('pluginname', 'block_oer_reviews');
        } else {
            $this->title = $this->config->title;
        }
    }

    /**
     * Enables global configuration of the block in settings.php.
     *
     * @return bool True if the global configuration is enabled.
     */
    public function has_config() {
        return true;
    }

    /**
     * Sets the applicable formats for the block.
     *
     * @return string[] Array of pages and permissions.
     */
    public function applicable_formats() {
        return array('all' => false, 'my' => true);
    }

    /**
     * @param null $userid - userid of real teacher. otherwise use $USER.
     * @return false|mixed
     * @throws dml_exception
     */
    public function is_user_real_teacher() {
        global $CFG;

        if (!isset($CFG->reviewcohort) || empty($CFG->reviewcohort)) {
            return false;
        }

        $teacherscohort = $CFG->reviewcohort;
        return $this->is_teacher_in_cohort($teacherscohort);
    }

    public function is_user_orderbydate() {
        global $CFG;

        if (!isset($CFG->eladresearch_cohort_orderbydate) || empty($CFG->eladresearch_cohort_orderbydate)) {
            return false;
        }

        $teacherscohort = $CFG->eladresearch_cohort_orderbydate;
        return $this->is_teacher_in_cohort($teacherscohort);
    }

    public function is_user_orderbyrelationship() {
        global $CFG;

        if (!isset($CFG->eladresearch_cohort_orderbyrelationship) || empty($CFG->eladresearch_cohort_orderbyrelationship)) {
            return false;
        }

        $teacherscohort = $CFG->eladresearch_cohort_orderbyrelationship;
        return $this->is_teacher_in_cohort($teacherscohort);
    }

    private function is_teacher_in_cohort($cohortid) {
        global $DB, $USER;

        $sql = "SELECT *
                FROM {cohort_members} cm
                JOIN {cohort} c ON c.id = cm.cohortid
                WHERE c.idnumber=? AND cm.userid=?";

        return $DB->get_records_sql($sql, [$cohortid, $USER->id]);
    }
}
