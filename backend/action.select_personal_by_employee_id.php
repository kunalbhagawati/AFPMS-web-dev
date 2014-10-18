<?php
/**
*  @return status as 1 in case of success 
*  @return status as 0 in case of failure
**/
// Comment below two lines to hide errors
/*ini_set("display_errors", "1");
error_reporting(E_ALL);*/
// ---


$serviceno=(!empty($_POST['serviceno']) ? trim($_POST['serviceno']) : "");
$memberno=(!empty($_POST['memberno']) ? trim($_POST['memberno']) : "");

require_once "vars/dbvars.php";

	try 
	{
		$mysqli = new mysqli($host, $username, $password, "afpms");

		
/*$q = "select * from afpms_personal_service_identity_info where membership_no = 17";
if(!$r = $mysqli->query($q)) {
	throw new Exception(mysqli_error($mysqli), 2);
}
print_r($r->fetch_assoc());
exit;*/

		
		/* check connection */
		if (mysqli_connect_errno()) {
    	throw new Exception(mysqli_connect_error(), 1);
    	}
    	
		$query_search_personal_by_employee_id = "select first_name,last_name,service_no,membership_no,email,amount,rank,group_name,service_type from afpms_circular_info_all_view, afpms_personal_info_all_view where afpms_circular_info_all_view.CategorizationID =afpms_personal_info_all_view.CategorizationID ";

		if (!empty($serviceno )) {
			$query_search_personal_by_employee_id = $query_search_personal_by_employee_id .  "and afpms_personal_info_all_view.service_no = '$serviceno'";
		}
		if (!empty($memberno )) {
			$query_search_personal_by_employee_id = $query_search_personal_by_employee_id . "and afpms_personal_info_all_view.membership_no = '$memberno'";
		}
        
		if(!$res_search_personal_by_employee_id = $mysqli->query($query_search_personal_by_employee_id)) {
			throw new Exception(mysqli_error($mysqli), 2);
		}

		if(mysqli_num_rows($res_search_personal_by_employee_id)==0) {
			echo json_encode(array('status' => 1, 'details'=> 0));
			mysqli_close($mysqliConn);
			exit;
		}

		$resultsArr = array();

		while($row = $res_search_personal_by_employee_id->fetch_assoc()) {
			$resultsArr[] = $row;
		}
		
		$sendArr = array();
		foreach($resultsArr as $rowNo => $row) {
			$row['first_name'] = (!empty($row['first_name']) ? $row['first_name'] : array(0));
			$row['last_name'] = (!empty($row['last_name']) ? $row['last_name'] : array(0));
			$row['service_no'] = (!empty($row['service_no']) ? $row['service_no'] : array(0));
			$row['membership_no'] = (!empty($row['membership_no']) ? $row['membership_no'] : array(0));
			$row['email'] = (!empty($row['email']) ? $row['email'] : array(0));
			$row['amount'] = (!empty($row['amount']) ? $row['amount'] : array(0));
			$row['rank'] = (!empty($row['rank']) ? $row['rank'] : array(0));
			$row['group'] = (!empty($row['group']) ? $row['group'] : array(0));
			$row['service_type'] = (!empty($row['service_type']) ? $row['service_type'] : array(0));
			
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
	$mysqli->close();		
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