<?php
	require_once("RestService.class.php");	
	require_once("../DB/DB.php");
	
	class MyService extends RestService{
		//normally we would have a connection to a data store i.e. db
		private $papers_list = array();
				
		public function __construct($request,$origin){
			parent::__construct($request);
			//create dummy data store
		}
		function random_str($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'){
		    $str = '';
		    $max = mb_strlen($keyspace, '8bit') - 1;
		    for ($i = 0; $i < $length; ++$i) {
		        $str .= $keyspace[rand(0, $max)];
		    }
		    return $str;
		}
		function stringOnly($arr){
			foreach ($arr as $key => $value) {
				if (is_int($key)) {
					unset($arr[$key]);
    			}elseif (is_array($arr[$key])){
	    			$this->stringOnly($arr[$key]);
    			}	
			}	
			return $arr;
		}
		protected function user($args,$params){
			//User - GET
			if(count($args) ==0 && $this->method=="GET"){
				$conn = new DB();
				if($conn->connect("localhost","finalProject","root","final")){	
					//User?Name=somename
					if(isset($params["Name"])){
						$columns = array("user_id","first_name","last_name","username","signup_date","email");
						$data = $conn->getData("users",$columns,"concat(first_name,' ' ,last_name) like'%".$params["Name"]."%'");
							return $data;
					}else{
						$columns = array("user_id","first_name","last_name","username","signup_date","email");
						$data = $conn->getData("users",$columns);
						
						return $data;
					}
				}else{
					return parent::_response("Failed to connect to DB",500);
				}	
			}
			//User/{id} - GET
			if(count($args) ==1 && $this->method=="GET"){
				if(is_numeric($args[0])){
					$conn = new DB();
					if($conn->connect("localhost","finalProject","root","final")){
						$columns = array("user_id","first_name","last_name","username","signup_date","email");
						$data = $conn->getData("users",$columns,"user_id='".$args[0]."'");
						return $data;
					}else{
						return parent::_response("Failed to connect to DB",500);
					}
				}else{
					return parent::_response("Must send a user id",400);
				}
			}	
			//User/{id} - PUT
			if(count($args) ==1 && $this->method=="PUT"){
				if(is_numeric($args[0])){
					if(isset($this->file['first_name']) && isset($this->file['last_name']) && isset($this->file['username']) && isset($this->file['signup_date']) && isset($this->file['email'])){
						$conn = new DB();
						if($conn->connect("localhost","finalProject","root","final")){
							$columns = array("first_name","last_name","username","signup_date","email");
							$values = array($this->file['first_name'],$this->file['last_name'],$this->file['username'],$this->file['signup_date'],$this->file['email']);
							if(isset($this->file['password'])){
								$columns[]="password";
								$values[]=$this->file['password'];
							}
							$changed = $conn->updateData("users",$columns,$values,"user_id='".$args[0]."'");
							if($changed != 0 && $changed != -1){
								return parent::_response("Updated User",202);
							}elseif($changed != -1){
								return parent::_response("No user by that ID",404);
							}else{
								return parent::_response("Failed to update user",500);
							}
						}else{
							return parent::_response("Failed to connect to db",500);
						}
					}else{
						return parent::_response("Required values not passed",400);
					}
				}else{
					return parent::_response("Must send a user id",400);
				}
			}
			//User - POST
			if(count($args) ==0 && $this->method=="POST"){
				if(isset($this->request['password']) && isset($this->request['first_name']) && isset($this->request['last_name']) && isset($this->request['username']) && isset($this->request['signup_date']) && isset($this->request['email'])){
					$conn = new DB();
					if($conn->connect("localhost","finalProject","root","final")){
						$columns = array("first_name","last_name","username","signup_date","email","password");
						$values = array($this->request['first_name'],$this->request['last_name'],$this->request['username'],$this->request['signup_date'],$this->request['email'],$this->request['password']);
						$id = $conn->insertData("users",$columns,$values);
						if($id != false){
							return array(
								"user_id"=>$id,
							);
						}else{
							return parent::_response("Failed to add new user",500);
						}
					}else{
						return parent::_response("Failed to connect to DB",500);
					}
				}else{
					return parent::_response("Required values not passed",400);
				}
			}
			//User/{id} - DELETE
			if(count($args) ==1 && $this->method=="DELETE"){
				if(is_numeric($args[0])){
					$conn = new DB();
					if($conn->connect("localhost","finalProject","root","final")){
						$changed = $conn->deleteData("users","user_id='".$args[0]."'");
						if($changed != 0 && $changed != -1){
							return parent::_response("Deleted user",202);
						}elseif($changed != -1){
							return parent::_response("No user by that ID",404);
						}else{
							return parent::_response("Failed to delete user",500);
						}
					}else{
						return parent::_response("Failed to connect to DB",500);
					}
				}else{
					return parent::_response("Must send a paper id",400);
				}
			}
		}
		
		protected function paper($args,$params){
			//Paper - GET
			if(count($args) ==0 && $this->method=="GET"){
				$conn = new DB();
				if($conn->connect("localhost","finalProject","root","final")){
					//Paper?Title=something
					if(isset($params["Title"])){
						$columns = array("paper_id","title","abstract","citation","current_people","max_people");
						$data = $conn->getData("papers",$columns,"title like'%".$params["Title"]."%'");
							return $data;
					}else{
						$columns = array("paper_id","title","abstract","citation","current_people","max_people");
						$data = $conn->getData("papers",$columns);
						
						return $data;
					}
				}else{
					return parent::_response("Failed to connect to DB",500);
				}
			}
			//Paper/{id} - GET
			if(count($args) ==1 && $this->method=="GET"){
				if(is_numeric($args[0])){
					$conn = new DB();
					if($conn->connect("localhost","finalProject","root","final")){
						$columns = array("paper_id","title","abstract","citation","current_people","max_people");
						$data = $conn->getData("papers",$columns,"paper_id='".$args[0]."'");
						return $data;
					}else{
						return parent::_response("Failed to connect to DB",500);
					}
				}else{
					return parent::_response("Must send a paper id",400);
				}
			}
			//Paper/{id} - PUT
			if(count($args) ==1 && $this->method=="PUT"){
				if(is_numeric($args[0])){
					if(isset($this->file['abstract']) && isset($this->file['title']) && isset($this->file['citation']) && isset($this->file['max_people']) && isset($this->file['current_people'])){
						$conn = new DB();
						if($conn->connect("localhost","finalProject","root","final")){
							$columns = array("title","abstract","citation","current_people","max_people");
							$values = array($this->file['title'],$this->file['abstract'],$this->file['citation'],$this->file['current_people'],$this->file['max_people']);
							if($conn->updateData("papers",$columns,$values,"paper_id='".$args[0]."'")){
								return parent::_response("Successfully Updated",204);
							}else{
								return parent::_response("Failed to update paper",500);
							}
						}else{
							return parent::_response("Failed to connect to DB",500);
						}
					}else{
						return parent::_response("Required values not passed",400);
					}
				}else{
					return parent::_response("Must send a paper id",400);
				}
			}
			//Paper - POST
			if(count($args) ==0 && $this->method=="POST"){
				if(isset($this->request['abstract']) && isset($this->request['title']) && isset($this->request['citation']) && isset($this->request['max_people']) && isset($this->request['current_people'])){
					$conn = new DB();
					if($conn->connect("localhost","finalProject","root","final")){
						$columns = array("title","abstract","citation","current_people","max_people");
						$values = array($this->request['title'],$this->request['abstract'],$this->request['citation'],$this->request['current_people'],$this->request['max_people']);
						$id = $conn->insertData("papers",$columns,$values);
						if($id != false){
							return array(
								"paper_id"=>$id,
							);
						}else{
							return parent::_response("Failed to add new paper",500);
						}
					}else{
						return parent::_response("Failed to connect to DB",500);
					}
				}else{
					return parent::_response("Required values not passed",400);
				}
			}
			//Paper/{id} - DELETE
			if(count($args) ==1 && $this->method=="DELETE"){
				if(is_numeric($args[0])){
					$conn = new DB();
					if($conn->connect("localhost","finalProject","root","final")){
						$changed = $conn->deleteData("papers","paper_id='".$args[0]."'");
						if($changed != 0 && $changed != -1){
							return parent::_response("Deleted paper",202);
						}elseif($changed != -1){
							return parent::_response("No paper by that ID",404);
						}else{
							return parent::_response("Failed to delete paper",500);
						}
					}else{
						return parent::_response("Failed to connect to DB",500);
					}
				}else{
					return parent::_response("Must send a paper id",400);
				}
			}
		}
		protected function login($args,$params){
			//Login
			//return $this->method;
			if($this->method=="POST"){
				if(isset($this->request['username']) && isset($this->request['password'])){
					$conn = new DB();
					if($conn->connect("localhost","finalProject","root","final")){
						//var_dump($this->request);
						$fields = array("user_id","username","password");
						$username = $this->request['username'];
						$data = $conn->getData("users",$fields,"username='" .$username. "'");
						if($data[0]["password"]==$this->request['password']){
							$access = $this->random_str(50);
							$columns = array("access","uid");
							$values = array($access,$data[0]["user_id"]);
							if($conn->insertData("sessions",$columns,$values)){
								return array(
									"status"=>"logged in",
									"uid"=>$data[0]["user_id"],
									"accesshash"=>$access,
								);
							}else{
								return parent::_response("Failed to add user session",500);
							}
						}else{
							return parent::_response("Invalid Login",403);
						}
					}else{
						return parent::_response("Failed to connect to DB",500);
					}
				}else{
					return parent::_response("User or password not supplied",400);
				}
				
			}else{
				return parent::_response("Incorrect login format or nothing sent",400);
			}
		}
	}// end of class
	//create the service
	try{
		$API = new MyService($_REQUEST['request'],$_SERVER_['HTTP_ORIGIN']);
		echo $API->processAPI();
			
		
	}catch(Exception $e){
		echo json_encode(Array("Error"=>$e->getMessage()));
	}
?>