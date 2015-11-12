<?php
class DB{
	private $username;
	private $password;
	private $host;
	private $db;
	function connect($host,$db,$username,$password){
		try{
			$dbh = new PDO("mysql:host=$host;dbname=$db",$username,$password);
			$dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
			
			$this->username = $username;
			$this->password = $password;
			$this->host = $host;
			$this->db = $db;
			
			return true;
		}
		catch(PDOException $e){
			echo $e->getMessage();
			return false;
		}
	}
	function getConnection(){
		try{
			$dbh = new PDO("mysql:host=" . $this->host.";dbname=".$this->db,$this->username,$this->password);
			return $dbh;
		
		}catch(PDOException $e){
			echo $e->getMessage();
		}
	}
	function disconnect(){
		try{
			$dbh->close();
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
	}
	function commaSeparate($array,$array2=null){
		$commaList="";
		if($array2==null){
			$commaList = implode(', ', $array);
		}else{
			$new_array = array_merge($array1,$array2);	
			$array_size=count($new_array);
			for($x=0;$x<$new_array;$x+=2){
				$commaList = $commandList . $new_array[$x] . "=" . $new_array[$x+1] . ",";
			}
		}
		$commaList = trim($commaList, ',');	
		return $commaList;
	}
	function buildQuery($type,$table,$columns,$where=null,$values=null){
		$query="";
		if($type=="select"){
			$query = $query . "select ";
			$query = $query . $this->commaSeparate($columns);
			$query = $query . " from " . $table;
			
			if($where !=null){
				$query = $query . " where " . $where;
			}
			return $query;
		}else if ($type=="insert"){
			$query = $query . "insert into " . $table . " ";
			$query = $query . "(" . $this->commaSeparate($columns) . ") values (" . $this->commaSeparate($values) . ")";
			return $query;
		}else if($type == "update"){
			$query = $query . "update " . $table . " set ". $this->commaSeparate($columns,$values) . " " . $where;
			 return $query;
		}else{
			echo "true";
			$query = $query . "delete from " . $table;
			
			if($where !=null){
				$query = $query . " where " . $where; 
			}
			return $query;
		}
	}
	function getData($table,$columns,$where=null){
		try{
			$dbh = $this->getConnection();
			$query = $this->buildQuery("select",$table,$columns,$where);
			$stmt = $dbh->prepare($query);
			$stmt->execute();
			$data=$stmt->fetchAll();
			
			return $data;
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
	}
	function insertData($table,$columns,$values,$where=null){
		try{
			$dbh = $this->getConnection();
			$query = $this->buildQuery("insert",$table,$columns,$where,$values);
			$stmt = $dbh->prepare($query);
			$stmt->execute();
			
			return true;
		}
		catch(PDOException $e){
			echo $e->getMessage();
			return false;
		}
	}
	function updateData($table,$columns,$values,$where=null){
		try{
			$dbh = $this->getConnection();
			$query = $this->buildQuery("update",$table,$columns,$where,$values);
			$stmt = $dbh->prepare($query);
			$stmt->execute();
			
			return true;
		}
		catch(PDOException $e){
			echo $e->getMessage();
			return false;
		}
	}
	function deleteData($table,$where=null){
		try{
			$columns=null;
			$dbh = $this->getConnection();
			$query = $this->buildQuery("delete",$table,$columns,$where);
			$stmt = $dbh->prepare($query);
			$stmt->execute();
			
			return true;
		}
		catch(PDOException $e){
			echo $e->getMessage();
			return false;
		}
	}
}
?>
