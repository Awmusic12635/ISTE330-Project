<?php
	require_once("DB.php");
	
	$conn = new DB();
	
	$conn->connect("localhost","finalProject","root","final");
	$columns = array("title","abstract","citation","current_people","max_people");
	$values = array("What is life?","The abstract of life","Professor Alex","1","3");
	$conn->updateData("papers",$columns,$values,"paper_id='1'")
	

?>