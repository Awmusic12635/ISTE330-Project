<?php
	require_once("DB.php");
	
	$conn = new DB();
	
	$conn->connect("host","dbname","username","password");
	$fields = array("field1","field2"); // or array("*") for all of them
	$data = $conn->getData("tablename",$fields); //optional where statement i.e. getData("tablename",$fields,"something=something and nothing=nothing")
	var_dump($data);
	

?>
