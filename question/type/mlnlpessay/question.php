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
 * mlnlpessay question definition class.
 *
 * @package    qtype
 * @subpackage mlnlpessay
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/questionbase.php');

/**
 * Represents an mlnlpessay question.
 *
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_mlnlpessay_question extends question_graded_automatically {

    public $responseformat;

    /** @var int Indicates whether an inline response is required ('0') or optional ('1') */
    public $responserequired;

    public $responsefieldlines;

    /** @var int indicates whether the minimum number of words required */
    public $minwordlimit;

    /** @var int indicates whether the maximum number of words required */
    public $maxwordlimit;

    public $attachments;

    /** @var int maximum file size in bytes */
    public $maxbytes;

    /** @var int The number of attachments required for a response to be complete. */
    public $attachmentsrequired;

    public $graderinfo;
    public $graderinfoformat;
    public $responsetemplate;
    public $responsetemplateformat;

    /** to use for auto-grade and auto teacher response per user response */
    public $truefeedback;
    public $trueanswerid;

    /** @var array The string array of file types accepted upon file submission. */
    public $filetypeslist;

    /** to use for getting customized response for each user answer */
    public function __construct() {
        parent::__construct();
        $this->truefeedback = ['gg' => 8];
    }

    /**
     * @param moodle_page the page we are outputting to.
     * @return renderer_base the response-format-specific renderer.
     */
    public function get_format_renderer(moodle_page $page) {
        return $page->get_renderer('qtype_mlnlpessay', 'format_' . $this->responseformat);
    }

    public function get_expected_data() {
        if ($this->responseformat == 'editorfilepicker') {
            $expecteddata = array('answer' => question_attempt::PARAM_RAW_FILES);
        } else {
            $expecteddata = array('answer' => PARAM_RAW);
        }
        $expecteddata['answerformat'] = PARAM_ALPHANUMEXT;
        if ($this->attachments != 0) {
            $expecteddata['attachments'] = question_attempt::PARAM_FILES;
        }
        return $expecteddata;
    }

    public function summarise_response(array $response) {
        $output = null;

        if (isset($response['answer'])) {
            $output .= question_utils::to_plain_text($response['answer'],
                    $response['answerformat'], array('para' => false));
        }

        if (isset($response['attachments']) && $response['attachments']) {
            $attachedfiles = [];
            foreach ($response['attachments']->get_files() as $file) {
                $attachedfiles[] = $file->get_filename() . ' (' . display_size($file->get_filesize()) . ')';
            }
            if ($attachedfiles) {
                $output .= get_string('attachedfiles', 'qtype_mlnlpessay', implode(', ', $attachedfiles));
            }
        }
        return $output;
    }

    public function un_summarise_response(string $summary) {
        if (empty($summary)) {
            return [];
        }

        if (str_contains($this->responseformat, 'editor')) {
            return ['answer' => text_to_html($summary), 'answerformat' => FORMAT_HTML];
        } else {
            return ['answer' => $summary, 'answerformat' => FORMAT_PLAIN];
        }
    }

    public function get_correct_response() {
        return null;
    }

    public function is_complete_response(array $response) {
        // Determine if the given response has online text and attachments.
        $hasinlinetext = array_key_exists('answer', $response) && ($response['answer'] !== '');

        // If there is a response and min/max word limit is set in the form then validate the number of words in response.
        if ($hasinlinetext) {
            if ($this->check_input_word_count($response['answer'])) {
                return false;
            }
        }
        $hasattachments = array_key_exists('attachments', $response)
                && $response['attachments'] instanceof question_response_files;

        // Determine the number of attachments present.
        if ($hasattachments) {
            // Check the filetypes.
            $filetypesutil = new \core_form\filetypes_util();
            $allowlist = $filetypesutil->normalize_file_types($this->filetypeslist);
            $wrongfiles = array();
            foreach ($response['attachments']->get_files() as $file) {
                if (!$filetypesutil->is_allowed_file_type($file->get_filename(), $allowlist)) {
                    $wrongfiles[] = $file->get_filename();
                }
            }
            if ($wrongfiles) { // At least one filetype is wrong.
                return false;
            }
            $attachcount = count($response['attachments']->get_files());
        } else {
            $attachcount = 0;
        }

        // Determine if we have /some/ content to be graded.
        $hascontent = $hasinlinetext || ($attachcount > 0);

        // Determine if we meet the optional requirements.
        $meetsinlinereq = $hasinlinetext || (!$this->responserequired) || ($this->responseformat == 'noinline');
        $meetsattachmentreq = ($attachcount >= $this->attachmentsrequired);

        // The response is complete iff all of our requirements are met.
        return $hascontent && $meetsinlinereq && $meetsattachmentreq;
    }

    /**
     * Return null if is_complete_response() returns true
     * otherwise, return the minmax-limit error message
     *
     * @param array $response
     * @return string
     */
    public function get_validation_error(array $response) {
        if ($this->is_complete_response($response)) {
            return '';
        }
        return $this->check_input_word_count($response['answer']);
    }

    public function is_gradable_response(array $response) {
        // Determine if the given response meets the minimum maximum wordcount requirements.
        if (!$this->check_wordcount_passed($response)) {
            return false;
        }
        // Determine if the given response has online text and attachments.
        if (array_key_exists('answer', $response) && ($response['answer'] !== '')) {
            return true;
        } else if (array_key_exists('attachments', $response)
                && $response['attachments'] instanceof question_response_files) {
            return true;
        }

        return false;
    }

    public function check_wordcount_passed(array $response) {
        return $this->check_input_word_count($response['answer']) === null;
    }

    public function is_same_response(array $prevresponse, array $newresponse) {
        if (array_key_exists('answer', $prevresponse) && $prevresponse['answer'] !== $this->responsetemplate) {
            $value1 = (string) $prevresponse['answer'];
        } else {
            $value1 = '';
        }
        if (array_key_exists('answer', $newresponse) && $newresponse['answer'] !== $this->responsetemplate) {
            $value2 = (string) $newresponse['answer'];
        } else {
            $value2 = '';
        }
        return $value1 === $value2 && ($this->attachments == 0 ||
                question_utils::arrays_same_at_key_missing_is_blank(
                $prevresponse, $newresponse, 'attachments'));
    }

    public function check_file_access($qa, $options, $component, $filearea, $args, $forcedownload) {
        if ($component == 'question' && $filearea == 'response_attachments') {
            // Response attachments visible if the question has them.
            return $this->attachments != 0;

        } else if ($component == 'question' && $filearea == 'response_answer') {
            // Response attachments visible if the question has them.
            return $this->responseformat === 'editorfilepicker';

        } else if ($component == 'qtype_essay' && $filearea == 'graderinfo') {
            return $options->manualcomment && $args[0] == $this->id;

        } else {
            return parent::check_file_access($qa, $options, $component,
                    $filearea, $args, $forcedownload);
        }
    }

    /**
     * Return the question settings that define this question as structured data.
     *
     * @param question_attempt $qa the current attempt for which we are exporting the settings.
     * @param question_display_options $options the question display options which say which aspects of the question
     * should be visible.
     * @return mixed structure representing the question settings. In web services, this will be JSON-encoded.
     */
    public function get_question_definition_for_external_rendering(question_attempt $qa, question_display_options $options) {
        // This is a partial implementation, returning only the most relevant question settings for now,
        // ideally, we should return as much as settings as possible (depending on the state and display options).

        $settings = [
            'responseformat' => $this->responseformat,
            'responserequired' => $this->responserequired,
            'responsefieldlines' => $this->responsefieldlines,
            'attachments' => $this->attachments,
            'attachmentsrequired' => $this->attachmentsrequired,
            'maxbytes' => $this->maxbytes,
            'filetypeslist' => $this->filetypeslist,
            'responsetemplate' => $this->responsetemplate,
            'responsetemplateformat' => $this->responsetemplateformat,
            'minwordlimit' => $this->minwordlimit,
            'maxwordlimit' => $this->maxwordlimit,
        ];

        return $settings;
    }

    public function classify_response(array $response) {
        if (!array_key_exists('answer', $response)) {
            return array($this->id => question_classified_response::no_response());
        }
        list($fraction) = $this->grade_response($response);
        return ($fraction);
    }

    public function getProtectedValue($obj, $name) {
        $array = (array) $obj;
        $prefix = chr(0) . '*' . chr(0);
        return isset($array[$prefix . $name]) ? $array[$prefix . $name] : null;
    }

    public function grade_response(array $response) {
        global $DB, $CFG;
        $answertext = strip_tags($response['answer']);
        $answertext = strval(str_replace("\r\n", "", $answertext));

        $question = $DB->get_record('qtype_mlnlpessay_options', array('questionid' => $this->id));
        $categoriesweight = json_decode($question->categoriesweightteacher);
        if (empty($categoriesweight)) {
            $categoriesweight = (array) json_decode($question->categoriesweight);
        }
        $catgoriesnames = '';
        $categoriesids = [];
        foreach ($categoriesweight as $key => $category) {
            if (isset($category->iscategoryselected) && $category->iscategoryselected) {
                $catgoriesnames .= static::clean($category->name) . '|';
                $categoriesids[] = $category->id;
            } else {
                unset($categoriesweight[$key]);
            }
        }
        $questionid = $this->id;

        $step = $this->getProtectedValue($response['answer'], 'step');

        if ($step == null) {
            $sql = "SELECT qa.*
                    FROM {question_attempts} qa
                    JOIN {quiz_attempts} qza ON qa.questionusageid = qza.uniqueid 
                    WHERE qa.questionid = ?
                    ORDER BY id DESC
                    LIMIT 1";
            $param = [$question->questionid];

            $question_attempt = $DB->get_record_sql($sql, $param);
        } else {
            $stepid = $step->get_id();

            $sql = "SELECT qa.*
                  FROM {question_attempts} qa INNER JOIN {question_attempt_steps} qas ON qa.id = qas.questionattemptid
                 WHERE qas.id = ?";
            $param = array($stepid);
            $question_attempt = $DB->get_record_sql($sql, $param);
        }

        //getting number of models to run on
        $models_number = get_config('qtype_mlnlpessay', 'numberofmodels');

        //Add graderesponse task.
        $data = new stdClass;
        $data->answertext = $answertext;
        $data->catgoriesnames = $catgoriesnames;
        $data->categoriesids = json_encode($categoriesids);
        $data->categoriesweight = $categoriesweight;
        $data->question_attempt = $question_attempt;
        $data->questionid = $questionid;
        $data->step = $step;
        $data->stepid = isset($stepid) ? $stepid : null;
        $data->models_number = $models_number;

        $task = new \qtype_mlnlpessay\task\adhoc_graderesponse();
        $task->set_custom_data(
                $data
        );
        \core\task\manager::queue_adhoc_task($task);
        return array(0, question_state::graded_state_for_fraction(0.5));
    }

    public static function clean($string) {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
        return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
    }

    /**
     * Check the input word count and return a message to user
     * when the number of words are outside the boundary settings.
     *
     * @param string $responsestring
     * @return string|null
     .*/
    private function check_input_word_count($responsestring) {
        if (!$this->responserequired) {
            return null;
        }
        if (!$this->minwordlimit && !$this->maxwordlimit) {
            // This question does not care about the word count.
            return null;
        }

        // Count the number of words in the response string.
        $count = count_words($responsestring);
        if ($this->maxwordlimit && $count > $this->maxwordlimit) {
            return get_string('maxwordlimitboundary', 'qtype_essay',
                    ['limit' => $this->maxwordlimit, 'count' => $count]);
        } else if ($count < $this->minwordlimit) {
            return get_string('minwordlimitboundary', 'qtype_essay',
                    ['limit' => $this->minwordlimit, 'count' => $count]);
        } else {
            return null;
        }
    }

    /**
     * If this question uses word counts, then return a display of the current
     * count, and whether it is within limit, for when the question is being reviewed.
     *
     * @param array $response responses, as returned by
     *      {@see question_attempt_step::get_qt_data()}.
     * @return string If relevant to this question, a display of the word count.
     */
    public function get_word_count_message_for_review(array $response): string {
        if (!$this->minwordlimit && !$this->maxwordlimit) {
            // This question does not care about the word count.
            return '';
        }

        if (!array_key_exists('answer', $response) || ($response['answer'] === '')) {
            // No response.
            return '';
        }

        $count = count_words($response['answer']);
        if ($this->maxwordlimit && $count > $this->maxwordlimit) {
            return get_string('wordcounttoomuch', 'qtype_essay',
                    ['limit' => $this->maxwordlimit, 'count' => $count]);
        } else if ($count < $this->minwordlimit) {
            return get_string('wordcounttoofew', 'qtype_essay',
                    ['limit' => $this->minwordlimit, 'count' => $count]);
        } else {
            return get_string('wordcount', 'qtype_essay', $count);
        }
    }
}
