<?php

defined('MOODLE_INTERNAL') || die;

$observers = [
    ['eventname' => '\mod_quiz\event\attempt_viewed',
    'includefile'     => '/question/type/mlnlpessay/locallib.php',
    'callback' => 'lambdawarmup',
    'internal' => false
    ]
];


