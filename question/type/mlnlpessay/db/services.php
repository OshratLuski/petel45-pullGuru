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
 * Format moetiles web services defintions
 *
 * @package   qtype_mlnlpessay
 * @category  event
 * @copyright 2018 David Watson {@link http://evolutioncode.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(

    'qtype_mlnlpessay_get_feedback' => array(
        'classname' => 'qtype_mlnlpessay_external',
        'methodname' => 'get_feedback',
        'classpath' => 'question/type/mlnlpessay/externallib.php',
        'description' => 'get sections data',
        'ajax' => true,

    ),

    'qtype_mlnlpessay_set_override' => array(
        'classname' => 'qtype_mlnlpessay_external',
        'methodname' => 'set_override',
        'classpath' => 'question/type/mlnlpessay/externallib.php',
        'description' => 'set overridden grade',
        'write' => true,
        'ajax' => true,
    ),

    'qtype_mlnlpessay_get_categories' => array(
        'classname' => 'qtype_mlnlpessay_external',
        'methodname' => 'get_categories',
        'classpath' => 'question/type/mlnlpessay/externallib.php',
        'description' => 'get category settings',
        'ajax' => true,
    ),
    'qtype_mlnlpessay_get_topics' => array(
        'classname' => 'qtype_mlnlpessay_external',
        'methodname' => 'get_topics',
        'classpath' => 'question/type/mlnlpessay/externallib.php',
        'description' => 'get topics',
        'ajax' => true,
    ),
    'qtype_mlnlpessay_get_subtopics' => array(
        'classname' => 'qtype_mlnlpessay_external',
        'methodname' => 'get_subtopics',
        'classpath' => 'question/type/mlnlpessay/externallib.php',
        'description' => 'get topics',
        'ajax' => true,
    ),
    'qtype_mlnlpessay_get_langs' => array(
        'classname' => 'qtype_mlnlpessay_external',
        'methodname' => 'get_langs',
        'classpath' => 'question/type/mlnlpessay/externallib.php',
        'description' => 'get topics',
        'ajax' => true,
    ),

    'qtype_mlnlpessay_save_settings' => array(
        'classname' => 'qtype_mlnlpessay_external',
        'methodname' => 'save_settings',
        'classpath' => 'question/type/mlnlpessay/externallib.php',
        'description' => 'sa',
        'write' => true,
        'ajax' => true,
    ),

    'qtype_mlnlpessay_delete_setting' => array(
        'classname' => 'qtype_mlnlpessay_external',
        'methodname' => 'delete_setting',
        'classpath' => 'question/type/mlnlpessay/externallib.php',
        'description' => 'delete',
        'write' => true,
        'ajax' => true,
    ),

    'qtype_mlnlpessay_toggle_visible' => array(
        'classname' => 'qtype_mlnlpessay_external',
        'methodname' => 'toggle_visible',
        'classpath' => 'question/type/mlnlpessay/externallib.php',
        'description' => 'toggle setting visibility',
        'write' => true,
        'ajax' => true,
    ),

    'qtype_mlnlpessay_csv_upload' => array(
        'classname' => 'qtype_mlnlpessay_external',
        'methodname' => 'csv_upload',
        'classpath' => 'question/type/mlnlpessay/externallib.php',
        'description' => 'csv precheck',
        'ajax' => true,
    ),
    'qtype_mlnlpessay_csv_upload_perform' => array(
        'classname' => 'qtype_mlnlpessay_external',
        'methodname' => 'csv_upload_perform',
        'classpath' => 'question/type/mlnlpessay/externallib.php',
        'description' => 'csv import (create/update)',
        'ajax' => true,
    ),
    'qtype_mlnlpessay_csv_upload_undo' => array(
        'classname' => 'qtype_mlnlpessay_external',
        'methodname' => 'csv_upload_undo',
        'classpath' => 'question/type/mlnlpessay/externallib.php',
        'description' => 'Undo last csv import',
        'ajax' => true,
    ),
);