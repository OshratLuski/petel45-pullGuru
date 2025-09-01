<?php
require_once(__DIR__ . '/../../../../../config.php');

header('Content-Type: application/json');

try {
    require_login();
    require_sesskey();

    $courseid = required_param('courseid', PARAM_INT);

    $context = context_course::instance($courseid);
    $contextid = $context->id;

    require_capability('moodle/course:managefiles', $context);

    if (!isset($_FILES['file'])) {
        throw new moodle_exception('nofile');
    }

    $temp = $_FILES['file']['tmp_name'];
    $originalname = clean_param($_FILES['file']['name'], PARAM_FILE);

    $info = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($info, $temp);
    finfo_close($info);

    if ($mime !== 'text/html') {
        throw new moodle_exception('invalidfiletype');
    }

    $fs = get_file_storage();
    $filename = uniqid('embed_', true) . '.html';
    $fileinfo = [
        'contextid' => $contextid,
        'component' => 'tiny_embedhtml',
        'filearea'  => 'content',
        'itemid'    => $USER->id,
        'filepath'  => '/',
        'filename'  => $filename
    ];

    $fs->create_file_from_pathname($fileinfo, $temp);

    $pluginfileurl = moodle_url::make_pluginfile_url(
        $contextid,
        'tiny_embedhtml',
        'content',
        $USER->id,
        '/',
        $filename
    );

    echo json_encode(['url' => $pluginfileurl->out()]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
