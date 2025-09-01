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
 * autocomplete type form element
 *
 * Contains HTML class for a autocomplete type element
 *
 * @package   core_form
 * @copyright 2015 Damyon Wiese <damyon@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CFG;

require_once('HTML/QuickForm/element.php');
require_once('templatable_form_element.php');

/**
 * Autocomplete as you type form element
 *
 * HTML class for a mathlive type element
 *
 * @package   core_form
 * @copyright 2015 Damyon Wiese <damyon@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class MoodleQuickForm_mathlive extends HTML_QuickForm_element implements templatable{
    use templatable_form_element {
        export_for_template as export_for_template_base;
    }

    /** @var string html for help button, if empty then no help will icon will be dispalyed. */
    var $_helpbutton='';

    /**
     * constructor
     *
     * @param string $elementName Select name attribute
     * @param mixed $elementLabel Label(s) for the select
     * @param mixed $options Data to be used to populate options
     * @param mixed $attributes Either a typical HTML attribute string or an associative array. Special options
     *                          "tags", "placeholder", "ajax", "multiple", "casesensitive" are supported.
     */
    public function __construct($elementName=null, $elementLabel=null, $attributes=null) {
        global $PAGE;

        $PAGE->requires->js_call_amd('core_form/mathlive');

        $this->_type = 'mathlive';

        parent::__construct($elementName, $elementLabel, $attributes);
    }

    /**
     * Old syntax of class constructor. Deprecated in PHP7.
     *
     * @deprecated since Moodle 3.1
     */
    public function MoodleQuickForm_mathlive($elementName=null, $elementLabel=null, $attributes=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $attributes);
    }

    /**
     * Sets name of filemanager
     *
     * @param string $name name of the mathlive
     */
    function setName($name) {
        $this->updateAttributes(array('name'=>$name));
    }

    /**
     * Returns name of filemanager
     *
     * @return string
     */
    function getName() {
        return $this->getAttribute('name');
    }

    /**
     * Updates mathlive attribute value
     *
     * @param string $value value to set
     */
    function setValue($value) {

        if(is_array($value) && isset($value['text'])){
            $value = $value['text'];
        }

        $this->updateAttributes(array('value'=>$value));
    }

    /**
     * Returns mathlive attribute value
     *
     * @return string
     */
    function getValue() {
        return $this->getAttribute('value');
    }

    /**
     * get html for help button
     *
     * @return string html for help button
     */
    function getHelpButton(){
        return $this->_helpbutton;
    }

    /**
     * Returns HTML for select form element.
     *
     * @return string
     */
    function toHtml(){

        $attr = $this->_attributes;

        $html =  '<label class="accesshide" for="math_'.$this->getAttribute('id').'" >'.
            $this->getLabel() . '</label>';

        if(!isset($attr['value'])){
            $attr['value'] = '';
        }

        $html .= '<math-field virtual-keyboard-mode=manual
                        style="
                            background-color: white;
                            font-size: 16px;
                            border-radius: 8px;
                            border: 1px solid rgba(0, 0, 0, .3);
                            /*box-shadow: 0 0 8px rgba(0, 0, 0, .2);*/
                            min-width: 10rem;
                            min-height: 2rem;
                            direction: ltr;
                            max-width: max-content;
                            padding: 3px;
                        "        
                        id="math_'.$attr['id'].'" value="'.$attr['value'].'">                    
                    </math-field>';

        $html .= '<input type="hidden" '.$this->_getAttrString($this->_attributes).'>';

        $html .= '<style>.ML__keyboard {direction:ltr;}</style>';

        $json = get_config('', 'mathlive_settings');

        // Check json.
        json_decode($json);
        if(json_last_error() !== JSON_ERROR_NONE){
            $json = '{}';
        }

        $jscode = '
            document.querySelector("#math_'.$attr['id'].'").addEventListener("input", function(){
                document.querySelector("#'.$attr['id'].'").value = this.value;
            });
            
            // Set settings to mathlive.
            let interval'.$attr['id'].' = setInterval(function (){                
                let obj = document.getElementById("math_'.$attr['id'].'");
                if (typeof obj.setOptions === "function") {
                    
                    obj.setOptions('.$json.');
                    clearInterval(interval'.$attr['id'].');
                }
            },100);              
        ';

        $html .= html_writer::script($jscode, '');

        return $html;
    }

    public function export_for_template(renderer_base $output){
        $context = $this->export_for_template_base($output);
        $context['html'] = $this->toHtml();

        $json = get_config('', 'mathlive_settings');

        // Check json.
        json_decode($json);
        if(json_last_error() !== JSON_ERROR_NONE){
            $json = '{}';
        }

        $context['json'] = $json;

        return $context;
    }

    /**
     * Check that all files have the allowed type.
     *
     * @param int $value Draft item id with the uploaded files.
     * @return string|null Validation error message or null.
     */
    public function validateSubmitValue($value){
        if (empty($value)) {
            //return 'error'; //TODO
        }
    }
}

class form_mathlive implements renderable {
    public $id = 'id';
    public $name = 'name';
    public $value = '';

    public function __construct() {
        global $PAGE;

        $PAGE->requires->js_call_amd('core_form/mathlive');
    }

    public function render($id, $name, $value){
        $id = str_replace(':', '_', $id);

        $html = '<math-field virtual-keyboard-mode=manual
                    style="
                        background-color: white;
                        font-size: 16px;
                        border-radius: 8px;
                        border: 1px solid rgba(0, 0, 0, .3);
                        /* box-shadow: 0 0 8px rgba(0, 0, 0, .2); */
                        min-width: 15rem;
                        min-height: 2rem;
                        direction: ltr;
                        max-width: max-content;
                        padding: 3px;
                    "        
                    id="math_'.$id.'" value="'.$value.'">                    
                </math-field>';

        $html .= '<input type="hidden" id="'.$id.'" name="'.$name.'" value="'.$value.'">';

        $html .= '<style>.ML__keyboard {direction:ltr;}</style>';


        $json = get_config('', 'mathlive_settings');

        // Check json.
        json_decode($json);
        if(json_last_error() !== JSON_ERROR_NONE){
            $json = '{}';
        }

        $jscode = '
            document.querySelector("#math_'.$id.'").addEventListener("input", function(){
                document.querySelector("#'.$id.'").value = this.value;
            });
            
            // Set settings to mathlive.
            let interval'.$id.' = setInterval(function (){                
                let obj = document.getElementById("math_'.$id.'");
                if (typeof obj.setOptions === "function") {
                    
                    obj.setOptions('.$json.');
                    clearInterval(interval'.$id.');
                }
            },100);              
        ';

        $html .= html_writer::script($jscode, '');

        return $html;
    }

    public function static_formula($value){
        $html = '<math-field 
                    style="
                        background-color: white;
                        font-size: 24px;
                        border-radius: 8px;
                        border: 1px solid rgba(0, 0, 0, .3);
                        /* box-shadow: 0 0 8px rgba(0, 0, 0, .2); */
                        min-width: 5rem;
                        min-height: 2rem;
                        direction: ltr;
                        max-width: max-content;
                        padding: 3px;
                        opacity: 1;
                    "  
                    disabled="disabled"      
                    value="'.$value.'" >                    
                </math-field>';

        return $html;
    }

    public function select_render($name, $choices, $default = null){
        global $PAGE;

        $identificator = str_replace( ':', '_', $name);

        if($default == null || !isset($choices[$default])){
            $formula = get_string('choose');
            $value = 0;
        }else{
            $formula = $this->static_formula($choices[$default]);
            $value = $default;
        }

        $html = '
            <div class="select-wrapper selectmathlive_'.$identificator.'  d-inline-flex position-relative">            
                <input id="'.$name.'" type="hidden" value="'.$value.'" name="'.$name.'"/>
                <div class="select position-relative">
                    <div class="custom-select selectmathlive-select select-trigger d-inline-flex align-items-center h-auto py-0" role="listbox" tabindex="0" aria-expanded="false">
                        <span class="">'.$formula.'</span>
                    </div>
                    <div class="custom-options selectmathlive-options position-absolute bg-white border border-dark" style="display:none;">
        ';
        foreach($choices as $key => $value){
            $html .= '<div class="custom-option selectmathlive-option border-bottom px-2" data-key="'. $key .'" data-value="'. $value .'" role="option" 
                        aria-selected="false" tabindex="0">'.$this->static_formula($value).'</div>';
        }
        $html .= '</div></div></div>';

        $html .= '
            <style>
                #page-question-bank-previewquestion-preview .selectmathlive_'.$identificator.' .custom-options,
                #page-question-preview .selectmathlive_'.$identificator.' .custom-options,
                #page-mod-quiz-report .selectmathlive_'.$identificator.' .custom-options,
                #page-mod-quiz-attempt .selectmathlive_'.$identificator.' .custom-options {
                    z-index: 1;
                }
                #page-question-bank-previewquestion-preview .selectmathlive_'.$identificator.' .custom-select.select-trigger,
                #page-question-preview .selectmathlive_'.$identificator.' .custom-select.select-trigger,
                #page-mod-quiz-report .selectmathlive_'.$identificator.' .custom-select.select-trigger,
                #page-mod-quiz-attempt .selectmathlive_'.$identificator.' .custom-select.select-trigger {
                    cursor: pointer;
                }
                #page-question-bank-previewquestion-preview .selectmathlive_'.$identificator.' .custom-select.select-trigger span,
                #page-question-preview .selectmathlive_'.$identificator.' .custom-select.select-trigger span,
                #page-mod-quiz-report .selectmathlive_'.$identificator.' .custom-select.select-trigger span,
                #page-mod-quiz-attempt .selectmathlive_'.$identificator.' .custom-select.select-trigger span {
                    white-space: nowrap;
                }
                #page-question-bank-previewquestion-preview .selectmathlive_'.$identificator.' .custom-option,
                #page-question-preview .selectmathlive_'.$identificator.' .custom-option,
                #page-mod-quiz-report .selectmathlive_'.$identificator.' .custom-option,
                #page-mod-quiz-attempt .selectmathlive_'.$identificator.' .custom-option  {
                    position: relative;
                }
                #page-question-bank-previewquestion-preview .selectmathlive_'.$identificator.' .custom-option math-field,
                #page-question-preview .selectmathlive_'.$identificator.' .custom-option math-field,
                #page-mod-quiz-report .selectmathlive_'.$identificator.' .custom-option math-field, 
                #page-mod-quiz-report .selectmathlive_'.$identificator.' .custom-select math-field
                #page-mod-quiz-attempt .selectmathlive_'.$identificator.' .custom-option math-field, 
                #page-mod-quiz-attempt .selectmathlive_'.$identificator.' .custom-select math-field {
                    font-size: 22px !important;
                    border-radius: 0 !important;
                    border: none !important;
                }
                #page-question-bank-previewquestion-preview .selectmathlive_'.$identificator.' .custom-option:hover,
                #page-mod-quiz-report .selectmathlive_'.$identificator.' .custom-option:hover,
                #page-question-preview .selectmathlive_'.$identificator.' .custom-option:hover,
                #page-mod-quiz-attempt .selectmathlive_'.$identificator.' .custom-option:hover {
                    background-color: #016de1 !important;
                }
                #page-question-bank-previewquestion-preview .selectmathlive_'.$identificator.' .custom-option:hover math-field,
                #page-mod-quiz-report .selectmathlive_'.$identificator.' .custom-option:hover math-field,
                #page-question-preview .selectmathlive_'.$identificator.' .custom-option:hover math-field,
                #page-mod-quiz-attempt .selectmathlive_'.$identificator.' .custom-option:hover math-field {
                    background-color: #016de1 !important;
                    color: #fff;
                }
                #page-question-bank-previewquestion-preview .selectmathlive_'.$identificator.' .custom-option::after,
                #page-mod-quiz-report .selectmathlive_'.$identificator.' .custom-option::after,
                #page-question-preview .selectmathlive_'.$identificator.' .custom-option::after,
                #page-mod-quiz-attempt .selectmathlive_'.$identificator.' .custom-option::after  {
                    content: "";
                    display: block;
                    position: absolute;
                    width: 100%;
                    height: 100%;
                    top: 0;
                    left: 0;
                    z-index: 10;
                }
                #page-question-bank-previewquestion-preview .selectmathlive_'.$identificator.' .custom-option.selected,
                #page-mod-quiz-report .selectmathlive_'.$identificator.' .custom-option.selected,
                #page-question-preview .selectmathlive_'.$identificator.' .custom-option.selected,
                #page-mod-quiz-attempt .selectmathlive_'.$identificator.' .custom-option.selected {
                    border: 2px solid #eee;
                }
                html[dir="rtl"] #page-question-bank-previewquestion-preview .selectmathlive_'.$identificator.' .custom-options,
                html[dir="rtl"] #page-mod-quiz-report .selectmathlive_'.$identificator.' .custom-options,
                html[dir="rtl"] #page-question-preview .selectmathlive_'.$identificator.' .custom-options,
                html[dir="rtl"] #page-mod-quiz-attempt .selectmathlive_'.$identificator.' .custom-options {
                    left: 0;
                }
                .selectmathlive_'.$identificator.' .selectmathlive-select {
                    min-width: 10rem;
                    min-height: 38px;
                }
                .selectmathlive_'.$identificator.' .custom-option.selectmathlive-option math-field {
                    border: none !important;
                    max-width: 100% !important;
                }
     
                .selectmathlive_'.$identificator.' .custom-options.selectmathlive-options {
                    z-index: 1000 !important;
                }
                #page-question-bank-previewquestion-preview .selectmathlive_'.$identificator.' math-field::part(virtual-keyboard-toggle),
                #page-question-preview .selectmathlive_'.$identificator.' math-field::part(virtual-keyboard-toggle),
                #page-mod-quiz-report .selectmathlive_'.$identificator.' math-field::part(virtual-keyboard-toggle),
                #page-mod-quiz-attempt .selectmathlive_'.$identificator.' math-field::part(virtual-keyboard-toggle) {
                    display: none;
                }
            </style>
        ';

        $PAGE->requires->js_amd_inline('
              require(["jquery", "core_form/mathlive"], function ($, mathlive) {
                let option = $(".selectmathlive_'.$identificator.' .selectmathlive-option");

                // Hide all showed optionslists by clicking outside
                $(document).on("click", (e)=>{
                    if($(e.target).closest(".selectmathlive-select.select-trigger").length == 0 && !$(e.target).hasClass(".selectmathlive-select.select-trigger") && $(e.target).closest(".selectmathlive-options").length != 1) {
                        $(".selectmathlive_'.$identificator.' .custom-options").hide();
                    }
                })
                // Show options list bi click on btn
                $(".selectmathlive_'.$identificator.' .select-trigger").on("click", function (e) {
                    let maxwidth = 0;
                    let target = $(e.target).hasClass("select-trigger") ? $(e.target) : $(e.target).closest(".select-trigger");
                    let parent = target.closest(".select-wrapper");
                    let btnsParent = parent.closest(".qtext");
                    let notCurrentBtns = $(document).find(".select-trigger").not(target);
                    target.attr("aria-expanded", function (i, attr) {return attr == "true" ? "false" : "true"});

                    maxwidth = parent.find(".selectmathlive-options").width();
                    $(this).css("min-width", maxwidth + "px");

                    notCurrentBtns.each((index) => {
                        notCurrentBtns.eq(index).closest(".select").find(".selectmathlive-options").attr("aria-selected", "false");
                        notCurrentBtns.eq(index).closest(".select").find(".selectmathlive-options").hide();
                    });
                    parent.find(".custom-options").toggle();
                });
    
                option.on("click", function (e) {
                    let parent = $(e.target).closest(".selectmathlive_'.$identificator.'.select-wrapper");
                    let hiddenInput = parent.find("input");
                    let options = parent.find(".selectmathlive-options");
                    let optionValue = $(this).data("value");
                    let optionKey = $(this).data("key");
                    let select = parent.find(".selectmathlive-select.select-trigger");
    
                    hiddenInput.val(optionKey);
    
                    options.find(".custom-option").each((index) => {
                        options.find(".selectmathlive-option").eq(index).removeClass("selected");
                        options.find(".selectmathlive-option").eq(index).attr("aria-selected", "false");
                    });
    
                    $(this).addClass("selected");
                    $(this).attr("aria-selected", "true");
                    select.find("span").hide();
                    $(this).closest(".select").find(".select-trigger").find("math-field").remove();
    
                    let cloned = `<math-field virtual-keyboard-mode=auto
                        style="
                            background-color: white;
                            border: none;
                            font-size: 22px;
                            min-width: 5rem;
                            min-height: 1rem;
                            direction: ltr;
                            user-select: none;
                        "
                        value="${optionValue}"></math-field>`;
    
                    $(cloned).appendTo(select);

                    if(select.find(".clickingTargetBlock").length === 0){
                        let clickingTargetBlock = `
                            <div class="position-absolute w-100 h-100 clickingTargetBlock"  style="z-index: 100;">
                            </div>
                        `;
                        $(clickingTargetBlock).appendTo(select);
                    }

                    select.find("math-field").prop( "disabled", true );
                    $(this).closest(".select").find(".selectmathlive-options").hide();
                });
            });
        ');

        return $html;
    }
}