<?php

require_once "vars/dbvars.php";

$mysqlConn = mysqli_connect($host, $username, $password, $DB);
if(mysqli_connect_errno()) {
	throw new Exception(mysqli_connect_error());
}

$qGetRank = "SELECT rank_id, rank from afpms.afpms_circular_rank_info";
$resGetRank = mysqli_query($mysqlConn, $qGetRank);

$ranks = array();
while($row = mysqli_fetch_assoc($resGetRank)) {
	$ranks[$row['rank_id']] = $row['rank'];
}

$qGetGroup = "SELECT group_id, group_name from afpms.afpms_circular_group_info";
$resGetGroup = mysqli_query($mysqlConn, $qGetGroup);

$groups = array();
while($row = mysqli_fetch_assoc($resGetGroup)) {
	$groups[$row['group_id']] = $row['group_name'];
}

echo json_encode(array('ranks'=>$ranks, 'groups'=>$groups));
exit;