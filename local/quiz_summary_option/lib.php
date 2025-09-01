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
 * Lib functions.
 *
 * @package   local_quiz_summary_option
 * @author    Christina Roperto (christinatheeroperto@catalyst-au.net)
 * @copyright Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Implements callbacks coursemodule_standard_elements to add an option to show/hide quiz summary page.
 *
 * @param \moodleform_mod $formwrapper An instance of moodleform_mod class.
 * @param \MoodleQuickForm $mform Course module form instance.
 */
define('SUMMARY_OPTION_SHOW', 'SHOW');
define('SUMMARY_OPTION_HIDE', 'HIDE');

function local_quiz_summary_option_coursemodule_standard_elements(\moodleform_mod $formwrapper, \MoodleQuickForm $mform) {
    global $DB, $PAGE, $CFG;

    $modulename = $formwrapper->get_current()->modulename;
    if ($modulename !== 'quiz') {
        return;
    }

    $cmid = $formwrapper->get_current()->coursemodule;
    $row = $DB->get_record('local_quiz_summary_option', ['cmid' => $cmid]);
    $show = true;
    if ($row) {
        $show = $row->show_summary;
    }

    $default = $show;

    $mform->addElement('header', 'summaryoptionhdr', get_string('summarypageoption', 'local_quiz_summary_option'));

    $mform->addElement('checkbox', 'summaryoption', get_string('summaryoption', 'local_quiz_summary_option'), ' ');

    $mform->setDefault('summaryoption', $default);
    $mform->addHelpButton('summaryoption', 'summaryoption', 'local_quiz_summary_option');

    // Default.
    $objdefault = \local_quiz_summary_option\funcs::quizquestiontitlepresets_default();

    if ($row) {
        $obj = json_decode($row->show_elements);

        if (is_object($obj)) {
            $objdefault->summary_hideall = isset($obj->summary_hideall) ? $obj->summary_hideall : 0;
            $objdefault->summary_numbering = isset($obj->summary_numbering) ? $obj->summary_numbering : 0;
            $objdefault->summary_state = isset($obj->summary_state) ? $obj->summary_state : 0;
            $objdefault->summary_grade = isset($obj->summary_grade) ? $obj->summary_grade : 0;
            $objdefault->summary_mark = isset($obj->summary_mark) ? $obj->summary_mark : 0;
            $objdefault->summary_teacherdialog = isset($obj->summary_teacherdialog) ? $obj->summary_teacherdialog : 0;
            $objdefault->summary_questionname = isset($obj->summary_questionname) ? $obj->summary_questionname : 0;
            $objdefault->summary_teamwork = isset($obj->summary_teamwork) ? $obj->summary_teamwork : 1;
            $objdefault->summary_fixedmin = isset($obj->summary_fixedmin) ? $obj->summary_fixedmin : 0;
        }
    }

    $mform->addElement('checkbox', 'summary_fixedmin', get_string('summaryoption_fixedmin', 'local_quiz_summary_option'), ' ');
    $mform->setDefault('summary_fixedmin', $objdefault->summary_fixedmin);
    $mform->addHelpButton('summary_fixedmin', 'summaryoption_fixedmin', 'local_quiz_summary_option');

    $mform->addElement('checkbox', 'summary_teamwork', get_string('summaryoption_teamwork', 'local_quiz_summary_option'), ' ');
    $mform->setDefault('summary_teamwork', $objdefault->summary_teamwork);
    $mform->addHelpButton('summary_teamwork', 'summaryoption_teamwork', 'local_quiz_summary_option');

    $mform->addElement('checkbox', 'summary_hideall',
            get_string('summaryoption_hideall', 'local_quiz_summary_option'), ' ', ['class' => 'summaryoption']);
    $mform->setDefault('summary_hideall', $objdefault->summary_hideall);

    $mform->addElement('checkbox', 'summary_numbering',
            get_string('summaryoption_numbering', 'local_quiz_summary_option'), ' ', ['class' => 'summaryoption']);
    $mform->setDefault('summary_numbering', $objdefault->summary_numbering);

    $mform->addElement('checkbox', 'summary_state',
            get_string('summaryoption_state', 'local_quiz_summary_option'), ' ', ['class' => 'summaryoption']);
    $mform->setDefault('summary_state', $objdefault->summary_state);

    $mform->addElement('checkbox', 'summary_grade',
            get_string('summaryoption_grade', 'local_quiz_summary_option'), ' ', ['class' => 'summaryoption']);
    $mform->setDefault('summary_grade', $objdefault->summary_grade);

    $mform->addElement('checkbox', 'summary_mark',
            get_string('summaryoption_mark', 'local_quiz_summary_option'), ' ', ['class' => 'summaryoption']);
    $mform->setDefault('summary_mark', $objdefault->summary_mark);

    $mform->addElement('checkbox', 'summary_teacherdialog',
            get_string('summaryoption_teacherdialog', 'local_quiz_summary_option'), ' ',
            ['class' => 'summaryoption']);
    $mform->setDefault('summary_teacherdialog', $objdefault->summary_teacherdialog);

    $mform->addElement('checkbox', 'summary_questionname',
            get_string('summaryoption_question_name', 'local_quiz_summary_option'), ' ', ['class' => 'summaryoption']);
    $mform->setDefault('summary_questionname', $objdefault->summary_questionname);

    $PAGE->requires->js_amd_inline("
        require(['jquery'], function($) {
            let allid = 'id_summary_hideall';
            let all = $('#id_summary_hideall');
            let checkbox1 = $('#id_summary_numbering');
            let checkbox2 = $('#id_summary_state');
            let checkbox3 = $('#id_summary_grade');
            let checkbox4 = $('#id_summary_mark');
            let checkbox5 = $('#id_summary_teacherdialog');
            let checkbox6 = $('#id_summary_questionname');
            let checkbox7 = $('#id_summary_teamwork');
            let checkbox8 = $('#id_summary_fixedmin');
            
            $(document).on('click', 'input.summaryoption', function() {
                let currentid = this.id;
                
                if(this.checked) {
                    if(currentid === allid){
                        checkbox1.prop('checked', true);
                        checkbox2.prop('checked', true);
                        checkbox3.prop('checked', true);
                        checkbox4.prop('checked', true);
                        checkbox5.prop('checked', true);
                        checkbox6.prop('checked', true);
                        checkbox7.prop('checked', true);
                        checkbox8.prop('checked', true);
                    }
                }else{
                    if(currentid === allid){
                        checkbox1.prop('checked', false);
                        checkbox2.prop('checked', false);
                        checkbox3.prop('checked', false);
                        checkbox4.prop('checked', false);
                        checkbox5.prop('checked', false);
                        checkbox6.prop('checked', false);
                        checkbox7.prop('checked', false);
                        checkbox8.prop('checked', false);
                    }
                }
                
                if(currentid !== allid){
                    if(checkbox1.is(':checked') && checkbox2.is(':checked')
                        && checkbox3.is(':checked') && checkbox4.is(':checked')
                        && checkbox5.is(':checked') && checkbox6.is(':checked')){
                       all.prop('checked', true);
                    }else{
                        all.prop('checked', false);
                    }
                }
            });
        });
    ");
}

/**
 * Implements hook coursemodule_edit_post_actions and adding a show flag.
 *
 * @param stdClass $moduleinfo Course module object.
 * @param int $course Course ID.
 */
function local_quiz_summary_option_coursemodule_edit_post_actions($moduleinfo, $course) {
    global $DB;

    if ($moduleinfo->modulename !== 'quiz') {
        return $moduleinfo;
    }

    $cmid = $moduleinfo->coursemodule;

    if (!isset($moduleinfo->summaryoption)) {
        $show = 0;
    } else {
        $show = $moduleinfo->summaryoption;
    }

    $row = $DB->get_record('local_quiz_summary_option', ['cmid' => $cmid], 'id');

    // Check if record exists, if yes then update otherwise insert the record.
    if ($row) {
        $quizsummaryoptions = new \stdClass();
        $quizsummaryoptions->id = $row->id;
        $quizsummaryoptions->show_summary = $show;
        $quizsummaryoptions->show_elements = '';
        $DB->update_record('local_quiz_summary_option', $quizsummaryoptions);
        $rowid = $row->id;
    } else {
        $rowid = $DB->insert_record('local_quiz_summary_option', ['cmid' => $cmid, 'show_summary' => $show,
                'show_elements' => ''], true);
    }

    $arr = [
            'summary_hideall' => isset($moduleinfo->summary_hideall) && $moduleinfo->summary_hideall === '1' ? '1' : '0',
            'summary_numbering' => isset($moduleinfo->summary_numbering) && $moduleinfo->summary_numbering === '1' ? '1' : '0',
            'summary_state' => isset($moduleinfo->summary_state) && $moduleinfo->summary_state === '1' ? '1' : '0',
            'summary_grade' => isset($moduleinfo->summary_grade) && $moduleinfo->summary_grade === '1' ? '1' : '0',
            'summary_mark' => isset($moduleinfo->summary_mark) && $moduleinfo->summary_mark === '1' ? '1' : '0',
            'summary_teacherdialog' => isset($moduleinfo->summary_teacherdialog) && $moduleinfo->summary_teacherdialog === '1' ? '1' : '0',
            'summary_questionname' => isset($moduleinfo->summary_questionname) && $moduleinfo->summary_questionname === '1' ? '1' : '0',
            'summary_teamwork' => isset($moduleinfo->summary_teamwork) && $moduleinfo->summary_teamwork === '1' ? '1' : '0',
            'summary_fixedmin' => isset($moduleinfo->summary_fixedmin) && $moduleinfo->summary_fixedmin === '1' ? '1' : '0'
    ];

    $obj = $DB->get_record('local_quiz_summary_option', ['id' => $rowid]);
    $obj->show_elements = json_encode($arr);
    $DB->update_record('local_quiz_summary_option', $obj);

    return $moduleinfo;
}
