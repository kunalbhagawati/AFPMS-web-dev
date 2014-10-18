<?php

// File to insert the veteran details in the respectve table

// Comment below two lines to hide errors
ini_set("display_errors", "1");
error_reporting(E_ALL);
// ---

$fname = $_POST['fname'];
$lname = $_POST['lname'];
$serviceno = $_POST['serviceno'];
$membershipno = $_POST['membershipno'];

$address1 = $_POST['address1'];
$address2 = $_POST['address2'];
$street= $_POST['street'];
$city = $_POST['city'];
$state = $_POST['state'];
$pincode = $_POST['pincode'];

$numsAvailable = array();
if(!($_POST['mobile']   == '' || $_POST['mobile'] == null)) {
	$numsAvailable[] =  array('num'=> $_POST['mobile'],'type'=> 'MOB');
}
if(!($_POST['ofcode']   == '' || $_POST['ofcode'] == null)) {
	$numsAvailable[] =  array('num'=> $_POST['ofcode'],'type'=> 'OFC');
}
if(!($_POST['ofcphone'] == '' || $_POST['ofcphone'] == null)) {
	$numsAvailable[] =  array('num'=> $_POST['ofcphone'],'type'=> 'OFLL');
}
if(!($_POST['rescode']  == '' || $_POST['rescode'] == null)) {
	$numsAvailable[] =  array('num'=> $_POST['rescode'],'type'=> 'HMC');
}
if(!($_POST['resphone'] == '' || $_POST['resphone'] == null))	{
	$numsAvailable[] =  array('num'=> $_POST['resphone'],'type'=> 'HMLL');
}

$email= $_POST['email'];

$trade = $_POST['trade'];
$dobirth = $_POST['dobirth'];
$doexpire = $_POST['doexpire'];
$doenroll = $_POST['doenroll'];
$dodischarge= $_POST['dodischarge'];

$awards = $_POST['awards'];
$tnmembno= $_POST['tnmemno'];
$rank= $_POST['rank'];
$groupid = $_POST['group'];

// if date of expiry is null - the service type is family (i.e 2) else indivaidual(i.e 1)

if (($doexpire == '') || ($doexpire == null)) {
	$servicetype = 1;
	$doexpire = "0000-00-00";

} else { 
	$servicetype = 2;
}

//echo $servicetype;

// calculation of service period 
$objDoenroll = date_create($doenroll);
$objDischarge = date_create($dodischarge);

$serviceperiod = $objDoenroll->diff($objDischarge);
$year = $serviceperiod-> format("%y");
$month = $serviceperiod-> format("%m");
if ( $month <= 5) {
	$month= 0;
} else {
	$month = 5;
}
$serviceperiod = $year.".".$month;

require_once "vars/dbvars.php";

try {
	$conn = mysqli_connect($host, $username, $password, "afpms");
		
	if(mysqli_connect_errno()) {
		throw new Exception(mysqli_connect_error(), 1);
	}
	
	// Set autocommit to off
	mysqli_autocommit($conn,FALSE);
	
	// INSERT PERSONAL DETAILS
	$sql = "INSERT INTO afpms_personal_info (first_name, last_name, address1, address2, street, city, state, pincode, email, date_of_birth, date_of_expiry) VALUES ('$fname', '$lname', '$address1', '$address2', '$street', '$city', '$state', '$pincode', '$email', '$dobirth', '$doexpire')";
	
	if(!mysqli_query($conn, $sql)) {
		throw new Exception("Could not insert into 'afpms_personal_info' [ ".mysqli_error($conn)." ]", 2);	
	}

	$per_id = mysqli_insert_id($conn);
	
	//INSERT PHONE NUMBER (ENUM)
	foreach ($numsAvailable as $key => $value) {
		if(!mysqli_query($conn, "INSERT INTO afpms_personal_phone_info (afpms_personal_info_id, digit, type) VALUES ($per_id, {$value['num']}, '{$value['type']}')")) {
			throw new Exception("Could not insert into 'afpms_personal_phone_info' QUERY : INSERT INTO afpms_personal_phone_info (afpms_personal_info_id, digit, type) VALUES ('$per_id', {$value['num']}, '{$value['type']}')", 2);	
		}
	}

	//INSERT SERVICE INFORMATION - FIRST get the "circular_categorization_info_id"

	$query_afpms_circular_categorization_info = "select id from  afpms_circular_categorization_info where rank = '$rank' and group_id = '$groupid' and service_period = '$serviceperiod' and service_type = '$servicetype'";

	if(!$res_afpms_circular_categorization_info_id = mysqli_query($conn, $query_afpms_circular_categorization_info)) {
		throw new Exception(mysqli_error($mysqli), 2);
	}

	if(mysqli_num_rows($res_afpms_circular_categorization_info_id)==0) {
		throw new Exception(mysqli_error($conn), 3);
	}

	while($row = mysqli_fetch_assoc($res_afpms_circular_categorization_info_id)) {
		$afpms_circular_categorization_info_id = $row['id'];
	}
				
		
	//INSERT SERVICE INFORMATION
	if(!mysqli_query($conn, "INSERT INTO afpms_personal_service_info (afpms_personal_info_id, afpms_circular_categorization_info_id, awards, tn_membership_no, trade) VALUES ('$per_id', '$afpms_circular_categorization_info_id', '$awards', $tnmembno, '$trade')")) {
		throw new Exception("Could not insert into 'afpms_personal_service_info'", 2);	
	}

	//INSERTION FOR UNIQUE IDENTITY OF VETERAN
	if(!mysqli_query($conn, "INSERT INTO afpms_personal_service_identity_info (afpms_personal_info_id, service_no, membership_no) VALUES ('$per_id', '$serviceno', '$membershipno')")) {
		throw new Exception("Could not insert into 'afpms_personal_service_identity_info'", 2);	
	}

	//INSERT DURATION DETAIL
	if(!mysqli_query($conn, "INSERT INTO afpms_personal_service_duration_info (afpms_personal_info_id, date_of_discharge, date_of_enrollment, service_period) VALUES ('$per_id', '$dodischarge', '$doenroll', '$serviceperiod')")) {
		throw new Exception("Could not insert into 'afpms_personal_service_duration_info'", 2);	
	}

	// Commit transaction
	if(!mysqli_commit($conn)) {
		throw new Exception("Could not commit transaction", 1);
	}

	echo json_encode(array('status'=>1));

	mysqli_close($conn);
}

catch(Exception $error) {
	if($error->getCode() == 1) {
		echo "Could not connect to DB :: ".$error->getMessage();
	}
	else {
		if($error->getCode() == 2) {
			echo "Query Error :: ".$error->getMessage();
		}
		if($error->getCode() == 3) {
			echo json_encode(array(0));
		}
		mysqli_close($conn);
	}
}

exit;