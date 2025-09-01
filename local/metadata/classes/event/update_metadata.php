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
 * The local_metadata assessable submitted event.
 *
 * @package    local_metadata
 * @copyright  2013 Frédéric Massart
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_metadata\event;

defined('MOODLE_INTERNAL') || die();

class update_metadata extends \core\event\base {
    /**
     * Create instance of event.
     *
     * @since Moodle 2.7
     *
     * @param \assign $assign
     * @param \stdClass $submission
     * @param bool $editable
     * @return update_metadata
     */
    public static function create_event(\stdClass $metadata) {

        switch ($metadata->contextlevel) {
            case CONTEXT_MODULE:
                try {
                    $context = \context_module::instance($metadata->id);
                } catch (\Exception $e) {
                    $context = false;
                }
                break;
            case CONTEXT_COURSE:
                try {
                    $context = \context_course::instance($metadata->id);
                } catch (\Exception $e) {
                    $context = false;
                }
                break;
            case CONTEXT_COURSECAT:
                try {
                    $context = \context_coursecat::instance($metadata->id);
                } catch (\Exception $e) {
                    $context = false;
                }
                break;
            case CONTEXT_USER:
                try {
                    $context = \context_user::instance($metadata->id);
                } catch (\Exception $e) {
                    $context = false;
                }
                break;
            default:
                $context = \context_system::instance();
        }

        $data = array(
            'context' => $context,
            'objectid' => $metadata->id,
            'other' => array(

            ),
        );

        /** @var update_metadata $event */
        $event = self::create($data);
        //$event->set_assign($assign);
        //$event->add_record_snapshot('upgrade_metadata', $metadata);
        return $event;
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' has updated the metadata with id '$this->objectid' ";
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return 'metadata_updated';
    }

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['objecttable'] = 'local_metadata';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception
     * @return void
     */
    protected function validate_data() {
        parent::validate_data();

        //if (!isset($this->other['submission_editable'])) {
        //    throw new \coding_exception('The \'submission_editable\' value must be set in other.');
        //}
    }

    public static function get_objectid_mapping() {
        //return array('db' => 'assign_submission', 'restore' => 'submission');

        return false;
    }

    public static function get_other_mapping() {
        // Nothing to map.
        return false;
    }
}
