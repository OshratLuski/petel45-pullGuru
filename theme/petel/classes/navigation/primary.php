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

namespace theme_petel\navigation;

use renderer_base;
use moodle_url;

/**
 * Primary navigation renderable
 *
 * This file combines primary nav, custom menu, lang menu and
 * usermenu into a standardized format for the frontend
 *
 * @package     core
 * @category    navigation
 * @copyright   2021 onwards Peter Dias
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class primary extends \core\navigation\output\primary {
    private $petelpage = null;

    /**
     * primary constructor.
     * @param \moodle_page $page
     */
    public function __construct($page) {
        $this->petelpage = $page;
        parent::__construct($page);
    }

    /**
     * Combine the various menus into a standardized output.
     *
     * @param renderer_base|null $output
     * @return array
     */
    public function export_for_template(?renderer_base $output = null): array {
        if (!$output) {
            $output = $this->petelpage->get_renderer('core');
        }

        $menudata = (object) array_merge($this->get_primary_nav(), $this->get_custom_menu($output));
        $moremenu = new \core\navigation\output\more_menu($menudata, 'navbar-nav', false);
        $mobileprimarynav = array_merge($this->get_primary_nav(), $this->get_custom_menu($output));

        $languagemenu = new \core\output\language_menu($this->petelpage);

        return [
            'mobileprimarynav' => $mobileprimarynav,
            'moremenu' => $moremenu->export_for_template($output),
            'lang' => !isloggedin() || isguestuser() ? $languagemenu->export_for_template($output) : [],
            'user' => $this->get_user_menu($output),
        ];
    }

    protected function get_primary_nav($parent = null): array {
        global $CFG;
        $nodes = [];

        // Add my learning space.
        if (isloggedin()) {
            $nodes[] = [
                    'title' => get_string('mylearningspace', 'community_oer'),
                    'url' => new moodle_url('/my'),
                    'text' => get_string('mylearningspace', 'community_oer'),
                    'icon' => '',
                    'isactive' => $this->petelpage->pagetype === 'my-index',
                    'key' => 'mylearningspace',
                    'classes' => [],
            ];
        }

        if ($pluginsfunction = get_plugins_with_function('get_primarynav_output')) {
            foreach ($pluginsfunction as $plugintype => $plugins) {
                foreach ($plugins as $name => $pluginfunction) {
                    if (in_array($name, $CFG->list_navbar_plugin_output_custom)) {
                        $nodes[] = $pluginfunction();
                    }
                }
            }
        }

        return $nodes;
    }

    /**
     * Get/Generate the user menu.
     *
     * This is leveraging the data from user_get_user_navigation_info and the logic in $OUTPUT->user_menu()
     *
     * @param renderer_base $output
     * @return array
     */
    public function get_user_menu(renderer_base $output): array {
        global $USER;

        $usermenudata = parent::get_user_menu($output);

        if (isloggedin() && !\core\session\manager::is_loggedinas()) {
            $usermenudata['metadata'] = [
                    'content' => $USER->firstname . ' ' . $USER->lastname,
                    'classes' => ''
            ];
        }

        return $usermenudata;
    }
}
