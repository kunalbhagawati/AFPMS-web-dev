<?php

ini_set("display_errors","1");
error_reporting(E_ALL);

$str = require_once "../basicFunctions.php";

$DOCROOTPATH = $_SERVER['DOCUMENT_ROOT'];
$DOCROOTBASEPATH = dirname($_SERVER['DOCUMENT_ROOT']);
require_once "$DOCROOTBASEPATH/iplib/ipgenericfunctions.cil14";

$stateID  = $_GET['stateID'];

$stateCityMap = $STATECITYMAPHASH;	// get statecitymap
$cityHash = $CITYHASH;	// get cityhash

$cityIDList = $stateCityMap[$stateID];

foreach ($cityIDList as $cityID) {
	$responseArray[] = array(
		'idOfThis' => $cityID,
		'label' => $cityHash[$cityID],
		'value' => $cityHash[$cityID],
	); 
}

if($responseArray == null) {	// just in case
	echo json_encode(array("Error", "No cities found"));
}
else {
	echo json_encode($responseArray);
}

exit;	// close file