<?php

define('CLI_SCRIPT', true);
// Load Moodle configuration
if (file_exists(__DIR__ . '/config.php')) {
    echo "Including Moodle config.php...\n";
    require_once(__DIR__ . '/config.php');
} else {
    echo "config.php not found.\n";
    exit;
}

require_once('blocks/panopto/lib/panopto_data.php');
require_once('blocks/panopto/lib/block_panopto_bulk_lib.php');

$admin = get_admin();
\core\session\manager::set_user(get_admin());

echo "Starting folder name update...\n";

if (isset($argv[1])) {
    $courseid = $argv[1];
} else {
    echo "Course ID is missing.\n";
    exit;
}

echo "Course ID: $courseid\n";

$params = [$courseid, 1, 1];
// Call internal function to update folder name
echo "Renaming folder...\n";
echo "Course ID: $courseid\n";
panopto_rename_all_folders($params, null);

echo "Folder name update script finished.\n";
