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
 * @package     qtype_numerical
 * @category    question
 * @copyright   2023 Devlion <info@devlion.co>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qtype_numerical;

defined('MOODLE_INTERNAL') || die();

class autocomplete {

    public static function get_units_array() {

        $units = self::prepare_units();

        $result = [];
        foreach ($units as $group) {
            foreach ($group as $item) {
                if (!empty($item['unit'])) {
                    $result[$item['unit']] = $item['unit'];
                }
            }
        }

        return array_values($result);
    }

    public static function prepare_units_for_student() {

        $units = self::prepare_units();

        // Get all rows.
        $groups = [];
        foreach ($units as $key => $group) {
            $groups[] = $key;
        }

        $groups = array_unique($groups);

        // Create form for js.
        $result = [];
        foreach ($groups as $group) {

            $groupunits = array_values($units[$group]);
            foreach ($groupunits as $item) {
                $result[] = $item['unit'];
            }
        }

        $result = array_unique($result);
        $result = array_values($result);

        return $result;
    }

    public static function split_answer($value) {
        $num = $unit = null;

        $length = strlen($value);
        for ($i = 0; $i <= $length; $i++) {

            $str = substr($value, 0, $length - $i);
            if (is_numeric($str)) {
                $num = floatval($str);
                $unit = substr($value, -$i, $i);
                break;
            }
        }

        $num = str_replace(' ', '', $num);
        $unit = str_replace(' ', '', $unit);

        if (strlen($num) == 0 || !is_numeric($num)) {
            $num = null;
        }

        if (empty($unit)) {
            $unit = null;
        }

        return [$num, $unit];
    }

    public static function check_for_penalty($given, $answer, $tolerance) {

        $wrongvaluepenalty = self::get_wrongvaluepenalty();
        $wrongunitpenalty = self::get_wrongunitpenalty();

        // Conversion K => C.
        if (in_array($given['unit'], ['C', '°C']) && $answer['unit'] == 'K') {
            $answer['unit'] = $given['unit'];
            $answer['value'] = $answer['value'] - 273;
        }

        // Conversion C => K.
        if ($given['unit'] == 'K' && in_array($answer['unit'], ['C', '°C'])) {
            $answer['unit'] = 'K';
            $answer['value'] = $answer['value'] + 273;
        }

        $unit_validation_aprox = 0;
        $unit_validation_accur = 0;
        $value_validation = 0;

        $given['unit'] = trim($given['unit']);
        $answer['unit'] = trim($answer['unit']);

        $obj = new \stdClass;
        $obj->result = false;
        $obj->penalty = 0;
        $obj->penaltytype = '';

        // If empty unit.
        if ($answer['value'] == $given['value'] && empty($answer['unit'])) {
            $obj->result = true;
            $obj->penalty = $wrongunitpenalty;
            $obj->penaltytype = 'unit';
            $obj->feedback = get_string('feedbackwrongunit', 'qtype_numerical');
            return $obj;
        }

        // Compare unit.
        $arrunits = self::get_units_row_array($given['unit']);
        if (empty($arrunits)) {
            return $obj;
        }

        if (in_array($answer['unit'], $arrunits)) {
            $unit_validation_aprox = 1;
        }

        if ($answer['unit'] == $given['unit']) {
            $unit_validation_accur = 1;
        }

        // Compare value.
        $arr = self::prepare_units();
        if (empty($arr)) {
            return $obj;
        }

        // Find needed row.
        foreach ($arr as $key => $group) {
            foreach ($group as $item) {
                if ($item['unit'] == $given['unit']) {
                    $rowidgiven = $key;
                }
            }
        }

        // Get coefficients.
        $arr_coeff_units = [];
        foreach ($arr[$rowidgiven] as $item) {
            if ($item['unit'] == $given['unit']) {
                $coeffgiven = $item['value'];
            }

            $arr_coeff_units[] = $item['value'];
        }

        // Prepare new values with selected coeff.
        $arr_coeff_units_new = [];
        foreach ($arr_coeff_units as $item) {
            if (floatval($coeffgiven) != 0) {
                $arr_coeff_units_new[] = floatval($item) * (floatval($given['value']) / floatval($coeffgiven));
            } else {
                $arr_coeff_units_new[] = floatval($item);
            }
        }

        // Compare with tolerance.
        foreach ($arr_coeff_units_new as $item) {
            $coefftolerance = ($given['value'] != 0) ? ($item / $given['value']) * $tolerance : 0;
            if (self::compare_with_tolerance($item, $answer['value'], $coefftolerance)) {
                $value_validation = 1;

            }
        }

        if (!$value_validation && !$unit_validation_aprox && !$unit_validation_accur) {
            return $obj;
        }

        if ($wrongvaluepenalty != '0' && $wrongunitpenalty != '0') {
            if ($value_validation) {
                $obj->result = true;
                $obj->penaltytype = 'unit';
                $obj->penalty = $wrongunitpenalty;
                $obj->feedback = get_string('feedbackwrongunit', 'qtype_numerical');
            } else if ($unit_validation_aprox) {
                $obj->result = true;
                $obj->penaltytype = 'value';
                $obj->penalty = $wrongvaluepenalty;
                $obj->feedback = get_string('feedbackwrongvalue', 'qtype_numerical');
            }
        }

        return $obj;
    }

    public static function compare_answer($given, $answer, $tolerance) {

        // Conversion K => C.
        if (in_array($given['unit'], ['C', '°C']) && $answer['unit'] == 'K') {
            $answer['unit'] = $given['unit'];
            $answer['value'] = $answer['value'] - 273;
        }

        // Conversion C => K.
        if ($given['unit'] == 'K' && in_array($answer['unit'], ['C', '°C'])) {
            $answer['unit'] = 'K';
            $answer['value'] = $answer['value'] + 273;
        }

        $given['unit'] = trim($given['unit']);
        $answer['unit'] = trim($answer['unit']);

        $arr = self::prepare_units();
        if (!empty($arr)) {

            // Find needed row.
            foreach ($arr as $key => $group) {
                foreach ($group as $item) {
                    if ($item['unit'] == $given['unit']) {
                        $rowidgiven = $key;
                    }

                    if ($item['unit'] == $answer['unit']) {
                        $rowidanswer = $key;
                    }
                }
            }

            if (!isset($rowidgiven) || !isset($rowidanswer)) {
                return false;
            }

            // Different rows.
            if ($rowidgiven != $rowidanswer) {
                return false;
            }

        } else {
            return false;
        }

        // If unit given == unit answer.
        if ($given['unit'] == $answer['unit']) {
            return self::compare_with_tolerance($given['value'], $answer['value'], $tolerance);
        }

        // Get coefficients.
        foreach ($arr[$rowidgiven] as $item) {
            if ($item['unit'] == $given['unit']) {
                $coeffgiven = $item['value'];
            }
            if ($item['unit'] == $answer['unit']) {
                $coeffanswer = $item['value'];
            }
        }

        if (floatval($coeffgiven) != 0) {
            $newgiven = $coeffanswer * ($given['value'] / floatval($coeffgiven));
        } else {
            $newgiven = $coeffanswer;
        }

        $coefftolerance = ($given['value'] != 0) ? ($newgiven / $given['value']) * $tolerance : 0;

        return self::compare_with_tolerance($newgiven, $answer['value'], $coefftolerance);
    }

    private static function prepare_units() {
        global $DB;

        $sql = "SELECT * FROM {config} WHERE name='qtype_numerical_units'";
        $config = $DB->get_record_sql($sql);

        if (!empty($config)) {
            $setting = $config->value;
            $arrlines = preg_split('/\r\n|[\r\n]/', $setting);

            $arrgroups = [];
            foreach ($arrlines as $line) {

                // Remove spaces.
                $line = str_replace(' ', '', $line);

                $arrgroups[] = explode('=', $line);
            }

            // Create array data.
            $arr_groups = [];
            foreach ($arrgroups as $group) {

                $arritems = [];
                foreach ($group as $item) {
                    $arr = preg_split('/(?<=[0-9])(?=[a-z]+)/i', $item);

                    if (count($arr) != 2 && strpos($item, '°') !== false) {
                        $arr = [];
                        $arr[0] = (double) $item;
                        $arr[1] = substr($item, strlen($arr[0]));
                    }

                    if (count($arr) != 2) {
                        $val = $arr[0];

                        $arr = [];
                        $arr[0] = $val;
                        $arr[1] = substr($item, strlen($arr[0]));
                    }

                    $arr1['value'] = trim($arr[0]);

                    if (isset($arr[1])) {
                        $arr1['unit'] = trim($arr[1]);
                    } else {
                        $arr1['unit'] = '';
                    }

                    $arritems[] = $arr1;
                }

                $arr_groups[] = $arritems;
            }

            //sort array by value
            $groupssorted = [];
            foreach ($arr_groups as $group) {
                ksort($group);
                $groupssorted[] = $group;

            }

            return $groupssorted;
        } else {
            return [];
        }

    }

    private static function compare_with_tolerance($given, $answer, $tolerance) {
        if ($tolerance != 0) {
            //$left = $given - abs($given * $tolerance);
            //$right = $given + abs($given * $tolerance);

            $left = $given - $tolerance;
            $right = $given + $tolerance;

            return ($left < $answer && $answer < $right) ? true : false;
        } else {
            return ($given == $answer) ? true : false;
        }
    }

    private static function get_units_row_array($givenunit) {

        $arr = self::prepare_units();

        if (!empty($arr)) {
            // Find needed row.
            foreach ($arr as $key => $group) {
                foreach ($group as $item) {
                    if ($item['unit'] == $givenunit) {
                        $rowid = $key;
                    }
                }
            }

            if (!isset($rowid)) {
                return false;
            }
        } else {
            return [];
        }

        if (!empty($arr)) {

            $result = [];
            foreach ($arr as $key => $group) {
                foreach ($group as $item) {
                    if ($key == $rowid) {
                        $result[$item['unit']] = $item['unit'];
                    }
                }
            }

            return $result;
        } else {
            return [];
        }

    }

    private static function get_wrongvaluepenalty() {
        global $DB;

        $sql = "SELECT * FROM {config} WHERE name='qtype_numerical_wrongvaluepenalty'";
        $config = $DB->get_record_sql($sql);

        return (!empty($config)) ? $config->value : 0;
    }

    private static function get_wrongunitpenalty() {
        global $DB;

        $sql = "SELECT * FROM {config} WHERE name='qtype_numerical_wrongunitpenalty'";
        $config = $DB->get_record_sql($sql);

        return (!empty($config)) ? $config->value : 0;
    }

}
