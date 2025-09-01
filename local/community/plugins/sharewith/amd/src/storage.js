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
 * Javascript controller for the "Actions" panel at the bottom of the page.
 *
 * @module     community_sharewith/storage
 * @package
 * @copyright  2020 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.6
 */

define(['jquery'], function($) {

    return {
        cmid: null,
        sectionid: null,
        activityChain: [],
        amit: false,
        sequence: false,
        copysub: false,
        hassubsections: false,
        cmlink: false,

        /**
         * Init state for object.
         *
         * @method initState
         */
        initState: function() {
            this.cmid = null;
            this.sectionid = null;
            this.activityChain = [];
            this.amit = false;
            this.sequence = false;
            this.copysub = false;
            this.cmlink = false;
        },

        /**
         * Get current course on which the system is located.
         *
         * @method getCurrentCourse
         * @param {string} handler name of the handler.
         * @return {int} id number of the course.
         */
        getCurrentCourse: function() {
            var str = $('body').attr('class'),
                result = str.match(/course-\d+/gi)[0].replace(/\D+/, '');
            return result;
        },
    };
});
