/* eslint-disable no-undef */
/* eslint-disable no-implicit-globals */
/* eslint-disable no-unused-vars */
/* eslint-disable max-len */
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
 * Javascript main event handler.
 *
 * @module     core_form/treeselectmain
 * @package    local_petel
 * @copyright  2019 Devlionco <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.6
 */

define([
    'jquery',
    'core/str',
    'core/templates',
    'core_form/treeselect'
], function ($, Str, Templates) {

    return {
        init: function (inputname, menu, defaults, disabled) {

            $(document).ready(function ($) {

                // Set default parameters.
                $("input[name='"+inputname+"']").val(defaults);

                let nodes = null;
                if(menu.length === 0){
                    nodes = [];
                    defaults = [];
                }else{
                    nodes = [menu];
                }

                var rootNode = nodes;

                let params = {
                    checkWithParent: true,
                    titleWithParent: true,
                    notViewClickParentTitle: true
                };

                if(disabled === true){
                    params.disabled = true;
                }

                $('div.treeSelector_'+inputname).treeSelector(rootNode, defaults, function (e, values) {

                    // Set selected in hidden input.
                    $("input[name='"+inputname+"']").val(values);

                }, params)
            })
        }
    };
});