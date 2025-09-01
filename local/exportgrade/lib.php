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
 * This page contains navigation hooks for local_diagnostic.
 *
 * @package local_exportgrade
 * @copyright 2021 Devlion.co
 * @author Devlion
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function local_exportgrade_init_debug()
{
    global $CFG;
    $CFG->mtrace_wrapper = 'local_exportgrade_debug';
    return get_config('local_exportgrade', 'debug');
}

function local_exportgrade_debug($string, $eol = "\n")
{
    global $CFG;
    $dir = $CFG->dataroot . "/exportgrade";
    $filename = 'trace.log';
    if (!is_dir($dir)) {
        mkdir($dir);
    }
    $myfile = fopen($dir . "/" . $filename, "a");
    fwrite($myfile, $string . $eol);
    fclose($myfile);
}


function local_exportgrade_get_config_webservices_by_instance($instancename)
{
    $webserviceinstances = get_config('local_exportgrade', 'webserviceinstances');
    if (!empty($webserviceinstances)) {
        if ($webserviceinstances) {
            for ($i = 0; $i < $webserviceinstances; $i++) {
                $temp = [];
                $temp['instancename'] = get_config('local_exportgrade', 'instancename_' . $i);
                $temp['webserviceurl'] = get_config('local_exportgrade', 'webserviceurl_' . $i);
                $temp['webservicename'] = get_config('local_exportgrade', 'webservicename_' . $i);
                $temp['webservicetoken'] = get_config('local_exportgrade', 'webservicetoken_' . $i);
                if ($temp['instancename'] == $instancename) {
                    return $temp;
                }
            }
        }
    }
    return [];
}

function local_exportgrade_get_config_webservices()
{
    $webserviceinstances = get_config('local_exportgrade', 'webserviceinstances');
    $webservice = [];
    if (!empty($webserviceinstances)) {
        if ($webserviceinstances) {
            for ($i = 0; $i < $webserviceinstances; $i++) {
                $temp = [];
                $temp['instancename'] = get_config('local_exportgrade', 'instancename_' . $i);
                $temp['webserviceurl'] = get_config('local_exportgrade', 'webserviceurl_' . $i);
                $temp['webservicename'] = get_config('local_exportgrade', 'webservicename_' . $i);
                $temp['webservicetoken'] = get_config('local_exportgrade', 'webservicetoken_' . $i);
                $webservice[] = $temp;
            }
        }
    }
    return $webservice;
}