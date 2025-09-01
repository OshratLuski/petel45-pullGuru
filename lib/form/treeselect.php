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
 * autocomplete type form element
 *
 * Contains HTML class for a autocomplete type element
 *
 * @package   core_form
 * @copyright 2015 Damyon Wiese <damyon@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CFG;

require_once('HTML/QuickForm/element.php');
require_once('templatable_form_element.php');

/**
 * Autocomplete as you type form element
 *
 * HTML class for a treeselect type element
 *
 * @package   core_form
 * @copyright 2015 Damyon Wiese <damyon@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class MoodleQuickForm_treeselect extends HTML_QuickForm_element implements templatable{
    use templatable_form_element {
        export_for_template as export_for_template_base;
    }

    /** @var string html for help button, if empty then no help will icon will be dispalyed. */
    var $_helpbutton='';

    var $_options = [];

    /**
     * constructor
     *
     * @param string $elementName Select name attribute
     * @param mixed $elementLabel Label(s) for the select
     * @param mixed $options Data to be used to populate options
     * @param mixed $attributes Either a typical HTML attribute string or an associative array. Special options
     *                          "tags", "placeholder", "ajax", "multiple", "casesensitive" are supported.
     */
    public function __construct($elementName=null, $elementLabel=null, $options=[], $attributes=null) {
        global $PAGE;

        $this->_type = 'treeselect';

        $PAGE->requires->css('/lib/form/css/treeselect.css');

        $this->_options = $options;

        parent::__construct($elementName, $elementLabel, $attributes);
    }

    /**
     * Old syntax of class constructor. Deprecated in PHP7.
     *
     * @deprecated since Moodle 3.1
     */
    public function MoodleQuickForm_treeselect($elementName=null, $elementLabel=null, $attributes=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $attributes);
    }

    /**
     * Sets name of filemanager
     *
     * @param string $name name of the treeselect
     */
    function setName($name) {
        $this->updateAttributes(array('name'=>$name));
    }

    /**
     * Returns name of filemanager
     *
     * @return string
     */
    function getName() {
        return $this->getAttribute('name');
    }

    /**
     * Updates treeselect attribute value
     *
     * @param string $value value to set
     */
    function setValue($value) {
        $this->updateAttributes(array('value'=>$value));
    }

    /**
     * Returns treeselect attribute value
     *
     * @return string
     */
    function getValue() {
        return $this->getAttribute('value');
    }

    /**
     * get html for help button
     *
     * @return string html for help button
     */
    function getHelpButton(){
        return $this->_helpbutton;
    }

    function setDefault(){
        return $this->_helpbutton;
    }

    function toHtml(){
        global $PAGE;

        // Enhance the select with javascript.
        //$this->_generateId();
        //$id = $this->getAttribute('id');

        $html = '';

        return $html;
    }

    public function export_for_template(renderer_base $output) {
        global $PAGE;

        $context = $this->export_for_template_base($output);

        $values = [];
        $json = $this->_attributes['value'];
        if(isset($this->_attributes['value']) && !empty($json)){
            $values = explode(',', $json);
        }

        $disabled = (isset($this->_attributes['disabled']) && $this->_attributes['disabled'] == 1) ? true : false;

        $PAGE->requires->js_call_amd('core_form/treeselectmain', 'init',
                [
                        $this->_attributes['name'],
                        $this->_options,
                        $values,
                        $disabled
                ]);


        return $context;
    }
}
