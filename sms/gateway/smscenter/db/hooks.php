<?php
defined('MOODLE_INTERNAL') || die();

$callbacks = [
    [
        'hook' => \core_sms\hook\after_sms_gateway_form_hook::class,
        'callback' => \smsgateway_smscenter\hook_listener::class . '::set_form_definition_for_smscenter_sms_gateway',
    ],
];