<?php
namespace smsgateway_smscenter;

use core_sms\hook\after_sms_gateway_form_hook;

class hook_listener {
    /**
     * @throws \coding_exception
     */
    public static function set_form_definition_for_smscenter_sms_gateway(after_sms_gateway_form_hook $hook): void {
        if ($hook->plugin !== 'smsgateway_smscenter') {
            return;
        }

        $mform = $hook->mform;

        // username
        $mform->addElement('text', 'username', get_string('username', 'smsgateway_smscenter'));
        $mform->setType('username', PARAM_TEXT);
        $mform->addRule('username', get_string('required'), 'required', null, 'client');

        // password
        $mform->addElement('passwordunmask', 'password', get_string('password', 'smsgateway_smscenter'));
        $mform->setType('password', PARAM_TEXT);
        $mform->addRule('password', get_string('required'), 'required', null, 'client');

        // sendername
        $mform->addElement('text', 'sendername', get_string('sendername', 'smsgateway_smscenter'));
        $mform->setType('sendername', PARAM_TEXT);
        $mform->setDefault('sendername', 'Petel');

        // URL
        $mform->addElement('text', 'url', get_string('url', 'smsgateway_smscenter'));
        $mform->setType('url', PARAM_URL);
        $mform->setDefault('url', 'https://www.smscenter.co.il/web/webservices/sendmessage.asmx/SendMessage');

        $additional_fields = [
            'cctomail' => 'CCToEmail',
            'smsoperation' => 'SMSOperation',
            'deliveryreporturl' => 'DeliveryReportURL',
            'deliveryreportmask' => 'DeliveryReportMask',
            'deliverydelayinminutes' => 'DeliveryDelayInMinutes',
            'expirationdelayinminutes' => 'ExpirationDelayInMinutes',
        ];

        foreach ($additional_fields as $field => $label) {
            $mform->addElement('text', $field, get_string($label, 'smsgateway_smscenter'));
            $mform->setType($field, PARAM_INT);
            $mform->setDefault($field, 0);
            $mform->addHelpButton($field, 'additional_desc', 'smsgateway_smscenter');
        }
    }
}