<?php
/**
 * Defines the editing form for the essay question type.
 *
 * @package    qtype
 * @subpackage mlnlpEssay
 * @copyright  2022 Dor-Herbesman Devlion
 */

/**
 * @package mlnlpessay
 * @copyright 2021 Devlion.co
 * @author Dor Herbesman
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $menu = array(
        new lang_string('rubiccategoryheader', 'qtype_mlnlpessay'),
    );

    // Processing mode.
    $choices = [
        0 => get_string('processing_mode_random', 'qtype_mlnlpessay'),
        1 => get_string('processing_mode_local', 'qtype_mlnlpessay'),
        2 => get_string('processing_mode_labmda', 'qtype_mlnlpessay'),
    ];
    $name        = 'qtype_mlnlpessay/processing_mode';
    $title       = get_string('processing_mode', 'qtype_mlnlpessay');
    $description = get_string('processing_mode_desc', 'qtype_mlnlpessay');
    $default     = 1;
    $settings->add(new admin_setting_configselect($name, $title, $description, 0, $choices));

    $settings->add(
        new admin_setting_configtext(
            'qtype_mlnlpessay/executechunks',
            get_string('executechunks', 'qtype_mlnlpessay'),
            get_string('executechunks_desc', 'qtype_mlnlpessay'),
            '4',
            PARAM_TEXT
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'qtype_mlnlpessay/errorsrequests',
            get_string('errorsrequests', 'qtype_mlnlpessay'),
            get_string('errorsrequests_desc', 'qtype_mlnlpessay'),
            '3',
            PARAM_TEXT
        )
    );

    // AWS Labmda.
    $settings->add(
        new admin_setting_configtext(
            'qtype_mlnlpessay/aws_labmda_key',
            get_string('aws_labmda_key', 'qtype_mlnlpessay'),
            get_string('aws_labmda_key_desc', 'qtype_mlnlpessay'),
            '',
            PARAM_TEXT
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'qtype_mlnlpessay/aws_labmda_secret',
            get_string('aws_labmda_secret', 'qtype_mlnlpessay'),
            get_string('aws_labmda_secret_desc', 'qtype_mlnlpessay'),
            '',
            PARAM_TEXT
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'qtype_mlnlpessay/aws_labmda_region',
            get_string('aws_labmda_region', 'qtype_mlnlpessay'),
            get_string('aws_labmda_region_desc', 'qtype_mlnlpessay'),
            '',
            PARAM_TEXT
        )
    );

    $settings->add(
        new admin_setting_configtext(
            'qtype_mlnlpessay/aws_labmda_functionname',
            get_string('aws_labmda_functionname', 'qtype_mlnlpessay'),
            get_string('aws_labmda_functionname_desc', 'qtype_mlnlpessay'),
            '',
            PARAM_TEXT
        )
    );

    $PAGE->requires->strings_for_js(
        [
            'catid',
            'categoryname',
            'modelid',
            'modelname',
            'categorytag',
            'descriptioncategory',
            'catlang',
            'cattopic',
            'catsubtopic',
            'catstatus',
            'catactive',
            'catdisabled',
            'catactions',
            'langid',
            'langcode',
            'langname',
            'langactive',
            'langactions',
            'topicid',
            'topicname',
            'topicactive',
            'topicactions',
            'subtopicid',
            'subtopicname',
            'subtopicactive',
            'subtopicactions',
            'deleteconfirm',
            'deletewarning',
            'deletesuccess',
            'saveerror',
            'saveconfirm',
            'savesuccess',
            'savewarning',
            'csvconfirm',
            'csvwarning',
            'csvproceed',
        ], 'qtype_mlnlpessay'
    );

    $PAGE->requires->js_call_amd('qtype_mlnlpessay/catsettings', 'init', [\context_system::instance()->id]);
    //TODO improve include
    //$PAGE->requires->css('/question/type/mlnlpessay/css/tabulator.css');

    $settings->add(
        new qtype_mlnlpessay\admin_setting_html(
            'qtype_mlnlpessay/categorywrapper',
            get_string('categories', 'qtype_mlnlpessay')
        )
    );
    //TODO remove it after
    //// Number of activities to customize settings
    //$name = 'qtype_mlnlpessay/numberofcategories';
    //$title = get_string('numberofcategories', 'qtype_mlnlpessay');
    //$description = get_string('numberofcategoriesdesc', 'qtype_mlnlpessay');
    //$default = 1;
    //
    //$choices = [];
    //for ($i = 1; $i < 51; $i++) {
    //    $choices[$i] = $i;
    //}
    //$settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));
    //
    //$choices = [];
    //for ($i = 1; $i < 6; $i++) {
    //    $choices[$i] = $i;
    //}
    //
    //$name = 'qtype_mlnlpessay/categorytypes';
    //$title = get_string('categorytypes', 'qtype_mlnlpessay');
    //$description = get_string('categorytypes', 'qtype_mlnlpessay');
    //$settings->add(new admin_setting_configtextarea($name, $title, $description, '', PARAM_TEXT, 60, 4));
    //
    //
    //$numberofcategories = get_config('qtype_mlnlpessay', 'numberofcategories');
    //for ($i = 1; $i <= $numberofcategories; ++$i) {
    //    $settings->add(
    //            new admin_setting_heading(
    //                    'category' . $i,
    //                    get_string('categoryblock', 'qtype_mlnlpessay', $i),
    //                    get_string('categoryblockinfo', 'qtype_mlnlpessay', $i)
    //            )
    //    );
    //    $indexname = "indextitle" . ($i - 1);
    //    $indextitle = get_string('indextitle', 'qtype_mlnlpessay', $i);
    //    $indexdescription = '';
    //    $setting = new admin_setting_description($indexname, $indextitle, $indexdescription);
    //    $settings->add($setting);
    //    // Set category name and tag
    //    $settings->add(
    //            new admin_setting_configtext(
    //                    'qtype_mlnlpessay/category' . $i . 'name',
    //                    get_string('categoryname', 'qtype_mlnlpessay', $i),
    //                    get_string('categorynamedesc', 'qtype_mlnlpessay'),
    //                    '',
    //                    PARAM_TEXT
    //            )
    //    );
    //
    //    $models = [
    //            1 => "AlephBert",
    //            2 => "DictaBert_A",
    //            3 => "DictaBert_B"
    //    ];
    //
    //    $settings->add(
    //            new admin_setting_configselect(
    //                    'qtype_mlnlpessay/model' . $i . 'name', // Setting name
    //                    get_string('modelname', 'qtype_mlnlpessay', $i), // Displayed label
    //                    get_string('modelnamedesc', 'qtype_mlnlpessay'), // Description
    //                    1, // Default value (key of the dropdown)
    //                    $models // Dropdown options (key => value pairs)
    //            )
    //    );
    //
    //
    //    $settings->add(
    //            new admin_setting_configtext(
    //                    'qtype_mlnlpessay/tag' . $i . 'name',
    //                    get_string('categorytag', 'qtype_mlnlpessay', $i),
    //                    get_string('categorytagdesc', 'qtype_mlnlpessay'),
    //                    '',
    //                    PARAM_TEXT
    //            )
    //    );
    //
    //    $settings->add(
    //            new admin_setting_configtextarea(
    //                    'qtype_mlnlpessay/category' . $i . "description",
    //                    get_string('descriptioncategory', 'qtype_mlnlpessay', $i),
    //                    get_string('descriptioncategorydesc', 'qtype_mlnlpessay'),
    //                    '',
    //                    PARAM_TEXT
    //            )
    //    );
    //}
}