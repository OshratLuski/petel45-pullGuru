<?php

namespace filter_forum;

defined('MOODLE_INTERNAL') || die();

/**
 * Change all instances of forum in the text
 *
 * @uses $CFG,$COURSE;
 * Apply the filter to the text
 *
 * @see  filter_manager::apply_filter_chain()
 * @param string $text to be processed by the text
 * @param array $options filter options
 * @return string text after processing
 */

if (class_exists('\core_filters\text_filter')) {
    class_alias('\core_filters\text_filter', 'filter_forum_base_text_filter');
} else {
    class_alias('\moodle_text_filter', 'filter_forum_base_text_filter');
}

class text_filter extends \filter_forum_base_text_filter {

    private $arrContextOptions = array(
        "ssl"=>array(
            "verify_peer"=>false,
            "verify_peer_name"=>false,
        ),
    );

    public function filter($text, array $options = array()) {

        global $CFG, $COURSE, $DB, $OUTPUT, $USER;
        $CFG->cachetext = false; // very cpu intensive !!! (hanna 23-4-13)

        // Do a quick check to avoid unnecessary work :  - Is there instance ? - Are we on the first page ?
        if (($COURSE->id == 1) || (strpos($text, '[[forum(') === false)) {
            return $text;
        }
        // There is job to do.... so let's do it !
        $pattern = '/\[\[forum\(([0-9]+),([0-9]+),([0-9]+),([0-9]+)\)\]\]/';
        $moduleid = $DB->get_record('modules', array('name' => 'forum'));

        // If there is an instance again...
        while (preg_match($pattern, $text, $regs)) {

            // For each instance
            if ($regs[4] > 0) {
                $cmid = $regs[1];
                $groupid = $regs[2];
                $groupingid = $regs[3];
                $nbpost = $regs[4];
                $forum = '';
                if ($groupid > 0) {
                    $group_list = $groupid;
                } else {
                    $group_list = '-1';
                }
                $nbcaract = 100;

                if ($groupingid != 0 AND $groupings_groupsids = $DB->get_records('groupings_groups', array('groupingid' => $groupingid))) {
                    $group_list = '';
                    foreach ($groupings_groupsids as $ggroup) {
                        $group_list .= $ggroup->groupid . ",";
                    }
                    //trim($group_list,",");
                    $group_list = mb_substr($group_list, 0, -1);
                }

                // Get the forum ID
                $data = array();
                if ($data = $DB->get_record('course_modules', array('id' => $cmid, 'module' => $moduleid->id))) {
                    $course = $DB->get_record('course', ['id' => $data->course]);
                    $foruminstance = $DB->get_record('forum', ['id' => $data->instance]);
                    $forumid = $data->instance;

                    // Get the discussions
                    $discussions = array();
                    $i = 0;
                    $time = time();

                    // Get last "x" discussion with timestart and store them in $data array
                    $query_with = "
					    SELECT
						*
					    FROM
						{$CFG->prefix}forum_discussions
					    WHERE
					    	forum = {$forumid} AND
						timestart <> 0 AND
						groupid IN ({$group_list}) AND
						(timeend > {$time} OR
						timeend = 0)
					    ORDER BY
						timestart DESC
						LIMIT {$nbpost}
				";
                    if ($datas = $DB->get_records_sql($query_with)) {
                        foreach ($datas as $data) {
                            $discussions[$i]["id"] = $data->id;
                            $discussions[$i]["userid"] = $data->userid;
                            $discussions[$i]["time"] = $data->timestart;
                            $discussions[$i]["name"] = $data->name;
                            $i++;
                        }
                    }

                    $use_group = ($group_list != '-1') ? " groupid  IN ({$group_list}) AND " : '';

                    // Get last "x" discussion without timestart and store them in $data array
                    $query_without = "
					    SELECT
						*
					    FROM
						{$CFG->prefix}forum_discussions
					    WHERE
					    	forum = {$forumid} AND
						timestart = 0 AND $use_group
						 (timeend > {$time} OR
						timeend = 0)
					    ORDER BY
						timemodified DESC
						LIMIT {$nbpost}
				";

                    if ($datas = $DB->get_records_sql($query_without)) {
                        foreach ($datas as $data) {
                            $discussions[$i]["id"] = $data->id;
                            $discussions[$i]["userid"] = $data->userid;
                            $discussions[$i]["time"] = $data->timemodified;
                            $discussions[$i]["name"] = $data->name;
                            $i++;
                        }
                    }

                    //$forum .= ' sql='.$query_without.' f count='.count($discussions).' ';
                    // Organize  $data array
                    // - sort on $discussions["time"] DESC
                    $discussions = $this->record_sort($discussions, 'time', true);
                    // - select only post nb, not more
                    $discussions = array_slice($discussions, 0, $nbpost);

                    $forum .= '<div class="filter_forum">';
                    $forum .= "<div class=\"forumtitle teacheronly\">[{$course->shortname} - {$foruminstance->name}]</div>";
                    if ($discussions) {
                        // There is posts, let's print them !
                        $forum .= '<ul>';
                        for ($i = 0; $i < count($discussions); $i++) {
                            $forum .= '<li>';
                            if ($user = $DB->get_record('user', array('id' => $discussions[$i]["userid"]))) {
//                            $forum .= print_user_picture($user->id, $COURSE->id, $user->picture, '16', true, true, '', false); //  'class'=>'profilepicture' ???
                                $forum .= $OUTPUT->user_picture($user, array('courseid' => $COURSE->id, 'size' => '16'));
                            }
                            // Make the full name
                            $fullname = $user->firstname . ' ' . $user->lastname;
                            if ($CFG->fullnamedisplay == 'firstname lastname') {
                                $fullname = $user->firstname . ' ' . $user->lastname;
                            } else if ($CFG->fullnamedisplay == 'lastname firstname') {
                                $fullname = $user->lastname . ' ' . $user->firstname;
                            } else if ($CFG->fullnamedisplay == 'firstname') {
                                $fullname = $user->firstname;
                            }
                            // Print the link
                            $forum .= '<a href="#" onclick="window.open(\'' . $CFG->wwwroot . '/mod/forum/discuss.php?d='
                                . $discussions[$i]["id"] . '\',\'discussion\',\'height=700,width=800\');" title="'
                                . userdate($discussions[$i]["time"], '%d/%m/%y ') . '- ' . $fullname . '" >';
                            $forum .= mb_substr($discussions[$i]["name"], 0, $nbcaract);
                            if (strlen($discussions[$i]["name"]) > $nbcaract) {
                                $forum .= '...';
                            }
                            $forum .= '</a></li>';
                        }
                        $forum .= '</ul>';
//					$forum .= '<a target="_new" href="#" id="youropinion" onclick="window.open(\''.$CFG->wwwroot.'/mod/forum/post.php?forum='.$forumid.'\',\'opinion\',\'height=700,width=800\');" >'.get_string("youropinion").'</a><br/>';
                        $forum .= '<a href="#" id="youropinion" onclick="window.open(\'' . $CFG->wwwroot . '/mod/forum/post.php?forum=' . $forumid . '\',\'opinion\',\'height=900,width=999,scrollbars=yes\');" >' . get_string("youropinion", "filter_forum") . '</a>';  // hanna 9/8/12
                        $forum .= '<a href="#" id="forumpage" onclick="window.open(\'' . $CFG->wwwroot . '/mod/forum/view.php?f=' . $forumid . '\',\'opinion\',\'height=900,width=999,scrollbars=yes\');" >' . get_string("forumpage", "filter_forum") . '</a>';  // hanna 9/8/12
                        //$forum .= '<form id="newdiscussionform" method="get" action="'.$CFG->wwwroot.'/mod/forum/post.php"><input type="hidden" name="forum" value="'.$forumid.'"><input type="submit" value="'.get_string('youropinion').'"></form>';
                    } else {
                    // if no discussions   nadavkav 27/9/17
                        $forum .= '<a href="#" id="youropinion" onclick="window.open(\'' . $CFG->wwwroot . '/mod/forum/post.php?forum=' . $forumid . '\',\'opinion\',\'height=900,width=999,scrollbars=yes\');" >' . get_string("youropinion", "filter_forum") . '</a>';  // hanna 9/8/12
                    }
                    $forum .= '</div><style>#youropinion {border: 2px outset gray;padding: 2px;text-decoration:none;} #youropinion:hover {background-color: #FDF4AF;}</style>';
                    $forum .= '<style>#forumpage {border: 2px outset gray;padding: 2px;text-decoration:none;} #forumpage:hover {background-color: #FDF4AF;}</style>';  // hanna 9/8/12
                    // TODO: remove
                    //$stylefile = file_get_contents($CFG->wwwroot . '/filter/forum/styles.css', FILE_USE_INCLUDE_PATH); // add filter's own styles // nadavkav 10-10-2012
                    //$forum .= '<style>' . $stylefile . '</style>';
                }
                // Change chain in text
                $text = str_replace('[[forum(' . $cmid . ',' . $groupid . ',' . $groupingid . ',' . $nbpost . ')]]', $forum, $text);
            } else {
                break;
            }

        }

        return $text;
    }

    protected function record_sort($records, $field, $reverse = false)
    {
        $hash = array();
        foreach ($records as $key => $record) {
            $hash[$record[$field] . $key] = $record;
        }
        ($reverse) ? krsort($hash) : ksort($hash);
        $records = array();
        foreach ($hash as $record) {
            $records [] = $record;
        }
        return $records;
    }
}  // end class
