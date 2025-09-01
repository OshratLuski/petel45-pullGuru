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
 * The community_sharewith helper.
 *
 * @package    community_sharewith
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

class functionHelp {

    public function __construct() {

    }

    public static function get_courses_by_tat_categories($catid) {
        global $DB;

        $sql = "SELECT * FROM {course_categories} WHERE path LIKE(?)";
        $categories = $DB->get_records_sql($sql, array('%/' . $catid . '/%'));
        $result = [];

        if (!empty($categories)) {
            foreach ($categories as $cat) {
                $tmp = array();
                $tmp['cat_id'] = $cat->id;
                $tmp['cat_name'] = $cat->name;

                $sql = "SELECT * FROM {course} WHERE category=? ORDER BY sortorder ASC";
                $tmp['courses'] = $DB->get_records_sql($sql, array($cat->id));

                $result[] = $tmp;
            }
        }

        return $result;
    }

    /**
     * Check ID value in metadata, updates if needed.
     *
     * @param string $cmid source activity ID
     * @return bool
     */
    public static function community_sharewith_check_metadata_id($cmid) {

        $value = \local_metadata\mcontext::module()->get($cmid, 'ID');
        if (empty($value)) {
            \local_metadata\mcontext::module()->save($cmid, 'ID', $cmid);
        }
    }

    /**
     * Inserts or updates activity metadata field.
     *
     * @param string $cmid source activity ID
     * @param int $fieldid source activity ID
     * @param string $data source activity ID
     * @param int $dataformat moodle data format
     */
    public static function community_sharewith_update_activity_metadata($cmid, $shortname, $data, $dataformat = 0) {
        \local_metadata\mcontext::module()->save($cmid, $shortname, $data, $dataformat);
    }

    /**
     * Update metadata
     *
     * @param community_sharewith $duplicationtask
     */
    public function update_metadata($post, $courseid, $userid, $sourceactivityid) {
        global $DB, $CFG;

        // Check permission to course oercatalog.
        $flagpermissionteacher = true;
        $namerole = 'editingteacher';
        $context = \context_course::instance($courseid);
        $roles = get_user_roles($context, $userid, false);
        foreach ($roles as $role) {
            if ($role->shortname == $namerole) {
                $flagpermissionteacher = false;
            }

        }

        // Remove permission teacher from course.
        if ($flagpermissionteacher) {
            $role = $DB->get_record('role', array('shortname' => $namerole));
            if (!empty($role)) {
                role_unassign($role->id, $userid, $context->id);
            }
        }

        require_once($CFG->dirroot . '/local/community/plugins/sharewith/classes/sharewith.php');

        $sharewith = new sharewith();
        $sharewith->setactivityid($sourceactivityid, $courseid);
        $sharewith->prepare_active_fields();

        // Userid.
        $this->community_sharewith_update_activity_metadata($sourceactivityid, 'userid', $userid);

        foreach ($sharewith->get_active_fields() as $item) {
            if (in_array($item->shortname, ['activitytitle', 'selectsections'])) {
                continue;
            }

            $shortname = $item->shortname;

            switch ($item->datatype) {

                // Not standart fields.
                case 'levelactivity':
                    $value = isset($post[$shortname]) ? $post[$shortname] : '';
                    $this->community_sharewith_update_activity_metadata($sourceactivityid, $shortname, $value, 1);
                    break;

                case 'durationactivity':
                    $arrdurationactivity = $this->get_types_content_metadata('durationactivity');
                    $durationactivitynum = isset($post[$shortname]) && !empty($post[$shortname]) ? $post[$shortname] : 0;
                    $value = $arrdurationactivity[$durationactivitynum];

                    $this->community_sharewith_update_activity_metadata($sourceactivityid, $shortname, $value['metadata_value']);
                    break;

                case 'originality':
                    if (isset($post['question_activity']) && isset($post['question_activity_url'])) {
                        if ($post['question_activity'] == 'true') {
                            $this->community_sharewith_update_activity_metadata($sourceactivityid, 'originality', 1);

                            if (!empty($post['question_activity_url'])) {
                                $this->community_sharewith_update_activity_metadata(
                                        $sourceactivityid, 'sourceurl', $post['question_activity_url']
                                );
                            }
                        }

                        if ($post['question_activity'] == 'false') {
                            $this->community_sharewith_update_activity_metadata($sourceactivityid, 'originality', 0);
                            $this->community_sharewith_update_activity_metadata($sourceactivityid, 'sourceurl', '');
                        }
                    }
                    break;

                case 'tags':
                    /* if (isset($post['tags']) && !empty($post['tags'])) {
                        $json = json_encode($post['tags'], JSON_UNESCAPED_UNICODE);
                        $this->community_sharewith_update_activity_metadata(
                            $sourceactivityid, 'keywords', $json);
                    } */

                    // Update tags.
                    if (isset($post['tags[]']) && !empty($post['tags[]'])) {

                        // Unique tags.
                        $arrtag = explode(',', $post['tags[]']);
                        $arrtagsunique = array_unique($arrtag);
                        $arrtagsreorder = array_values($arrtagsunique);

                        if (!empty($arrtagsreorder)) {
                            foreach ($arrtagsreorder as $ordertag => $nametag) {
                                $nameexists =
                                        $DB->get_record_sql("SELECT * FROM {tag} WHERE name='" . \core_text::strtolower($nametag) .
                                                "'");

                                if (!empty($nameexists)) {
                                    $tagid = $nameexists->id;
                                } else {
                                    $objinsert = new stdClass();
                                    $objinsert->userid = $userid;
                                    $objinsert->tagcollid = 1;
                                    $objinsert->name = \core_text::strtolower($nametag);
                                    $objinsert->rawname = $nametag;
                                    $objinsert->timemodified = time();

                                    $tagid = $DB->insert_record('tag', $objinsert);
                                }

                                $context = context_course::instance($courseid);
                                $contextid = $context->id;

                                $objinsert = new stdClass();
                                $objinsert->tagid = $tagid;
                                $objinsert->component = 'core';
                                $objinsert->itemtype = 'course_modules';
                                $objinsert->itemid = $sourceactivityid;
                                $objinsert->contextid = $contextid;
                                $objinsert->ordering = $ordertag;
                                $objinsert->timecreated = time();
                                $objinsert->timemodified = time();

                                $taginstance = $DB->insert_record('tag_instance', $objinsert);
                            }
                        }
                    }
                    break;

                // Standart fields.
                case 'textarea':
                    $value = isset($post[$shortname]) ? $post[$shortname] : '';
                    $this->community_sharewith_update_activity_metadata($sourceactivityid, $shortname, $value, 1);
                    break;

                case 'multiselect':
                    if ($item->multiselecttype == 'single') {
                        $value = isset($post[$shortname]) ? $post[$shortname] : '';
                        $this->community_sharewith_update_activity_metadata($sourceactivityid, $shortname, $value);
                    }

                    if ($item->multiselecttype == 'multi') {
                        $value = json_encode(explode(',', $post[$shortname]), JSON_UNESCAPED_UNICODE);
                        $this->community_sharewith_update_activity_metadata($sourceactivityid, $shortname, $value);
                    }
                    break;

                case 'multimenu':
                    $value = json_encode(explode(',', $post[$shortname]), JSON_UNESCAPED_UNICODE);
                    $this->community_sharewith_update_activity_metadata($sourceactivityid, $shortname, $value);
                    break;

                case 'menu':
                    $value = isset($post[$shortname]) ? $post[$shortname] : '';
                    $this->community_sharewith_update_activity_metadata($sourceactivityid, $shortname, $value);
                    break;

                case 'checkbox':
                    $value = isset($post[$shortname]) ? $post[$shortname] : '';
                    $this->community_sharewith_update_activity_metadata($sourceactivityid, $shortname, $value);
                    break;

                case 'fileupload':

                    $fs = get_file_storage();
                    $context = \context_module::instance($sourceactivityid);

                    $value = isset($post[$shortname]) ? $post[$shortname] : '';
                    if (!empty($value)) {

                        $itemid = trim($value);

                        $sql = "
                            SELECT *
                            FROM {files}
                            WHERE filename != '.' AND component = 'user' AND filearea = 'draft' AND itemid = ?";

                        $draft = $DB->get_record_sql($sql, array($itemid));
                        if (!empty($draft)) {

                            // Get previus file and delete.
                            $sql = "
                                SELECT *
                                FROM {files}
                                WHERE filename != '.' AND component = 'local_metadata' AND filearea = 'image' AND contextid = ?";
                            $prevfiles = $DB->get_records_sql($sql, array($context->id));
                            foreach ($prevfiles as $file) {
                                $fs->delete_area_files($file->contextid, $file->component, $file->filearea, $file->itemid);
                            }

                            // Create file.
                            $draft->contextid = $context->id;
                            $draft->component = 'local_metadata';
                            $draft->filearea = 'image';

                            $fs->create_file_from_storedfile($draft, $draft->id);

                            // Insert to local_metadata.
                            $this->community_sharewith_update_activity_metadata($sourceactivityid, $shortname, $itemid);
                        }
                    }
                    break;
            }
        }
    }

    /**
     * Update metadata
     *
     * @param community_sharewith $duplicationtask
     */
    public function update_metadata_cron($post, $userid, $sourceactivityid) {
        global $DB;

        $row = $DB->get_record('course_modules', array('id' => $sourceactivityid));
        $courseid = $row->course;

        $data = json_decode($post['data']);
        $this->update_metadata((array) $data, $courseid, $userid, $sourceactivityid);

        // Update title activity.
        $sql = "
            SELECT m.name, cm.instance
            FROM {course_modules} cm
            LEFT JOIN {modules} m ON(cm.module=m.id)
            WHERE cm.id=?;
        ";
        $mod = $DB->get_record_sql($sql, array($sourceactivityid));

        $objupdate = new stdClass();
        $objupdate->id = $mod->instance;
        $objupdate->name = $post['activitytitle'];

        $DB->update_record($mod->name, $objupdate);

        // Rebuild cm and course cache.
        \course_modinfo::purge_course_module_cache($courseid, $sourceactivityid);
        rebuild_course_cache($courseid, true, true);
    }

    public function get_types_content_metadata($shortname) {
        global $DB;

        $obj = $DB->get_record_sql("
            SELECT * FROM {local_metadata_field} WHERE contextlevel=? AND shortname=?",
                [CONTEXT_MODULE, $shortname]
        );

        $res = preg_split('/\R/', $obj->param1);
        $res = array_unique($res);

        $result = array();
        if (!empty($res)) {
            $countchecked = 0;
            foreach ($res as $str) {
                $arrstr = explode('|', $str);

                if (isset($arrstr[1]) && !empty($arrstr[1])) {
                    $icon = 'involve__button-image--' . $arrstr[1];
                } else {
                    $icon = '';
                }

                $checked = ($countchecked == 0) ? 'checked' : '';

                $tmp = array(
                        'metadata_name' => $arrstr[0],
                        'metadata_icon' => $icon,
                        'metadata_value' => $str,
                        'metadata_checked' => $checked
                );
                $result[] = $tmp;

                $countchecked++;
            }
        }

        return $result;
    }
}
