<?php
/**
*  @return status as 1 in case of success 
*  @return status as 0 in case of failure
**/
// ini_set("display_errors", "1");
// error_reporting(E_ALL);
// ---

$circularNo = $_POST['circularNo'];
$rank=(!empty($_POST['rank']) ? trim($_POST['rank']) : "");
$group=(!empty($_POST['group']) ? trim($_POST['group']) : "");
$service_type=(!empty($_POST['service_type']) ? trim($_POST['service_type']) : "");


require_once "vars/dbvars.php";


	try 
	{
		$mysqli = new mysqli($host, $username, $password, "afpms");
		
		
		/* check connection */
		if (mysqli_connect_errno()) {
    	throw new Exception(mysqli_connect_error(), 1);
    	}
    	
/*        $query_search_circular_rank_info_by_id = "select rank from afpms_circular_rank_info where rank_id='$rank'";
        
        if(!$res_search_circular_rank_info_by_id = $mysqli->query($query_search_circular_rank_info_by_id)) {
			throw new Exception(mysqli_error($mysqli), 2);
		}

		if(mysqli_num_rows($res_search_circular_rank_info_by_id)==0) {
			throw new Exception(0, 3);
		}
        
        if($row = $res_search_circular_rank_info_by_id->fetch_assoc()) {            
			$rank = $row['rank'];
		}*/
        		
		$query_search_personal_info_by_cir_id = "select first_name,last_name,service_no,membership_no,email,amount,rank,group_name,service_type from afpms_circular_info_all_view, afpms_personal_info_all_view where afpms_circular_info_all_view.CategorizationID =afpms_personal_info_all_view.CategorizationID and afpms_circular_info_all_view.circular_no='$circularNo' ";

		if (!empty($rank )) {
			$query_search_personal_info_by_cir_id = $query_search_personal_info_by_cir_id .  "and afpms_circular_info_all_view.rank = '$rank' ";
		}
		if (!empty($group )) {
			$query_search_personal_info_by_cir_id = $query_search_personal_info_by_cir_id . "and afpms_circular_info_all_view.group_id = '$group' ";
		}

		if (!empty($service_type )) {
			$query_search_personal_info_by_cir_id = $query_search_personal_info_by_cir_id . "and afpms_circular_info_all_view.service_type = '$service_type'";
		}

        print $query_search_personal_info_by_cir_id;
        print "\n";
        
        //echo $query_search_personal_info_by_cir_id;
                
		if(!$res_search_personal_info_by_cir_id = $mysqli->query($query_search_personal_info_by_cir_id)) {
			throw new Exception(mysqli_error($mysqli), 2);
		}

		if(mysqli_num_rows($res_search_personal_info_by_cir_id)==0) {
			throw new Exception(0, 3);
		}

		$resultsArr = array();

		while($row = $res_search_personal_info_by_cir_id->fetch_assoc()) {
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
		if($error->getCode() == 3) {
			echo json_encode(array('status' => 0, 'usrErr'=> 'No results found', 'msg'=>$error->getMessage()));
		}
		$mysqli->close();

	}
	
exit;