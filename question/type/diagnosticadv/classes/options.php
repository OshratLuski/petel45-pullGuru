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

namespace qtype_diagnosticadv;

use core\persistent;

defined('MOODLE_INTERNAL') || die();

class options extends persistent
{
    const TABLE = 'qtype_diagnosticadv_options';

    /**
     * Return the list of properties.
     *
     * @return array
     */
    protected static function define_properties()
    {
        return array(
                'questionid' => array(
                        'type' => PARAM_INT,
                ),
                'security' => array(
                        'type' => PARAM_INT,
                ),
                'required' => array(
                        'type' => PARAM_INT,
                ),
                'teacherdesc' => array(
                        'type' => PARAM_RAW,
                ),
                'hidemark' => array(
                        'type' => PARAM_INT,
                ),
                'usecase' => array(
                        'type' => PARAM_INT,
                        'default' => 0,
                ),
                'anonymous' => array(
                        'type' => PARAM_INT,
                        'default' => 0,
                ),
                'aianalytics' => array(
                        'type' => PARAM_BOOL,
                        'default' => false,
                ),
                'promt' => array(
                        'type' => PARAM_TEXT,
                        'default' => '',
                ),
                'temperature' => array(
                        'type' => PARAM_FLOAT,
                        'default' => 1,
                )
        );
    }
}


