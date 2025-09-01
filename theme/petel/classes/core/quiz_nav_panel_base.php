<?php

namespace theme_petel;

use mod_quiz\output\renderer;
use html_writer;
use mod_quiz\output\navigation_panel_base;
use mod_quiz\output\navigation_question_button;
use mod_quiz\output\navigation_section_heading;

class quiz_nav_panel_base extends navigation_panel_base {
    public function get_question_url($slot) {
        return $this->attemptobj->review_url($slot, -1, $this->showall, $this->page);
    }

    public function render_end_bits(renderer $output) {
        $html = '';
        if ($this->attemptobj->get_num_pages() > 1) {
            if ($this->showall) {
                $html .= html_writer::link($this->attemptobj->review_url(null, 0, false),
                        get_string('showeachpage', 'quiz'));
            } else {
                $html .= html_writer::link($this->attemptobj->review_url(null, 0, true),
                        get_string('showall', 'quiz'));
            }
        }
        $html .= $output->finish_review_link($this->attemptobj);
        $html .= $this->render_restart_preview_link($output);
        return $html;
    }

    public function get_question_buttons() {
        $buttons = array();
        foreach ($this->attemptobj->get_slots() as $slot) {
            if ($heading = $this->attemptobj->get_heading_before_slot($slot)) {
                $buttons[] = new navigation_question_button(format_string($heading));
            }

            $qa = $this->attemptobj->get_question_attempt($slot);
            $showcorrectness = $this->options->correctness && $qa->has_marks();

            $button = new navigation_question_button();
            $button->id          = 'quiznavbutton' . $slot;
            $button->number      = $this->attemptobj->get_question_number($slot);
            $button->stateclass  = $qa->get_state_class($showcorrectness);
            $button->navmethod   = $this->attemptobj->get_navigation_method();
            if (!$showcorrectness && $button->stateclass == 'notanswered') {
                $button->stateclass = 'complete';
            }
            $button->statestring = $this->get_state_string($qa, $showcorrectness);
            $button->page        = $this->attemptobj->get_question_page($slot);
            $button->currentpage = $this->showall || $button->page == $this->page;
            $button->flagged     = $qa->is_flagged();
            $button->url         = $this->get_question_url($slot);
            if ($this->attemptobj->is_blocked_by_previous_question($slot)) {
                $button->url = null;
                $button->stateclass = 'blocked';
                $button->statestring = get_string('questiondependsonprevious', 'quiz');
            }
            $buttons[] = $button;
        }

        // return $buttons;
        return [];
    }
}