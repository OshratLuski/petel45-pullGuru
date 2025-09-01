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
 * popup js
 *
 * @package    mod_sharedgeogebra
 * @copyright   2019 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
    "jquery",
    "core/modal_factory",
    "core/modal_events",
], function ($, ModalFactory, ModalEvents) {
    "use strict";

    return {
        init: function (url, title) {
            $("#page-mod-sharedgeogebra-view #sharedgeogebraopen").on("click", function () {
                ModalFactory.create({
                    type: ModalFactory.types.DEFAULT,
                    title: title,
                    body: '<embed class="sharedgeogebra-embed" src=' + url + ' />',
                    large: true
                }).then(function (modal) {
                    var root = modal.getRoot();
                    root.on(ModalEvents.save, function () {
                    });
                    modal.show();
                });

            });
        }
    };
});
