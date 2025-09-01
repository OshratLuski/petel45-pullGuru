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

namespace local_petel;

use core\hook\after_config;
use core\hook\output\before_footer_html_generation;

/**
 * Callbacks for hooks.
 *
 * @package    local_petel
 * @copyright  2024 Andrew Lyons <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {
    /**
     * Listener for the after_config hook.
     *
     * @param after_config $hook
     */
    public static function after_config(\core\hook\after_config $hook): void {
        global $USER, $CFG;

        if (during_initial_install()) {
            return;
        }

        // Workaround for DEMO instance that have special
        // generic login users for demo courses
        if (isset($CFG->instancename) && $CFG->instancename === 'demo') {
            return;
        }

        $timerequred = \local_petel\funcs::get_session_timeout($USER->id);
        if (!\core\session\manager::is_loggedinas() &&
                isset($USER->lastaccess) && isset($timerequred)
                && (time() - $USER->lastaccess) > $timerequred) {
            \core\session\manager::terminate_current();
        }
    }


    public static function before_footer_html_generation(before_footer_html_generation $hook): void {
        global $PAGE, $USER, $CFG;

        if (during_initial_install()) {
            return;
        }

        if ($PAGE->pagetype !== 'mod-hvp-view') {
            return;
        }

        $sesskey = sesskey();
        $logurl = $CFG->wwwroot . '/local/petel/timeonpage.php';

        // Count the time a user was looking at the page (page was in focus).
        $PAGE->requires->js_amd_inline("
        require(['jquery'], function($) {

            $(document).ready(function() {
                var start = new Date();
                // Save user's focus time on page.
                // end - start = all the time the page was visible, but not necessarily focused.
                /*
                // This method pause user navigation, it is sync.
                $(window).on('beforeunload', function(e) {
                    console.log('Total focus time on page = ' + window.timercounter + ' sec.');
                    var end = new Date();
                    $.ajax({
                        type: 'POST',
                        url: '{$logurl}',
                        data: {
                            'timespent': window.timercounter,
                            'contextid': {$PAGE->context->id},
                            'userid': {$USER->id},
                            'sesskey': '{$sesskey}'},
                        //async: false
                    })
                });
                */

                // https://usefulangle.com/post/62/javascript-send-data-to-server-on-page-exit-reload-redirect
                // This method does not pause user navigation, it is async.
                $(window).on('beforeunload', function(e) {
                    var fd = new FormData();
	                fd.append('timespent', window.timercounter);
	                fd.append('contextid', {$PAGE->context->id});
	                fd.append('userid', {$USER->id});
	                fd.append('sesskey', '{$sesskey}');

	                navigator.sendBeacon('$logurl', fd);
                });

                // Let's start counting user focus on page, immediately after it fully loads
                startTimer();
            });
        
            window.timercounter = 0;
            window.timerstate = 0;
            var myInterval;
            // Active
            window.addEventListener('focus', startTimer);
            
            /*
            window.addEventListener('focus', function(){
              if (document.activeElement instanceof HTMLIFrameElement) {
                console.log('Wow! Iframe Click!');
                if (window.timerstate == 0) {
                    console.log('IFRAME: start timer');
                    startTimer();
                } else {
                    console.log('IFRAME: stop timer');
                    stopTimer();
                }
              } else {
                  startTimer();
              }
            });
            */

            // Inactive
            window.addEventListener('blur', stopTimer);
            
            function timerHandler() {
                window.timercounter++;
                //document.getElementById('seconds').innerHTML = timercounter;
                //console.log('timer ON ' + window.timercounter + ' sec.');
            }
            
            // Start timer
            function startTimer() {
                window.timerstate = 1;
                console.log('got focus (timer ON) @ ' + window.timercounter + ' sec.');
                window.clearInterval(myInterval);
                myInterval = window.setInterval(timerHandler, 1000);
            }
            
            // Stop timer
            function stopTimer() {
                if (document.activeElement instanceof HTMLIFrameElement) {
                    console.log('Wow! Iframe Click!');
                } else {
                    window.timerstate = 0;
                    console.log('lost focus (time OFF) @ ' + window.timercounter + ' sec.');
                    window.clearInterval(myInterval);
                }
            }
        });
    ");
    }
}
