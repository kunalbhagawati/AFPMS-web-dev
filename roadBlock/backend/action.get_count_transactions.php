<?php

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

$selectFieldsArray	= array('COUNT(1)');
$whereClause = '1';
$selectResource		= $dbClassSelect->select($DBNAME['IPADMIN'],$TABLE['IPROADBLOCKBANNERS'],$selectFieldsArray,$whereClause);
if($selectResource[1] > 0)
	$fetchData = $dbClassSelect->fetchArray('MYSQL_ASSOC', $selectResource[0],1);

echo_json($fetchData[0]['COUNT(1)']);

$dbClassSelect->dbClose();		// close DB connection

exit;