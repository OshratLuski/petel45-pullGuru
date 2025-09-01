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
 * Menu profile field.
 *
 * @package    profilefield_treeselect
 * @copyright  2007 onwards Shane Elliot {@link http://pukunui.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace metadatafieldtype_treeselect;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot .'/local/metadata/fieldtype/treeselect/locallib.php');

/**
 * Class local_metadata_field_menu
 *
 * @copyright  2007 onwards Shane Elliot {@link http://pukunui.com}
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
     * Pulls out the options for the menu from the database and sets the the corresponding key for the data if it exists.
     *
     * @param int $fieldid
     * @param int $instanceid
     */
    public function __construct($fieldid = 0, $instanceid = 0) {
        global $PAGE;

        // First call parent constructor.
        parent::__construct($fieldid, $instanceid);

        $val = isset($this->field->param1) ? $this->field->param1 : '';

        list($options, $ids) = \class_treeselect::get_options($val);
        $this->options = $options;

        // Set the name for display; will need to be a language string.
        $this->name = 'Tree select';
    }

    /**
     * Create the code snippet for this field instance
     * Overwrites the base class method
     * @param moodleform $mform Moodle form instance
     */
    public function edit_field_add($mform) {

        $attr = [
                'disabled' => $this->field->locked == 1 && !$this->if_field_editable() ? true : false
        ];

        $mform->addElement('treeselect', $this->inputname, format_string($this->field->name), $this->options, $attr);
    }

    /**
     * Sets the required flag for the field in the form object
     *
     * @param moodleform $mform instance of the moodleform class
     */
    public function edit_field_set_required($mform) {
        if($this->field->locked != 1 || $this->if_field_editable()){
            parent::edit_field_set_required($mform);
        }
    }

    /**
     * Set the default value for this field instance
     * Overwrites the base class method.
     * @param moodleform $mform Moodle form instance
     */
    public function edit_field_set_default($mform) {

        $defaultkey = '';
        if(!empty($this->data)){
            $defaultkey = $this->data;
        }else{
            if(!empty($this->field->defaultdata)){
                $defaultkey = $this->field->defaultdata;
            }
        }

        $mform->setDefault($this->inputname, $defaultkey);
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

        list($options, $ids) = \class_treeselect::get_options($this->field->param1);

        if(!empty($data)) {
            $tmp = [];
            foreach (explode(',', $data) as $key) {
                if(in_array($key, $ids)){
                    $tmp[] = $key;
                }
            }

            $data = implode(',', $tmp);
        }

        //$data = json_encode($data, JSON_UNESCAPED_UNICODE);
		return $data;
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
        $instance->{$this->inputname} = $this->datakey;
    }

    /**
     * HardFreeze the field if locked.
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
     * Convert external data (csv file) from value to key for processing later by edit_save_data_preprocess
     *
     * @param string $value one of the values in menu options.
     * @return int options key for the menu
     */
    public function convert_external_data($value) {
        if (isset($this->options[$value])) {
            $retval = $value;
        } else {
            $retval = array_search($value, $this->options);
        }

        // If value is not found in options then return null, so that it can be handled
        // later by edit_save_data_preprocess.
        if ($retval === false) {
            $retval = null;
        }
        return $retval;
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


