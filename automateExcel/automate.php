<?php
/**
 * Author : Kunal Bhagawati
 * 
 * This file automates the data entry from excell files 
 */

ini_set("display_errors", "1"); error_reporting(E_ALL);
ini_set('memory_limit', '128M');


$DOCUMENTROOT = $_SERVER['DOCUMENT_ROOT']."/";
$moduleFolder = "afpms";
$moduleFolderPath = $DOCUMENTROOT.$moduleFolder."/";

$moduleBackendFolder = "backend";
$moduleBackendFolderPath = $moduleFolderPath.$moduleBackendFolder."/";
$miscFilesFolder = "MISC";
$miscFilesFolderPath = $moduleFolderPath.$miscFilesFolder."/";
$vendorFilesFolder = "vendor";
$vendorFilesFolderPath = $moduleFolderPath.$vendorFilesFolder."/";
$PHPExcelFolder = "PHPExcel";
$PHPExcelFolderPath = $vendorFilesFolderPath.$PHPExcelFolder."/";


require_once $PHPExcelFolderPath."Classes/PHPExcel.php";

class MyReadFilter implements PHPExcel_Reader_IReadFilter
{
	private $_startRow = 0;

	private $_endRow = 0;

	private $_columns = array();

	public function __construct($startRow, $endRow, $columns) {
		$this->_startRow	= $startRow;
		$this->_endRow		= $endRow;
		$this->_columns		= $columns;
	}

	public function readCell($column, $row, $worksheetName = '') {
		if ($row >= $this->_startRow && $row <= $this->_endRow) {
			if (in_array($column,$this->_columns)) {
				return true;
			}
		}
		return false;
	}
}


$inputFileName = $miscFilesFolderPath."data_for_Tamilnadu_and_Pondicherry.xlsx";

$inputFileType = PHPExcel_IOFactory::identify($inputFileName); 		// Identify the type of $inputFileName
$objReader = PHPExcel_IOFactory::createReader($inputFileType); 		// Create a new Reader of the type that has been identified


$filterSubset = new MyReadFilter(2,3,range('A','B'));


$objReader->setReadFilter($filterSubset);
$objPHPExcel = $objReader->load($inputFileName); 					// Load $inputFileName to a PHPExcel Object

$subsetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
var_dump($subsetData);

exit;