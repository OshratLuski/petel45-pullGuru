<?php
require_once('../../config.php');
require_login();

if (!defined('AJAX_SCRIPT')) {
    define('AJAX_SCRIPT', true);
}

global $CFG, $USER, $DB;

// Get search query and additional options from request
$query = required_param('query', PARAM_TEXT);
$course_count = optional_param('course_count', 50, PARAM_INT);
$mycoursesflag = optional_param('my_courses_flag', 'false', PARAM_ALPHA);

// Determine if current user is admin by capability
$systemctx = context_system::instance();
$isadmin = has_capability('moodle/site:config', $systemctx);

$courses = [
    'query' => $query,
    'results' => []
];

if ($isadmin) {
    // Admins: search in ALL courses
    $where = [];
    $params = [];
    if (!empty($query)) {
        $where[] = $DB->sql_like('fullname', ':query', false, false);
        $params['query'] = '%' . $DB->sql_like_escape($query) . '%';
    }
    $sql = "SELECT id, fullname, shortname, category, visible
            FROM {course} " .
            (count($where) ? "WHERE " . implode(" AND ", $where) : "") .
            " ORDER BY fullname ASC";
    $allcourses = $DB->get_records_sql($sql, $params, 0, $course_count);

    foreach ($allcourses as $course) {
        $courses['results'][] = [
            'id' => $course->id,
            'fullname' => $course->fullname,
            'shortname' => $course->shortname,
            'category' => $course->category,
            'visible' => $course->visible
        ];
    }

} else {
    // Non-admins: get only courses the user is enrolled in or has access to
    require_once($CFG->dirroot . '/course/lib.php');
    $usercourses = enrol_get_my_courses(
        ['id', 'fullname', 'shortname', 'category', 'visible'],
        'fullname ASC'
    );

    $addcapcourses = [];
    foreach ($usercourses as $id => $course) {
        $addcapcourses[$id] = $course;
    }

    $results = [];
    foreach ($addcapcourses as $course) {
        if (
            empty($query) ||
            (mb_stripos($course->fullname, $query) !== false) ||
            (mb_stripos($course->shortname, $query) !== false)
        ) {
            $results[] = [
                'id' => $course->id,
                'fullname' => $course->fullname,
                'shortname' => $course->shortname,
                'category' => $course->category,
                'visible' => $course->visible
            ];
        }
    }
    $courses['results'] = array_slice($results, 0, $course_count);
}

if (empty($courses['results'])) {
    $courses['results'][] = [
        'id' => 'na',
        'msg' => get_string('noresults', 'block_searchcourses')
    ];
}

header('Content-Type: application/json');
echo json_encode($courses);
exit;
