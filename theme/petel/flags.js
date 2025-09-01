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
 * JavaScript library for dealing with the question flags.
 *
 * This script, and the YUI libraries that it needs, are inluded by
 * the $PAGE->requires->js calls in question_get_html_head_contributions in lib/questionlib.php.
 *
 * @package    moodlecore
 * @subpackage questionengine
 * @copyright  2010 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

M.theme_petel_question_flags = {
    flagattributes: null,
    actionurl: null,
    flagtext: null,
    listeners: [],

    init: function (Y, actionurl, flagattributes, flagtext) {
        M.theme_petel_question_flags.flagattributes = flagattributes;
        M.theme_petel_question_flags.actionurl = actionurl;
        M.theme_petel_question_flags.flagtext = flagtext;

        console.log('init theme_petel/flags.js');

        Y.all('div.customquestionflag').each(function (flagdiv, i) {
            var checkbox = flagdiv.one('input[type=checkbox]');
            if (!checkbox) {
                return;
            }

            var input = Y.Node.create('<input type="hidden" class="customquestionflagvalue" />');
            input.set('id', checkbox.get('id'));
            input.set('name', checkbox.get('name'));
            input.set('value', checkbox.get('checked') ? 1 : 0);

            // Create an image input to replace the img tag.
            var image = Y.Node.create('<input type="image" class="customquestionflagimage" />');
            var flagtext = Y.Node.create('<i class="fas fa-bookmark active-state"></i><i class="fal fa-bookmark regular-state"></i><i class="far fa-bookmark hover-state"></i>');
            M.theme_petel_question_flags.update_flag(input, image, flagtext, flagdiv);

            checkbox.remove();
            flagdiv.one('label').remove();
            flagdiv.append(input);
            flagdiv.append(flagtext);
            flagdiv.append(image);
        });

        Y.delegate('click', function (e) {
            var input = this.one('input.customquestionflagvalue');
            input.set('value', 1 - input.get('value'));
            M.theme_petel_question_flags.update_flag(input, this.one('input.customquestionflagimage'),
                this.one('span.customquestionflagtext'), this);
            var postdata = this.one('input.customquestionflagpostdata').get('value') +
                input.get('value');

            e.halt();
            Y.io(M.theme_petel_question_flags.actionurl, { method: 'POST', 'data': postdata });
            M.theme_petel_question_flags.fire_listeners(postdata);
        }, document.body, 'div.customquestionflag');


        Y.delegate('key', function (e) {
            var input = this.one('input.customquestionflagvalue');
            input.set('value', 1 - input.get('value'));
            M.theme_petel_question_flags.update_flag(input, this.one('input.customquestionflagimage'),
                this.one('span.customquestionflagtext'), this);
            var postdata = this.one('input.customquestionflagpostdata').get('value') +
                input.get('value');

            e.halt();
            Y.io(M.theme_petel_question_flags.actionurl, { method: 'POST', 'data': postdata });
            M.theme_petel_question_flags.fire_listeners(postdata);
        }, document.body, 'enter', 'div.customquestionflag');

        // M.mod_quiz.nav = M.mod_quiz.nav || {};
        if (typeof M.mod_quiz !== 'undefined' && typeof M.mod_quiz.nav !== 'undefined') {

            var update_flag_state = function (attemptid, questionid, newstate) {
                var Y = M.mod_quiz.nav.Y;
                var navlink = Y.one('#quiznavbutton' + questionid);
                navlink.removeClass('flagged');
                if (newstate == 1) {
                    navlink.addClass('flagged');
                    navlink.one('.accesshide .flagstate').setContent(M.util.get_string('flagged', 'question'));
                } else {
                    navlink.one('.accesshide .flagstate').setContent('');
                }
            };

            // if (M.core_question_flags) {
            M.theme_petel_question_flags.add_listener(update_flag_state);
            // }
        }

    },

    update_flag: function (input, image, flagtext, flagdiv) {
        var parent = image._node.closest('.quiz-btn');
         var tooltipTitle = flagdiv.one('.petel-custom-tooltip .flag')._node;
        var value = input.get('value');
        image.setAttrs(M.theme_petel_question_flags.flagattributes[value]);
        console.log( M.theme_petel_question_flags.flagattributes[value].text);
        tooltipTitle.innerText = M.theme_petel_question_flags.flagattributes[value].text;
        // flagtext.replaceChild(flagtext.create(M.theme_petel_question_flags.flagtext[value]),
        //     flagtext.get('firstChild'));
        // flagtext.set('title', M.theme_petel_question_flags.flagattributes[value].title);
    },

    add_listener: function (listener) {
        M.theme_petel_question_flags.listeners.push(listener);
    },

    fire_listeners: function (postdata) {
        for (var i = 0; i < M.theme_petel_question_flags.listeners.length; i++) {
            M.theme_petel_question_flags.listeners[i](
                postdata.match(/\bqubaid=(\d+)\b/)[1],
                postdata.match(/\bslot=(\d+)\b/)[1],
                postdata.match(/\bnewstate=(\d+)\b/)[1]
            );
        }
    }
};
