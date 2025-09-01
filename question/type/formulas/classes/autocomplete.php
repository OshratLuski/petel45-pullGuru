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
 * @package     qtype_formulas
 * @category    question
 * @copyright   2023 Devlion <info@devlion.co>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qtype_formulas;

defined('MOODLE_INTERNAL') || die();

class autocomplete {

    public $given;
    public $answer;
    public $tolerance;

    public function __construct($given, $answer, $texpression) {
        $this->given = $given;
        $this->answer = $answer;
        $this->tolerance = 0;

        // Prepare tolerance.
        list($type, $sign, $value) = explode(' ', $texpression);

        if (is_numeric($value) && $value >= 0) {
            switch ($type) {
                case '_err':
                    $this->tolerance = $value;
                    break;
                case '_relerr':
                    if (!empty($given['value']) && is_numeric($given['value']) && $value > 0 && $value < 1) {
                        $this->tolerance = abs($given['value'] * $value);
                    }
                    break;
            }
        }
    }

    public static function get_units_array() {
        $arr = self::prepare_units();

        $result = [];
        if (!empty($arr)) {
            foreach ($arr as $group) {
                foreach ($group as $item) {
                    if (!empty($item['unit'])) {
                        $result[$item['unit']] = $item['unit'];
                    }
                }
            }
        }

        return array_values($result);
    }

    public static function split_answer($value) {
        $num = $unit = null;

        $length = strlen($value);
        for ($i=0; $i <= $length; $i++) {

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

    private static function prepare_units() {
        global $DB;

        if ($config = $DB->get_record('config', ['name' => 'qtype_formulas_units'])) {

            $arrlines = preg_split('/\r\n|[\r\n]/', $config->value);

            $arrgroupstmp = [];
            foreach ($arrlines as $line) {

                // Remove spaces.
                $line = str_replace(' ', '', $line);
                $arrgroupstmp[] = explode('=', $line);
            }

            // Create array data.
            $arrgroups = [];
            foreach ($arrgroupstmp as $group) {

                $arritem = [];
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

                    $arritem[] = $arr1;
                }

                $arrgroups[] = $arritem;
            }

            // Sort array by value.
            $arrgroupssorted = [];
            foreach ($arrgroups as $group) {
                ksort($group);
                $arrgroupssorted[] = $group;
            }

            return $arrgroupssorted;
        } else {
            return [];
        }
    }

    public function compare_answer() {

        $given = $this->given;
        $answer = $this->answer;
        $tolerance = $this->tolerance;

        if (empty($answer['unit'])) {
            return false;
        }

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
                        $rowiddano = $key;
                    }

                    if ($item['unit'] == $answer['unit']) {
                        $rowidanswer = $key;
                    }
                }
            }

            if (!isset($rowiddano) || !isset($rowidanswer)) {
                return false;
            }

            // Different rows.
            if ($rowiddano != $rowidanswer) {
                return false;
            }

        } else {
            return false;
        }

        // If unit dano == unit answer.
        if ($given['unit'] == $answer['unit']) {
            return $this->compare_with_tolerance($given['value'], $answer['value'], $tolerance);
        }

        // Get coefficients.
        foreach ($arr[$rowiddano] as $item) {
            if ($item['unit'] == $given['unit']) {
                $coeffdano = $item['value'];
            }
            if ($item['unit'] == $answer['unit']) {
                $coeffanswer = $item['value'];
            }
        }

        if (floatval($coeffdano) != 0) {
            $newgivenvalue = $coeffanswer * ($given['value'] / floatval($coeffdano));
        } else {
            $newgivenvalue = $coeffanswer;
        }

        $coefftolerance = ($given['value'] != 0) ? ($newgivenvalue / $given['value']) * $tolerance : 0;

        return $this->compare_with_tolerance($newgivenvalue, $answer['value'], $coefftolerance);
    }

    public function check_for_penalty() {

        $given = $this->given;
        $answer = $this->answer;
        $tolerance = $this->tolerance;

        $wrongvaluepenalty = $this->get_wrongvaluepenalty();
        $wrongunitpenalty = $this->get_wrongunitpenalty();

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
            return $obj;
        }

        // Compare unit.
        $arrunits = $this->get_units_row_array($given['unit']);
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
            if (is_numeric($given['value'])) {
                if (floatval($coeffgiven) != 0) {
                    $arr_coeff_units_new[] = floatval($item) * (floatval($given['value']) / floatval($coeffgiven));
                } else {
                    $arr_coeff_units_new[] = floatval($item);
                }
            }
        }

        // Compare with tolerance.
        foreach ($arr_coeff_units_new as $index => $item) {
            $unit_in_row = $arr[$rowidgiven][$index]['unit'] ?? '';
            $coefftolerance = ($given['value'] != 0) ? ($item / $given['value']) * $tolerance : 0;
        
            // Case 1: The student provided both a value and a unit.
            if (!empty($answer['unit'])) {
                // Skip if the unit does not match the current item's unit.
                if ($unit_in_row !== $answer['unit']) {
                    continue;
                }
        
                // If the value matches within tolerance and the unit matches, it's valid.
                if ($this->compare_with_tolerance($item, $answer['value'], $coefftolerance)) {
                    $value_validation = 1;
                    break;
                }
        
            // Case 2: The student did not provide any unit.
            } else {
                // It's allowed to compare against all possible units.
                if ($this->compare_with_tolerance($item, $answer['value'], $coefftolerance)) {
                    $value_validation = 1;
                    break;
                }
            }
        }

        if (!$value_validation && !$unit_validation_aprox && !$unit_validation_accur) {
            return $obj;
        }

        if ($wrongvaluepenalty != '0' && $wrongunitpenalty != '0') {
            if ($value_validation) {
                $obj->result = true;
                $obj->penalty = $wrongunitpenalty;
                $obj->penaltytype = 'unit';
            } else if ($unit_validation_aprox) {
                $obj->result = true;
                $obj->penalty = $wrongvaluepenalty;
                $obj->penaltytype = 'value';
            }
        }

        return $obj;
    }

    private function compare_with_tolerance($givenvalue, $answervalue, $coefftolerance) {

        if ($coefftolerance != 0) {
            $left = $givenvalue - $coefftolerance;
            $right = $givenvalue + $coefftolerance;

            return $left < $answervalue && $answervalue < $right ? true : false;
        } else {
            return $givenvalue == $answervalue ? true : false;
        }
    }

    private function get_units_row_array($givenunit) {

        $arr = self::prepare_units();
        if (!empty($arr)) {

            // Find needed row.
            foreach ($arr as $key => $group) {
                foreach ($group as $item) {
                    if ($item['unit'] == $givenunit) {
                        $rowidgiven = $key;
                    }
                }
            }

            if (!isset($rowidgiven)) {
                return false;
            }

        } else {
            return [];
        }

        if (!empty($arr)) {
            $result = [];
            foreach ($arr as $key => $group) {
                foreach ($group as $item) {
                    if ($key == $rowidgiven) {
                        $result[$item['unit']] = $item['unit'];
                    }
                }
            }

            return $result;
        } else {
            return [];
        }
    }

    private function get_wrongvaluepenalty() {
        global $DB;

        $config = $DB->get_record('config', ['name' => 'qtype_formulas_wrongvaluepenalty']);
        return $config ? $config->value : 0;
    }

    private function get_wrongunitpenalty() {
        global $DB;

        $config = $DB->get_record('config', ['name' => 'qtype_formulas_wrongunitpenalty']);
        return $config ? $config->value : 0;
    }
}
