<?php

/**
* Author : Kunal Bhagawati
*
* This file recieves the form from the index file and processess it like so :
* 	- It first sends the images to the image server .62 to be uploaded there.
* 	- It then recieves a response from the server.
* 	- if the response == uploadFailed then send a fail back to the user, and no data is physically written to any folder.
* 	- else if the response == successfull, write the rest of the data along with the image paths to the json file.
* 	- finally, enter the data to the DB.
*
* @params 		$_POST[]						form data from the index page
*           	$_POST['citiesListAsString']	selected cities list 
*            	$_FILES['RBImage']				image to be uploaded
*
* @response 	0								Transaction fail. Something went wrong. 
*            	$fetchedRecords					json encoded array of records inserted into the DB 
*
*
* NOTES : Error handling still incomplete
*/ 

// ini_set("display_errors","1");
// error_reporting(E_ALL);

$str = require_once "../basicFunctions.php";

if(!isset($_POST['citiesListAsString']) || !isset($_FILES['RBImage'])) 	// if no values recieved
	exit_json(array("err" => array("errText" => "No Post Vals", "contProc" => 0, "fileName" => __FILE__))); 

/* ===
GLOBAL VARIABLES
=== */

$DOCROOTPATH = $_SERVER['DOCUMENT_ROOT'];
$DOCROOTBASEPATH = dirname($_SERVER['DOCUMENT_ROOT']);

if(!require_once $DOCROOTBASEPATH."/iplib/ipsqlclass.cil14")	// include mysqlclass
	exit_json(array("err" => array("errText" => "ipsqlclass.cil14 not found", "contProc" => 0, "fileName" => __FILE__)));

if(!require_once "$DOCROOTBASEPATH/iplib/ipgenericfunctions.cil14") 
	exit_json(array("err" => array("errText" => "ipgenericfunctions.cil14 not found", "contProc" => 0, "fileName" => __FILE__)));


$dbClassInsert = new db();	// use ipsql class
$dbClassInsert->connect("M", $DBNAME['IPADMIN'], $DBINFO['USERNAME'], $DBINFO['PASSWORD']);
$dbClassSelect = new db();
$dbClassSelect->connect("S", $DBNAME['IPADMIN'], $DBINFO['USERNAME'], $DBINFO['PASSWORD']);

// ini_set("display_errors","1");
// error_reporting(E_ALL);

$currentUserID 		= $_POST['currentUserID'];
$landingPage        = $_POST['landingPage']; 
$startDate          = $_POST['startDate']; 
$expireDate         = $_POST['expireDate'];
$GASkipText         = $_POST['GASkipText']; 
$citiesListAsString = $_POST['citiesListAsString'];		// "cityid1,cityid2,cityid3..."

$imageExt           = end(explode(".", $_FILES['RBImage']['name']));	// get image extention
$citiesArray        = array();	// cities neccessary to the transaction


// get cities necessary for us
$cityIDList = explode(",", $citiesListAsString);
$cityHash = $CITYHASH;

foreach($cityIDList as $cityID) {
	$citiesArray[$cityID] = $cityHash[$cityID];
	$citiesArrayImgUpload[$cityID]['name'] = $cityHash[$cityID];

	$selectFieldsArray = array('CityId', 'Status');
	$whereClause       = "CityId = $cityID";
	$selectResource    = $dbClassSelect->select($DBNAME['IPADMIN'],$TABLE['IPROADBLOCKBANNERS'],$selectFieldsArray,$whereClause);
	$fetchData = $dbClassSelect->fetchArray('MYSQL_ASSOC', $selectResource[0],1);

	if(($fetchedData == null) || ($fetchedData[0]['Status'] == 1)) {
		$citiesArrayImgUpload[$cityID]['status'] = 1;
	}
	else if($fetchedData[0]['Status'] == 0) { 
		$citiesArrayImgUpload[$cityID]['status'] = 0;
	}
}

$fetchedData = null;

/**
 * send the images to be uploaded. 
 * @recieved	 	$response 		0 					: uploading failed
 *									'serialized string'	: serialized array of the format 'cityid' => 'imagePath' 
 */	//<--- BEGIN
$postVals = array('citiesArrayImgUpload'=>serialize($citiesArrayImgUpload), 'GASkipText' => $GASkipText, 'imageExt' => $imageExt );
$postVals['RBImage'] = '@'.$_FILES['RBImage']['tmp_name'];

$curlReq = curl_init();	
$url = "http://image.indiaproperty.com/images/RB_images/action.upload_images.php";
curl_setopt($curlReq, CURLOPT_HEADER, 0);
curl_setopt($curlReq, CURLOPT_VERBOSE, 0);
curl_setopt($curlReq, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curlReq, CURLOPT_POST, true);
curl_setopt($curlReq, CURLOPT_POSTFIELDS, $postVals);	// put feilds as POST values
curl_setopt($curlReq, CURLOPT_USERAGENT , "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
curl_setopt($curlReq, CURLOPT_URL, $url);
$response = curl_exec($curlReq);	// get response from curl
curl_close($curlReq);

$cityImagePathMap = unserialize($response);

if(array_key_exists("err", $cityImagePathMap))	{	// if files not uploaded successfully   // does not work if does not recieve as serialized. Cant figure out
	$dbClassInsert->dbClose();		// close master connection
	$dbClassSelect->dbClose();		// close slave connectio 
	exit_json(array("err" => array("errText" => "Could Not Upload Images", "contProc" => 0, "fileName" => __FILE__, "prevErr" => $cityImagePathMap)));
}
// ---> END


// Take all post values along with the image paths and saves them in the database 
// <--- BEGIN
$GAClickCode = "_gaq.push(['_trackEvent','SearchRes Page', '$GASkipText $cityName', 'Click on Banner']);";
$GASkipCode = "_gaq.push(['_trackEvent','SearchRes Page', '$GASkipText $cityName', 'Click on Skip this Banner Ad Link']);";

foreach($citiesArray as $cityID => $cityName) {
	$insertData = null;
	$selectFieldsArray = array('CityId');
	$whereClause       = "CityId = $cityID";
	$selectResource    = $dbClassSelect->select($DBNAME['IPADMIN'],$TABLE['IPROADBLOCKBANNERS'],$selectFieldsArray,$whereClause);
	$fetchData = $dbClassSelect->fetchArray('MYSQL_ASSOC', $selectResource[0],1);

	$insertData = array(
		'LandingPageURL' => $landingPage,
		'ImagePath'      => $cityImagePathMap[$cityID],
		'GASkipText'     => $GASkipText,
		'GAClickCode'    => $GAClickCode,
		'GASkipCode'     => $GASkipCode,
		'StartDate'      => date('Y-m-d',strtotime($startDate)),	 // from dd-mm-yyyy
		'ExpireDate'     => date('Y-m-d',strtotime($expireDate)),	 // from dd-mm-yyyy
		'UploadedBy'     => $currentUserID,
	);

	if($fetchData == null || $fetchData == 0) {				// <- !!! CHECK EXTENSIVLY
		$insertData['CityId'] = $cityID;
		$dbClassInsert->insert($DBNAME['IPADMIN'], $TABLE['IPROADBLOCKBANNERS'], $insertData);

		if($dbClassInsert->error > 0) {
			$dbClassInsert->dbClose();		// close master connection
			$dbClassSelect->dbClose();		// close slave connection	
			exit_json(array("err" => array("errText" => "DB Insert Fail", "contProc" => 0, "fileName" => __FILE__)));
		}
	}
	else {
		$dbClassInsert->update($DBNAME['IPADMIN'], $TABLE['IPROADBLOCKBANNERS'], $insertData, $whereClause);

		if($dbClassInsert->error > 0) {
			$dbClassInsert->dbClose();		// close master connection
			$dbClassSelect->dbClose();		// close slave connection	
			exit_json(array("err" => array("errText" => "DB Update Fail", "contProc" => 0, "fileName" => __FILE__)));
		}
	}
}


// Now take all values and dump them to a json file in .63/search/RBData. File name is cityid.json 
// <--- BEGIN
foreach($citiesArray as $cityID => $cityName) {
	$jsonData = array(
		'CityId'         => $cityID,
		'LandingPageURL' => $landingPage,
		'CityName'       => $cityName,	// <- New field
		'ImagePath'      => $cityImagePathMap[$cityID],
		'GASkipText'     => $GASkipText,
		'GAClickCode'    => $GAClickCode,
		'GASkipCode'     => $GASkipCode,
		'StartDate'      => date('Y-m-d', strtotime($startDate)), // from dd-mm-yyyy
		'ExpireDate'     => date('Y-m-d', strtotime($expireDate)), // from dd-mm-yyyy
		'UploadedBy'     => $currentUserID,
	);

	if(!file_put_contents($DOCROOTPATH."/search/RBData/$cityID.json", json_encode($jsonData))) {
		$dbClassInsert->dbClose();		// close master connection
		$dbClassSelect->dbClose();		// close slave connection	
		exit_json(array("err" => array("errText" => "JSON file write failed", "contProc" => 0, "fileName" => __FILE__))); 	// if no values recieved

		// error handling here
		// use echo since master and slave not closed. else close here and exit
	}
	unset($jsonData);
}

// ---> END


// get last inserted records. !!! Fetching from master to avoid slave delay. Inform Mohan once
$selectFieldsArray	= array('CityId', 'LandingPageURL', 'GASkipText', 'ImagePath','StartDate','ExpireDate', 'UploadedTime', 'Status');
$whereClause = "CityId IN ($citiesListAsString) ORDER BY UploadedTime Desc";
$selectResource		= $dbClassInsert->select($DBNAME['IPADMIN'],$TABLE['IPROADBLOCKBANNERS'],$selectFieldsArray,$whereClause);
if($selectResource[1] > 0)
	$fetchedRecords = $dbClassInsert->fetchArray('MYSQL_ASSOC', $selectResource[0],1);


// put cityname in the return array
foreach ($fetchedRecords as $recordNo => $record) {
	$cityid = $record['CityId'];
	$fetchedRecords[$recordNo]['CityName'] = $CITYHASH[$cityid];
}

$dbClassInsert->dbClose();		// close master connection
$dbClassSelect->dbClose();		// close slave connection	

exit_json($fetchedRecords);

exit; // exit file