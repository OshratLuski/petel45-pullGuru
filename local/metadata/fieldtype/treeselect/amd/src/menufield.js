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
 * Javascript settings handler.
 *
 * @module     metadatafieldtype_treeselect/settings
 * @package    local_metadata
 * @copyright  2019 Devlionco <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.6
 */

define([
    'jquery', 'local_jsoneditor/usage'
], function ($, JSONEditor) {

    var Selector = {
        FIELD_SRC: '#id_param1',
        JSON_FIELD1: '.form-textarea',
    };

    return {
        init: function () {

            var json = $(Selector.FIELD_SRC).val().trim();
            if (!$(Selector.FIELD_SRC).val().trim()) {
                $(Selector.FIELD_SRC).text(json);
            }

            var options = {
                onChangeText: function (jsonString) {
                    $(Selector.FIELD_SRC).text(jsonString);
                }
            };

            var divEditorId = 'jsoneditor';
            $(Selector.FIELD_SRC).hide();
            $(Selector.JSON_FIELD1).find('[data-fieldtype="textarea"]').attr('id', divEditorId);
            $(Selector.JSON_FIELD1).find('[data-fieldtype="textarea"]').attr('style', "width: 100%; height: 400px;");

            if (json.length === 0) {
                json = '{}';
            }

            new JSONEditor.init(document.getElementById(divEditorId), JSON.parse(json), options);
        }
    };
});
