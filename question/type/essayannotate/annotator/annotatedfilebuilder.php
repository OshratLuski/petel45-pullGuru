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
require_once('parser.php');
define("QTYPE_ESSAYANNOTATE_BRUSHSIZE", 0.50);
define("QTYPE_ESSAYANNOTATE_FONTTYPE", 'Times');
define("QTYPE_ESSAYANNOTATE_OPACITY", 0.30);
define("QTYPE_ESSAYANNOTATE_FULLOPACITY", 1);
define("QTYPE_ESSAYANNOTATE_FONTRATIO", 1.6);
define("QTYPE_ESSAYANNOTATE_XOFFSET", 1);
define("QTYPE_ESSAYANNOTATE_YOFFSET", 1);
define("QTYPE_ESSAYANNOTATE_ADJUSTPAGESIZE", false);

/**
 * This page contains functions that annotates using fpdf
 * The data stored as suitable arrays by the parser.php file is utilized here.
 * Depending on the data, different objects can be drawn on top of a pdf using these functions.
 *
 * @package    qtype_essayannotate
 * @copyright  2024 IIT Palakkad
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Asha Jose, Parvathy S Kumar (IIT Palakkad)
 * Updated by Nideesh N, VM Sreeram
 *    Added prefix to the constants.
 *    Moved functions inside the class qtype_essayannotate_annotatedfilebuilder.
 */
class qtype_essayannotate_annotatedfilebuilder {
    /**
     * Takes file to annotate and the annotation data passed from upload.php and
     * returns the annotated PDF File Object
     *
     * @param string $file the name of the file to annotate
     * @param object $json the annotation data
     * @return object $pdf the fpdi object that has information related to pdf file and annotations.
     */
    public static function build_annotated_file($file, $json) {
        // Get the page orientation.
        $orientation = $json["page_setup"]['orientation'];
        $orientation = ($orientation == "portrait") ? 'p' : 'l';

        // FPDI class defined in alphapdf.php.
        $pdf = new AlphaPDF($orientation);
        $pagecount = $pdf->setSourceFile($file);
        // Take the pages of PDF one-by-one and annotate them.
        for ($i = 1; $i <= $pagecount; $i++) {
            // Functions from FPDI.
            $template = $pdf->importPage($i);
            $size = $pdf->getTemplateSize($template);
            $pdf->addPage();
            $pdf->useTemplate($template, QTYPE_ESSAYANNOTATE_XOFFSET, QTYPE_ESSAYANNOTATE_YOFFSET, $size['width'], $size['height'], QTYPE_ESSAYANNOTATE_ADJUSTPAGESIZE);
            $currpage = $json["pages"][$i - 1];

            if (count((array)$currpage) == 0) { // To check whether the current page has no annotations.
                continue;
            }
            // Number of objects in the current page.
            $objnum = count((array)$currpage[0]["objects"]);

            for ($j = 0; $j < $objnum; $j++) {
                $arr = $currpage[0]["objects"][$j];
                if ($arr["type"] == "path") {
                    self::draw_path($arr, $pdf);
                } else if ($arr["type"] == "i-text") {
                    self::insert_text($arr, $pdf);
                } else if ($arr["type"] == "rect") {
                    self::draw_rect($arr, $pdf);
                }
            }
        }

        return $pdf;
    }

    /**
     * Function to draw free hand drawing
     * Given the array containing information related to path containg the FPDF line object and
     * FPDI file object, it adds the path as series of line object to FPDI file object
     *
     * @param array $arr the deserialized data array for the path in FPDF line format
     * @param object $pdf the fpdi object that has information related to pdf file and annotations.
     */
    public static function draw_path($arr, $pdf) {
        $list = qtype_essayannotate_parser::parser_path($arr);
        $stroke = qtype_essayannotate_parser::process_color(end($list));
        $pdf->SetDrawColor($stroke[0], $stroke[1], $stroke[2]);   // R G B of stroke color.
        $pdf->SetLineWidth(QTYPE_ESSAYANNOTATE_BRUSHSIZE);
        for ($k = 0; $k < count($list) - 2; $k++) {
            $pdf->Line($list[$k][0],                      // X1.
            $list[$k][1],                                 // Y1.
            $list[$k + 1][0],                             // X2.
            $list[$k + 1][1]);                            // Y2.
        }
    }

    /**
     * Function to insert text
     * Given the array containing information related to FPDF text object and FPDI file object,
     * it adds the text object to FPDI file object
     *
     * @param array $arr the deserialized data array in FPDF text format
     * @param object $pdf the fpdi object that has information related to pdf file and annotations.
     */
    public static function insert_text($arr, $pdf) {
        $list = qtype_essayannotate_parser::parser_text($arr);
        $color = qtype_essayannotate_parser::process_color($list[5]);
        $pdf->SetTextColor($color[0], $color[1], $color[2]);       // R G B.
        $pdf->SetFont(QTYPE_ESSAYANNOTATE_FONTTYPE);
        // Converting fabricjs font size to that of fpdf.
        $pdf->SetFontSize($list[6] / QTYPE_ESSAYANNOTATE_FONTRATIO);
        $pdf->text($list[0],                                       // X.
        $list[1] + $list[3],                                       // Y  ( base + height).
        $list[4]);                                                 // Text content.
    }

    /**
     * Function to draw a rectangle
     * Given the array containing information related to FPDF Rect object and FPDI file object,
     * it adds the Rect object to FPDI file object
     *
     * @param array $arr the deserialized data array in FPDF Rect format
     * @param object $pdf the fpdi object that has information related to pdf file and annotations.
     */
    public static function draw_rect($arr, $pdf) {
        $list = qtype_essayannotate_parser::parser_rectangle($arr);
        $fill = qtype_essayannotate_parser::process_color($list[4]);
        $pdf->SetFillColor($fill[0], $fill[1], $fill[2]);              // R G B.
        $pdf->SetAlpha(QTYPE_ESSAYANNOTATE_OPACITY);                   // For highlighting.
        $pdf->Rect($list[0],                      // X.
        $list[1],                                 // Y.
        $list[2],                                 // Width.
        $list[3], 'F');                           // Height.
        // F refers to syle fill.
        $pdf->SetAlpha(QTYPE_ESSAYANNOTATE_FULLOPACITY);               // Setting the opacity back to 1.
    }
}
