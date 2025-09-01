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
 * Overridden fontawesome icons.
 *
 * @package     theme_petel
 * @copyright   2019 Moodle
 * @author      Bas Brands <bas@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_petel\output;

defined('MOODLE_INTERNAL') || die();

/**
 * Class overriding some of the Moodle default FontAwesome icons.
 *
 * @package    theme_petel
 * @copyright  2019 Moodle
 * @author     Bas Brands <bas@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class icon_system_fontawesome extends \core\output\icon_system_fontawesome {

    /**
     * Overridable function to get a mapping of all icons.
     * Default is to do no mapping.
     */
    public function get_icon_name_map() {
        $iconmap = parent::get_icon_name_map();

        //unset($iconmap['core:i/grade_correct']);
        //unset($iconmap['core:i/grade_incorrect']);
        //unset($iconmap['core:i/grade_partiallycorrect']);

        //$iconmap['core:a/add_file'] = 'fa-file';
        //$iconmap['core:b/document-new'] = 'fa-file';
        //$iconmap['core:e/new_document'] = 'fa-file';
        //$iconmap['theme:fp/add_file'] = 'fa-file';
        //$iconmap['theme:fp/create_folder'] = 'fa-folder';
        //$iconmap['core:a/create_folder'] = 'fa-folder';
        //$iconmap['core:i/competencies'] = 'fa-check-square';
        //$iconmap['core:e/share_square'] = 'fa-share-square';
        //$iconmap['core:i/badge'] = 'fa-trophy';
        //$iconmap['core:t/grades'] = 'fa-shield-check';

        return $iconmap;
    }

    public function render_pix_icon(\renderer_base $output, \pix_icon $icon) {
        $render = parent::render_pix_icon($output, $icon);

        if ($icon->pix == 'i/grade_partiallycorrect') {
            $changedicon = new \pix_icon('grade_partiallycorrect', '', 'theme_petel',
                    array('class' => 'iconsmall', 'title' => ''));

            $render = self::render_pix_icon($output, $changedicon);
        }
        if ($icon->pix == 'i/grade_correct') {
            $changedicon = new \pix_icon('grade_correct', '', 'theme_petel',
                    array('class' => 'iconsmall', 'title' => ''));

            $render = self::render_pix_icon($output, $changedicon);
        }
        if ($icon->pix == 'i/grade_incorrect') {
            $changedicon = new \pix_icon('grade_incorrect', '', 'theme_petel',
                    array('class' => 'iconsmall', 'title' => ''));

            $render = self::render_pix_icon($output, $changedicon);
        }

        return $render;
    }
}
