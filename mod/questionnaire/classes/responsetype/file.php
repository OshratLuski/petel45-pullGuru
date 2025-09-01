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
 * This file contains the parent class for questionnaire question types.
 *
 * @author Mike Churchward
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package questiontypes
 */

namespace mod_questionnaire\responsetype;
defined('MOODLE_INTERNAL') || die();

use mod_questionnaire\db\bulk_sql_config;

/**
 * Class for text response types.
 *
 * @author Mike Churchward
 * @package responsetypes
 */

class file extends responsetype {
    /**
     * @return string
     */
    static public function response_table() {
        return 'questionnaire_response_file';
    }

    /**
     * Provide an array of answer objects from web form data for the question.
     *
     * @param \stdClass $responsedata All of the responsedata as an object.
     * @param \mod_questionnaire\question\question $question
     * @return array \mod_questionnaire\responsetype\answer\answer An array of answer objects.
     */
    static public function answers_from_webform($responsedata, $question) {
        $answers = [];
        if (isset($responsedata->{'q'.$question->id}) && !empty($responsedata->{'q'.$question->id})) {
            $val = $responsedata->{'q' . $question->id};
            if ($question->type_id == QUESNUMERIC) {
                $val = str_replace(",", ".", $val); // Allow commas as well as points in decimal numbers.
                $val = preg_replace("/[^0-9.\-]*(-?[0-9]*\.?[0-9]*).*/", '\1', $val);
            }
            $record = new \stdClass();
            $record->responseid = $responsedata->rid;
            $record->questionid = $question->id;
            $record->value = $val;
            $answers[] = answer\answer::create_from_data($record);
        }
        return $answers;
    }

    public function insert_response($rid)
    {
        global $DB, $COURSE, $USER, $PAGE;
        $fs = get_file_storage();
        $file_context = $this->question->context->id;
        $draftitemid = file_get_submitted_draft_itemid('file_itemid_'.$this->question->id);
        file_save_draft_area_files($draftitemid, $file_context, 'mod_questionnaire', 'response_file', $draftitemid);

        $files = $fs->get_area_files($file_context, 'mod_questionnaire', 'response_file', $draftitemid);
        foreach ($files as $f) {
            $filecontext = $f->get_contextid();
            $filecomponent = $f->get_component();
            $filefilearea = $f->get_filearea();
            $fileitemid = $f->get_itemid();
            $filefilepath = $f->get_filepath();
            $filename = $f->get_filename();
            $filerid = $f->get_id();
        }

        if ($files) {
            $record = new \stdClass();
            $record->response_id = $rid->rid;
            $record->question_id = $this->question->id;
            $record->response = $filerid;
            return $DB->insert_record(static::response_table(), $record);
        } else {
            return false;
        }
    }

    /**
     * @param bool $rids
     * @param bool $anonymous
     * @return array
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_results($rids=false, $anonymous=false) {
        global $DB;

        $rsql = '';
        if (!empty($rids)) {
            list($rsql, $params) = $DB->get_in_or_equal($rids);
            $rsql = ' AND response_id ' . $rsql;
        }

        if ($anonymous) {
            $sql = 'SELECT t.id, t.response, r.submitted AS submitted, ' .
                    'r.questionnaireid, r.id AS rid ' .
                    'FROM {'.static::response_table().'} t, ' .
                    '{questionnaire_response} r ' .
                    'WHERE question_id=' . $this->question->id . $rsql .
                    ' AND t.response_id = r.id ' .
                    'ORDER BY r.submitted DESC';
        } else {
            $sql = 'SELECT t.id, '.
                'f.filename as response, r.submitted AS submitted, r.userid, u.username AS username, ' .
                'u.id as usrid, ' .
                'r.questionnaireid, r.id AS rid, ' .
                'f.*' .
                'FROM {'.static::response_table().'} t, ' .
                '{questionnaire_response} r, ' .
                '{user} u, ' .
                '{files} f '.
                'WHERE question_id=' . $this->question->id . $rsql .
                ' AND t.response_id = r.id' .
                ' AND u.id = r.userid ' .
                ' AND f.id = t.response '.
                'ORDER BY u.lastname, u.firstname, r.submitted';
        }
        return $DB->get_records_sql($sql, $params);

    }

    public function display_results($rids=false, $sort='', $anonymous=false) {
        $output = '';

        if ($rows = $this->get_results($rids, $anonymous)) {
            // Count identical answers (numeric questions only).
            foreach ($rows as $row) {
                if (!empty($row->response) || $row->response === "0") {
                    $url = \moodle_url::make_pluginfile_url($row->contextid, $row->component, $row->filearea, $row->itemid, '/', $row->filename, false);
                    if (strpos('image/png, image/jpg, image/gif', $row->mimetype) !== false) {
                        $output .= '<div class="qtypefile"><img class="img-responsive" src="'.$url->out().'" alt="'.$row->filename.'"></div>';
                    } else {
                        $output .= '<div class="qtypefile"><a target="_blank" href="'.$url->out().'">'.$row->filename.'</a></div>';
                    }
                }
            }
        } else {
            $output .= '<p class="generaltable">&nbsp;'.get_string('noresponsedata', 'questionnaire').'</p>';
        }
        return $output;
    }

    /**
     * Return an array of answers by question/choice for the given response. Must be implemented by the subclass.
     *
     * @param int $rid The response id.
     * @return array
     */
    static public function response_select($rid) {
        global $DB;

        $values = [];
        $sql = 'SELECT q.id ,f.contextid, f.itemid , f.filename as aresponse '.
            'FROM {'.static::response_table().'} a, {questionnaire_question} q , {files} f '.
            'WHERE a.response_id=? AND a.question_id=q.id AND a.response = f.id ';
        $records = $DB->get_records_sql($sql, [$rid]);
        foreach ($records as $qid => $row) {
            unset($row->id);
            $row = (array)$row;
            $newrow = [];
            foreach ($row as $key => $val) {
                if (!is_numeric($key)) {
                    $newrow[] = $val;
                }
            }

            $url = \moodle_url::make_pluginfile_url($newrow[1], 'mod_questionnaire', 'response_file', $newrow[2], '/', $newrow[3], false);
            $response ='<a target="_blank" href="'.$url->out().'">'.$newrow[3].'</a>';
            $values[$qid] = $newrow;
            $val = array_pop($values[$qid]);
            array_push($values[$qid], $val,$response);
        }

        return $values;
    }

    /**
     * Configure bulk sql
     * @return bulk_sql_config
     */
    protected function bulk_sql_config() {
        return new bulk_sql_config(static::response_table(), 'qrt', false, true, false);
    }
}

