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

namespace qtype_mlnlpessay;
use admin_setting;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

/**
 * No setting - just html
 * Note: since admin_setting is not namespaced, this can not be namespaced and put into a class
 */
class admin_setting_html extends admin_setting {

    private $categories = [];

    /**
     * not a setting, just html
     *
     * @param string $name unique ascii name, either 'mysetting' for settings that in config, or 'myplugin/mysetting' for ones in
     *     config_plugins.
     */
    public function __construct($name, $translation) {
        global $DB;
        $dbman = $DB->get_manager();
        $this->nosave = true;
        if ($dbman->table_exists('qtype_mlnlpessay_categories')) {
            $this->categories = \qtype_mlnlpessay\persistent\categories::get_records();
        }

        parent::__construct($name, $translation, '', '');
    }

    /**
     * Always returns true
     *
     * @return bool Always returns true
     */
    public function get_setting() {
        return true;
    }

    /**
     * Always returns true
     *
     * @return bool Always returns true
     */
    public function get_defaultsetting() {
        return true;
    }

    /**
     * Never write settings
     *
     * @return string Always returns an empty string
     */
    public function write_setting($data) {
        // Do not write any setting.
        return '';
    }

    /**
     * Returns an HTML string
     *
     * @return string Returns an HTML string
     */
    public function output_html($data, $query = '') {
        global $OUTPUT;

        $templatedata = [
            'categories' => $this->categories,
            'hascategories' => !empty($this->categories),
        ];

        return $OUTPUT->render_from_template('qtype_mlnlpessay/categories_table', $this->categories);
    }
}