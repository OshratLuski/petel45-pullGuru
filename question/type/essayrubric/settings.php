<?php
/**
 * Defines the editing form for the essay question type.
 *
 * @package    qtype
 * @subpackage essayrubric
 * @copyright  2023 Anton P. Devlion
 */

defined('MOODLE_INTERNAL') || die();

$settings = null;

if ($hassiteconfig) {
    /** @var admin_root $ADMIN */
    $ADMIN->add('qtypesettings', new admin_category('qtype_essayrubric_category', get_string('pluginname', 'qtype_essayrubric')));
    $settingspage = new admin_settingpage('essayrubricsettings', get_string('essayrubricsettings', 'qtype_essayrubric'));

    // Category types.
     if ($ADMIN->fulltree) {

         // Number of activities to customize settings
         $name = 'qtype_essayrubric/numberofcategories';
         $title = get_string('numberofcategories', 'qtype_essayrubric');
         $description = get_string('numberofcategoriesdesc', 'qtype_essayrubric');
         $default = 9;

         $choices = [];
         for ($i = 1; $i <= 60; $i++) {
             $choices[$i] = $i;
         }
         $settingspage->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

         $numberofcategories = get_config('qtype_essayrubric', 'numberofcategories');
         for ($i = 1; $i <= $numberofcategories; ++$i) {

             $indexname = "indextitle" . ($i - 1);
             $indextitle = get_string('indextitle', 'qtype_essayrubric', $i);
             $indexdescription = '';
             $setting = new admin_setting_description($indexname, $indextitle, $indexdescription);
             $settingspage->add($setting);

             // Set category name in English.
             $settingspage->add(
                 new admin_setting_configtext(
                     'qtype_essayrubric/category' . $i . 'name_en',
                     get_string('categorynameen', 'qtype_essayrubric', $i),
                     get_string('categorynameendesc', 'qtype_essayrubric'),
                     '',
                     PARAM_TEXT
                 )
             );

             // Set category name in Hebrew.
             $settingspage->add(
                 new admin_setting_configtext(
                     'qtype_essayrubric/category' . $i . 'name_he',
                     get_string('categorynamehe', 'qtype_essayrubric', $i),
                     get_string('categorynamehedesc', 'qtype_essayrubric'),
                     '',
                     PARAM_TEXT
                 )
             );
         }
     }

    $ADMIN->add('qtype_essayrubric_category', $settingspage);
    $ADMIN->add('qtype_essayrubric_category',
        new admin_externalpage(
            'qtype_essayrubric_indicators',
            get_string('indicatorssettings', 'qtype_essayrubric'),
            new moodle_url('/question/type/essayrubric/indicators.php')));
}
