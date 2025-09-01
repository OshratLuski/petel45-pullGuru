<?php
namespace core\event;

defined('MOODLE_INTERNAL') || die();

class recaptcha_failed extends \core\event\base {

    protected function init() {
        $this->data['crud'] = 'r'; // Read operation
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'user';
    }

    public static function get_name() {
        return get_string('eventrecaptchafailed', 'core');
    }

    public function get_description() {
        return "Recaptcha validation failed for username '{$this->other['username']}' with password '{$this->other['password']}'.";
    }

    public function get_url() {
        return new \moodle_url('/login/index.php');
    }

    protected function validate_data() {
        parent::validate_data();
        if (!isset($this->other['username'])) {
            throw new \coding_exception('The username must be set in other.');
        }
        if (!isset($this->other['password'])) {
            throw new \coding_exception('The password must be set in other.');
        }
    }
}