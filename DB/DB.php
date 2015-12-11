<?php
class DB{
	private $username;
	private $password;
	private $host;
	private $db;
	//verifies you can connect and sets username and password for future use
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
	//gets new connection object
	function getConnection(){
		try{
			$dbh = new PDO("mysql:host=" . $this->host.";dbname=".$this->db,$this->username,$this->password);
			return $dbh;
		
		}catch(PDOException $e){
			echo $e->getMessage();
		}
	}
	//Disconnects from the db
	function disconnect(){
		try{
			$dbh->close();
		}
		catch(PDOException $e){
			echo $e->getMessage();
		}
	}
	//used to comma separate user inputs based on one or two arrays. Used mainly in query builder for field names, and values.
	//If two arrays are sent in, the format is adjusted to "array1value[0]=array2value[0],array1value[1]=array2value[1]"
	function commaSeparate($array,$type="select",$array2=null){
		$commaList="";
		if($array2==null){
			if($type=="insert"){
				$commaList = implode("','", $array);
			}else{
				$commaList = implode(', ', $array);	
			}
		}else{	
			$array_size=count($array);
			for($x=0;$x<$array_size;$x++){
				$commaList = $commaList . $array[$x] . "='" . $array2[$x] . "',";
			}
		}
		$commaList = trim($commaList, ',');	
		return $commaList;
	}
	//Builds the query based on user input to be used to query the db
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
			$query = $query . "(" . $this->commaSeparate($columns) . ") values ('" . $this->commaSeparate($values,"insert") . "')";
			return $query;
		}else if($type == "update"){
			$query = $query . "update " . $table . " set ". $this->commaSeparate($columns,"update",$values);
			if($where !=null){
				$query = $query . " where " . $where;
			}
			 return $query;
		}else{
			$query = $query . "delete from " . $table;
			
			if($where !=null){
				$query = $query . " where " . $where; 
			}
			return $query;
		}
	}
	//Select statements method to execute them against the database, has optional where param to further filter results
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
	//insert statements method to execute against the database. 
	function insertData($table,$columns,$values){
		try{
			$where=null;
			$dbh = $this->getConnection();
			$query = $this->buildQuery("insert",$table,$columns,$where,$values);
			//echo $query;
			$stmt = $dbh->prepare($query);
			$stmt->execute();
			
			return $dbh->lastInsertId();
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
	//delete statements to execute against the database, optional where param to further clarify the statement
	function deleteData($table,$where=null){
		try{
			$columns=null;
			$dbh = $this->getConnection();
			$query = $this->buildQuery("delete",$table,$columns,$where);
			$stmt = $dbh->prepare($query);
			$stmt->execute();
			$changed = $stmt->rowCount();
			return $changed;
		}
		catch(PDOException $e){
			echo $e->getMessage();
			return -1;
		}
	}
}
?>