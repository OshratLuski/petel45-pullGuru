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

defined('MOODLE_INTERNAL') || die();
require_once('../../../../config.php');
require_login();
define("QTYPE_ESSAYANNOTATE_RATIO", 0.238); // Ratio to convert FabricJS objects to FPDF objects.

/**
 * This page contains functions for parsing
 * The data from serialiser of fabricjs is read and processed.
 * The processed data is then stored in utiliszable manner for fpdf
 *
 * @package    qtype_essayannotate
 * @copyright  2024 IIT Palakkad
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Parvathy S Kumar, Asha Jose (IIT Palakkad)
 * Updated By  Nideesh N, VM Sreeram (IIT Palakkad)
 *    Added prefix to the constant RATIO.
 *    Moved functions inside the class qtype_essayannotate_parser.
 */
class qtype_essayannotate_parser {
    /**
     * for finding corresponding size of fpdf object given a fabricjs object
     * @param float $fabricjsunit variable corresponding to FabricJS distance unit(px)
     * @return float $fpdfunit corresponding distance unit value in FPDF
     */
    public static function normalize($fabricjsunit) {
        $fpdfunit = QTYPE_ESSAYANNOTATE_RATIO * $fabricjsunit;
        return $fpdfunit;
    }

    /**
     * Parser for free hand drawings
     * Given an array containing the data related to FabricJS path object,
     * deserialize it, taking the relevant data to convert the path to an FPDF line object.
     *
     * @param object $arrpath the array containing data related to a path object.
     * @return array $list deserialized data for the path in FPDF line format
     */
    public static function parser_path($arrpath) {
        // Stored as a set of points (x and y coordinates).
        $list = [];
        $len = count($arrpath["path"]);
        for ($i = 0; $i < $len - 1; $i++) {
            // First and Last array elements are dummy values.
            if ($i == 0 || $i == $len - 2) {
                continue;
            }

            $temp = [];
            array_push($temp, self::normalize($arrpath["path"][$i][1]));  // X1.
            array_push($temp, self::normalize($arrpath["path"][$i][2]));  // Y1.
            array_push($list, $temp);
            $temp = [];
            array_push($temp, self::normalize($arrpath["path"][$i][3]));  // X2.
            array_push($temp, self::normalize($arrpath["path"][$i][4]));  // Y2.
            array_push($list, $temp);
        }
        array_push($list, $arrpath["stroke"]);                       // Stroke color.
        return $list;
    }

    /**
     * Parser for text
     * Given an array containing the data related to FabricJS text object,
     * deserialize it, taking the relevant data to convert the text to an FPDF text object.
     *
     * @param object $arrtext the array containing data related to a text object.
     * @return array $list deserialized data in FPDF text format
     */
    public static function parser_text($arrtext) {
        $list = [];
        // Left and top refers to x and y coordinates of top left corner.
        array_push($list, self::normalize($arrtext["left"]), self::normalize($arrtext["top"]),
                    self::normalize($arrtext["width"]), self::normalize($arrtext["height"]));
        // Text refers to the content and fill is the color of the text.
        array_push($list, $arrtext["text"], $arrtext["fill"]);
        array_push($list, $arrtext["fontSize"]);
        return $list;
    }

    /**
     * Parser for rectangle
     * Given an array containing the data related to FabricJS Rect object,
     * deserialize it, taking the relevant data to convert the Rect to an FPDF Rect object.
     * scaling factor is also taken into consideration while transforming.
     *
     * @param object $arrrect the array containing data related to a Rect object.
     * @return array $list deserialized data in FPDF Rect format
     */
    public static function parser_rectangle($arrrect) {
        $list = [];
        // The variables scaleX and scaleY is 1 if the rectangle is not transformed.
        // During transformation, width and height remains same but the scaleX and scaleY change.
        $width = (self::normalize($arrrect["width"])) * ($arrrect["scaleX"]);
        $height = (self::normalize($arrrect["height"])) * ($arrrect["scaleY"]);
        array_push($list, self::normalize($arrrect["left"]), self::normalize($arrrect["top"]), $width, $height);
        array_push($list, $arrrect["fill"]);
        return $list;

    }

    /**
     * Utility function for converting color format from fabricJS to rgb values
     * Given a string containing the color data in fabricJS format,
     * return the corresponding color values in [r,g,b]
     *
     * @param string $colorstring the string containing color data
     * @return array $rgb colors in [r,g,b] format
     */
    public static function process_color($colorstring) {
        if ($colorstring == "null") {
            $rgb = [0, 0, 0];
        }
        if ($colorstring == "red"
            || $colorstring == 'rgba(251, 17, 17, 0.3)'
            || $colorstring == 'rgb(251, 17, 17)') {
            $rgb = [251, 17, 17];              // Converting string to rgb.
        } else if ($colorstring == "green"
            || $colorstring == 'rgba(13, 93, 13, 0.3)'
            || $colorstring == 'rgb(13, 93, 13)') {
            $rgb = [13, 93, 13];
        } else if ($colorstring == "blue"
            || $colorstring == 'rgba(2, 2, 182, 0.3)'
            || $colorstring == 'rgb(2, 2, 182)') {
            $rgb = [2, 2, 182];
        } else if ($colorstring == "black"
            || $colorstring == 'rgba(0, 0, 0, 0.3)'
            || $colorstring == 'rgb(0, 0, 0)') {
            $rgb = [0, 0, 0];
        } else if ($colorstring == "yellow"
            || $colorstring == 'rgba(255, 255, 0, 0.3)'
            || $colorstring == 'rgb(255, 255, 0)') {
            $rgb = [255, 255, 0];
        } else {
            $rgb = [];
            list($r, $g, $b) = sscanf($colorstring, "#%02x%02x%02x"); // Hexadecimal format.
            $rgb = [$r, $g, $b];
        }
        return $rgb;
    }
}
