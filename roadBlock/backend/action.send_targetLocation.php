<?php

// ini_set("display_errors","1");
// error_reporting(E_ALL);

$str = require_once "../basicFunctions.php";

if(!isset($_GET)) exit_json(0);

$DOCROOTPATH = $_SERVER['DOCUMENT_ROOT'];
$DOCROOTBASEPATH = dirname($_SERVER['DOCUMENT_ROOT']);

require_once "$DOCROOTBASEPATH/iplib/ipgenericfunctions.cil14";


$term  = $_GET['term'];
$requestedBy = $_GET['requestedBy'];

if($requestedBy == 'city') {
	$decodedList = $CITYHASH;
}
else if($requestedBy == 'state') {
	$decodedList = $INDIANSTATEHASH;
}

foreach($decodedList as $k => $v) {
	if((stripos($v, $term) !== false)) {
		$responseArray[] = array(
			"idOfThis" => $k,
			"label" => $v,
			"value" => $v,
		); 
	}
}

if($responseArray==null) echo json_encode(array(0));	// return 0 if nothing available
else echo json_encode($responseArray);
exit;