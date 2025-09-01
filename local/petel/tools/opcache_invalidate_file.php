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

define('CLI_SCRIPT', true);

require_once(__DIR__ . '/../../../config.php');
require_once("{$CFG->libdir}/clilib.php");

list($options, $unrecognized) = cli_get_params(
        array(
                'help' => false,
                'file' => false,
                'ump' => false,
        ),
        array(
                'h' => 'help',
                'f' => 'file',
                'u' => 'ump',
        )
);

if ($options['help'] || empty($options['file'])) {
    $help = <<<EOT
Invalidate opcache file

Options:
 -h, --help      Print out this help
 -f, --file      Filename, including absolute path.
 -u, --ump       Use Moodle Path, and allow file to relative.

Example:
\$sudo -u www-data /usr/bin/php opcache_invalidate_file -f=/var/www/moodle/config.php

EOT;

    echo $help;
    die;
}

if ($options['ump']) {
    $fullfilename = $CFG->dirroot . '/' . $options['file'];
} else {
    $fullfilename = $options['file'];
}

flush_file($fullfilename);

function flush_file($filename) {

    if (function_exists('opcache_invalidate')) {
        return opcache_invalidate($filename, true);
    } else {
        if (function_exists('apc_compile_file')) {
            return apc_compile_file($filename);
        }
    }
    return false;
}
