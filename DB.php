<?php
	class DB{
		private $dbh;
		
		function connect($host,$db,$username,$password){
			$dbh = new PDO("mysql:host=$hostname;dbname=$db",$username,$password);
		}
		function disconnect(){
			$dbh->close();
		}
		function commaSeparate($array,$array2=null){
			$commaList="";
			if($array2==null){
				$commaList = implode(', ', $array);
			}else{
				$new_array = array_merge($array1,$array2);	
				$array_size=count($new_array);
				for($x=0;$x<$new_array;$x+=2){
					$commaList+=$new_array[$x] . "=" . $new_array[$x+1] . ",";
				}
			}
			$commaList = trim($commaList, ',');	
			return $commaList;
		}
		function buildQuery($type,$table,$columns,$where=null,$values=null){
			$query="";
			
			if($query=="select"){
				$query+="select ";
				$query+=commaSeparate($columns);
				$query+=" from " . $table;
				
				if($where !=null){
					$query += "where " . $where;
				}
				return $query;
			}else if ($type=="insert"){
				$query+="insert into " . $table . " ";
				$query+="(" . commaSeparate($columns) . ") values (" . commaSeparate($values) . ")";
				return $query;
			}else if($type == "update"){
				$query+="update " . $table . " set ". commaSeparate($columns,$values) . $where;
				 return $query;
			}else{
				$query+="delete from " . $table;
				
				if($where !=null){
					$query+=" where " . $where; 
				}
				return $query;
			}
		}
		function getData($table,$columns,$where=null){
			$query = buildQuery("select",$table,$columns,$where);
		}
		function insertData($table,$columns,$values,$where=null){
			$query = buildQuery("insert",$table,$columns,$where,$values);
		}
		function updateData($table,$columns,$values,$where=null){
			$query = buildQuery("update",$table,$columns,$where,$values);
		}
		function deleteData($table,$where=null){
			$columns=null;
			$query = buildQuery("delete",$table,$columns,$where);
		}
	}

?>
