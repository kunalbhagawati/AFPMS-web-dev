<?php
/**
 * Author : Kunal Bhagawati
 * 
 * This file automates the data entry from excell files 
 */

ini_set("display_errors", "1"); error_reporting(E_ALL);
ini_set('memory_limit', '-1');

// $DOCUMENTROOT = "/var/www/";
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

$sheetname = "Data for Tamilnadu and Pondiche";

require_once $PHPExcelFolderPath."Classes/PHPExcel.php";
PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;

class chunkReadFilter implements PHPExcel_Reader_IReadFilter
{
	private $_startRow = 0;
	private $_endRow = 0;

	/**  We expect a list of the rows that we want to read to be passed into the constructor  */
	public function __construct($startRow, $chunkSize) {
		$this->_startRow	= $startRow;
		$this->_endRow		= $startRow + $chunkSize;
	}

	public function readCell($column, $row, $worksheetName = '') {
		//  Only read the heading row, and the rows that were configured in the constructor
		if (($row == 1) || ($row >= $this->_startRow && $row < $this->_endRow)) {
			return true;
		}
		return false;
	}
}

$inputFileName = $miscFilesFolderPath."data_for_Tamilnadu_and_Pondicherry.xlsx";

$inputFileType = PHPExcel_IOFactory::identify($inputFileName); 		// Identify the type of $inputFileName
$objReader = PHPExcel_IOFactory::createReader($inputFileType); 		// Create a new Reader of the type that has been identified

$filterSubset = new chunkReadFilter(2,1);

$objReader->setReadDataOnly(true);
$objReader->setLoadSheetsOnly($sheetname);
$objReader->setReadFilter($filterSubset);
$objPHPExcel = $objReader->load($inputFileName); 					// Load $inputFileName to a PHPExcel Object

$subsetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
echo "<pre>";
print_r($subsetData);

unset($objPHPExcel);

exit;