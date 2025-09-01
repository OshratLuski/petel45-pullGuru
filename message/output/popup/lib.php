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
 * Contains standard functions for message_popup.
 *
 * @package   message_popup
 * @copyright 2016 Ryan Wyllie <ryan@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Renders the popup.
 *
 * @param renderer_base $renderer
 * @return string The HTML
 */
function message_popup_render_navbar_output(\renderer_base $renderer) {
    global $USER, $CFG, $PAGE;

    // Early bail out conditions.
    if (!isloggedin() || isguestuser() || \core_user::awaiting_action()) {
        return '';
    }

    $output = '';

    // Add the notifications popover.
    $enabled = \core_message\api::is_processor_enabled("popup");
    if ($enabled) {
        $unreadcount = \message_popup\api::count_unread_popup_notifications($USER->id);
        $caneditownmessageprofile = has_capability('moodle/user:editownmessageprofile', context_system::instance());
        $preferencesurl = $caneditownmessageprofile ? new moodle_url('/message/notificationpreferences.php') : null;
        $context = [
            'userid' => $USER->id,
            'unreadcount' => $unreadcount,
            'urls' => [
                'seeall' => (new moodle_url('/message/output/popup/notifications.php'))->out(),
                'preferences' => $preferencesurl ? $preferencesurl->out() : null,
            ],
        ];
        $output .= $renderer->render_from_template('message_popup/notification_popover', $context);
    }

    // Add the messages popover.
    if (!empty($CFG->messaging)) {
        $unreadcount = \core_message\api::count_unread_conversations($USER);
        $requestcount = \core_message\api::get_received_contact_requests_count($USER->id);
        $context = [
            'userid' => $USER->id,
            'unreadcount' => $unreadcount + $requestcount
        ];

        // PTL-7462.
        $themenames = \core_component::get_plugin_list('theme');
        if (isset($themenames['petel'])) {
            list($enable, $user) = \theme_petel\funcs::custom_messages();

            if ($enable) {
                $attr = \core_message\helper::messageuser_link_params($user->id);
                $attr['id'] = $attr['id'] . '-global-' . $user->id;

                $context['data-id'] = $attr['id'];
                $context['data-conversationid'] = $attr['data-conversationid'];
                $context['data-userid'] = $attr['data-userid'];
                $context['data-url'] = $CFG->wwwroot . '/message/index.php?id=' . $user->id;
                $context['enable_custom_messages'] = true;

                $PAGE->requires->js_amd_inline('
                require(["jquery", "core/ajax", "core/notification"], function($, Ajax, Notification) {
                    $("#' . $attr['id'] . '").on("click", function(e) {

                        let obj = $("*[data-region=' . "'message-drawer'" . ']").parent();
                        
                        if(obj.hasClass("hidden")){
                            Ajax.call([{
                                methodname: "theme_petel_quiz_student_question_message",
                                args: {
                                    fromuserid: ' . $USER->id . ',
                                    touserid: ' . $user->id . ',                            
                                },
                                done: function (response) {                            
                                },
                                fail: Notification.exception
                            }]);  
                        }
                                           
                        setTimeout(function() {
                            $(".showrouteback").hide();
                            $("#conversation-actions-menu-button").hide();
                        }, 1000);                    
                    })
                });         
            ');

                $PAGE->requires->js_call_amd('core_message/message_user_button', 'send', array('#' . $attr['id']));
            }
        }

        $output .= $renderer->render_from_template('core_message/message_popover', $context);
    }

    return $output;
}
