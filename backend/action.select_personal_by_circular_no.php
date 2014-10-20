<?php
/**
 * Authors : Janani Iyer / Swati Rao
 * 
 * @return status as 1 in case of success 
 * @return status as 0 in case of failure
 *
 * Modification : Kunal Bhagawati
 *
 * Notes :
 * 		Original file in mysqli, converted to PDO/mysql (Data Objects).
 * 		Probably not good code since i'm new to PDO but still better than using mysqli
 */

ini_set("display_errors", "1"); error_reporting(E_ALL);

// get POST values
$circularNo = $_POST['circularNo'];
$rank=(!empty($_POST['rank']) ? trim($_POST['rank']) : "");
$group=(!empty($_POST['group']) ? trim($_POST['group']) : "");
$service_type=(!empty($_POST['service_type']) ? trim($_POST['service_type']) : "");


require_once "vars/dbvars.php";

try 
{
	$DBH = new PDO("mysql:host=$host;dbname=$DB", $username, $password);
	
	// $DBH->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT );	// Similar to mysql
	$DBH->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );		// issues php warnning but continues

/* 	// kunalB <- No idea whats going on here. Ask Swati / Janani
	$query_search_circular_rank_info_by_id = "select rank from afpms_circular_rank_info where rank_id='$rank'";
    
    if(!$res_search_circular_rank_info_by_id = $mysqli->query($query_search_circular_rank_info_by_id)) {
		throw new Exception(mysqli_error($mysqli), 2);
	}

	if(mysqli_num_rows($res_search_circular_rank_info_by_id)==0) {
		throw new Exception(0, 3);
	}
    
    if($row = $res_search_circular_rank_info_by_id->fetch_assoc()) {            
		$rank = $row['rank'];
	}*/
    		
	$qSatement = "select first_name,last_name,service_no,membership_no,email,amount,rank,group_name,service_type from afpms_circular_info_all_view, afpms_personal_info_all_view where afpms_circular_info_all_view.CategorizationID =afpms_personal_info_all_view.CategorizationID and afpms_circular_info_all_view.circular_no='$circularNo' ";

	if (!empty($rank )) {
		$qSatement .= "and afpms_circular_info_all_view.rank = '$rank' ";
	}
	if (!empty($group )) {
		$qSatement .= "and afpms_circular_info_all_view.group_id = '$group' ";
	}

	if (!empty($service_type )) {
		$qSatement .= "and afpms_circular_info_all_view.service_type = '$service_type'";
	}

	$query_search_personal_info_by_cir_id = $DBH->query($qSatement);

	$resultsArr = array();
	$resultsArr = $query_search_personal_info_by_cir_id->fetchAll(PDO::FETCH_ASSOC);
	
	if(count($resultsArr)==0) {
		throw new Exception(0, 3);
	}

	$sendArr = array();
	foreach($resultsArr as $rowNo => $row) {
		$row['first_name'] = (!empty($row['first_name']) ? $row['first_name'] : '');
		$row['last_name'] = (!empty($row['last_name']) ? $row['last_name'] : '');
		$row['service_no'] = (!empty($row['service_no']) ? $row['service_no'] : '');
		$row['membership_no'] = (!empty($row['membership_no']) ? $row['membership_no'] : '');
		$row['email'] = (!empty($row['email']) ? $row['email'] : '');
		$row['amount'] = (!empty($row['amount']) ? $row['amount'] : '');
		$row['rank'] = (!empty($row['rank']) ? $row['rank'] : '');
		$row['group'] = (!empty($row['group']) ? $row['group'] : '');
		$row['service_type'] = (!empty($row['service_type']) ? $row['service_type'] : '');

		$sendArr[] = array(
		'first_name' => $row['first_name'],
		'last_name' => $row['last_name'],
		'service_no' => $row['service_no'],
		'membership_no' => $row['membership_no'],
		'email' => $row['email'],
		'amount' => $row['amount'],
		'rank' => $row['rank'],
		'group' => $row['group_name'],
		'service_type' => $row['service_type'],
		);
	}

echo json_encode(array('status' => 1, 'details'=> $sendArr));
$DBH = null;
}
catch(Exception $error) {
	if($error->getCode() == 3) {
		echo json_encode(array('status' => 0, 'usrErr'=> 'No results found', 'msg'=>$error->getMessage()));
	}
}
catch(PDOException $error) {	// catch all PDO errors

}
	
exit;