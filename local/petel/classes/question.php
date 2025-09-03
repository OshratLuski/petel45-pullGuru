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
 * Question class for PETEL local plugin
 *
 * @package    local_petel
 * @copyright  2025 Weizzman
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_petel;

defined('MOODLE_INTERNAL') || die();

use core_customfield\api as customfield_api;
use core_customfield\category_controller;
use core_customfield\field_controller;
use qbank_customfields\customfield\question_handler;
use question_bank;

/**
 * Question class handling question-related functionality
 */
class question {

    /**
     * Constructor
     */
    public function __construct() {
    }

    /**
     * Ensures a custom field exists for questions, creating it if necessary.
     *
     * @param string $shortname The shortname of the custom field
     * @param string $name The name of the custom field
     * @param string $description The description of the custom field
     * @param string $type The type of the custom field (default: 'text')
     * @param string $category The category name for the custom field (default: 'General')
     * @param array $config Additional configuration for the custom field (default: [])
     * @return field_controller The field controller for the custom field
     */
    public static function ensure_question_customfield(
            string $shortname,
            string $name,
            string $description,
            string $type = 'text',
            string $category = 'General',
            array $config = []
    ): field_controller {
        // 1) Get the questions handler (Question Bank custom fields use a dedicated core qbank plugin).
        $handler = question_handler::create(); // itemid is always 0 for questions.

        // 2) Find or create the target category.
        $categories = customfield_api::get_categories_with_fields('qbank_customfields', 'question', 0);
        $cat = null;
        foreach ($categories as $c) {
            if (trim($c->get('name')) === $category) {
                $cat = $c;
                break;
            }
        }
        if (!$cat) {
            $catid = $handler->create_category($category);
            $cat = category_controller::create($catid, null, $handler);
        }

        // 3) If the field already exists, return it.
        foreach ($cat->get_fields() as $existing) {
            if ($existing->get('shortname') === $shortname) {
                return $existing;
            }
        }

        // 4) Create the field in that category.
        $record = (object) [
                'type' => $type,
                'shortname' => $shortname,
                'name' => $name,
                'description' => $description,
                'categoryid' => $cat->get('id'),
                'sortorder' => 0,
                'configdata' => json_encode($config)
        ];

        /** @var field_controller $field */
        $field = field_controller::create(0, $record);
        $field->save(); // Persists definition + config.

        return $field;
    }

    /**
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public static function set_question_customfield_value(int $questionid, string $shortname, $value): void {
        global $DB;

        $handler = \core_customfield\handler::get_handler('qbank_customfields', 'question', 0);
        $fields = $handler->get_fields();

        $field = null;

        foreach ($fields as $f) {
            if ($f->get('shortname') === $shortname) {
                $field = $f;
                break;
            }
        }

        if (!$field) {
            throw new \moodle_exception('unknownfield', 'error', '', $shortname);
        }

        $version = $DB->get_record('question_versions', ['questionid' => $questionid], 'questionbankentryid', MUST_EXIST);
        $entry =
                $DB->get_record('question_bank_entries', ['id' => $version->questionbankentryid], 'questioncategoryid', MUST_EXIST);
        $category = $DB->get_record('question_categories', ['id' => $entry->questioncategoryid], 'contextid', MUST_EXIST);
        $context = \context::instance_by_id($category->contextid);

        $fieldcontroller = \core_customfield\field_controller::create($field->get('id'));
        $existingdata = $DB->get_record('customfield_data', [
                'fieldid' => $field->get('id'),
                'instanceid' => $questionid
        ]);

        $datacontroller = \core_customfield\data_controller::create($existingdata ? $existingdata->id : 0, null, $fieldcontroller);
        $datacontroller->set('instanceid', $questionid);
        $datacontroller->set('contextid', $existingdata ? $existingdata->contextid : $context->id);
        $datacontroller->set('value', $value);
        $datacontroller->set('charvalue', $value);
        $datacontroller->save();
    }

    public static function calculate_qhash(int $questionid): ?string {
        global $DB;

        try {
            $question = question_bank::load_question_data($questionid);
            $hashdata = str_replace(" ", "", $question->questiontext . $question->name);
            $type = question_bank::get_qtype($question->qtype, false);

            $possibleidfields = ['questionid', 'question', 'idquestion'];

            // 1. extra_question_fields
            $extraquestionfields = $type->extra_question_fields();
            if (is_array($extraquestionfields)) {
                $table = array_shift($extraquestionfields);
                if ($DB->get_manager()->table_exists($table)) {
                    $fields = 'id, ' . implode(', ', $extraquestionfields);
                    foreach ($possibleidfields as $idfield) {
                        if ($DB->get_manager()->field_exists($table, $idfield)) {
                            $extra = $DB->get_record($table, [$idfield => $question->id], $fields);
                            if ($extra) {
                                foreach ($extraquestionfields as $field) {
                                    if (isset($extra->$field)) {
                                        $hashdata .= $extra->$field;
                                    }
                                }
                            }
                            break;
                        }
                    }
                }
            }

            // 2. qtype_xxx_options
            $optionstable = 'qtype_' . $type->name() . '_options';
            if ($DB->get_manager()->table_exists($optionstable)) {
                foreach ($possibleidfields as $idfield) {
                    if ($DB->get_manager()->field_exists($optionstable, $idfield)) {
                        $qoptions = $DB->get_record($optionstable, [$idfield => $question->id]);
                        if ($qoptions) {
                            $excluded = ['id', 'questionid', 'question', 'idquestion',
                                    'usermodified', 'timecreated', 'timemodified'];
                            foreach ($excluded as $k) {
                                unset($qoptions->$k);
                            }
                            foreach ((array) $qoptions as $val) {
                                $hashdata .= $val;
                            }
                        }
                        break;
                    }
                }
            }

            // 3. Custom tables
            $customtables = [
                    'calculated' => 'question_calculated_options',
                    'combined' => 'qtype_combined',
                    'ddimageortext' => ['qtype_ddimageortext_drops', 'qtype_ddimageortext_drags'],
                    'ddmarker' => ['qtype_ddmarker_drops', 'qtype_ddmarker_drags'],
                    'ddmatch' => ['qtype_ddmatch_options', 'qtype_ddmatch_subquestions'],
                    'ddwtos' => 'question_ddwtos',
                    'diagnosticadvdesc' => 'qtype_diagnosticadvdesc',
                    'gapselect' => 'question_gapselect',
                    'gapselectmath' => 'question_gapselectmath',
                    'hvp' => 'qtype_hvp',
                    'multianswer' => 'question_multianswer',
                    'numerical' => ['question_numerical', 'question_numerical_options'],
                    'oumultiresponse' => 'question_oumultiresponse',
                    'truefalse' => 'question_truefalse',
            ];

            if (isset($customtables[$type->name()])) {
                $tables = (array) $customtables[$type->name()];
                foreach ($tables as $tbl) {
                    if ($DB->get_manager()->table_exists($tbl)) {
                        foreach ($possibleidfields as $idfield) {
                            if ($DB->get_manager()->field_exists($tbl, $idfield)) {
                                $records = $DB->get_records($tbl, [$idfield => $question->id]);
                                foreach ($records as $rec) {
                                    foreach ($rec as $val) {
                                        $hashdata .= $val;
                                    }
                                }
                                break;
                            }
                        }
                    }
                }
            }

            // 4. text + type fallback
            $hashdata .= $question->qtype;
            return md5($hashdata);

        } catch (\Throwable $e) {
            $question = $DB->get_record('question', ['id' => $questionid], 'questiontext, qtype');
            if ($question) {
                return md5($question->questiontext . $question->qtype);
            }
            return null;
        }
    }

    public static function question_in_repository(int $questionid): bool {
        global $DB;
        $results = \community_oer\main_oer::structure_main_catalog();
        $courses = [];
        foreach ($results as $result) {
            if (!empty($result['courses'])) {
                $courses = array_merge($courses, array_column($result['courses'], 'id'));
            }
        }
        $sql = "SELECT ctx.instanceid as courseid
                  FROM {question} q
            INNER JOIN {question_versions} qv
                    ON qv.questionid = q.id
            INNER JOIN {question_bank_entries} qbe
                    ON qbe.id = qv.questionbankentryid
            INNER JOIN {question_categories} qc
                    ON qc.id = qbe.questioncategoryid
            INNER JOIN {context} ctx
                    ON ctx.id = qc.contextid
                 WHERE q.id = ? AND ctx.contextlevel = ?";
        $courseid = $DB->get_field_sql($sql, [$questionid, CONTEXT_COURSE]);
        if (!$courseid) {
            $coursemodule = $DB->get_field_sql($sql, [$questionid, CONTEXT_MODULE]);
            $courseid = $DB->get_field('course_modules', 'course', ['id' => $coursemodule]);
        }
        return $courseid && in_array($courseid, $courses);
    }

    public static function update_qhash($questionid) {
        $hash = self::calculate_qhash($questionid);
        if ($hash && !self::question_in_repository($questionid)) {
            self::set_question_customfield_value($questionid, 'qhash', $hash);
        }
    }
}