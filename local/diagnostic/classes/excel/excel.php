<?php

namespace local_diagnostic\excel;

require_once($CFG->libdir.'/excellib.class.php');


/**
 * A wrapper for MoodleExcelWorkbook class.
 *
 * @package    report
 * @subpackage learningcompletion
 * @copyright 2019 onwards The Open University of Israel
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class excel extends \MoodleExcelWorkbook {
    public function add_worksheet($name = '') {
        return new MoodleExcelWorksheet($name, $this->objspreadsheet);
    }
}

/**
 * A wrapper for MoodleExcelWorksheet class.
 *
 * @package    report
 * @subpackage learningcompletion
 * @copyright 2019 onwards The Open University of Israel
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class MoodleExcelWorksheet extends \MoodleExcelWorksheet
{
    /**
     * Is the worksheet instance of PHPExcel or PhpSpreadsheet.
     * @var bool
     */
    protected $isPHPLibrary;

    public function __construct($name, $workbook)
    {
        parent::__construct($name, $workbook);
        $classname = get_class($this->worksheet);
        $this->isPHPLibrary = strpos($classname, 'PHPExcel') === 0 || strpos($classname, 'PhpSpreadsheet') === 0;
    }

    /**
     * Set spreadsheet right to left.
     * @param boolean $value
     * @return boolean
     */
    public function setRightToLeft($value = false)
    {
        if ($this->isPHPLibrary) {
            $this->worksheet->setRightToLeft($value);
            return true;
        }
        return false;
    }
}