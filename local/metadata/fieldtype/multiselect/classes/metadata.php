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
 * Multiselect profile field.
 *
 * @package    metadatafieldtype_multiselect
 * @copyright  2022 Tamir Hajaj {@link https://sysbind.co.il}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace metadatafieldtype_multiselect;

defined('MOODLE_INTERNAL') || die;

/**
 * Class metadata
 *
 * @copyright  2022 Avi Levy {@link https://sysbind.co.il}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class metadata extends \local_metadata\fieldtype\metadata {

    /** @var array $options */
    public $options;

    /** @var int $datakey */
    public $datakey;

    /**
     * Constructor method.
     *
     * Pulls out the options for the multiselect from the database and sets the the corresponding key for the data if it exists.
     *
     * @param int $fieldid
     * @param int $instanceid
     * @param object $fielddata optional data for the field object.
     */
    public function __construct($fieldid = 0, $instanceid = 0, $fielddata = null) {
        // First call parent constructor.
        parent::__construct($fieldid, $instanceid, $fielddata);

        // Param 1 for multiselect type is the options.
        if (isset($this->field->param1)) {
            $options = array_filter(explode("\n", $this->field->param1));
        } else {
            $options = [];
        }
        // Option for value in HTML.
        $this->options = [];

        // Multi lang formatting parser.
        foreach ($options as $option) {
            // ID value for separator.
            $idvalue = explode(':', $option);
            // Lang values separator.
            preg_match_all("/([^|=]+)=([^|=]+)/", end($idvalue), $r);
            $result = array_combine($r[1], $r[2]);

            // Check current language on system for display.
            if(!$lang = get_parent_language()){
                $lang = current_language();
            }

            if (array_key_exists($lang, $result)) {
                $this->options[$idvalue[0]] = format_string($result[$lang]);
            } else {
                // If the value is not in supported in current lang set the result to be the first value.
                $this->options[$idvalue[0]] = format_string($result['en']);
            }
        }

        // Set the data key.
        if ($this->data !== null) {
            $key = json_decode($this->data);
            $this->data = $key;
            $this->datakey = $key;
        }

        // Set the name for display; will need to be a language string.
        $this->name = get_string('displayname', 'metadatafieldtype_multiselect');
    }
    /**
     * Create the code snippet for this field instance
     * Overwrites the base class method
     * @param moodleform $mform Moodle form instance
     */
    public function edit_field_add($mform) {
        global $USER;

        $admins = [];
        foreach (get_admins() as $admin) {
            $admins[] = $admin->id;
        }

        $attr = [];
        if ($this->field->locked == 1 && !in_array($USER->id, $admins)) {
            $attr = ['disabled'];
        }

        //$mform->addElement('header', 'general', format_string($this->field->name));

        $counter = 0;
        if ($this->field->param2 == 1) {
            foreach ($this->options as $key => $name) {

                if ($counter == 0) {
                    $mform->addElement('checkbox', "$this->inputname[{$key}]", format_string($this->field->name), $name, $attr);
                } else {
                    $mform->addElement('checkbox', "$this->inputname[{$key}]", '', $name, $attr);
                }

                $mform->setType("$this->inputname[{$key}]", PARAM_INT);

                if (is_array($this->data) && in_array($key, $this->data)) {
                    $mform->setDefault("$this->inputname[{$key}]", true);
                } else {
                    $mform->setDefault("$this->inputname[{$key}]", false);
                }

                $counter++;
            }
        } else {
            foreach ($this->options as $key => $name) {
                if ($counter == 0) {
                    $mform->addElement('radio', $this->inputname, format_string($this->field->name), $name, $key, $attr);
                } else {
                    $mform->addElement('radio', $this->inputname, null, $name, $key, $attr);
                }

                $counter++;
            }
        }
    }

    /**
     * Sets the required flag for the field in the form object
     *
     * @param moodleform $mform instance of the moodleform class
     */
    public function edit_field_set_required($mform) {
        global $USER;

        if ($this->field->param2 == 1) {
            return;
        }

        $admins = [];
        foreach (get_admins() as $admin) {
            $admins[] = $admin->id;
        }
        if ($this->field->locked != 1 || in_array($USER->id, $admins)) {
            parent::edit_field_set_required($mform);
        }
    }

    /**
     * Set the default value for this field instance
     * Overwrites the base class method.
     * @param moodleform $mform Moodle form instance
     */
    public function edit_field_set_default($mform) {
        $key = $this->data;
        $mform->setDefault($this->inputname, $key);
    }

    /**
     * The data from the form returns the key.
     *
     * This should be converted to the respective option string to be saved in database
     * Overwrites base class accessor method.
     *
     * @param mixed $data The key returned from the select input in the form
     * @param stdClass $datarecord The object that will be used to save the record
     * @return mixed Data or null
     */
    public function edit_save_data_preprocess($data, $datarecord) {

        if ($this->field->param2 == 1) {

            $arr = [];
            if ($data != null) {
                foreach ($data as $key => $name) {
                    $arr[] = $key;
                }
            }

            $data = $arr;
        }

        $datastr = json_encode($data, JSON_UNESCAPED_UNICODE);
        return $datastr;
    }

    /**
     * Saves the data coming from form
     * @param stdClass $new data coming from the form
     * @return mixed returns data id if success of db insert/update, false on fail, 0 if not permitted
     */
    public function edit_save_data($new) {
        global $DB;

        if (!isset($new->{$this->inputname})) {
            // Field not present in form, probably locked and invisible - skip it.

            $new->{$this->inputname} = null;
        }

        $data = new \stdClass();

        $new->{$this->inputname} = $this->edit_save_data_preprocess($new->{$this->inputname}, $data);

        $data->instanceid  = $new->id;
        $data->fieldid = $this->field->id;
        $data->data    = $new->{$this->inputname};

        if ($dataid = $DB->get_field('local_metadata', 'id', ['instanceid' => $data->instanceid, 'fieldid' => $data->fieldid])) {
            $data->id = $dataid;
            $DB->update_record('local_metadata', $data);
        } else {
            $DB->insert_record('local_metadata', $data);
        }
    }

    /**
     * When passing the instance object to the form class for the edit page
     * we should load the key for the saved data
     *
     * Overwrites the base class method.
     *
     * @param stdClass $instance Instance object.
     */
    public function edit_load_instance_data($instance) {
        $instance->{$this->inputname} = $this->data;
    }

    /**
     * HardFreeze the field if locked.
     *
     * @param moodleform $mform instance of the moodleform class
     */
    public function edit_field_set_locked($mform) {
        if (!$mform->elementExists($this->inputname)) {
            return;
        }
        if ($this->is_locked() && !has_capability('moodle/user:update', \context_system::instance())) {
            $mform->hardFreeze($this->inputname);
            $mform->setConstant($this->inputname, format_string($this->datakey));
        }
    }

    /**
     * Return the field type and null properties.
     * This will be used for validating the data submitted by a user.
     *
     * @return array the param type and null property
     * @since Moodle 3.2
     */
    public function get_field_properties() {
        return [PARAM_TEXT, NULL_NOT_ALLOWED];
    }
}
