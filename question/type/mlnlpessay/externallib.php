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
 * Format multitopicmoe external API
 *
 * @package    qtype_mlnlpessay
 * @copyright  2018 David Watson {@link http://evolutioncode.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die;
global $CFG;
require_once("$CFG->libdir/externallib.php");
require_once $CFG->dirroot . '/question/type/rendererbase.php';
require_once $CFG->dirroot . '/question/type/mlnlpessay/renderer.php';
require_once $CFG->dirroot . '/mod/quiz/locallib.php';



class qtype_mlnlpessay_external extends external_api {

    public static function get_feedback($questionid, $questionattemptid) {
        global $DB, $USER, $PAGE;

        $PAGE->set_context(context_system::instance());

        $params = self::validate_parameters(
            self::get_feedback_parameters(),
            array(
                'questionid' => $questionid,
                'questionattemptid' => $questionattemptid,
            )
        );

        $questionid = $params['questionid'];
        $questionattemptid = $params['questionattemptid'];

        $result = [];
        $response = '';
        if ($attemptinfo = $DB->get_record_sql("SELECT qa.id, qua.slot, qua.id as qattemptid FROM {question_attempts} qua 
                JOIN {quiz_attempts} qa ON qua.questionusageid = qa.uniqueid WHERE qua.id = ?", [$questionattemptid])) {
            $attemptobj = \quiz_attempt::create($attemptinfo->id);
            $qa = $attemptobj->get_question_attempt($attemptinfo->slot);

            $renderer = new qtype_mlnlpessay_renderer($PAGE, '');
            $response = $renderer->specific_feedback($qa, false);

            if (!$response) {
                $adhocs = $DB->get_records('task_adhoc', ['component' => 'qtype_mlnlpessay', 'classname' => '\qtype_mlnlpessay\task\adhoc_graderesponse']);
                $adhockexists = 0;
                foreach ($adhocs as $adhoc) {
                    $data = json_decode($adhoc->customdata);
                    if ($data && $data->question_attempt->id == $attemptinfo->qattemptid && $data->question_attempt->questionid == $questionid) {
                        $adhockexists = 1;
                        break;
                    }
                }
                if (!$adhockexists) {
                    self::triger_adchock($questionid, $questionattemptid);
                }
            }
        }
        $result['response'] = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $result['status'] = !empty($response);
        return $result;
    }

    /**
     * Simulate the resource/view.php web interface page: trigger events, completion, etc...
     *
     * This is a re-implementation of the core service, only required because the core
     * version is not callable from AJAX
     * @see mod_resource_external::log_resource_view_parameters()
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function get_feedback_parameters() {
        return new external_function_parameters(
            array(
                'questionid' => new external_value(PARAM_INT, 'questionid'),
                'questionattemptid' => new external_value(PARAM_INT, 'questionattemptid'),
            )
        );
    }

    /**
     *
     * Returns description of method result value
     *
     * This is a re-implementation of the core service only required because the core
     * version is not callable from AJAX
     * @see mod_resource_external::log_resource_view_returns()
     * @return external_description
     * @since Moodle 3.0
     */
    public static function get_feedback_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'response' => new external_value(PARAM_RAW, 'response')
            )
        );
    }

    /**
     * Simulate the resource/view.php web interface page: trigger events, completion, etc...
     *
     * This is a re-implementation of the core service, only required because the core
     * version is not callable from AJAX
     * @see mod_resource_external::log_resource_view_parameters()
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function set_override_parameters() {
        return new external_function_parameters(
            array(
                'categoryid' => new external_value(PARAM_RAW, 'category id'),
                'questionid' => new external_value(PARAM_INT, 'questionid'),
                'questionattemptid' => new external_value(PARAM_INT, 'questionattemptid'),
            )
        );
    }

    public static function set_override($categoryid, $questionid, $questionattemptid) {
        global $DB, $USER, $PAGE;

        $params = self::validate_parameters(
            self::set_override_parameters(),
            array(
                'categoryid' => $categoryid,
                'questionid' => $questionid,
                'questionattemptid' => $questionattemptid,
            )
        );

        $categoryid = $params['categoryid'];
        $questionid = $params['questionid'];
        $questionattemptid = $params['questionattemptid'];
        $result = ['status' => true, 'error' => ''];
        $response = $error = '';
        $toregrade = false;

        try {
            $pythonfeedbacksql = check_response($questionid, $questionattemptid);
            if ($pythonfeedbacksql && !empty($pythonfeedbacksql->pythonresponse)) {
                $fraction = 0;
                $pythonresponse = json_decode($pythonfeedbacksql->pythonresponse);
                $enabledcategoriesids = [];
                if ($enabledcategories = get_enabled_categories($questionid)) {
                    $enabledcategoriesids = array_keys($enabledcategories);
                }

                foreach ($pythonresponse as $key => $response) {
                    if ($response->id == $categoryid) {
                        $pythonresponse[$key]->correct = $response->correct ? 0 : 1;
                        $pythonresponse[$key]->overridden = isset($response->overridden) && !empty($response->overridden) ? 0 : 1;

                        $toregrade = true;
                    }

                    if (in_array($response->id, $enabledcategoriesids)) {
                        $fraction += (int) $enabledcategories[$response->id]->weight * (int) $pythonresponse[$key]->correct / 100;
                    }
                }

                $pythonfeedbacksql->pythonresponse = json_encode($pythonresponse, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                $DB->update_record('qtype_mlnlpessay_response', $pythonfeedbacksql);

                if ($toregrade) {
                    ob_start();
                    \qtype_mlnlpessay\task\adhoc_graderesponse::regrade_attempt_by_questionattempt($questionattemptid, $fraction);
                    ob_end_clean();
                }
            }
        } catch (\Exception $e) {
            $result['status'] = false;
            $response['error'] = $e->getMessage();
        }


        if ($result['status']) {
            $result = array_replace($result, static::get_feedback($questionid, $questionattemptid));
        }

        return $result;
    }

    /**
     *
     * Returns description of method result value
     *
     * This is a re-implementation of the core service only required because the core
     * version is not callable from AJAX
     * @see mod_resource_external::log_resource_view_returns()
     * @return external_description
     * @since Moodle 3.0
     */
    public static function set_override_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'response' => new external_value(PARAM_RAW, 'response'),
                'error' => new external_value(PARAM_RAW, 'error text')
            )
        );
    }

    public static function triger_adchock($questionid, $questionattemptid) {
        global $DB;

        if ($attemptinfo = $DB->get_record_sql("SELECT qa.id, qua.slot, qua.id as attemptid  FROM {question_attempts} qua 
                JOIN {quiz_attempts} qa ON qua.questionusageid = qa.uniqueid WHERE qua.id = ?", [$questionattemptid])) {

            $sql = "SELECT attd.*
                FROM mdl_question_attempt_steps atts
                LEFT JOIN mdl_question_attempt_step_data attd ON (atts.id = attd.attemptstepid AND attd.name = 'answer')
                WHERE  atts.questionattemptid= ?  ORDER BY attd.id DESC LIMIT 1";
            $attdata = $DB->get_record_sql($sql, [$questionattemptid]);

            $answertext = strip_tags($attdata->value);
            $answertext = strval(str_replace("\r\n", "", $answertext));
            $question = $DB->get_record('qtype_mlnlpessay_options', array('questionid' => $questionid));
            $categoriesweight = json_decode($question->categoriesweightteacher);
            if ($categoriesweight == '' || $categoriesweight == null) {
                $categoriesweight = (array) json_decode($question->categoriesweight);
            }
            $catgoriesnames = '';
            $categoriesids = [];
            foreach ($categoriesweight as $key => $category) {
                if (isset($category->iscategoryselected) && $category->iscategoryselected) {
                    $catgoriesnames .= self::fn_clean($category->name) . '|';
                    $categoriesids[] = $category->id;
                } else {
                    unset($categoriesweight[$key]);
                }
            }

            $question_attempt_id = $attemptinfo->attemptid;
            $question_attempt = $DB->get_record('question_attempts', ['id' => $question_attempt_id]);
            //getting number of models to run on
            $models_number = get_config('qtype_mlnlpessay', 'numberofmodels');

            //Add graderesponse task.
            $data = new stdClass;
            $data->answertext = $answertext;
            $data->catgoriesnames = $catgoriesnames;
            $data->categoriesids = json_encode($categoriesids, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $data->categoriesweight = $categoriesweight;
            $data->question_attempt = $question_attempt;
            $data->questionid = $questionid;
            $data->step = null;
            $data->stepid = null;
            $data->models_number = $models_number;

            $task = new \qtype_mlnlpessay\task\adhoc_graderesponse();
            $task->set_custom_data(
                $data
            );
            \core\task\manager::queue_adhoc_task($task);
        }
    }

    static function fn_clean($string) {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
        return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
    }

    public static function get_categories() {
        global $DB, $USER, $PAGE;

        $return = [];
        $status = true;
        $message = '';
        try {
            $context = context_system::instance();
            $PAGE->set_context($context);
            foreach (\qtype_mlnlpessay\persistent\categories::get_records() as $persistent) {
                $record = $persistent->to_record();
                $related = ['context' => $context, 'activeint' => intval($record->active), 'disabledint' => intval($record->disabled)];
                unset($record->active);
                unset($record->disabled);
                $catsettingsdata = new \qtype_mlnlpessay\external\catsettings_exporter($record, $related);
                $return[] = $catsettingsdata->export($PAGE->get_renderer('core'));
            }
        } catch (\Exception $e) {
            $status = false;
            $message = $e->getMessage();
        }

        return ['status' => $status, 'message' => $message, 'response' => $return];
    }

    /**
     * Simulate the resource/view.php web interface page: trigger events, completion, etc...
     *
     * This is a re-implementation of the core service, only required because the core
     * version is not callable from AJAX
     * @see mod_resource_external::log_resource_view_parameters()
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function get_categories_parameters() {
        return new external_function_parameters(
            []
        );
    }

    /**
     *
     * Returns description of method result value
     *
     * This is a re-implementation of the core service only required because the core
     * version is not callable from AJAX
     * @see mod_resource_external::log_resource_view_returns()
     * @return external_description
     * @since Moodle 3.0
     */
    public static function get_categories_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'message' => new external_value(PARAM_TEXT, 'error msg'),
                'response' => new external_multiple_structure(
                    \qtype_mlnlpessay\external\catsettings_exporter::get_read_structure()
                )
            )
        );
    }

    public static function get_langs() {
        global $DB, $USER, $PAGE;

        $return = [];
        $status = true;
        $message = '';
        try {
            $context = context_system::instance();
            $PAGE->set_context($context);
            foreach (\qtype_mlnlpessay\persistent\langs::get_records() as $persistent) {
                $record = $persistent->to_record();
                $related = ['context' => $context, 'activeint' => intval($record->active)];
                unset($record->active);
                $langsdata = new \qtype_mlnlpessay\external\lang_exporter($record, $related);
                $return[] = $langsdata->export($PAGE->get_renderer('core'));
            }
        } catch (\Exception $e) {
            $status = false;
            $message = $e->getMessage();
        }

        return ['status' => $status, 'message' => $message, 'response' => $return];
    }

    /**
     * Simulate the resource/view.php web interface page: trigger events, completion, etc...
     *
     * This is a re-implementation of the core service, only required because the core
     * version is not callable from AJAX
     * @see mod_resource_external::log_resource_view_parameters()
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function get_langs_parameters() {
        return new external_function_parameters(
            []
        );
    }

    /**
     *
     * Returns description of method result value
     *
     * This is a re-implementation of the core service only required because the core
     * version is not callable from AJAX
     * @see mod_resource_external::log_resource_view_returns()
     * @return external_description
     * @since Moodle 3.0
     */
    public static function get_langs_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'message' => new external_value(PARAM_TEXT, 'error msg'),
                'response' => new external_multiple_structure(
                    \qtype_mlnlpessay\external\lang_exporter::get_read_structure()
                )
            )
        );
    }

    public static function get_topics() {
        global $DB, $USER, $PAGE;

        $return = [];
        $status = true;
        $message = '';
        try {
            $context = context_system::instance();
            $PAGE->set_context($context);
            foreach (\qtype_mlnlpessay\persistent\topics::get_records() as $persistent) {
                $record = $persistent->to_record();
                $related = ['context' => $context, 'activeint' => intval($record->active)];
                unset($record->active);
                $langsdata = new \qtype_mlnlpessay\external\topic_exporter($record, $related);
                $return[] = $langsdata->export($PAGE->get_renderer('core'));
            }
        } catch (\Exception $e) {
            $status = false;
            $message = $e->getMessage();
        }

        return ['status' => $status, 'message' => $message, 'response' => $return];
    }

    /**
     * Simulate the resource/view.php web interface page: trigger events, completion, etc...
     *
     * This is a re-implementation of the core service, only required because the core
     * version is not callable from AJAX
     * @see mod_resource_external::log_resource_view_parameters()
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function get_topics_parameters() {
        return new external_function_parameters(
            []
        );
    }

    /**
     *
     * Returns description of method result value
     *
     * This is a re-implementation of the core service only required because the core
     * version is not callable from AJAX
     * @see mod_resource_external::log_resource_view_returns()
     * @return external_description
     * @since Moodle 3.0
     */
    public static function get_topics_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'message' => new external_value(PARAM_TEXT, 'error msg'),
                'response' => new external_multiple_structure(
                    \qtype_mlnlpessay\external\topic_exporter::get_read_structure()
                )
            )
        );
    }

    public static function get_subtopics() {
        global $DB, $USER, $PAGE;

        $return = [];
        $status = true;
        $message = '';
        try {
            $context = context_system::instance();
            $PAGE->set_context($context);
            foreach (\qtype_mlnlpessay\persistent\subtopics::get_records() as $persistent) {
                $record = $persistent->to_record();
                $related = ['context' => $context, 'activeint' => intval($record->active)];
                unset($record->active);
                $langsdata = new \qtype_mlnlpessay\external\subtopic_exporter($record, $related);
                $return[] = $langsdata->export($PAGE->get_renderer('core'));
            }
        } catch (\Exception $e) {
            $status = false;
            $message = $e->getMessage();
        }

        return ['status' => $status, 'message' => $message, 'response' => $return];
    }

    /**
     * Simulate the resource/view.php web interface page: trigger events, completion, etc...
     *
     * This is a re-implementation of the core service, only required because the core
     * version is not callable from AJAX
     * @see mod_resource_external::log_resource_view_parameters()
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function get_subtopics_parameters() {
        return new external_function_parameters(
            []
        );
    }

    /**
     *
     * Returns description of method result value
     *
     * This is a re-implementation of the core service only required because the core
     * version is not callable from AJAX
     * @see mod_resource_external::log_resource_view_returns()
     * @return external_description
     * @since Moodle 3.0
     */
    public static function get_subtopics_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'message' => new external_value(PARAM_TEXT, 'error msg'),
                'response' => new external_multiple_structure(
                    \qtype_mlnlpessay\external\subtopic_exporter::get_read_structure()
                )
            )
        );
    }

    public static function save_settings($formdata, $action) {
        global $DB, $USER, $PAGE;

        $status = true;
        $message = '';

        $params = self::validate_parameters(
            self::save_settings_parameters(),
            array(
                'formdata' => $formdata,
                'action' => $action
            )
        );

        try {
            parse_str($params['formdata'], $data);
            $class = 'qtype_mlnlpessay\persistent\\' . $params['action'];
            $dataobject = new \stdClass();
            foreach ($class::properties_definition() as $propertyname => $property) {
                if (isset($data[$propertyname])) {
                    $dataobject->$propertyname = $data[$propertyname];
                }
            }

            if ($dataobject->id) {
                $persistent = new $class($dataobject->id);
                unset($dataobject->id);
                $persistent->from_record($dataobject);
                $persistent->update();
            } else {
                unset($dataobject->id);
                $persistent = new $class(0, $dataobject);
                $persistent->create();
            }

            foreach (['topic', 'subtopic'] as $field) {
                $persistentname = $field . 's';
                $classname = 'qtype_mlnlpessay\persistent\\categories_' . $persistentname;
                foreach ($classname::get_records(['categoryid' => $persistent->get('id')]) as $catpersistent) {
                    if ($key = array_search($catpersistent->get('id'), $data[$persistentname])) {
                        unset($data[$persistentname][$key]);
                    } else {
                        $catpersistent->delete();
                    }
                }

                foreach ($data[$persistentname] as $fieldid) {
                    $catpersistent =
                        new $classname(0, (object) ['categoryid' => $persistent->get('id'), $field . 'id' => $fieldid]);
                    $catpersistent->create();
                }
            }

        } catch (\Exception $e) {
            $status = false;
            $message = $e->getMessage();
        }


        return ['status' => $status, 'message' => $message];
    }

    /**
     * Simulate the resource/view.php web interface page: trigger events, completion, etc...
     *
     * This is a re-implementation of the core service, only required because the core
     * version is not callable from AJAX
     * @see mod_resource_external::log_resource_view_parameters()
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function save_settings_parameters() {
        return new external_function_parameters(
            [
                'formdata' => new external_value(PARAM_RAW, 'form data'),
                'action' => new external_value(PARAM_ALPHA, 'action class', VALUE_REQUIRED),
            ]
        );
    }

    /**
     *
     * Returns description of method result value
     *
     * This is a re-implementation of the core service only required because the core
     * version is not callable from AJAX
     * @see mod_resource_external::log_resource_view_returns()
     * @return external_description
     * @since Moodle 3.0
     */
    public static function save_settings_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'message' => new external_value(PARAM_TEXT, 'error msg'),
            )
        );
    }

    public static function delete_setting($id, $action) {
        global $DB, $USER, $PAGE;

        $status = true;
        $message = '';

        $params = self::validate_parameters(
            self::delete_setting_parameters(),
            array(
                'id' => $id,
                'action' => $action
            )
        );

        try {
            $class = 'qtype_mlnlpessay\persistent\\' . $params['action'];
            $persistent = new $class($params['id']);
            $persistent->delete();
        } catch (\Exception $e) {
            $status = false;
            $message = $e->getMessage();
        }

        return ['status' => $status, 'message' => $message];
    }

    /**
     * Simulate the resource/view.php web interface page: trigger events, completion, etc...
     *
     * This is a re-implementation of the core service, only required because the core
     * version is not callable from AJAX
     * @see mod_resource_external::log_resource_view_parameters()
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function delete_setting_parameters() {
        return new external_function_parameters(
            [
                'id' => new external_value(PARAM_INT, 'setting id', VALUE_REQUIRED),
                'action' => new external_value(PARAM_ALPHA, 'action class', VALUE_REQUIRED),
            ]
        );
    }

    /**
     *
     * Returns description of method result value
     *
     * This is a re-implementation of the core service only required because the core
     * version is not callable from AJAX
     * @see mod_resource_external::log_resource_view_returns()
     * @return external_description
     * @since Moodle 3.0
     */
    public static function delete_setting_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'message' => new external_value(PARAM_TEXT, 'error msg'),
            )
        );
    }

    public static function toggle_visible($id, $action) {
        global $DB, $USER, $PAGE;

        $status = true;
        $message = '';

        $params = self::validate_parameters(
            self::toggle_visible_parameters(),
            array(
                'id' => $id,
                'action' => $action
            )
        );

        try {
            $class = 'qtype_mlnlpessay\persistent\\' . $params['action'];
            $persistent = new $class($params['id']);
            if ($persistent->get('active')) {
                $persistent->set('active', 0);
            } else {
                $persistent->set('active', 1);
            }
            $persistent->update();
        } catch (\Exception $e) {
            $status = false;
            $message = $e->getMessage();
        }

        return ['status' => $status, 'message' => $message];
    }

    /**
     * Simulate the resource/view.php web interface page: trigger events, completion, etc...
     *
     * This is a re-implementation of the core service, only required because the core
     * version is not callable from AJAX
     * @see mod_resource_external::log_resource_view_parameters()
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function toggle_visible_parameters() {
        return new external_function_parameters(
            [
                'id' => new external_value(PARAM_INT, 'setting id', VALUE_REQUIRED),
                'action' => new external_value(PARAM_ALPHA, 'action class', VALUE_REQUIRED),
            ]
        );
    }

    /**
     *
     * Returns description of method result value
     *
     * This is a re-implementation of the core service only required because the core
     * version is not callable from AJAX
     * @see mod_resource_external::log_resource_view_returns()
     * @return external_description
     * @since Moodle 3.0
     */
    public static function toggle_visible_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'message' => new external_value(PARAM_TEXT, 'error msg'),
            )
        );
    }

    public static function csv_upload($formdata) {
        global $DB, $USER, $PAGE;

        $status = true;
        $message = '';
        $response = [
            'rows' => [],
            'rowcount' => 0,
            'skippedrows' => '',
        ];
        $PAGE->set_context(\context_system::instance());
        $params = self::validate_parameters(
            self::csv_upload_parameters(),
            array(
                'formdata' => $formdata
            )
        );
        parse_str($params['formdata'], $data);
        $skipped = [];
        if (isset($data['csvuploadfile'])) {
            $sql = "SELECT * FROM {files} WHERE filename != '.' AND component = 'user' AND filearea = 'draft' AND itemid = ?";
            if ($filerecord = $DB->get_record_sql($sql, [$data['csvuploadfile']])) {
                $fs = get_file_storage();
                if ($file = $fs->get_file(
                    $filerecord->contextid,
                    $filerecord->component,
                    $filerecord->filearea,
                    $filerecord->itemid,
                    $filerecord->filepath,
                    $filerecord->filename,
                )) {
                    $data = str_getcsv($file->get_content(), "\n"); //parse the rows
                    $headerskip = true;
                    $rownumber = 1;
                    foreach($data as &$row) {
                        if ($headerskip) {
                            $headerskip = false;
                            $rownumber++;
                            continue;
                        }
                        $parsedrow = str_getcsv($row);
                        // Require at least name and tag
                        if (empty($parsedrow[0]) || empty($parsedrow[4])) {
                            $skipped[] = $rownumber;
                            $rownumber++;
                            continue;
                        }
                        $response['rows'][] = [
                            'name' => trim($parsedrow[0]),
                            'description' => trim($parsedrow[1] ?? ''),
                            'modelid' => trim($parsedrow[2] ?? ''),
                            'modelname' => trim($parsedrow[3] ?? ''),
                            'tag' => trim($parsedrow[4]),
                            'lang' => trim($parsedrow[5] ?? ''),
                            'topics' => trim($parsedrow[6] ?? ''),
                            'subtopics' => trim($parsedrow[7] ?? ''),
                        ];
                        $rownumber++;
                    }
                }
            }
        }
        $response['rowcount'] = count($response['rows']);
        $response['skippedrows'] = $skipped ? implode(', ', $skipped) : '';
        return ['status' => $status, 'message' => $message, 'response' => $response];
    }

    /**
     * Simulate the resource/view.php web interface page: trigger events, completion, etc...
     *
     * This is a re-implementation of the core service, only required because the core
     * version is not callable from AJAX
     * @see mod_resource_external::log_resource_view_parameters()
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function csv_upload_parameters() {
        return new external_function_parameters(
            [
                'formdata' => new external_value(PARAM_RAW, 'form data', VALUE_REQUIRED),
            ]
        );
    }

    /**
     *
     * Returns description of method result value
     *
     * This is a re-implementation of the core service only required because the core
     * version is not callable from AJAX
     * @see mod_resource_external::log_resource_view_returns()
     * @return external_description
     * @since Moodle 3.0
     */
    public static function csv_upload_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'message' => new external_value(PARAM_TEXT, 'error msg'),
                'response' => new external_single_structure(
                    [
                        'rows' => new external_multiple_structure(
                            \qtype_mlnlpessay\external\csvupload_exporter::get_read_structure()
                        )
                    ]
                )
            )
        );
    }

    /**
     * Perform the actual CSV upload: create or update categories, return summary and per-row results.
     * @param string $formdata
     * @return array
     */
    public static function csv_upload_perform($formdata) {
        global $DB, $USER, $PAGE, $SESSION;

        $status = true;
        $message = '';
        $created = 0;
        $updated = 0;
        $perrow = [];
        $createdids = [];
        $updatedids = [];
        $rowmap = [];
        $PAGE->set_context(\context_system::instance());
        $params = self::validate_parameters(
            self::csv_upload_perform_parameters(),
            array(
                'formdata' => $formdata
            )
        );
        parse_str($params['formdata'], $data);
        if (isset($data['csvuploadfile'])) {
            $sql = "SELECT * FROM {files} WHERE filename != '.' AND component = 'user' AND filearea = 'draft' AND itemid = ?";
            if ($filerecord = $DB->get_record_sql($sql, [$data['csvuploadfile']])) {
                $fs = get_file_storage();
                if ($file = $fs->get_file(
                    $filerecord->contextid,
                    $filerecord->component,
                    $filerecord->filearea,
                    $filerecord->itemid,
                    $filerecord->filepath,
                    $filerecord->filename,
                )) {
                    $data = str_getcsv($file->get_content(), "\n"); //parse the rows
                    $headerskip = true;
                    $rownumber = 1;
                    foreach($data as &$row) {
                        if ($headerskip) {
                            $headerskip = false;
                            $rownumber++;
                            continue;
                        }
                        $parsedrow = str_getcsv($row);
                        $catdata = [
                            'name' => trim($parsedrow[0]),
                            'description' => trim($parsedrow[1] ?? ''),
                            'modelid' => trim($parsedrow[2] ?? ''),
                            'model' => trim($parsedrow[3] ?? ''),
                            'tag' => trim($parsedrow[4]),
                            'langid' => 0, // will resolve below
                        ];
                        $langcode = trim($parsedrow[5] ?? '');
                        $topics = trim($parsedrow[6] ?? '');
                        $subtopics = trim($parsedrow[7] ?? '');
                        if ($langcode) {
                            $langrec = \qtype_mlnlpessay\persistent\langs::get_record(['code' => $langcode]);
                            if ($langrec) {
                                $catdata['langid'] = $langrec->get('id');
                            }
                        }

                        // Try to find existing category by name and tag.
                        $cat = \qtype_mlnlpessay\persistent\categories::get_record([
                            'name' => $catdata['name'],
                            'modelid' => $catdata['modelid']
                        ]);
                        if ($cat) {
                            $cat->from_record((object)$catdata);
                            $cat->update();
                            $updated++;
                            $updatedids[] = $cat->get('id');
                            $perrow[] = [
                                'row' => $rownumber,
                                'action' => 'updated',
                                'id' => $cat->get('id'),
                                'name' => $catdata['name'],
                                'model' => $catdata['model'],
                                'modelid' => $catdata['modelid'],
                                'tag' => $catdata['tag'],
                                'reason' => 'Updated existing category.'
                            ];
                            $rowmap[$rownumber] = ['action' => 'updated', 'id' => $cat->get('id')];
                        } else if (!empty($catdata['model']) && !empty($catdata['modelid']) && !empty($catdata['tag'])) {
                            $cat = new \qtype_mlnlpessay\persistent\categories(0, (object)$catdata);
                            $cat->create();
                            $created++;
                            $createdids[] = $cat->get('id');
                            $perrow[] = [
                                'row' => $rownumber,
                                'action' => 'created',
                                'id' => $cat->get('id'),
                                'name' => $catdata['name'],
                                'model' => $catdata['model'],
                                'modelid' => $catdata['modelid'],
                                'tag' => $catdata['tag'],
                                'reason' => 'Created new category.'
                            ];
                            $rowmap[$rownumber] = ['action' => 'created', 'id' => $cat->get('id')];
                        } else {
                            $perrow[] = [
                                'row' => $rownumber,
                                'action' => 'skipped',
                                'id' => null,
                                'name' => $catdata['name'],
                                'model' => $catdata['model'],
                                'modelid' => $catdata['modelid'],
                                'tag' => $catdata['tag'],
                                'reason' => 'Missing required fields.'
                            ];
                            $rowmap[$rownumber] = ['action' => 'skipped', 'id' => null];
                        }

                        if ($cat) {
                            foreach (['topic', 'subtopic'] as $fieldname) {
                                $fieldnameplural = $fieldname . 's';
                                if ($$fieldnameplural) {
                                    $$fieldnameplural = explode(',', $$fieldnameplural);
                                    foreach ($$fieldnameplural as $fieldvalue) {
                                        $fieldvalue = trim($fieldvalue, ' \'"');
                                        $classname = '\qtype_mlnlpessay\persistent\\' . $fieldnameplural;
                                        $fieldrec = $classname::get_record(['name' => $fieldvalue]);
                                        if (!$fieldrec) {
                                            $fieldrec = new $classname(0, (object)['name' => $fieldvalue, 'active' => 1]);
                                            $fieldrec->create();
                                        }

                                        $fieldid = $fieldrec->get('id');
                                        $linkedclass = '\qtype_mlnlpessay\\persistent\categories_' . $fieldnameplural;
                                        if (!$linkedclass::get_record(['categoryid' => $cat->get('id'), $fieldname . 'id' => $fieldid])) {
                                            $linkedclassinstance = new $linkedclass(0, (object) ['categoryid' => $cat->get('id'), $fieldname . 'id' => $fieldid]);
                                            $linkedclassinstance->create();
                                        }
                                    }
                                }
                            }
                        }

                        $rownumber++;
                    }
                }
            }
        }
        // Store import session for undo (in session for simplicity, could use a DB table for persistence)
        $importid = uniqid('mlnlpessaycsv_', true);
        if (!isset($SESSION->mlnlpessay_csv_imports)) {
            $SESSION->mlnlpessay_csv_imports = [];
        }
        $SESSION->mlnlpessay_csv_imports[$importid] = [
            'createdids' => $createdids,
            'updatedids' => $updatedids,
            'rowmap' => $rowmap,
            'time' => time(),
            'userid' => $USER->id,
        ];
        $response = [
            'created' => $created,
            'updated' => $updated,
            'importid' => $importid,
            'perrow' => $perrow
        ];

        return ['status' => $status, 'message' => $message, 'response' => $response];
    }

    /**
     * Undo the last CSV import by deleting created records and reverting updated ones if possible.
     * @param string $importid
     * @return array
     */
    public static function csv_upload_undo($importid) {
        global $DB, $USER, $SESSION;
        $status = true;
        $message = '';
        $undone = 0;
        if (!isset($SESSION->mlnlpessay_csv_imports[$importid])) {
            return ['status' => false, 'message' => 'Import session not found.', 'response' => []];
        }
        $import = $SESSION->mlnlpessay_csv_imports[$importid];
        // Only allow the user who did the import to undo
        if ($import['userid'] != $USER->id) {
            return ['status' => false, 'message' => 'Permission denied.', 'response' => []];
        }
        // Delete created categories
        foreach ($import['createdids'] as $id) {
            try {
                $cat = new \qtype_mlnlpessay\persistent\categories($id);
                $cat->delete();
                $undone++;
            } catch (\Exception $e) {}
        }
        // Optionally, could restore previous state for updated records if you store a backup
        unset($SESSION->mlnlpessay_csv_imports[$importid]);
        return ['status' => $status, 'message' => $message, 'response' => ['undone' => $undone]];
    }

    public static function csv_upload_undo_parameters() {
        return new external_function_parameters([
            'importid' => new external_value(PARAM_TEXT, 'Import session ID', VALUE_REQUIRED),
        ]);
    }

    public static function csv_upload_undo_returns() {
        return new external_single_structure([
            'status' => new external_value(PARAM_BOOL, 'status: true if success'),
            'message' => new external_value(PARAM_TEXT, 'error msg'),
            'response' => new external_single_structure([
                'undone' => new external_value(PARAM_INT, 'Number of records undone'),
            ])
        ]);
    }

    public static function csv_upload_perform_parameters() {
        return new external_function_parameters([
            'formdata' => new external_value(PARAM_RAW, 'form data', VALUE_REQUIRED),
        ]);
    }

    public static function csv_upload_perform_returns() {
        return new external_single_structure([
            'status' => new external_value(PARAM_BOOL, 'status: true if success'),
            'message' => new external_value(PARAM_TEXT, 'error msg'),
            'response' => new external_single_structure([
                'perrow' => new external_multiple_structure(
                    \qtype_mlnlpessay\external\csvperform_exporter::get_read_structure()
                ),
                'created' => new external_value(PARAM_INT, 'Number of categories created'),
                'updated' => new external_value(PARAM_INT, 'Number of categories updated'),
            ])
        ]);
    }
}