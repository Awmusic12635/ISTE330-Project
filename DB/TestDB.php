<?php
	require_once("DB.php");
	
	$conn = new DB();
	
	$conn->connect("localhost","finalProject","root","final")
	$fields = array("user_id","username","password");
	$data = $conn->getData("users",$fields,"username='AWacker'");
	echo ($data);
	

?>