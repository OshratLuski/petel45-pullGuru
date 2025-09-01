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
 * Contains question_list renderable used for the recommended questions page.
 *
 * @package local_question_chooser
 * @copyright 2022 Devlion.co
 * @author Devlion
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_question_chooser\output\recommendations;

/**
 * Question list renderable.
 *
 * @package local_question_chooser
 * @copyright 2020 Adrian Greeve
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question_list implements \renderable, \templatable {

    /** @var array $modules questions to display in the recommendations page. */
    protected $modules;

    /** @var string $searchquery The search query. */
    protected $searchquery;

    /**
     * Constructor method.
     *
     * @param array $modules Questions to display
     * @param string $searchquery The search query if present
     */
    public function __construct(array $modules, string $searchquery) {
        $this->modules = $modules;
        $this->searchquery = $searchquery;
    }

    /**
     * Export method to configure information into something the template can use.
     *
     * @param  \renderer_base $output Not actually used.
     * @return array Template context information.
     */
    public function export_for_template(\renderer_base $output): array {

        $info = array_map(function($module) {
            return [
                'id' => $module->id ?? '',
                'name' => $module->title,
                'componentname' => $module->componentname,
                'icon' => $module->icon,
                'recommended' => $module->recommended ?? ''
            ];
        }, $this->modules);

        return [
            'categories' => [
                [
                    'categoryname' => get_string('questions', 'core_question'),
                    'hascategorydata' => !empty($info),
                    'categorydata' => $info
                ]
            ],
            'search' => [
                'query' => $this->searchquery,
                'searchresultsnumber' => count($this->modules)
            ]
        ];
    }
}
