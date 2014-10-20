<?php
/**
 * Authors : Janani Iyer / Swati Rao
 *
 * Modification : Kunal Bhagawati
 *
 * Notes :
 * 		Original file in mysqli, converted to PDO/mysql (Data Objects).
 * 		Probably not good code since i'm new to PDO but still better than using mysqli
 */
require_once "vars/dbvars.php";

try{
	$DBH = new PDO("mysql:host=$host;dbname=$DB", $username, $password);
	
	// $DBH->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT );	// Similar to mysql
	$DBH->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );		// issues php warnning but continues

	$ranks = array();
	$qGetRank = $DBH->query("SELECT rank_id, rank from afpms.afpms_circular_rank_info");
	while($row = $qGetRank->fetch(FETCH_ASSOC)) {
		$ranks[$row['rank_id']] = $row['rank'];
	}

	$groups = array();
	$qGetGroup = $DBH->query("SELECT group_id, group_name from afpms.afpms_circular_group_info");
	while($row = $qGetGroup->fetch(FETCH_ASSOC)) {
		$groups[$row['group_id']] = $row['group_name'];
	}

	echo json_encode(array('ranks'=>$ranks, 'groups'=>$groups));
	exit;
}
catch(PDOException $errObj) {
	echo $errObj->getMessage();
	exit;
}