<?php

/**
* Author : Kunal Bhagawati
*
* 
* @params 		$_POST['transactionID'] 	transaction id for record to be updated
*           	$_POST						values of the record to be updated
*           	$_POST['updateType']		1 : change status, 2 : edit record 					
*
* @response 	$response 					0	: not changed/fail
*            								1	: changed/success
*/ 

/*ini_set("display_errors","1");
error_reporting(E_ALL);*/

$str = require_once "../basicFunctions.php";

if(!isset($_POST['cityID'])) 	// if no values recieved
	exit_json(array("err" => array("errText" => "No Post Vals", "contProc" => 0, "fileName" => __FILE__))); 

// MAGIC NUMBERS and variables
define("MAXFILESINACTIVEFOLDER", 300);

$DOCROOTPATH = $_SERVER['DOCUMENT_ROOT'];
$DOCROOTBASEPATH = dirname($_SERVER['DOCUMENT_ROOT']);


$activeTransactionsFolder       = "RBData";
$activeTransactionsFolderPath   = "$DOCROOTPATH/search/$activeTransactionsFolder/";
$inactiveTransactionsFolder     = "inactive";
$inactiveTransactionsFolderPath = $activeTransactionsFolderPath."$inactiveTransactionsFolder/";


if(!require_once $DOCROOTBASEPATH."/iplib/ipsqlclass.cil14")	// include mysqlclass
	exit_json(array("err" => array("errText" => "ipsqlclass.cil14 not found", "contProc" => 0, "fileName" => __FILE__)));


/*ini_set("display_errors","1");
error_reporting(E_ALL);*/

$cityID     = $_POST['cityID'];
$cityName   = $_POST['cityName'];
$status     = $_POST['status'];
$updateType = $_POST['updateType'];

$dbClassInsert = new db();	// create master connection class
$dbClassInsert->connect("M", $DBNAME['IPADMIN'], $DBINFO['USERNAME'], $DBINFO['PASSWORD']);

$dbClassSelect	= new db();	// create slave connection class
$dbClassSelect->connect("S", $DBNAME['IPADMIN'], $DBINFO['USERNAME'], $DBINFO['PASSWORD']);

$selectFieldsArray = array('ImagePath');
$whereClause       = "CityId = $cityID";
$selectResource    = $dbClassSelect->select($DBNAME['IPADMIN'],$TABLE['IPROADBLOCKBANNERS'],$selectFieldsArray,$whereClause);
$fetchedData       = $dbClassSelect->fetchArray('MYSQL_ASSOC', $selectResource[0],1);

$imagePath = $fetchedData[0];
$imagePath = $imagePath['ImagePath'];

// // change status
if($updateType == 1) {
	$fileName = "$cityID.json";
	$file = $activeTransactionsFolderPath.$fileName;
	$fileNamePattern = "/\/$fileName/";

	$url = "http://image.indiaproperty.com/images/RB_images/action.change_status.php";
	$postVals = array('status'=>$status, 'cityID' => $cityID, 'imagePath' => $imagePath);
	$curlReq = curl_init();	
	curl_setopt($curlReq, CURLOPT_HEADER, 0);
	curl_setopt($curlReq, CURLOPT_VERBOSE, 0);
	curl_setopt($curlReq, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curlReq, CURLOPT_POST, true);
	curl_setopt($curlReq, CURLOPT_POSTFIELDS, $postVals);	// put feilds as POST values
	curl_setopt($curlReq, CURLOPT_USERAGENT , "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
	curl_setopt($curlReq, CURLOPT_URL, $url);
	$response = curl_exec($curlReq);	// get response from curl
	curl_close($curlReq);

	$whereClause = "CityId = $cityID";

	if($status == 1) { 			// currently active, change to inactive
		$insertData = array(
			'Status' => 0,
			'ImagePath' => $response,
		);

		$dbClassInsert->update($DBNAME['IPADMIN'], $TABLE['IPROADBLOCKBANNERS'], $insertData, $whereClause);	// insert data first for transaction safety

		if($dbClassInsert->error > 0) {
			$dbClassInsert->dbClose();		// close master connection
			$dbClassSelect->dbClose();		// close slave connection	
			exit_json(array("err" => array("errText" => "DB Insert Fail", "contProc" => 0, "fileName" => __FILE__)));
		}
		unlink($file);
	}

	else if($status == 0) { 	// currently inactive, change to active
		$insertData = array(
			'Status'     => 1,
			'ImagePath' => $response,
		);

		$dbClassInsert->update($DBNAME['IPADMIN'], $TABLE['IPROADBLOCKBANNERS'], $insertData, $whereClause);	// insert data first for transaction safety
		
		if($dbClassInsert->error > 0) {
			$dbClassInsert->dbClose();		// close master connection
			$dbClassSelect->dbClose();		// close slave connection	
			exit_json(array("err" => array("errText" => "DB Insert Fail", "contProc" => 0, "fileName" => __FILE__)));
		}
		
		// get data from database
		$selectFieldsArray = array('LandingPageURL', 'GASkipText', 'GAClickCode', 'GASkipCode', 'StartDate', 'ExpireDate', 'UploadedBy');
		$selectResource    = $dbClassSelect->select($DBNAME['IPADMIN'],$TABLE['IPROADBLOCKBANNERS'],$selectFieldsArray,$whereClause);
		$fetchedRecord = $dbClassSelect->fetchArray('MYSQL_ASSOC', $selectResource[0], 1);
		$fetchedRecord = $fetchedRecord[0];

		$jsonData = array(
			'CityId'         => $cityID,
			'LandingPageURL' => $fetchedRecord['LandingPageURL'],
			'CityName'       => $cityName,	// <- New field
			'ImagePath'      => $response,
			'GASkipText'     => $fetchedRecord['GASkipText'],
			'GAClickCode'    => $fetchedRecord['GAClickCode'],
			'GASkipCode'     => $fetchedRecord['GASkipCode'],
			'StartDate'      => $fetchedRecord['StartDate'], // from dd-mm-yyyy
			'ExpireDate'     => $fetchedRecord['ExpireDate'], // from dd-mm-yyyy
			'UploadedBy'     => $fetchedRecord['UploadedBy'],
		);

		file_put_contents($DOCROOTPATH."/search/RBData/$cityID.json", json_encode($jsonData)); 
	}

	echo_json(1); // success
}

$dbClassInsert->dbClose();		// close master connection
$dbClassSelect->dbClose();		// close slave connection

exit;