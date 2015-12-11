<?php
	require_once("DB.php");
	
	$conn = new DB();
	
	$conn->connect("localhost","finalProject","root","final");
	$columns = array("title","abstract","citation","current_people","max_people");
	$values = array("2nd is the best!","Like first, but 2nd","Hip Jerry","10","15");
	$id = $conn->insertData("papers",$columns,$values);
	echo $id;

?>