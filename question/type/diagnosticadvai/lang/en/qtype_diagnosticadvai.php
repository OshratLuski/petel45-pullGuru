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
 * Strings for component 'qtype_diagnosticadvai', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package    qtype_diagnosticadvai
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['informationtext'] = 'Information text';
$string['pluginname'] = 'Diagnostic ADV AI';
$string['pluginname_help'] = 'A diagnostic adv ai is not really a question type. It simply enables text to be displayed without requiring any answers, similar to a label on the course page.

The question text is displayed both during the attempt and on the review page. Any general feedback is displayed on the review page only.';
$string['pluginnameadding'] = 'Adding a description';
$string['pluginnameediting'] = 'Editing a diagnosticadvai';
$string['pluginnamesummary'] = 'This is not actually a question. Instead it is a way to add some instructions, rubric or other content to the activity. This is similar to the way that labels can be used to add content to the course page.';
$string['privacy:metadata'] = 'The diagnosticadvai question type plugin does not store any personal data.';
$string['question_label'] = 'AI Chat';
$string['ask_your_question'] = 'Ask your question';
$string['send_message'] = 'Send message';
$string['answer_label'] = 'Answer';


$string['settingstitle'] = 'Diagnostic AI Settings';

$string['systemprompt'] = 'System prompt';
$string['systemprompt_help'] = 'Default system prompt for AI interactions.';
$string['systemprompt_default'] = 'This is the default system prompt.';

$string['prompttemaplate'] = 'Prompt template';
$string['prompttemaplate_help'] = 'Template for AI-generated prompts.';
$string['prompttemaplate_dafault'] = 'You are teacher assistant you must answer for the students with results
The result of the student {LOG}
The history  conversation with you is {CONVERSATION}
The STUDENT History {STUDENTTEXT}';

$string['disclaimer'] = 'Disclaimer';
$string['disclaimer_help'] = 'Text shown as a disclaimer to students.';
$string['disclaimer_default'] = 'AI-generated responses may not always be accurate.';

$string['temperature'] = 'Temperature';
$string['temperature_help'] = 'Controls the randomness of AI responses. A lower value makes responses more deterministic, while a higher value makes them more random.';
$string['teacherprompt'] = 'Teacher prompt';
$string['selectdiagnosticadv'] = 'Select a Diagnostic ADV question';

$string['runbutton'] = 'Start Ai Conversation';
