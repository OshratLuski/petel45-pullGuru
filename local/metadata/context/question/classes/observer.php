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

namespace metadatacontext_question;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/metadata/context/question/classes/context_handler.php');

/**
 * Local metadatacontext_question event handler.
 * @package metadatacontext_question
 * @subpackage metadatacontext_question
 * @author Mike Churchward <mike.churchward@poetopensource.org>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2017, onwards Poet
 */
class observer {
    /**
     * Triggered via question_deleted event.
     * - Removes question metadata
     *
     * @param \core\event\question_deleted $event
     * @return bool true on success
     */
    public static function question_deleted(\core\event\question_deleted $event) {
        global $DB;

        $obj = $DB->get_record('question_versions', ['questionid' => $event->objectid]);
        if (!empty($obj) && isset($obj->questionbankentryid)) {
            foreach ($DB->get_records('question_versions', ['questionbankentryid' => $obj->questionbankentryid]) as $item) {
                \local_metadata\observer::delete_metadata(CONTEXT_QUESTION, $item->questionid);
            }
        }

        return true;
    }

    /**
     * Triggered via question_deleted event.
     * - Removes question metadata
     *
     * @param \core\event\question_created $event
     * @return bool true on success
     */
    public static function question_created(\core\event\question_created $event) {
        global $DB;

        $obj = $DB->get_record('question_versions', ['questionid' => $event->objectid]);
        if ($oldversion = $obj->version - 1) {
            $prevq = $DB->get_record('question_versions', ['version' => $oldversion, 'questionbankentryid' => $obj->questionbankentryid]);

            foreach (\local_metadata\mcontext::question()->getFields() as $field) {
                if ($oldvalue = \local_metadata\mcontext::question()->get($prevq->questionid, $field->shortname)){
                    \local_metadata\mcontext::question()->save($event->objectid, $field->shortname, $oldvalue);
                }
            }
        }

        return true;
    }
}
