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
 * @package    community_oer
 * @copyright  2018 Devlion <info@devlion.co>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace community_oer;

class object_query {
    public $data;
    public $uniquefield;
    public $prevdata;
    public $field;
    public $order;

    public function __construct($data, $uniquefield, $prevdata = null) {
        $this->data = $data;
        $this->uniquefield = $uniquefield;
        $this->prevdata = ($prevdata == null) ? $data : $prevdata;

        $this->data_unique();
    }

    public function compareArrayField($place, $field, $value) {
        $result = [];

        foreach ($this->data as $key => $item) {
            if (isset($item->$place)) {
                foreach ($item->$place as $obj) {
                    $obj = (array) $obj;

                    if (isset($obj[$field]) && $obj[$field] == $value) {
                        $result[] = $item;
                    }
                }
            }
        }

        return new object_query($result, $this->uniquefield, $this->prevdata);
    }

    public function orCompareArrayField($place, $field, $value) {
        $result = [];

        foreach ($this->prevdata as $key => $item) {
            if (isset($item->$place)) {
                foreach ($item->$place as $obj) {
                    $obj = (array) $obj;

                    if (isset($obj[$field]) && $obj[$field] == $value) {
                        $result[] = $item;
                    }
                }
            }
        }

        $result = array_merge($this->data, $result);

        return new object_query($result, $this->uniquefield, $this->prevdata);
    }

    public function inArray($place, $value) {
        $result = [];

        if (is_string($value)){
            $arr = explode(',', $value);
            if (count($arr) > 1) {
                $value = $arr;
            }
        }

        foreach ($this->data as $key => $item) {
            if (isset($item->$place)) {

                $arrdata = $item->$place;
                if (!is_array($arrdata)) {
                    $arrdata = json_decode($arrdata);
                }

                if (!is_array($arrdata)) {
                    $arrdata = [];
                }

                if (!is_array($value) && in_array($value, $arrdata)) {
                    $result[] = $item;
                }

                if (is_array($value)) {
                    foreach ($value as $str) {
                        if (in_array($str, $arrdata)) {
                            $result[] = $item;
                            break;
                        }
                    }
                }
            }
        }

        return new object_query($result, $this->uniquefield, $this->prevdata);
    }

    public function orInArray($place, $value) {
        $result = [];

        if (is_string($value)){
            $arr = explode(',', $value);
            if (count($arr) > 1) {
                $value = $arr;
            }
        }

        foreach ($this->prevdata as $key => $item) {
            if (isset($item->$place)) {

                $arrdata = $item->$place;
                if (!is_array($arrdata)) {
                    $arrdata = json_decode($arrdata);
                }

                if (!is_array($arrdata)) {
                    $arrdata = [];
                }

                if (!is_array($value) && in_array($value, $arrdata)) {
                    $result[] = $item;
                }

                if (is_array($value)) {
                    foreach ($value as $str) {
                        if (in_array($str, $arrdata)) {
                            $result[] = $item;
                            break;
                        }
                    }
                }
            }
        }

        $result = array_merge($this->data, $result);

        return new object_query($result, $this->uniquefield, $this->prevdata);
    }

    public function compare($field, $value) {
        $result = [];
        foreach ($this->data as $key => $item) {
            if ($item->$field == $value) {
                $result[] = $item;
            }
        }

        return new object_query($result, $this->uniquefield, $this->prevdata);
    }

    public function orCompare($field, $value) {
        $result = [];
        foreach ($this->prevdata as $key => $item) {
            if ($item->$field == $value) {
                $result[] = $item;
            }
        }

        $result = array_merge($this->data, $result);

        return new object_query($result, $this->uniquefield, $this->prevdata);
    }

    public function like($field, $value) {
        $result = [];
        foreach ($this->data as $key => $item) {
            if (strpos($item->$field, strval($value)) !== false) {
                $result[] = $item;
            }
        }

        return new object_query($result, $this->uniquefield, $this->prevdata);
    }

    public function orLike($field, $value) {
        $result = [];
        foreach ($this->prevdata as $key => $item) {
            if (strpos($item->$field, strval($value)) !== false) {
                $result[] = $item;
            }
        }

        $result = array_merge($this->data, $result);

        return new object_query($result, $this->uniquefield, $this->prevdata);
    }

    public function likeLower($field, $value) {
        $result = [];
        foreach ($this->data as $key => $item) {
            if (strpos(strtolower($item->$field), strtolower(strval($value))) !== false) {
                $result[] = $item;
            }
        }

        return new object_query($result, $this->uniquefield, $this->prevdata);
    }

    public function orLikeLower($field, $value) {
        $result = [];
        foreach ($this->prevdata as $key => $item) {
            if (strpos(strtolower($item->$field), strtolower(strval($value))) !== false) {
                $result[] = $item;
            }
        }

        $result = array_merge($this->data, $result);

        return new object_query($result, $this->uniquefield, $this->prevdata);
    }

    public function notIn($field, $values) {
        $result = [];
        $values = explode(',', $values);
        foreach ($this->data as $key => $item) {

            $flag = false;
            foreach ($values as $value) {
                if ($item->$field == $value) {
                    $flag = true;
                    break;
                }
            }

            if ($flag == false) {
                $result[] = $item;
            }
        }

        return new object_query($result, $this->uniquefield, $this->prevdata);
    }

    public function orNotIn($field, $values) {
        $result = [];
        $values = explode(',', $values);
        foreach ($this->prevdata as $key => $item) {

            $flag = false;
            foreach ($values as $value) {
                if ($item->$field == $value) {
                    $flag = true;
                    break;
                }
            }

            if ($flag == false) {
                $result[] = $item;
            }
        }

        $result = array_merge($this->data, $result);

        return new object_query($result, $this->uniquefield, $this->prevdata);
    }

    public function empty($field) {
        $result = [];
        foreach ($this->data as $key => $item) {
            if (empty($item->$field)) {
                $result[] = $item;
            }
        }

        return new object_query($result, $this->uniquefield, $this->prevdata);
    }

    public function orEmpty($field) {
        $result = [];
        foreach ($this->prevdata as $key => $item) {
            if (empty($item->$field)) {
                $result[] = $item;
            }
        }

        $result = array_merge($this->data, $result);

        return new object_query($result, $this->uniquefield, $this->prevdata);
    }

    // Orders.
    public function orderNumber($field, $order = 'desc') {
        $result = $this->data;

        $this->field = $field;
        $this->order = $order;

        usort($result, function($a, $b) {

            $field = $this->field;

            if ($a->$field == $b->$field) {
                return 0;
            }

            if ($this->order == 'desc') {
                return ($a->$field > $b->$field) ? -1 : 1;
            }

            if ($this->order == 'asc') {
                return ($a->$field < $b->$field) ? -1 : 1;
            }

            return 0;
        });

        return new object_query($result, $this->uniquefield, $this->prevdata);
    }

    public function orderString($field, $order = 'desc') {
        $result = $this->data;

        $this->field = $field;
        $this->order = $order;

        $new = array();
        $sortable = array();

        foreach ($result as $k => $obj) {
            $sortable[$k] = trim(preg_replace('/\s+/', ' ', $obj->$field));
        }

        switch ($order) {
            case 'asc':
                asort($sortable);
                break;
            case 'desc':
                arsort($sortable);
                break;
        }

        foreach ($sortable as $k => $v) {
            $new[$k] = $result[$k];
        }

        return new object_query($new, $this->uniquefield, $this->prevdata);
    }

    public function multiOrder($fields) {

        foreach ($fields as $key => $sort) {
            switch ($sort) {
                case 'desc':
                    $fields[$key] = SORT_DESC;
                    break;
                case 'asc':
                    $fields[$key] = SORT_ASC;
                    break;
                default:
                    $fields[$key] = SORT_DESC;
            }
        }

        $array = [];
        foreach ($this->data as $key => $obj) {
            $tmp['key'] = $key;

            foreach (array_keys($fields) as $field) {
                $tmp[$field] = $obj->$field;
            }

            $array[] = $tmp;
        }

        $result = [];
        foreach ($this->array_msort($array, $fields) as $arr) {
            $result[$arr['key']] = $this->data[$arr['key']];
        }

        return new object_query($result, $this->uniquefield, $this->prevdata);
    }

    public function groupBy($field) {
        $values = [];
        foreach ($this->data as $item) {
            $values[] = trim($item->$field);
        }

        $result = [];
        $values = array_values($values);
        foreach ($values as $val) {
            $flag = false;
            foreach ($this->data as $item) {
                if (trim($item->$field) == $val && $flag == false) {
                    $result[] = $item;
                    $flag = true;
                }
            }
        }

        return new object_query($result, $this->uniquefield, $this->prevdata);
    }

    public function limit($start, $offset) {

        $this->data_unique();
        $result = [];

        $counter = 1;
        foreach ($this->data as $item) {
            if ($counter >= $start && $counter < $start + $offset) {
                $result[] = $item;
            }

            $counter++;
        }

        return new object_query($result, $this->uniquefield, $this->prevdata);
    }

    public function get() {
        $this->data_unique();

        return $this->data;
    }

    public function count() {
        $this->data_unique();

        return count($this->data);
    }

    private function data_unique() {
        $result = [];
        $field = $this->uniquefield;
        foreach ($this->data as $item) {
            if (isset($item->$field)) {
                $result[$item->$field] = $item;
            }
        }

        $this->data = $result;
        return true;
    }

    private function array_msort_old($array, $cols) {
        $colarr = [];
        foreach ($cols as $col => $order) {
            $colarr[$col] = array();
            foreach ($array as $k => $row) { $colarr[$col]['_'.$k] = strtolower($row[$col]); }
        }

        $eval = 'array_multisort(';
        foreach ($cols as $col => $order) {
            $eval .= '$colarr[\''.$col.'\'],'.$order.',';
        }

        $eval = substr($eval,0,-1).');';
        eval($eval);
        
        $ret = [];
        foreach ($colarr as $col => $arr) {
            foreach ($arr as $k => $v) {
                $k = substr($k,1);
                if (!isset($ret[$k])) $ret[$k] = $array[$k];
                $ret[$k][$col] = $array[$k][$col];
            }
        }

        return $ret;
    }

    private function array_msort($array, $cols) {
        $colarr = [];
        $sortParams = [];

        foreach ($cols as $col => $order) {
            $colarr[$col] = [];
            foreach ($array as $k => $row) {
                $colarr[$col][$k] = strtolower($row[$col]);
            }
            $sortParams[] = $colarr[$col];
            $sortParams[] = $order;
        }

        $sortParams[] = &$array;

        call_user_func_array('array_multisort', $sortParams);

        return $array;
    }
}
