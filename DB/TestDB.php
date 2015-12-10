<?php
	require_once("DB.php");
	
	$conn = new DB();
	
	$conn->connect("localhost","finalProject","root","final");
	$access = "test";
	$columns = array("access","uid");
	$values = array($access,$data[0]["user_id"]);
	echo $conn->insertData("sessions",$columns,$values);
	

?>