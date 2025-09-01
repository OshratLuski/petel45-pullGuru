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
 * @package report
 * @subpackage mindmaap
 * @copyright 2019 ofekpoint
 * @author Evgeniy Voevodin
 * @license http://www.gnu.org/copyleft/gpl.html@package l GNU GPL v3 or later
 */

namespace mod_mindmaap;

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

class mindmaap extends \core\persistent
{
    const TABLE = 'mindmaap';

    /**
     * Return the definition of the properties of this model.
     *
     * @return array
     */
    protected static function define_properties() {
        return array(
            'course' => array(
                'type' => PARAM_INT
            ),
            'name' => array(
                'type' => PARAM_TEXT
            ),
            'type' => array(
                'type' => PARAM_TEXT
            ),
            'intro' => array(
                'type' => PARAM_CLEANHTML
            ),
            'introformat' => array(
                'type' => PARAM_INT
            )
        );
    }
}