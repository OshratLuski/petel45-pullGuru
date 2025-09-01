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
 * Plugin capabilities are defined here.
 *
 * @package     quiz_assessmentdiscussion
 * @category    cache
 * @copyright   2022 Devlion <info@devlion.co>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quiz_assessmentdiscussion;

defined('MOODLE_INTERNAL') || die();

class assessmentcache {

    private $cmid;
    private $cacheallattempts;
    private $cacheuserattemptsgrade;
    private $cachequestions;

    public function __construct($cmid) {
        $this->cmid = $cmid;

        $this->cacheallattempts = \cache::make_from_params(\cache_store::MODE_APPLICATION, 'assessmentdiscussion_all_attempts', $this->cmid);
        $this->cacheuserattemptsgrade = \cache::make_from_params(\cache_store::MODE_APPLICATION, 'assessmentdiscussion_user_attempts_grade', $this->cmid);
        $this->cachequestions = \cache::make_from_params(\cache_store::MODE_APPLICATION, 'assessmentdiscussion_questions', $this->cmid);
    }

    public function all_attempts() {
        return new assessmentfunc($this->cacheallattempts);
    }

    public function user_attempts_grade() {
        return new assessmentfunc($this->cacheuserattemptsgrade);
    }

    public function questions() {
        return new assessmentfunc($this->cachequestions);
    }
}

class assessmentfunc {
    private $cacheobj;
    private $delta;
    private $enabled;

    public function __construct($cacheobj) {
        $this->cacheobj = $cacheobj;

        $cacheenable = get_config('quiz_assessmentdiscussion', 'cacheenable');
        $this->enabled = $cacheenable == 1 ? true : false;

        // Time in seconds. 0 - disabled.
        $this->delta = 0;
    }

    public function check_cache($instance) {

        if ($this->cacheobj->has($instance) && $this->enabled) {

            // Delta disabled.
            if ($this->delta == 0) {
                return true;
            }

            $res = $this->cacheobj->get($instance);

            if ($res['time'] + $this->delta > time()) {
                return true;
            }
        }

        return false;
    }

    public function set($instance, $data) {
        $res = ['time' => time(), 'data' => $data];
        $this->cacheobj->set($instance, $res);
    }

    public function get($instance) {
        $res = $this->cacheobj->get($instance);
        return $res['data'];
    }

    public function purge() {
        $this->cacheobj->purge();
    }
}
