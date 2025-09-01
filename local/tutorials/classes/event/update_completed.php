<?php

/**
 * toturials update completed event.
 *
 * @package    local_toturials
 * @since      Moodle 3.9
 * @copyright  2022  Matan Berkovicth <matan.berkovitch@weizmann.ac.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace local_tutorials\event;
defined('MOODLE_INTERNAL') || die();

class update_completed extends \core\event\base {

    /**
     * Init method.
     *
     * Please override this in extending class and specify objecttable.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'local_tutorials';

    }


    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' completed the '{$this->objecttable}'  ";
    }

    /**
     * Return localised event name.
     *
     * @return string
     */

    public static function get_name() {
        return get_string('eventupdatecompleted', 'local_toturials');
    }

}