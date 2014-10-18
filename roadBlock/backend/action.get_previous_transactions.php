<?php

/**
* Author : Kunal Bhagawati
*
* This file gets the previously entered data from the database and throws it back to the index page.
* 	- It check if records are already displayed in the index page. If so it takes the last shown record and uses it as the start record.
* 	- If not, it then takes the limit as the start record
* 	- If limit is not provided, it takes 15 as the default limit
* 	- It then sends back the data to the user
* 	
* @params 		$_POST['lastUploadedTime'] 		last uploaded time shown to the user
*           	$_POST['cityID']				optional. cityID for specific records (for search in frontend)
*
* @response 	$response 						0 				: when data could not be fetched
*            									$fetchedRecords	: json encoded array of records starting from ($lastTransactionID-1) to ($limit-1)
*/ 

// ini_set("display_errors","1");
// error_reporting(E_ALL);

$str = require_once "../basicFunctions.php";

$DOCROOTPATH = $_SERVER['DOCUMENT_ROOT'];
$DOCROOTBASEPATH = dirname($_SERVER['DOCUMENT_ROOT']);

if(!require_once $DOCROOTBASEPATH."/iplib/ipsqlclass.cil14") {	// include mysqlclass
	exit_json(array("err" => array("errText" => "ipsqlclass.cil14 not found", "contProc" => 0, "fileName" => __FILE__)));
}

if(isset($_POST['cityID'])) {
	$flagSearch = 1;
	$cityID = $_POST['cityID'];
}
else {
	$flagSearch = 0;
}

// open and set slave connection class
$dbClassSelect		= new db();
$dbClassSelect->connect("S", $DBNAME['IPADMIN'], $DBINFO['USERNAME'], $DBINFO['PASSWORD']);

$limit = 15;

/**
 * Check last uploaded time.
 * - if set use that and add the offset
 * - else set last uploaded time = 0
 */

if($flagSearch) {			// get record for one city
	$whereClause = "CityId = $cityID";
}
else {						// get all records
	if(isset($_POST['pageNo'])) {	// if pagination
		$start = ($_POST['pageNo']-1)*$limit;
	}
	$whereClause = "1 ORDER BY UploadedTime Desc LIMIT $start, $limit";
}


/**
 * get the records for transactionID = $lastUploadedTime to $endUploadedTime
 * The final array should be in the form of 
 * 	[0] => Array
 *       (
 *          [TransactionId] => 4
 *          [CityId] => 4761
 *          [ImagePath] => /home/indiaproperty/www/images/RB_images/asdf_Dafahat.jpg
 *          [StartDate] => 0000-00-00
 *          [ExpireDate] => 0000-00-00
 *          [UploadedTime] => 2014-07-24 01:06:50
 *          [Status] => 1
 *      )
 */
$selectFieldsArray	= array('CityId', 'LandingPageURL', 'GASkipText', 'ImagePath','StartDate','ExpireDate', 'UploadedTime', 'Status');
$selectResource		= $dbClassSelect->select($DBNAME['IPADMIN'],$TABLE['IPROADBLOCKBANNERS'],$selectFieldsArray,$whereClause);
if($selectResource[1] > 0)
	$fetchedRecords = $dbClassSelect->fetchArray('MYSQL_ASSOC', $selectResource[0],1);

$dbClassSelect->dbClose();		// close DB connection


/**
 * get the city name from city list and append to the array
 * the final array should be in the form of 
 * 	[0] => Array
 *       (
 *          [TransactionId] => 4
 *          [CityId] => 4761
 *          [CityName] => Dafahat 	<--- New 
 *          [ImagePath] => /home/indiaproperty/www/images/RB_images/asdf_Dafahat.jpg
 *          [StartDate] => 0000-00-00
 *          [ExpireDate] => 0000-00-00
 *          [UploadedTime] => 2014-07-24 01:06:50
 *          [Status] => 1
 *      )
 *
 */
require_once "$DOCROOTBASEPATH/iplib/ipgenericfunctions.cil14";

foreach ($fetchedRecords as $recordNo => $record) {
	$cityid = $record['CityId'];
	$fetchedRecords[$recordNo]['CityName'] = $CITYHASH[$cityid];
}

exit_json($fetchedRecords);