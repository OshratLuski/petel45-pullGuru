<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * CLI script for recalculating and updating qhash field of questions.
 *
 * @package    local_petel
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->dirroot . '/question/engine/bank.php');
require_once($CFG->dirroot . '/local/petel/classes/question.php');

use local_petel\question;

global $DB;

$usage = "Recalculate and update qhash for questions.

Usage:
    # php run.php [--qids=<qids>]
    # php run.php [--help|-h]

Options:
    -h --help               Print this help.
    --qids=<ids>            Comma separated question ids to process (default: all). Use -1 to process all questions.

Examples:
    # php run.php
        Recalculate qhash for all questions.

    # php run.php --qids=-1
        Recalculate qhash for all questions.

    # php run.php --qids=12
        Recalculate qhash only for question id=12.

    # php run.php --qids=12,13,14
        Recalculate qhash for multiple questions.

    # php run.php --help
        Prints this help.
";

list($options, $unrecognised) = cli_get_params(
    [
        'qids' => '',
        'help' => false,
    ],
    [
        'h' => 'help',
    ]
);

if ($unrecognised) {
    $unrecognised = implode(PHP_EOL.'  ', $unrecognised);
    cli_error(get_string('cliunknowoption', 'core_admin', $unrecognised));
}

if (!empty($options['help'])) {
    cli_writeln($usage);
    exit(0);
}
if (empty($options['qids'])){
    cli_writeln("Run php local/petel/cli/run.php --help for more info");
    exit;
}

if (!empty($options['qids']) && $options['qids'] !== '-1') {
    $ids = array_map('intval', explode(',', $options['qids']));
    list($insql, $params) = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED);
    $questions = $DB->get_records_sql("SELECT id FROM {question} WHERE id $insql ORDER BY id", $params);
} else {
    $questions = $DB->get_records_sql("SELECT id FROM {question} ORDER BY id");
}

cli_writeln("== Recalculating and updating qhash ==");

$ok = 0;
$errors = 0;

foreach ($questions as $q) {
    try {
        $hash = question::calculate_qhash($q->id);
        if ($hash) {
            question::set_question_customfield_value($q->id, 'qhash', $hash);
            cli_writeln("[OK] Updated qhash for question id={$q->id} => {$hash}");
            $ok++;
        } else {
            cli_writeln("[SKIP] Question id={$q->id} (no hash)");
        }
    } catch (Throwable $e) {
        cli_writeln("[ERROR] updating qhash for question id={$q->id}: " . $e->getMessage());
        $errors++;
    }
}

cli_writeln("== Finished updating qhash: {$ok} updated, {$errors} errors ==");