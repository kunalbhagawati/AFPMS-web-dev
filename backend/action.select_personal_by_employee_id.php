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

$serviceno=(!empty($_POST['serviceno']) ? trim($_POST['serviceno']) : "");
$memberno=(!empty($_POST['memberno']) ? trim($_POST['memberno']) : "");

require_once "vars/dbvars.php";
try {
	$DBH = new PDO("mysql:host=$host;dbname=$DB", $username, $password);

	/* 	// kunalB <- No idea whats going on here. Ask Swati / Janani
	$q = "select * from afpms_personal_service_identity_info where membership_no = 17";
	if(!$r = $mysqli->query($q)) {
	throw new Exception(mysqli_error($mysqli), 2);
	}
	print_r($r->fetch_assoc());
	exit;
	*/
	
	$qSatement = "select first_name,last_name,service_no,membership_no,email,amount,rank,group_name,service_type from afpms_circular_info_all_view, afpms_personal_info_all_view where afpms_circular_info_all_view.CategorizationID =afpms_personal_info_all_view.CategorizationID ";

	if (!empty($serviceno )) {
		$qSatement .= "and afpms_personal_info_all_view.service_no = '$serviceno'";
	}
	if (!empty($memberno )) {
		$qSatement .= "and afpms_personal_info_all_view.membership_no = '$memberno'";
	}

	$query_search_personal_by_employee_id = $DBH->query($qSatement);

	$resultsArr = array();
	$resultsArr = $query_search_personal_by_employee_id->fetchAll(PDO::FETCH_ASSOC);

	if(count($resultsArr)==0) {
		echo json_encode(array('status' => 1, 'details'=> 0));
		exit;
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
}
catch(Exception $error)
{
	if($error->getCode() == 1) {
		echo json_encode(array('status' => 0, 'usrErr'=> 'Sorry, we could not connect to the Database at the moment. Please contact the developers to have a look?', 'msg'=> $error->getMessage()));
	}
	if($error->getCode() == 2) {
		echo json_encode(array('status' => 0, 'usrErr'=> 'Sorry, something went wrong.. Please contact the developers to have a look?', 'msg'=>$error->getMessage()));
	}	
	$mysqli->close();
}
	
exit;