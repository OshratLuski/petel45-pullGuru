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
 * Defines the editing form for the mlnlpessay question type.
 *
 * @package    qtype
 * @subpackage mlnlpEssay
 * @copyright  2022 Dor-Herbesman Devlion
 */


defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot . '/question/type/mlnlpessay/locallib.php';

/**
 * mlnlpEssay question type editing form.
 *
 * @copyright  2022 Dor Herbesman - team Devlion
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_mlnlpessay_edit_form extends question_edit_form {

    protected function definition_inner($mform) {
        global $CFG, $OUTPUT, $USER, $DB, $PAGE, $COURSE;

        $qtype = question_bank::get_qtype('mlnlpessay');
        $context = \context_user::instance($USER->id);

        // to preload categories weights from DB to JS
        // $mform->addElement('hidden', 'categoriesweight');
        // $mform->setType('categoriesweight', PARAM_RAW);
        $parsedcategories = [];
        if (isset($this->question->options)) {
            $categoriesweight = json_decode($this->question->options->categoriesweight);
            // foreach ($categoriesweight as $category) {
            //     $parsedcategories[] = ['name' => str_replace('_', ' ', $category->name), 'weight' => $category->weight];
            // }
            $parsedcategories = (array) $categoriesweight;
        }
        //add categoriers name,tag,weight,and selected status.
        $mform->addElement('header', 'rubiccategoryheader', get_string('rubiccategoryheader', 'qtype_mlnlpessay'));
        $mform->setExpanded('rubiccategoryheader');

        $tag = [];

        //getting categories names for js display
        $categories = \qtype_mlnlpessay\persistent\categories::get_records_with_topics(['active' => 1, 'disabled' => 0]);
        foreach ($categories as $persistent) {
            $catid = "{$persistent->get('model')}_{$persistent->get('modelid')}";
            $tag[$catid] = $persistent->get('tag');
        }

        $nametagarray = [];

        $types = get_category_types();

        $params = [];
        $params['types'] = $types;

        $hascapedit = hascapedit($COURSE->id, $USER->id);

        if (count($parsedcategories) != 0) {
            foreach ($parsedcategories as $value) {
                $value->edit = $hascapedit;
                $value->tag = $tag[$value->id];
                $nametagarray[$value->id] = $value;
            }
        } else {
            foreach ($categories as $persistent) {
                $catid = "{$persistent->get('model')}_{$persistent->get('modelid')}";
                $nametagarray[$catid] = [
                    'name' => $persistent->get('name'),
                    'model' => $persistent->get('model'),
                    'modelid' => $persistent->get('modelid'),
                    'tag' => $persistent->get('tag'),
                    'lang' => $persistent->lang,
                    'topics' => $persistent->topics,
                    'subtopics' => $persistent->subtopics,
                    'edit' => $hascapedit,
                    'id' => $catid
                ];
            }
        }

        $datatemp = [
                $nametagarray,
                $params,
                $hascapedit,
        ];

        $html = '
                <link href="https://unpkg.com/tabulator-tables/dist/css/tabulator.min.css" rel="stylesheet">
                <style>
                .tabulator-row:not(:hover).tabulator-selectable:not(:hover).tabulator-row-even .editablecol:not(:hover) {
                    background: #99c1de;
                }
                .tabulator-row:not(:hover).tabulator-selectable:not(:hover).tabulator-row-odd .editablecol:not(:hover) {
                    background: #bcd4e6;
                }
                </style>
                <div id="rubiccategoryfulltable-table"></div>
                ';
        $mform->addElement('html', $html, get_string('rubiccategorytable', 'qtype_mlnlpessay'), 'rubiccategorytable');

        $mform->addElement('hidden', 'rubiccategoryfulltable', '', ['id' => 'rubiccategoryfulltable']);
        $mform->setType('rubiccategoryfulltable', PARAM_RAW);

        $PAGE->requires->js_call_amd('qtype_mlnlpessay/categories', 'init', $datatemp);

        $mform->addElement('static', 'rubriccategoryfake', ' ', ' ');

        $mform->addElement('header', 'responseoptions', get_string('responseoptions', 'qtype_mlnlpessay'));
        $mform->setExpanded('responseoptions');

        $mform->addElement('select', 'responseformat',
                get_string('responseformat', 'qtype_mlnlpessay'), $qtype->response_formats());
        $mform->setDefault('responseformat', $this->get_default_value('responseformat', 'editor'));

        $mform->addElement('select', 'responserequired',
                get_string('responserequired', 'qtype_mlnlpessay'), $qtype->response_required_options());
        $mform->setDefault('responserequired', $this->get_default_value('responserequired', 1));
        $mform->hideIf('responserequired', 'responseformat', 'eq', 'noinline');

        $mform->addElement('select', 'responsefieldlines',
                get_string('responsefieldlines', 'qtype_mlnlpessay'), $qtype->response_sizes());
        $mform->setDefault('responsefieldlines', $this->get_default_value('responsefieldlines', 10));
        $mform->hideIf('responsefieldlines', 'responseformat', 'eq', 'noinline');

        // Create a text box that can be enabled/disabled for max/min word limits options.
        $wordlimitoptions = ['size' => '6', 'maxlength' => '6'];
        $mingrp[] = $mform->createElement('text', 'minwordlimit', '', $wordlimitoptions);
        $mform->setType('minwordlimit', PARAM_INT);
        $mingrp[] = $mform->createElement('checkbox', 'minwordenabled', '', get_string('enable'));
        $mform->setDefault('minwordenabled', 0);
        $mform->addGroup($mingrp, 'mingroup', get_string('minwordlimit', 'qtype_mlnlpessay'), ' ', false);
        $mform->addHelpButton('mingroup', 'minwordlimit', 'qtype_mlnlpessay');
        $mform->disabledIf('minwordlimit', 'minwordenabled', 'notchecked');
        $mform->hideIf('mingroup', 'responserequired', 'eq', '0');
        $mform->hideIf('mingroup', 'responseformat', 'eq', 'noinline');

        $maxgrp[] = $mform->createElement('text', 'maxwordlimit', '', $wordlimitoptions);
        $mform->setType('maxwordlimit', PARAM_INT);
        $maxgrp[] = $mform->createElement('checkbox', 'maxwordenabled', '', get_string('enable'));
        $mform->setDefault('maxwordenabled', 0);
        $mform->addGroup($maxgrp, 'maxgroup', get_string('maxwordlimit', 'qtype_mlnlpessay'), ' ', false);
        $mform->addHelpButton('maxgroup', 'maxwordlimit', 'qtype_mlnlpessay');
        $mform->disabledIf('maxwordlimit', 'maxwordenabled', 'notchecked');
        $mform->hideIf('maxgroup', 'responserequired', 'eq', '0');
        $mform->hideIf('maxgroup', 'responseformat', 'eq', 'noinline');

        $mform->addElement('select', 'attachments',
                get_string('allowattachments', 'qtype_mlnlpessay'), $qtype->attachment_options());
        $mform->setDefault('attachments', $this->get_default_value('attachments', 0));

        $mform->addElement('select', 'attachmentsrequired',
                get_string('attachmentsrequired', 'qtype_mlnlpessay'), $qtype->attachments_required_options());
        $mform->setDefault('attachmentsrequired', $this->get_default_value('attachmentsrequired', 0));
        $mform->addHelpButton('attachmentsrequired', 'attachmentsrequired', 'qtype_mlnlpessay');
        $mform->hideIf('attachmentsrequired', 'attachments', 'eq', 0);

        $mform->addElement('filetypes', 'filetypeslist', get_string('acceptedfiletypes', 'qtype_mlnlpessay'));
        $mform->addHelpButton('filetypeslist', 'acceptedfiletypes', 'qtype_mlnlpessay');
        $mform->hideIf('filetypeslist', 'attachments', 'eq', 0);

        $mform->addElement('select', 'maxbytes', get_string('maxbytes', 'qtype_mlnlpessay'), $qtype->max_file_size_options());
        $mform->setDefault('maxbytes', $this->get_default_value('maxbytes', 0));
        $mform->hideIf('maxbytes', 'attachments', 'eq', 0);

        $mform->addElement('header', 'responsetemplateheader', get_string('responsetemplateheader', 'qtype_mlnlpessay'));
        $mform->addElement('editor', 'responsetemplate', get_string('responsetemplate', 'qtype_mlnlpessay'),
                array('rows' => 10),  array_merge($this->editoroptions, array('maxfiles' => 0)));
        $mform->addHelpButton('responsetemplate', 'responsetemplate', 'qtype_mlnlpessay');

        $mform->addElement('header', 'graderinfoheader', get_string('graderinfoheader', 'qtype_mlnlpessay'));
        $mform->setExpanded('graderinfoheader');
        $mform->addElement('editor', 'graderinfo', get_string('graderinfo', 'qtype_mlnlpessay'),
                array('rows' => 10), $this->editoroptions);
    }

    protected function data_preprocessing($question) {

        $question = parent::data_preprocessing($question);

        if (empty($question->options)) {
            return $question;
        }

        $question->responseformat = $question->options->responseformat;
        $question->responserequired = $question->options->responserequired;
        $question->responsefieldlines = $question->options->responsefieldlines;
        $question->minwordenabled = $question->options->minwordlimit ? 1 : 0;
        $question->minwordlimit = $question->options->minwordlimit;
        $question->maxwordenabled = $question->options->maxwordlimit ? 1 : 0;
        $question->maxwordlimit = $question->options->maxwordlimit;
        $question->attachments = $question->options->attachments;
        $question->attachmentsrequired = $question->options->attachmentsrequired;
        $question->filetypeslist = $question->options->filetypeslist;
        $question->maxbytes = $question->options->maxbytes;

        $draftid = file_get_submitted_draft_itemid('graderinfo');
        $question->graderinfo = array();
        $question->graderinfo['text'] = file_prepare_draft_area(
                $draftid, // Draftid
                $this->context->id, // context
                'qtype_mlnlpessay', // component
                'graderinfo', // filarea
                !empty($question->id) ? (int) $question->id : null, // itemid
                $this->fileoptions, // options
                $question->options->graderinfo// text.
        );
        $question->graderinfo['format'] = $question->options->graderinfoformat;
        $question->graderinfo['itemid'] = $draftid;

        $question->responsetemplate = array(
            'text' => $question->options->responsetemplate,
            'format' => $question->options->responsetemplateformat,
        );
        $question->categoriesweight = $question->options->categoriesweight;

        return $question;
    }

    public function validation($fromform, $files) {
        global $COURSE, $USER;

        $errors = parent::validation($fromform, $files);

        // Don't allow both 'no inline response' and 'no attachments' to be selected,
        // as these options would result in there being no input requested from the user.
        if ($fromform['responseformat'] == 'noinline' && !$fromform['attachments']) {
            $errors['attachments'] = get_string('mustattach', 'qtype_mlnlpessay');
        }

        // If 'no inline response' is set, force the teacher to require attachments;
        // otherwise there will be nothing to grade.
        if ($fromform['responseformat'] == 'noinline' && !$fromform['attachmentsrequired']) {
            $errors['attachmentsrequired'] = get_string('mustrequire', 'qtype_mlnlpessay');
        }

        // Don't allow the teacher to require more attachments than they allow; as this would
        // create a condition that it's impossible for the student to meet.
        if ($fromform['attachments'] > 0 && $fromform['attachments'] < $fromform['attachmentsrequired'] ) {
            $errors['attachmentsrequired']  = get_string('mustrequirefewer', 'qtype_mlnlpessay');
        }

        $hascapedit = hascapedit($COURSE->id, $USER->id);

        $hasCategory = false;
        if ($hascapedit) {
            foreach (json_decode($fromform['rubiccategoryfulltable']) as $key => $value) {
                if (isset($value->iscategoryselected) && $value->iscategoryselected) {
                    $hasCategory = true;

                    if(!isset($value->weight) || !is_numeric($value->weight)) {
                        $errors['rubriccategoryfake'] = get_string('weighterror', 'qtype_mlnlpessay');
                    }
                }
            }
        }
        if(!$hasCategory) {
            $errors['rubriccategoryfake'] = get_string('categoryerror', 'qtype_mlnlpessay');
        }

        if ($fromform['responserequired']) {
            if (isset($fromform['minwordenabled'])) {
                if (!is_numeric($fromform['minwordlimit'])) {
                    $errors['mingroup'] = get_string('err_numeric', 'form');
                }
                if ($fromform['minwordlimit'] < 0) {
                    $errors['mingroup'] = get_string('err_minwordlimitnegative', 'qtype_mlnlpessay');
                }
                if (!$fromform['minwordlimit']) {
                    $errors['mingroup'] = get_string('err_minwordlimit', 'qtype_mlnlpessay');
                }
            }
            if (isset($fromform['maxwordenabled'])) {
                if (!is_numeric($fromform['maxwordlimit'])) {
                    $errors['maxgroup'] = get_string('err_numeric', 'form');
                }
                if ($fromform['maxwordlimit'] < 0) {
                    $errors['maxgroup'] = get_string('err_maxwordlimitnegative', 'qtype_mlnlpessay');
                }
                if (!$fromform['maxwordlimit']) {
                    $errors['maxgroup'] = get_string('err_maxwordlimit', 'qtype_mlnlpessay');
                }
            }
            if (isset($fromform['maxwordenabled']) && isset($fromform['minwordenabled'])) {
                if ($fromform['maxwordlimit'] < $fromform['minwordlimit'] &&
                    $fromform['maxwordlimit'] > 0 && $fromform['minwordlimit'] > 0) {
                    $errors['maxgroup'] = get_string('err_maxminmismatch', 'qtype_mlnlpessay');
                }
            }
        }
        return $errors;
    }

    public function qtype() {
        return 'mlnlpessay';
    }
}
