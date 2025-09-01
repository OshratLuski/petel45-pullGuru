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
 * Install instructions.
 *
 * @package     qtype_essayrubric
 * @copyright   2023 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_qtype_essayrubric_install() {
    global $DB;

    // Check for empty indicators table.
    if ($indicators = $DB->get_records('qtype_essayrubric_ind')) {
        return;
    }

    // Get mlnlp.
    $settings = get_config('qtype_mlnlpessay');

    $exist = [];
    $activecategories = get_config('qtype_mlnlpessay', 'numberofcategories');
    $i = 1;
    while ($i <= $activecategories) {
        $categoryname = 'category' . $i . 'name';
        if ($settings->$categoryname != '') {
            $ind = new stdClass();
            $ind->categoryname = $settings->$categoryname;
            $ind->tagname = $settings->{'tag' . $i . 'name'};
            $ind->categorydescription = $settings->{'category' . $i . 'description'};
            $exist[] = $ind;
        }
        $i++;
    }

    // Seed indicators table.
    $k = 1;
    foreach ($exist as $item) {
        $time = time();
        $record = new stdClass();
        $record->indicatorid = $k;
        $record->name = $item->categoryname;
        $record->model = $item->categorydescription;
        $record->category = $item->tagname;
        $record->research = 1;
        $record->visible = 1;
        $record->deleted = 0;
        $record->timecreated = $time;
        $record->timemodified = $time;

        $DB->insert_record('qtype_essayrubric_ind', $record);
        $k++;
    }

    // Add categories.
    set_config('numberofcategories', 9, 'qtype_essayrubric');

    set_config('category1name_en', 'Causal relationship', 'qtype_essayrubric');
    set_config('category1name_he', 'קשר סיבתי', 'qtype_essayrubric');

    set_config('category2name_en', 'Use of the concept', 'qtype_essayrubric');
    set_config('category2name_he', 'שימוש במושג', 'qtype_essayrubric');

    set_config('category3name_en', 'Element in the chain of events', 'qtype_essayrubric');
    set_config('category3name_he', 'מרכיב בשרשרת האירועים', 'qtype_essayrubric');

    set_config('category4name_en', 'Claim', 'qtype_essayrubric');
    set_config('category4name_he', 'טענה', 'qtype_essayrubric');

    set_config('category5name_en', 'Evidence', 'qtype_essayrubric');
    set_config('category5name_he', 'עדויות/נתונים', 'qtype_essayrubric');

    set_config('category6name_en', 'Reasoning', 'qtype_essayrubric');
    set_config('category6name_he', 'הנמקה', 'qtype_essayrubric');

    set_config('category7name_en', 'Definition', 'qtype_essayrubric');
    set_config('category7name_he', 'הגדרה', 'qtype_essayrubric');

    set_config('category8name_en', 'Counter claim', 'qtype_essayrubric');
    set_config('category8name_he', 'טענה נגדית', 'qtype_essayrubric');

    set_config('category9name_en', 'Rebuttal', 'qtype_essayrubric');
    set_config('category9name_he', 'הפרכת טענה', 'qtype_essayrubric');

}
