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
 * H5P settings link.
 *
 * @package    core_form
 * @copyright  2019 Amaia Anabitarte <amaia@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$ADMIN->add('root', new admin_category('form', new lang_string('settingsform', 'local_jsoneditor')));

$settings = new admin_settingpage('formsettings', new lang_string('settingsmathlive', 'local_jsoneditor'));

$default = '';
$settings->add(new admin_setting_configtextarea('mathlive_settings', new lang_string('namesettingsmathlive', 'local_jsoneditor'),
    new lang_string('descsettingsmathlive', 'local_jsoneditor'), $default, PARAM_RAW));

$ADMIN->add('form', $settings);

global $PAGE;
$PAGE->requires->js_amd_inline('
    require(["jquery", "local_jsoneditor/usage"], function($, JSONEditor) {
    
        let divEditorId = "jsoneditor_mathlive";
              
        $("#admin-mathlive_settings .form-textarea").attr("id", divEditorId);
        $("#" + divEditorId).css({"width": "100%", "height": "400px"});
                
        let options = {
            onChangeText: function (jsonString) {
                $("#id_s__mathlive_settings").text(jsonString);
            }
        };
        
        let json = "{}";
        if($("#id_s__mathlive_settings").val() !== undefined){
            let val = $("#id_s__mathlive_settings").val().trim();  
            if (val.length !== 0) {
                json = val;
            }
        }
        
        if($("#id_s__mathlive_settings").is(":visible")){        
            new JSONEditor.init(document.getElementById(divEditorId), JSON.parse(json), options);            
            $("#id_s__mathlive_settings").hide();  
        }
    });
');
