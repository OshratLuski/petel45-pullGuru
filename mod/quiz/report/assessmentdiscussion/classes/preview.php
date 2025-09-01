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
 * Plugin capabilities are defined here.
 *
 * @package     quiz_assessmentdiscussion
 * @category    access
 * @copyright   2022 Devlion <info@devlion.co>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quiz_assessmentdiscussion;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/lib.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');

class preview {

    private $cmid;
    private $qid;
    private $iframeenable;
    private $stylequestionview;
    private $styleanswerview;

    public function __construct($cmid, $qid) {

        $this->cmid = $cmid;
        $this->qid = $qid;

        $this->preset_qtype();
    }

    private function preset_qtype() {
        global $DB;

        // TODO Iframe or html.
        $this->iframeenable = get_user_preferences('quiz_assessmentdiscussion_iframe', 0);

        $question = $DB->get_record('question', array('id' => $this->qid));
        switch ($question->qtype) {
            case 'essayrubric':
                $this->iframeenable = 0;

                $this->stylequestionview = "
                    INIQUEID .ablock, INIQUEID .im-controls {
                        display: none!important;
                    }
                    
                    INIQUEID .que, INIQUEID .que .qtext {
                        margin-bottom: 0px!important;
                    }
                    
                    INIQUEID .que .formulation {
                        padding-top: 0px!important;
                        padding-bottom: 0px!important;
                        margin-bottom: 0px!important; 
                    }
                    
                    INIQUEID fieldset.hidden {
                        display: none!important;
                    }                      
                ";

                $this->styleanswerview = "
                    INIQUEID .qtext, INIQUEID .im-controls {
                        display: none!important;
                    }
                    
                    INIQUEID .ablock .answer-label {
                        display: none!important;
                    }
                    
                    INIQUEID .que, INIQUEID .que .qtext {
                        margin-bottom: 0px!important;
                    }
                    
                    INIQUEID .que .formulation {
                        padding-top: 0px!important;
                        padding-bottom: 0px!important;
                        margin-bottom: 0px!important; 
                    }                   
                    
                    INIQUEID fieldset.hidden {
                        display: none!important;
                    }
                    
                    INIQUEID .comment {
                        display: none!important;
                    }                                   
                ";
                break;
            case 'essay':
                $this->iframeenable = 0;

                $this->stylequestionview = "
                    INIQUEID .ablock, INIQUEID .im-controls {
                        display: none!important;
                    }
                    
                    INIQUEID .que, INIQUEID .que .qtext {
                        margin-bottom: 0px!important;
                    }
                    
                    INIQUEID .que .formulation {
                        padding-top: 0px!important;
                        padding-bottom: 0px!important;
                        margin-bottom: 0px!important; 
                    }
                ";

                $this->styleanswerview = "
                    INIQUEID .qtext, INIQUEID .im-controls {
                        display: none!important;
                    }
                    
                    INIQUEID .ablock .answer-label {
                        display: none!important;
                    }
                    
                    INIQUEID .que, INIQUEID .que .qtext {
                        margin-bottom: 0px!important;
                    }
                    
                    INIQUEID .que .formulation {
                        padding-top: 0px!important;
                        padding-bottom: 0px!important;
                        margin-bottom: 0px!important; 
                    } 
                    
                    INIQUEID .comment {
                        display: none!important;
                    }                                       
                ";
                break;
            case 'combined':
                $this->iframeenable = 0;

                $this->stylequestionview = "";

                $this->styleanswerview = "
                    INIQUEID .comment {
                        display: none!important;
                    }                                       
                ";
                break;
            case 'poodllrecording':
                $this->iframeenable = 0;

                $this->stylequestionview = " ";

                $this->styleanswerview = "
                    INIQUEID .comment {
                        display: none!important;
                    }                                       
                ";
                break;
            case 'mlnlpessay':
                $this->iframeenable = 0;

                $this->stylequestionview = "  ";

                $this->styleanswerview = "
                    INIQUEID .comment {
                        display: none!important;
                    }
                       
                    INIQUEID .outcome {
                        display: none!important;
                    }                                                          
                ";
                break;
            case 'aitext':
                $this->iframeenable = 0;

                $this->stylequestionview = "";

                $this->styleanswerview = "
                    INIQUEID .comment {
                        display: none!important;
                    }                                                                            
                ";
                break;
            default:
                $this->stylequestionview = '';
                $this->styleanswerview = '';
                //$this->iframeenable = 0;
        }
    }

    public function preview_question_data() {
        $data = new \StdClass();

        if ($this->iframeenable) {
            $url = new \moodle_url('/mod/quiz/report/assessmentdiscussion/previewquestion.php', [
                    'cmid' => $this->cmid,
                    'qid' => $this->qid
            ]);

            $data->preview_question = false;
            $data->preview_question_link = $url->out();
            $data->iframeenable = true;
        } else {
            $data->preview_question = $this->preview_question($this->qid);
            $data->preview_question_link = false;
            $data->iframeenable = false;
        }

        return $data;
    }

    public function preview_question() {
        global $USER, $DB;

        $maxvariants = 100;
        $question = \question_bank::load_question($this->qid);

        // Get and validate display options.
        $maxvariant = min($question->get_num_variants(), $maxvariants);
        $options = new \qbank_previewquestion\question_preview_options($question);
        $options->load_user_defaults();
        $options->set_from_request();

        $quba = \question_engine::make_questions_usage_by_activity(
                'core_question_preview', \context_user::instance($USER->id));
        $quba->set_preferred_behaviour($options->behaviour);
        $slot = $quba->add_question($question, $options->maxmark);

        if ($options->variant) {
            $options->variant = min($maxvariant, max(1, $options->variant));
        } else {
            $options->variant = rand(1, $maxvariant);
        }

        $quba->start_question($slot, $options->variant);

        $transaction = $DB->start_delegated_transaction();
        \question_engine::save_questions_usage_by_activity($quba);
        $transaction->allow_commit();

        $options->behaviour = $quba->get_preferred_behaviour();
        $options->maxmark = $quba->get_question_max_mark($slot);

        ob_start();

        $uniqueid = 'preview_question_' . $this->qid;

        // Style.
        $style = "
            <style>
                INIQUEID .info {
                    display: none!important;
                }    
              
                ". $this->stylequestionview ."
            </style>        
        ";

        echo str_replace('INIQUEID', '#' . $uniqueid, $style);

        echo '<div id="'. $uniqueid .'">';
        echo $quba->render_question($slot, $options, '');
        echo '</div>';

        $out = ob_get_contents();

        ob_end_clean();

        return $out;
    }

    public function preview_answer_data($attemptid, $slot) {
        $data = new \StdClass();

        if ($this->iframeenable) {
            $url = new \moodle_url('/mod/quiz/report/assessmentdiscussion/previewattempt.php', [
                    'cmid' => $this->cmid,
                    'qid' => $this->qid,
                    'attemptid' => $attemptid,
                    'slot' => $slot,
            ]);

            $data->iframeenable  = true;
            $data->previewanswer = false;
            $data->previewanswer_link = $url->out();
        } else {
            $data->iframeenable = false;
            $data->previewanswer = $this->preview_answer($attemptid, $slot);
            $data->previewanswer_link = false;
        }

        return $data;
    }

    public function preview_answer($attemptid, $slot) {

        $attemptobj = quiz_create_attempt_handling_errors($attemptid, $this->cmid);
        $attemptobj->preload_all_attempt_step_users();

        $uniqueid = 'preview_answer_' . $attemptid . $slot;

        ob_start();

        // Style.
        $style = "
            <style>
                INIQUEID .info, INIQUEID .history, INIQUEID .navbar, INIQUEID .drawer-toggles, INIQUEID #page-header {
                    display: none!important;
                }
                
                INIQUEID #page, INIQUEID #topofscroll {
                    margin-top: 0px!important;
                }
                
                ". $this->styleanswerview ."
            </style>        
        ";

        echo str_replace('INIQUEID', '#' . $uniqueid, $style);

        echo '<div id="'. $uniqueid .'">';
        echo $attemptobj->render_question_for_commenting($slot);
        echo '</div>';

        $out = ob_get_contents();

        ob_end_clean();

        return $out;
    }
}
