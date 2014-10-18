<?php
/**
 * DB queries in this file need to be converted to PDO
 */
// Comment below two lines to hide errors
// ini_set("display_errors", "1");
// error_reporting(E_ALL);
// ---

$circularNo = $_POST['circularNo'];
$circular_issue_date = "{$_POST['circular_issue_date']} 00:00:00";
$circular_effective_date = $_POST['circular_effective_date'];
$circular_status = $_POST['circular_status'];

$rank = $_POST['rank'];
$group = $_POST['group'];
$service_period = $_POST['service_period'];
$service_type = $_POST['service_type'];

$amount = $_POST['amount'];

require_once "vars/dbvars.php";

try {
	$mysqliConn = mysqli_connect($host, $username, $password, $DB);
	
	/* check connection */
	if (mysqli_connect_errno()) {
	throw new Exception(mysqli_connect_error(), 0);
	}

	/* turn autocommit off */
	mysqli_autocommit($mysqliConn, FALSE);

	$query_afms_circular_info = "INSERT INTO afpms_circular_info ( `circular_no`, `circular_issue_date`, `circular_effective_date`, `circular_status`) VALUES ('$circularNo', '$circular_issue_date', '$circular_effective_date', '$circular_status')";

	if(!mysqli_query($mysqliConn, $query_afms_circular_info)) {
		throw new Exception("Could not insert record [ ".mysqli_error($mysqliConn)." ]", 1);
	}
	
	$afpms_circular_info_id = mysqli_insert_id($mysqliConn);

	$query_afpms_circular_categorization_info = "select id, group_id from  afpms_circular_categorization_info where rank = '$rank' and group_id = '$group' and service_period = '$service_period' and service_type = '$service_type'";

	if(!$res_afpms_circular_categorization_info_id = mysqli_query($mysqliConn, $query_afpms_circular_categorization_info)) {
		throw new Exception("Could not fetch record [ ".mysqli_error($mysqliConn)." ]", 1);
	}

	if(mysqli_num_rows($res_afpms_circular_categorization_info_id)==0) {
		throw new Exception("No records found from 'afpms_circular_categorization_info'", 1);
	}

	while($row = mysqli_fetch_assoc($res_afpms_circular_categorization_info_id)) {
		$afpms_circular_categorization_info_id = $row['id'];
	}
			
	$query_afpms_circular_amount_info = "INSERT INTO afpms_circular_amount_info (`afpms_circular_info_id`, `afpms_circular_categorization_info_id`, `amount`) VALUES ('$afpms_circular_info_id','$afpms_circular_categorization_info_id','$amount')";

	if(!mysqli_query($mysqliConn, $query_afpms_circular_amount_info)) {
		throw new Exception("Could not insert record [ ".mysqli_error($mysqliConn)." ]", 1);
	}

	if(!mysqli_commit($mysqliConn)) {
		throw new Exception("Could not commit transaction", 1);
	}

	echo json_encode(array('status'=>1));

	mysqli_close($mysqliConn);
}
catch(Exception $error)
{
	if($error->getCode() == 0) {
		echo json_encode(array('status' => 0, 'usrErr'=> 'Sorry, we could not connect to the Database at the moment. Please contact the developers to have a look?', 'msg'=> $error->getMessage()));
	}
	if($error->getCode() == 1) {
		echo json_encode(array('status' => 0, 'usrErr'=> 'Sorry, something went wrong.. Please contact the developers to have a look?', 'msg'=>$error->getMessage()." | Line ".$error->getLine()));
	}

	mysqli_close($mysqliConn);
	exit;
}

exit;