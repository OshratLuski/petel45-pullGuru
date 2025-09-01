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
 * Drag-and-drop onto image question renderer class.
 *
 * @package    qtype_ddimageortext
 * @copyright  2010 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

/**
 * Generates the output for drag-and-drop onto image questions.
 *
 * @copyright  2010 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_ddtoimage_renderer_base extends qtype_with_combined_feedback_renderer {

    public function clear_wrong(question_attempt $qa) {
        $question = $qa->get_question();
        $response = $qa->get_last_qt_data();

        if (!empty($response)) {
            $cleanresponse = $question->clear_wrong_from_response($response);
        } else {
            $cleanresponse = $response;
        }
        $cleanresponsehtml = '';
        foreach ($cleanresponse as $fieldname => $value) {
            list (, $html) = $this->hidden_field_for_qt_var($qa, $fieldname, $value);
            $cleanresponsehtml .= $html;
        }
        return $cleanresponsehtml;
    }

    public function formulation_and_controls(question_attempt $qa,
            question_display_options $options) {
        global $PAGE;

        $question = $qa->get_question();
        $response = $qa->get_last_qt_data();

        $questiontext = $question->format_questiontext($qa);

        $dropareaclass = 'droparea';
        $draghomesclass = 'draghomes';
        if ($options->readonly) {
            $dropareaclass .= ' readonly';
            $draghomesclass .= ' readonly';
        }

        $output = html_writer::div($questiontext, 'qtext');

        $output .= html_writer::start_div('ddarea');
        $output .= html_writer::start_div($dropareaclass);
        $output .= html_writer::img(self::get_url_for_image($qa, 'bgimage'), get_string('dropbackground', 'qtype_ddmarker'),
                ['class' => 'dropbackground img-fluid w-100']);

        $output .= html_writer::div('', 'dropzones');
        $output .= html_writer::end_div();
        $output .= html_writer::start_div($draghomesclass);

        $dragimagehomes = '';
        foreach ($question->choices as $groupno => $group) {
            $dragimagehomesgroup = '';
            $orderedgroup = $question->get_ordered_choices($groupno);
            foreach ($orderedgroup as $choiceno => $dragimage) {
                $dragimageurl = self::get_url_for_image($qa, 'dragimage', $dragimage->id);
                $classes = [
                        'group' . $groupno,
                        'draghome',
                        'user-select-none',
                        'choice' . $choiceno
                ];
                if ($dragimage->infinite) {
                    $classes[] = 'infinite';
                }
                if ($dragimageurl === null) {
                    $dragimage->text = question_utils::format_question_fragment($dragimage->text, $this->page->context);
                    $dragimagehomesgroup .=
                            html_writer::div($dragimage->text . $this->get_feedback_image($qa, $options, $dragimage->no),
                                    join(' ', $classes), ['src' => $dragimageurl]);
                } else {
                    $dragimagehomesgroup .= html_writer::img($dragimageurl, $dragimage->text, ['class' => join(' ', $classes)]);
                }
            }
            $dragimagehomes .= html_writer::div($dragimagehomesgroup, 'dragitemgroup' . $groupno);
        }

        $output .= $dragimagehomes;
        $output .= html_writer::end_div();

        // Note, the mobile app implementation of ddimageortext relies on extracting the
        // blob of places data out of the rendered HTML, which makes it impossible
        // to clean up this structure of otherwise unnecessary stuff.
        $placeinfoforjsandmobileapp = [];
        foreach ($question->places as $placeno => $place) {
            $varname = $question->field($placeno);
            [$fieldname, $html] = $this->hidden_field_for_qt_var($qa, $varname, null,
                    ['placeinput', 'place' . $placeno, 'group' . $place->group]);
            $output .= $html;
            $placeinfo = (object) (array) $place;
            $placeinfo->fieldname = $fieldname;
            $placeinfo->text = format_string($placeinfo->text);
            $placeinfoforjsandmobileapp[$placeno] = $placeinfo;

            // Correct answer for images.
            $class = $this->get_feedback_class($qa, $options, $placeno);

            if(!empty($class)) {
                $PAGE->requires->js_amd_inline("
                    require(['jquery'], function($) {
                        let t = setInterval(function () {
                          if ($('#".$qa->get_outer_question_div_unique_id().".que.ddimageortext div.droparea .dropzones img.inplace".$placeno."').length > 0) {
                            clearInterval(t);                            
                            $('#".$qa->get_outer_question_div_unique_id().".que.ddimageortext div.droparea .dropzones img.inplace".$placeno."').addClass('".$class."');
                          }
                        }, 50);
                    });
                ");
            }
        }

        $this->page->requires->js_amd_inline("
            require(['jquery'], function($) {
               let k = setInterval(function () {
                   $('.draghomes').find('.unplaced i').hide();   
               }, 50);
            });
        ");


        $output .= html_writer::end_div();

        $this->page->requires->string_for_js('blank', 'qtype_ddimageortext');
        $this->page->requires->js_call_amd('qtype_ddimageortext/question', 'init',
                [$qa->get_outer_question_div_unique_id(), $options->readonly, $placeinfoforjsandmobileapp]);

        if ($qa->get_state() == question_state::$invalid) {
            $output .= html_writer::div($question->get_validation_error($qa->get_last_qt_data()), 'validationerror');
        }
        return $output;
    }

    /**
     * Returns the URL for an image
     *
     * @param object $qa Question attempt object
     * @param string $filearea File area descriptor
     * @param int $itemid Item id to get
     * @return string Output url, or null if not found
     */
    protected static function get_url_for_image(question_attempt $qa, $filearea, $itemid = 0) {
        $question = $qa->get_question();
        $qubaid = $qa->get_usage_id();
        $slot = $qa->get_slot();
        $fs = get_file_storage();
        if ($filearea == 'bgimage') {
            $itemid = $question->id;
        }
        $componentname = $question->qtype->plugin_name();
        $draftfiles = $fs->get_area_files($question->contextid, $componentname,
                                                                        $filearea, $itemid, 'id');
        if ($draftfiles) {
            foreach ($draftfiles as $file) {
                if ($file->is_directory()) {
                    continue;
                }
                $url = moodle_url::make_pluginfile_url($question->contextid, $componentname,
                                            $filearea, "$qubaid/$slot/{$itemid}", '/',
                                            $file->get_filename());
                return $url->out();
            }
        }
        return null;
    }

    /**
     * Returns a hidden field for a qt variable
     *
     * @param object $qa Question attempt object
     * @param string $varname The hidden var name
     * @param string $value The hidden value
     * @param array $classes Any additional css classes to apply
     * @return array Array with field name and the html of the tag
     */
    protected function hidden_field_for_qt_var(question_attempt $qa, $varname, $value = null,
                                                $classes = null) {
        if ($value === null) {
            $value = $qa->get_last_qt_var($varname);
        }
        $fieldname = $qa->get_qt_field_name($varname);
        $attributes = array('type' => 'hidden',
                                'id' => str_replace(':', '_', $fieldname),
                                'name' => $fieldname,
                                'value' => $value);
        if ($classes !== null) {
            $attributes['class'] = join(' ', $classes);
        }
        return array($fieldname, html_writer::empty_tag('input', $attributes)."\n");
    }

    public function specific_feedback(question_attempt $qa) {
        return $this->combined_feedback($qa);
    }

    public function correct_response(question_attempt $qa) {
        return '';
    }

    private function get_feedback_image(question_attempt $qa, $options, $no) {
        $result = '';

        $response = $qa->get_last_qt_data();

        foreach ($qa->get_question()->classify_response($response) as $item) {
            if ($item->responseclassid == $no) {
                $fraction = $item->fraction;
                break;
            }
        }

        if ($options->correctness) {
            $result = $this->feedback_image($fraction);
        }

        return $result;
    }

    private function get_feedback_class(question_attempt $qa, $options, $placeno) {
        $class = '';
        $places = [];

        $response = $qa->get_last_qt_data();
        foreach ($qa->get_question()->places as $place => $notused) {
            if (!array_key_exists($qa->get_question()->field($place), $response)) {
                continue;
            }
            if ($response[$qa->get_question()->field($place)] == $qa->get_question()->get_right_choice_for($place)) {
                $places[] = $place;
            }
        }

        if ($options->correctness) {
            if(in_array($placeno, $places)){
                $class = 'answer-right';
            }else{
                $class = 'answer-wrong';
            }
        }

        return $class;
    }

    protected function feedback_image($fraction, $selected = true) {
        $feedbackclass = question_state::graded_state_for_fraction($fraction)->get_feedback_class();

        return $this->output->pix_icon('i/grade_' . $feedbackclass, get_string($feedbackclass, 'question'),
                'moodle', ['class' => 'ml-2 mr-0']);
    }
}
