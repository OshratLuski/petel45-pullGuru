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
 * Code to be executed after the plugin's database scheme has been installed is defined here.
 *
 * @package     local_metadata
 * @category    upgrade
 * @copyright   2019 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Custom code to be run on installing the plugin.
 */
function xmldb_local_metadata_install() {

    \local_metadata\mcontext::module()->add_field()->textarea('teacherremarks', 'הנחיות למורים על הפעילות', [
            'description' => '<p>אנא ספרו למורים אחרים במספר מילים על הפעילות, מטרת הפעילות,&nbsp;ידע קודם הדרוש לביצוע הפעילות, תובנות מהפעלה עם תלמידים והמלצות שלכם להפעלה&nbsp;מוצלחת. המידע יופיע מתחת לכותרת הפריט במאגר המשותף</p>',
            'visible' => 2,
    ]);

    return true;
}
