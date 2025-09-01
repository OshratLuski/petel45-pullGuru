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
 * @package    local
 * @subpackage diagnostic
 * @copyright  2021 Devlion.co
 * @author  Evgeniy Voevodin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_diagnostic;

use core\persistent;

defined('MOODLE_INTERNAL') || die();

class cache extends persistent
{
    const TABLE = 'local_diagnostic_cache';

    /**
     * Return the list of properties.
     *
     * @return array
     */
    protected static function define_properties()
    {
        return array(
            'mid' => array(
                'type' => PARAM_INT,
            ),
            'rebuild' => array(
                'type' => PARAM_INT,
            ),
            'readytouse' => array(
                'type' => PARAM_INT,
            ),
            'data' => array(
                'type' => PARAM_RAW,
                'default' => null,
                'null' => NULL_ALLOWED
            ),
            'extra' => array(
                'type' => PARAM_RAW,
                'default' => null,
                'null' => NULL_ALLOWED
            ),
            'centroids' => array(
                'type' => PARAM_RAW,
                'default' => null,
                'null' => NULL_ALLOWED
            ),
            'extracentroids' => array(
                'type' => PARAM_RAW,
                'default' => null,
                'null' => NULL_ALLOWED
            ),
            'activities' => array(
                'type' => PARAM_RAW,
                'default' => null,
                'null' => NULL_ALLOWED
            ),
            'buildtime' => array(
                'type' => PARAM_TEXT,
                'default' => null,
                'null' => NULL_ALLOWED
            ),
        );
    }
}


