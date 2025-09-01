<?php

namespace theme_petel;

use question_attempt;
use question_engine_data_mapper;
use moodle_exception;

/**
 * Contains the logic for handling question flags.
 *
 * @copyright  2010 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question_flags extends \question_flags {

    public static function initialise_js() {
        global $CFG, $PAGE, $OUTPUT;

        static $done = false;
        if ($done) {
            return;
        }
        $module = array(
            'name' => 'theme_petel_question_flags',
            'fullpath' => '/theme/petel/flags.js',
            'requires' => array('base', 'dom', 'event-delegate', 'io-base'),
        );
        $actionurl = $CFG->wwwroot . '/question/toggleflag.php';
        $flagtext = array(
            0 => 'FLAG', // get_string('clickflag', 'question'),
            1 => 'UNFLAG', // get_string('clickunflag', 'question')
        );
        $flagattributes = array(
            0 => array(
                'src' => $OUTPUT->image_url('i/bookmark-regular') . '',
                'title' => 'ADD', // get_string('clicktoflag', 'question'),
                'alt' => get_string('notflagged', 'theme_petel'),
               'text' => get_string('clickflag', 'question'),
            ),
            1 => array(
                'src' => $OUTPUT->image_url('i/bookmark-solid') . '',
                'title' => 'REMOVE', // get_string('clicktounflag', 'question'),
                'alt' => get_string('flagged', 'question'),
               'text' => get_string('clickunflag', 'question'),
            ),
        );
        $PAGE->requires->js_init_call('M.theme_petel_question_flags.init',
                array($actionurl, $flagattributes, $flagtext), false, $module);
        $done = true;
    }
}