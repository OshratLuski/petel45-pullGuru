<?php

// Remember state of Hide/Show course side blocks, per user.

define('AJAX_SCRIPT', true);
require('../../config.php');

global $USER, $PAGE;

$blockexpanded = optional_param('blockexpanded', '', PARAM_ALPHAEXT);
$url = optional_param('url', '', PARAM_RAW);
require_login();
$PAGE->set_url('/theme/petel/blockexpanded.php');

if (get_config('theme_petel', 'blockexpanded')) {
    if ($blockexpanded === 'off') {
        set_user_preference('blockexpanded', 'off', $USER->id);
        echo "Blocks hidden for this user.";
    }
    if ($blockexpanded === 'on') {
        unset_user_preference('blockexpanded', $USER->id);
        echo "Blocks visible for this user.";
    }
}

$obj = parse_url($url);
if (strpos($obj['path'], '/mod/quiz/attempt.php') !== false || strpos($obj['path'], '/mod/quiz/review.php') !== false) {
    if ($blockexpanded === 'off') {
        set_user_preference('quizblockexpanded', 'off', $USER->id);
        echo "Quiz blocks hidden for this user.";
    }
    if ($blockexpanded === 'on') {
        unset_user_preference('quizblockexpanded', $USER->id);
        echo "Quiz blocks visible for this user.";
    }
}
