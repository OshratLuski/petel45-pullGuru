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
 *  Oer
 *
 * @package    community_oer
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once(__DIR__ . '/../../../../../question/engine/bank.php');

/**
 * Multiple checkboxes with icons for each label
 */
class oer_admin_setting_config_multicheckbox_with_icon extends admin_setting_configmulticheckbox {
    /** @var array Array of icons value=>icon */
    protected $icons;

    /**
     * Constructor: uses parent::__construct
     *
     * @param string $name unique ascii name, either 'mysetting' for settings that in config, or 'myplugin/mysetting' for ones in
     *         config_plugins.
     * @param string $visiblename localised
     * @param string $description long localised info
     * @param array $defaultsetting array of selected
     * @param array $choices array of $value=>$label for each checkbox
     * @param array $icons array of $value=>$icon for each checkbox
     */
    public function __construct($name, $visiblename, $description, $defaultsetting, array $choices, array $icons) {
        $this->icons = $icons;
        parent::__construct($name, $visiblename, $description, $defaultsetting, $choices);
    }

    /**
     * Returns XHTML field(s) as required by choices
     *
     * Relies on data being an array should data ever be another valid vartype with
     * acceptable value this may cause a warning/error
     * if (!is_array($data)) would fix the problem
     *
     * @param array $data An array of checked values
     * @param string $query
     * @return string XHTML field
     * @todo Add vartype handling to ensure $data is an array
     *
     */
    public function output_html($data, $query = '') {
        if (!$this->load_choices() || empty($this->choices)) {
            return '';
        }
        $default = $this->get_defaultsetting();
        if (is_null($default)) {
            $default = array();
        }
        if (is_null($data)) {
            $data = array();
        }
        $options = array();
        $defaults = array();
        foreach ($this->choices as $key => $description) {
            if (!empty($data[$key])) {
                $checked = 'checked="checked"';
            } else {
                $checked = '';
            }
            if (!empty($default[$key])) {
                $defaults[] = $description;
            }

            $options[] = '<input type="checkbox" id="' . $this->get_id() . '_' . $key . '" name="' . $this->get_full_name() . '[' .
                    $key . ']" value="1" ' . $checked . ' />'
                    . '<label for="' . $this->get_id() . '_' . $key . '">' . $this->icons[$key] .
                    highlightfast($query, $description) . '</label>';
        }

        if (is_null($default)) {
            $defaultinfo = null;
        } else if (!empty($defaults)) {
            $defaultinfo = implode(', ', $defaults);
        } else {
            $defaultinfo = get_string('none');
        }

        $return = '<div class="form-multicheckbox">';
        $return .= '<input type="hidden" name="' . $this->get_full_name() . '[xxxxx]" value="1" />';
        if ($options) {
            $return .= '<ul>';
            foreach ($options as $option) {
                $return .= '<li>' . $option . '</li>';
            }
            $return .= '</ul>';
        }
        $return .= '</div>';

        return format_admin_setting($this, $this->visiblename, $return, $this->description, false, '', $defaultinfo, $query);
    }
}

class oer_admin_setting_config_multiselect_with_icon extends admin_setting {
    /** @var array Array of choices value=>label */
    public $choices;
    protected $icons;
    protected $select;

    /**
     * Constructor: uses parent::__construct
     *
     * @param string $name unique ascii name, either 'mysetting' for settings that in config, or 'myplugin/mysetting' for ones in
     *         config_plugins.
     * @param string $visiblename localised
     * @param string $description long localised info
     * @param array $defaultsetting array of selected
     * @param array $choices array of $value=>$label for each checkbox
     */
    public function __construct($name, $visiblename, $description, $defaultsetting, array $choices, array $icons, array $select) {
        $this->choices = $choices;
        $this->icons = $icons;
        $this->select = $select;
        parent::__construct($name, $visiblename, $description, $defaultsetting);
    }

    /**
     * This public function may be used in ancestors for lazy loading of choices
     *
     * @return bool true if loaded, false if error
     * @todo Check if this function is still required content commented out only returns true
     */
    public function load_choices() {
        return true;
    }

    /**
     * Is setting related to query text - used when searching
     *
     * @param string $query
     * @return bool true on related, false on not or failure
     */
    public function is_related($query) {
        if (!$this->load_choices() || empty($this->choices)) {
            return false;
        }
        if (parent::is_related($query)) {
            return true;
        }

        foreach ($this->choices as $desc) {
            if (strpos(core_text::strtolower($desc), $query) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns the current setting if it is set
     *
     * @return mixed null if null, else an array
     */
    public function get_setting() {
        $result = $this->config_read($this->name);

        if (is_null($result)) {
            return null;
        }
        if ($result === '') {
            return array();
        }

        return (array) json_decode($result);
    }

    /**
     * Saves the setting(s) provided in $data
     *
     * @param array $data An array of data, if not array returns empty str
     * @return mixed empty string on useless data or bool true=success, false=failed
     */
    public function write_setting($data) {
        if (!is_array($data)) {
            return '';
        }
        if (!$this->load_choices() || empty($this->choices)) {
            return '';
        }
        unset($data['xxxxx']);
        $result = json_encode($data);

        return $this->config_write($this->name, $result) ? '' : get_string('errorsetting', 'admin');
    }

    /**
     * Returns XHTML field(s) as required by choices
     *
     * Relies on data being an array should data ever be another valid vartype with
     * acceptable value this may cause a warning/error
     * if (!is_array($data)) would fix the problem
     *
     * @param array $data An array of checked values
     * @param string $query
     * @return string XHTML field
     * @todo Add vartype handling to ensure $data is an array
     *
     */
    public function output_html($data, $query = '') {
        if (!$this->load_choices() || empty($this->choices)) {
            return '';
        }
        $default = $this->get_defaultsetting();
        if (is_null($default)) {
            $default = array();
        }
        if (is_null($data)) {
            $data = array();
        }

        $options = array();
        $defaults = array();
        foreach ($this->choices as $key => $description) {
            $checked = '';

            if (isset($data[$key])) {
                $checked = $data[$key];
            } else {
                if (isset($default[$key])) {
                    $checked = $default[$key];
                } else {
                    $firstkey = current(array_keys($this->select));
                    if (isset($data[$firstkey])) {
                        $checked = $data[$firstkey];
                    }
                }
            }

            $tmp = '<select class="custom-select" id="' . $this->get_id() . '_' . $key . '" name="' . $this->get_full_name() . '[' .
                    $key . ']">';

            foreach ($this->select as $value => $name) {
                if ($value == $checked) {
                    $tmp .= '<option selected value="' . $value . '">' . $name . '</option>';
                } else {
                    $tmp .= '<option value="' . $value . '">' . $name . '</option>';
                }
            }

            $tmp .= '</select>&nbsp;<label for="' . $this->get_id() . '_' . $key . '">' . $this->icons[$key] .
                    highlightfast($query, $description) . '</label>';

            $options[] = $tmp;
        }

        if (is_null($default)) {
            $defaultinfo = null;
        } else if (!empty($defaults)) {
            $defaultinfo = implode(', ', $defaults);
        } else {
            $defaultinfo = get_string('none');
        }

        $return = '<div class="form-multicheckbox">';
        $return .= '<input type="hidden" name="' . $this->get_full_name() . '[xxxxx]" value="1" />';
        if ($options) {
            $return .= '<ul>';
            foreach ($options as $option) {
                $return .= '<li>' . $option . '</li>';
            }
            $return .= '</ul>';
        }
        $return .= '</div>';

        return format_admin_setting($this, $this->visiblename, $return, $this->description, false, '', $defaultinfo, $query);
    }
}

/**
 * Multiple checkboxes for module types
 */
class oer_admin_setting_mod_types extends oer_admin_setting_config_multiselect_with_icon {
    /**
     * @param string $name
     * @param string $visiblename
     * @param string $description
     * @param array $defaultsetting
     * @global moodle_database $DB
     * @global core_renderer $OUTPUT
     */
    public function __construct($name, $visiblename, $description, $defaultsetting = null) {
        global $OUTPUT;
        $choices = array();
        $icons = array();

        foreach (get_module_types_names(true) as $type => $modname) {
            $choices[$type] = $modname->__toString();
            $icons[$type] = ' ' . $OUTPUT->pix_icon('icon', '', $type, array('class' => 'icon'));
        }

        $select = [
                PROFILE_VISIBLE_NONE => get_string('profilevisiblenone', 'admin'),
                PROFILE_VISIBLE_PRIVATE => get_string('profilevisibleprivate', 'admin'),
                PROFILE_VISIBLE_ALL => get_string('profilevisibleall', 'admin'),

        ];

        parent::__construct($name, $visiblename, $description, $defaultsetting, $choices, $icons, $select);
    }
}

/**
 * Multiple checkboxes for question types
 */
class oer_admin_setting_question_types extends oer_admin_setting_config_multiselect_with_icon {
    /**
     * @param string $name
     * @param string $visiblename
     * @param string $description
     * @param array $defaultsetting
     * @global core_renderer $OUTPUT
     */
    public function __construct($name, $visiblename, $description, $defaultsetting = null) {
        global $OUTPUT;
        $choices = array();
        $icons = array();
        $qtypes = question_bank::get_all_qtypes();
        unset($qtypes['missingtype']);
        unset($qtypes['random']);

        $qtypenames = array_map(function($qtype) {
            return $qtype->local_name();
        }, $qtypes);
        foreach (question_bank::sort_qtype_array($qtypenames) as $qname => $label) {
            $choices[$qname] = $label;
            $icons[$qname] = ' ' . $OUTPUT->pix_icon('icon', '', $qtypes[$qname]->plugin_name()) . ' ';
        }

        $select = [
                PROFILE_VISIBLE_NONE => get_string('profilevisiblenone', 'admin'),
                PROFILE_VISIBLE_PRIVATE => get_string('profilevisibleprivate', 'admin'),
                PROFILE_VISIBLE_ALL => get_string('profilevisibleall', 'admin'),

        ];

        parent::__construct($name, $visiblename, $description, $defaultsetting, $choices, $icons, $select);
    }
}
