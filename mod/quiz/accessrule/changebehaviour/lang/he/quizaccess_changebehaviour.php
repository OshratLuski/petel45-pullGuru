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
 * Strings for the quizaccess_changebehaviour plugin.
 *
 * @package   quizaccess_changebehaviour
 * @copyright 2016 Daniel Thies <dthies@ccal.edu>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'הגשה באיחור';
$string['behaviourtime_abs'] = 'מתן הארכת זמן למועד מסוים';
$string['behaviourtime_abs_help'] = 'מאפשר לתת הארכת זמן לפי תאריך או באופן יחסי, וגם להגדיר התנהגות חישוב ניקוד של שאלה, כולל קנס.';
$string['behaviourtime_relative'] = 'או: מתן הארכת זמן יחסית';
$string['behaviourtime_relative_help'] = 'Set a new close time or an amount of time to extend the quiz and a new behaviour to apply to attempts started after the original close. This is an opportunity to change the conditions for students who wait too late to attempt the quiz.';
$string['changebehaviournotice'] = 'זמן סיום המשימה המקורי {$a->time} עבר, וכעת ניתן להגיש באיחור. שימו לב! יתכנו שינויים בניקוד להגשה מאוחרת.';
$string['newbehaviour'] = 'הגשה באחור - מנגנון התנהגות שאלות (וניקוד/קנסות)';
$string['penalty'] = 'הגשה באחור - אחוז קנס';
$string['penalty_help'] = 'אחוז קנס להגשה באיחור, אפס = ללא קנס';
$string['privacy:metadata'] = 'The Change question behaviour access rule plugin does not store any personal data.';
