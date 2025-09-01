<?php

use mod_quiz\quiz_settings;

require_once (__DIR__ . '/../../config.php');
require_once ($CFG->dirroot . '/local/community/plugins/sharewith/classes/duplicate.php');

$questionid = optional_param('questionid', 0, PARAM_INT);
$quizid = optional_param('quizid', 0, PARAM_INT);
$redirecturl = optional_param('redirecturl', '', PARAM_URL);

$PAGE->set_url('/theme/petel/fix_question.php');
$PAGE->set_context(context_system::instance());

// Get quiz object
$quizobj = quiz_settings::create($quizid);
$quizobj->preload_questions();
$quizobj->load_questions();

$contextmodule = context_module::instance($quizobj->get_cm()->id);
require_capability('mod/quiz:manage', $contextmodule);

$questionobj = $quizobj->get_question($questionid);

$newquestionid = duplicate::fix_question_default_category($questionobj, $quizobj);

redirect($redirecturl);
