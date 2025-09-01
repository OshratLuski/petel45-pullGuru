<?php
   namespace qtype_diagnosticadv\event;

   use \core\event\base;

   defined('MOODLE_INTERNAL') || die();

   /**
    * Event for AI Analytics export.
    *
    * This event is triggered when a new AI Analytics instance is created.
    */
   class ai_analytics_exportpdf extends base {

       protected function init() {
           $this->data['crud'] = 'r';
           $this->data['edulevel'] = self::LEVEL_TEACHING;
           $this->data['objecttable'] = 'diagnosticadv';
       }

       /**
        * Returns a description for the event.
        *
        * @return string
        */
       public static function get_name() {
           return get_string('eventaianalyticsexportpdf', 'qtype_diagnosticadv');
       }


       /**
        * Returns a localized description of what happened.
        *
        * @return string
        */
       public function get_description() {
           return "The AI Analytics export to pdf event with id '{$this->objectid}' was created.";
       }



   }