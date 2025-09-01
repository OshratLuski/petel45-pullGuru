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
 * Functions and classes for comments management
 *
 * @package   local_redmine
 * @copyright 2010 Dongsheng Cai {@link http://dongsheng.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/redmine/vendor/autoload.php');

/**
 * comment_manager is helper class to manage moodle comments in admin page (Reports->Comments)
 *
 * @package   core
 * @copyright 2010 Dongsheng Cai {@link http://dongsheng.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_redmine {

    private static $statuses;
    /**
     * Constructs the comment_manage object
     */
    public function __construct() {}

    public static function userQuery(){
        global $USER, $DB;

        $count = $DB->count_records('user', ['email' => $USER->email]);
        if ($count == 1) {
            return '*דואל*: '.$USER->email;
        } else {
            return (new moodle_url('/user/profile.php', ['id' => $USER->id]))->out(false);
        }
    }

    public static function stringBetweenTwoWords($str, $startingword, $endingword){
        $subtring_start = mb_strpos($str, $startingword);
        $subtring_start += mb_strlen($startingword);
        $size = mb_strpos($str, $endingword, $subtring_start) - $subtring_start;

        return trim(mb_substr($str, $subtring_start, $size));
    }

    public static function getStatusName($issueid){
        global $CFG;

        if(empty(self::$statuses)) {
            $client = new \Redmine\Client\NativeCurlClient(
                    get_config('local_redmine', 'redmineurl'),
                    get_config('local_redmine', 'redmineusername'),
                    get_config('local_redmine', 'redminepassword'));

            if (!empty($CFG->proxyhost)) {
                $client->setCurlOption(CURLOPT_PROXY, $CFG->proxyhost . ':' . $CFG->proxyport);
            }

            $statuses = ['opened' => [], 'closed' => []];
            $data = $client->getApi('issue_status')->all();
            foreach ($data['issue_statuses'] as $item) {
                if (isset($item['is_closed']) && $item['is_closed'] == 1) {
                    $statuses['closed'][] = $item['id'];
                } else {
                    $statuses['opened'][] = $item['id'];
                }
            }

            self::$statuses = $statuses;
        }

        if(in_array($issueid, self::$statuses['closed'])){
            $statusname = get_string('statusclosed', 'local_redmine');
        }

        if(in_array($issueid, self::$statuses['opened'])){
            switch ($issueid) {
                case 1:
                    $statusname = get_string('statusnew', 'local_redmine');
                    break;
                case 21:
                    $statusname = get_string('statusyouranswer', 'local_redmine');
                    break;
                default:
                    $statusname = get_string('statustreatment', 'local_redmine');
            }
        }

        return $statusname;
    }

    protected static function prepareData($data, $filter, $sortcol, $sortdir) {
        global $USER, $DB;

        $result = [];
        $result['firstname'] = $USER->firstname;
        $result['lastname'] = $USER->lastname;

        // Period selector.
        $period = [
                ['name' => get_string('periodmonth', 'local_redmine'), 'value' => 2, 'active' => false],
                ['name' => get_string('periodhalfyear', 'local_redmine'), 'value' => 3, 'active' => false],
                ['name' => get_string('periodlastyear', 'local_redmine'), 'value' => 4, 'active' => false],
                ['name' => get_string('all'), 'value' => 1, 'active' => false]
        ];

        if(!empty($filter) && is_number($filter)){
            foreach($period as $key => $item){
                if($filter == $item['value']){
                    $period[$key]['active'] = true;
                }
            }
        }

        $result['period'] = $period;

        // No data.
        if(isset($data['total_count']) && $data['total_count'] == 0){
            $result['table_empty'] = true;
            return $result;
        }

        // Errors.
        if(isset($data['errors'])){
            $result['table_empty'] = true;
            return $result;
        }

        $result['table_empty'] = false;
        $result['total_count'] = $data['total_count'];

        // Columns sorting.
        if(!empty($sortcol) && in_array($sortdir, ['asc', 'desc'])){
            $result['columns'][$sortcol] = ['sorting' => $sortdir];
        }

        // Issues.
        foreach($data['issues'] as $issue){
            $row = [];

            $row['id'] = isset($issue['id']) ? $issue['id'] : '';

            //$row['type'] = self::stringBetweenTwoWords($issue['description'], 'סוג*:', '*');
            $row['type'] = isset($issue['tracker']['name']) ? $issue['tracker']['name'] :
                    self::stringBetweenTwoWords($issue['description'], 'סוג*:', '*');

            $row['status'] = self::getStatusName($issue['status']['id']);
            $row['subject'] = isset($issue['subject']) ? $issue['subject'] : '';

            if(isset($issue['created_on'])){
                $datetime = str_replace(['T', 'Z'], [' ', ''], $issue['created_on']);
                $dtime = DateTime::createFromFormat('Y-m-d H:i:s', $datetime);
                $createdtimestamp = $dtime->getTimestamp();

                $row['created_on'] = date('d.m.Y', $createdtimestamp);
            }else{
                $row['created_on'] = '';
            }

            if(isset($issue['updated_on'])){
                $datetime = str_replace(['T', 'Z'], [' ', ''], $issue['updated_on']);
                $dtime = DateTime::createFromFormat('Y-m-d H:i:s', $datetime);
                $updatetimestamp = $dtime->getTimestamp();

                $row['updated_on'] = date('d.m.Y', $updatetimestamp);
            }else{
                $row['updated_on'] = '';
            }

            // Alert note.
            $row['alert_note_enable'] = false;
            if($issue['status']['id'] == 21) {

                $query = "
                    SELECT *
                    FROM {local_redmine_chat}
                    WHERE issueid = ?
                    ORDER BY timecreated DESC
                    LIMIT 1                
                ";
                $lastmessage = $DB->get_record_sql($query, [$issue['id']]);

                if(isset($lastmessage->timecreated) && !empty($lastmessage->timecreated)) {
                    $lasttimestamp = $lastmessage->timecreated;
                }else{
                    $lasttimestamp = $createdtimestamp;
                }

                $daysbefore = ceil((time() - $lasttimestamp) / (60 * 60 * 24));
                $delta = 10 - $daysbefore;
                if ($delta > 0) {
                    $row['alert_note_enable'] = true;
                    $row['alert_note'] = get_string('attentioninfotext', 'local_redmine', $delta);
                }
            }

            if(isset($issue['closed_on'])){
                $datetime = str_replace(['T', 'Z'], [' ', ''], $issue['closed_on']);
                $dtime = DateTime::createFromFormat('Y-m-d H:i:s', $datetime);
                $timestamp = $dtime->getTimestamp();

                $row['closed_on'] = date('d-m-Y', $timestamp);
            }else{
                $row['closed_on'] = '';
            }

            $result['table'][] = $row;
        }

        // Pagination.
        $totalpages = ceil($data['total_count'] / $data['limit']);
        $currentpage = ($data['offset'] + $data['limit']) / $data['limit'];

        $result['pagination']['limit'] = $data['limit'];
        $result['pagination']['total_count'] = $data['total_count'];
        $result['pagination']['total_pages'] = $totalpages;
        $result['pagination']['current_page'] = $currentpage;

        $viewpages = [];
        $flag = true;
        $count = $shift = 0;
        $viewpages[$currentpage] = ['page_num' => $currentpage, 'active' => true];
        while ($flag) {
            $shift++;

            // Check left side.
            if($currentpage - $shift > 0){
                $viewpages[$currentpage - $shift] = ['page_num' => $currentpage - $shift, 'active' => false];
                $count++;
            }

            // Check right side.
            if($currentpage + $shift > 0 && $currentpage + $shift <= $totalpages){
                $viewpages[$currentpage + $shift] = ['page_num' => $currentpage + $shift, 'active' => false];
                $count++;
            }

            if($count >= 16 || count($viewpages) >= $totalpages){
                $flag = false;
            }
        }

        ksort($viewpages);
        $viewpages = array_values($viewpages);

        $result['pagination_enable'] = (count($viewpages) > 1) ? true : false;
        $result['pagination']['view_pages'] = $viewpages;
        $result['pagination']['next_button'] = $currentpage < $totalpages ? true : false;
        $result['pagination']['prev_button'] = $currentpage > 1 ? true : false;

        return $result;
    }

    public static function buildTableIssuesForUser($status, $limit, $filterfield, $params) {
        global $CFG, $USER, $DB;

        $search = $params['search'];
        $filter = $params['filter'];
        $sortcol = $params['sort_col'];
        $sortdir = $params['sort_dir'];
        $page = $params['page'];

        $client = new \Redmine\Client\NativeCurlClient(
                get_config('local_redmine', 'redmineurl'),
                get_config('local_redmine', 'redmineusername'),
                get_config('local_redmine', 'redminepassword'));

        if (!empty($CFG->proxyhost)) {
            $client->setCurlOption(CURLOPT_PROXY, $CFG->proxyhost.':'.$CFG->proxyport);
        }

        $offset = ($page - 1) * $limit;

        // Get issue id by user and search.
        $searchdata = $client->getApi('search')->search(self::userQuery(), ['offset' => 0, 'limit' => 100]);

        // Search.
        $ids = [];
        foreach($searchdata['results'] as $issue){

            // Check instance.
            if(isset($CFG->instancename) && !empty($CFG->instancename)) {
                if (strpos($issue['description'], $CFG->instancename) == false) {
                    continue;
                }
            }

            $search = trim($search);
            if(!empty($search)){

                // Find in issue id.
                if ($issue['id'] == $search) {
                    $ids[] = $issue['id'];
                }

                // Find in title.
                if (strpos($issue['title'], $search) !== false) {
                    $ids[] = $issue['id'];
                }

                // Find in description.
                if (strpos($issue['description'], $search) !== false) {
                    $ids[] = $issue['id'];
                }

                // Find in chat.
                $sql = "
                    SELECT * 
                    FROM {local_redmine_chat}
                    WHERE message LIKE('%".$search."%') AND issueid = ?                
                ";

                if($DB->get_records_sql($sql, [$issue['id']])){
                    $ids[] = $issue['id'];
                }
            }else{
                $ids[] = $issue['id'];
            }
        }

        // If empty issue ids.
        if(empty($ids)){
            $res = [
                    'issues' => [],
                    'total_count' => 0,
                    'offset' => $offset,
                    'limit' => $limit
            ];

            return json_encode(self::prepareData($res, $filter, $sortcol, $sortdir));
        }

        $params = [
                'issue_id' => implode(',', array_unique($ids)),
                'status_id' => $status,
                'offset' => $offset,
                'limit' => $limit,
        ];

        // Period.
        switch ($filter) {
            case 2:
                $datestart = date("Y-m-").'01';
                $dateend = date("Y-m-d", strtotime ("+1 day"));
                $params[$filterfield] = '><'.$datestart.'|'.$dateend;
                break;
            case 3:
                $datestart = date("Y-m-d", strtotime ("-6 month"));
                $dateend = date("Y-m-d", strtotime ("+1 day"));
                $params[$filterfield] = '><'.$datestart.'|'.$dateend;
                break;
            case 4:
                $datestart = (date("Y") - 1).'-01-01';
                $dateend = (date("Y") - 1).'-12-31';
                $params[$filterfield] = '><'.$datestart.'|'.$dateend;
                break;
        }

        // Sorting columns.
        if(!empty($sortcol) && in_array($sortdir, ['asc', 'desc'])){
            $params['sort'] = $sortcol.':'.$sortdir;
        }

        $data = $client->getApi('issue')->all($params);

        return json_encode(self::prepareData($data, $filter, $sortcol, $sortdir));
    }
}
