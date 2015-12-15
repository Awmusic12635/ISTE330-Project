<?php
	/*
		ALL OF THE API FUNCTIONALITY IS BASICALLY HERE
		
		
	*/
	require_once("RestService.class.php");	
	require_once("../DB/DB.php");
	
	class MyService extends RestService{
		//normally we would have a connection to a data store i.e. db
		private $papers_list = array();
				
		public function __construct($request,$origin){
			parent::__construct($request);
			//create dummy data store
		}
		//Generates a random string 
		function random_str($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'){
		    $str = '';
		    $max = mb_strlen($keyspace, '8bit') - 1;
		    for ($i = 0; $i < $length; ++$i) {
		        $str .= $keyspace[rand(0, $max)];
		    }
		    return $str;
		}
		//The entire /Keyword chain for the API
		protected function keyword($args,$params){
			//Keyword - GET
			if(count($args) ==0 && $this->method=="GET"){
				//Connects to db
				$conn = new DB();
				if($conn->connect("localhost","finalProject","root","final")){	
					//Keyword?Name=somename
					if(isset($params["Name"])){
						$columns = array("keyword_id","keyword");
						$data = $conn->getData("keywords",$columns,null,"keyword like'%".$params["Name"]."%'");
							return $data;
					}else{
						$columns = array("keyword_id","keyword");
						$data = $conn->getData("keywords",$columns);
						
						return $data;
					}
				}else{
					return parent::_response("Failed to connect to DB",500);
				}	
			}
			//Keyword/{id} - GET
			if(count($args) ==1 && $this->method=="GET"){
				//Verifies that it is an int id
				if(is_numeric($args[0])){
					//connect to db
					$conn = new DB();
					if($conn->connect("localhost","finalProject","root","final")){
						$columns = array("keyword_id","keyword");
						$data = $conn->getData("keywords",$columns,null,"keyword_id='".$args[0]."'");
						return $data;
					}else{
						return parent::_response("Failed to connect to DB",500);
					}
				}else{
					return parent::_response("Must send a user id",400);
				}
			}	
			//Keyword/{id} - PUT
			if(count($args) ==1 && $this->method=="PUT"){
				//Verifies that it is an int id
				if(is_numeric($args[0])){
					//Verifies the required send in values are there
					if(isset($this->file['keyword'])){
						//Connect to db
						$conn = new DB();
						if($conn->connect("localhost","finalProject","root","final")){
							//Prepare for update
							$columns = array("keyword");
							$values = array($this->file['keyword']);
							//Updates and checks # of changed rows
							$changed = $conn->updateData("keywords",$columns,$values,"keyword_id='".$args[0]."'");
							if($changed != 0 && $changed != -1){
								return parent::_response("Updated keyword",202);
							}elseif($changed != -1){
								return parent::_response("No keyword by that ID",404);
							}else{
								return parent::_response("Failed to update keyword",500);
							}
						}else{
							return parent::_response("Failed to connect to db",500);
						}
					}else{
						return parent::_response("Required values not passed",400);
					}
				}else{
					return parent::_response("Must send a keyword id",400);
				}
			}
			//Keyword - POST
			if(count($args) ==0 && $this->method=="POST"){
				//Verifies required send in values are there
				if(isset($this->request['keyword'])){
					//connects to db
					$conn = new DB();
					if($conn->connect("localhost","finalProject","root","final")){
						//prepares for insert
						$columns = array("keyword");
						$values = array($this->request['keyword']);
						$id = $conn->insertData("keywords",$columns,$values);
						//as long as the insert didn't fail, return id of the last inserted row
						if($id != false){
							return array(
								"keyword_id"=>$id,
							);
						}else{
							return parent::_response("Failed to add new keyword",500);
						}
					}else{
						return parent::_response("Failed to connect to DB",500);
					}
				}else{
					return parent::_response("Required values not passed",400);
				}
			}
			//Keyword/{id} - DELETE
			if(count($args) ==1 && $this->method=="DELETE"){
				//verifies ID sent in is a int
				if(is_numeric($args[0])){
					//connect to db
					$conn = new DB();
					if($conn->connect("localhost","finalProject","root","final")){
						$changed = $conn->deleteData("keywords","keyword_id='".$args[0]."'");
						//Checks # of changed rows to see if it worked
						if($changed != 0 && $changed != -1){
							return parent::_response("Deleted keyword",202);
						}elseif($changed != -1){
							return parent::_response("No keyword by that ID",404);
						}else{
							return parent::_response("Failed to delete keyword",500);
						}
					}else{
						return parent::_response("Failed to connect to DB",500);
					}
				}else{
					return parent::_response("Must send a keyword id",400);
				}
			}
		}
		//Entire /User API path
		protected function user($args,$params){
			//User - GET
			if(count($args) ==0 && $this->method=="GET"){
				//Connect to db
				$conn = new DB();
				if($conn->connect("localhost","finalProject","root","final")){	
					//User?Name=somename
					//verifies required values are sent in and prepares for select
					if(isset($params["Name"])){
						$columns = array("user_id","first_name","last_name","username","signup_date","email");
						$data = $conn->getData("users",$columns,null,"concat(first_name,' ' ,last_name) like'%".$params["Name"]."%'");
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
				//check if id is number
				if(is_numeric($args[0])){
					$conn = new DB();
					if($conn->connect("localhost","finalProject","root","final")){
						$columns = array("user_id","first_name","last_name","username","signup_date","email","user_type_id");
						$data = $conn->getData("users",$columns,null,"user_id='".$args[0]."'");
						return $data;
					}else{
						return parent::_response("Failed to connect to DB",500);
					}
				}else{
					return parent::_response("Must send a user id",400);
				}
			}	
			//User/{id}/Paper - GET - All papers for user
			if(count($args) ==2 && $this->method=="GET" && $args[1] == "Paper"){
				if(is_numeric($args[0])){
					$conn = new DB();
					if($conn->connect("localhost","finalProject","root","final")){
						$columns = array("paper_id","title","abstract","citation","current_people","max_people");
						$data = $conn->getData("user_papers",$columns,"join users using(user_id) join papers using(paper_id)","user_id='".$args[0]."'");
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
				//check if everything comes in 
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
				//check if id is num
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
		//All papers
		protected function paper($args,$params){
			//Paper - GET
			if(count($args) ==0 && $this->method=="GET"){
				$conn = new DB();
				if($conn->connect("localhost","finalProject","root","final")){
					//Paper?Title=something
					if(isset($params["Title"])){
						$columns = array("paper_id","title","abstract","citation","current_people","max_people");
						$data = $conn->getData("papers",$columns,null,"title like'%".$params["Title"]."%'");
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
						$data = $conn->getData("papers",$columns,null,"paper_id='".$args[0]."'");
						return $data;
					}else{
						return parent::_response("Failed to connect to DB",500);
					}
				}else{
					return parent::_response("Must send a paper id",400);
				}
			}
			//Paper/{id}/Keyword - GET - List all keywords of paper
			if(count($args) ==2 && $this->method=="GET" && $args[1] == "Keyword"){
				if(is_numeric($args[0])){
					$conn = new DB();
					if($conn->connect("localhost","finalProject","root","final")){
						$columns = array("keyword_id","keyword");
						$data = $conn->getData("papers_keywords",$columns,"join keywords using(keyword_id) join papers using(paper_id)","paper_id='".$args[0]."'");
						return $data;
					}else{
						return parent::_response("Failed to connect to DB",500);
					}
				}else{
					return parent::_response("Must send a paper id",400);
				}
			}
			//Paper/{id}/Keyword/{id} - POST - Add specific keyword to paper
			if(count($args) ==3 && $this->method=="POST" && $args[1] == "Keyword"){
				//return "adding keyword " . $args[2] . " to paper " . $args[0];
				if(is_numeric($args[0]) && is_numeric($args[2])){
					$conn = new DB();
					if($conn->connect("localhost","finalProject","root","final")){
						$columns = array("keyword_id","paper_id");
						$values = array($args[2],$args[0]);
						$id = $conn->insertData("papers_keywords",$columns,$values,true);
						if($id != false){
							return parent::_response("keyword added to paper",200);
						}else{
							return parent::_response("keyword or paper doesn't exist or is already added",400);
						}
					}else{
						return parent::_response("Failed to connect to DB",500);
					}
				}else{
					return parent::_response("Must send a paper id and keyword id",400);
				}
				
			}
			//Paper/{id}/Keyword/{id} - DELTE - Remove specific keyword to paper
			if(count($args) ==3 && $this->method=="DELETE" && $args[1] == "Keyword"){
				//return "adding keyword " . $args[2] . " to paper " . $args[0];
				if(is_numeric($args[0]) && is_numeric($args[2])){
					$conn = new DB();
					if($conn->connect("localhost","finalProject","root","final")){
						$changed = $conn->deleteData("papers_keywords","paper_id='".$args[0]."' and keyword_id='" . $args[2]."'");
						if($changed != 0 && $changed != -1){
							return parent::_response("Removed keyword from paper",200);
						}elseif($changed != -1){
							return parent::_response("paper does not have that key word",404);
						}else{
							return parent::_response("Failed to remove keyword from paper",500);
						}
					}else{
						return parent::_response("Failed to connect to DB",500);
					}
				}else{
					return parent::_response("Must send a paper id and keyword id",400);
				}	
			}
			
			//Paper/{id}/User - GET - List all users of paper
			if(count($args) ==2 && $this->method=="GET" && $args[1] == "User"){
				if(is_numeric($args[0])){
					$conn = new DB();
					if($conn->connect("localhost","finalProject","root","final")){
						$columns = array("user_id","first_name","last_name","username","signup_date","email","user_type_id");
						$data = $conn->getData("user_papers",$columns,"join users using(user_id) join papers using(paper_id)","paper_id='".$args[0]."'");
						return $data;
					}else{
						return parent::_response("Failed to connect to DB",500);
					}
				}else{
					return parent::_response("Must send a paper id",400);
				}
			}
			//Paper/{id}/User/{id} - POST Add Specific user to paper
			if(count($args) ==3 && $this->method=="POST" && $args[1] == "User"){
				if(is_numeric($args[0]) && is_numeric($args[2])){
					$conn = new DB();
					if($conn->connect("localhost","finalProject","root","final")){
						$columns = array("user_id","paper_id");
						$values = array($args[2],$args[0]);
						$id = $conn->insertData("user_papers",$columns,$values,true);
						if($id != false){
							return parent::_response("user added to paper",200);
						}else{
							return parent::_response("user or paper doesn't exist or is already added",400);
						}
					}else{
						return parent::_response("Failed to connect to DB",500);
					}
				}else{
					return parent::_response("Must send a paper id and user id",40);
				}
			}
			//Paper/{id}/User{id} - DELETE Delete Specific user to paper
			if(count($args) ==3 && $this->method=="DELETE" && $args[1] == "User"){
				//return "removing user " . $args[2] . " from paper " . $args[0];
				if(is_numeric($args[0]) && is_numeric($args[2])){
					$conn = new DB();
					if($conn->connect("localhost","finalProject","root","final")){
						$changed = $conn->deleteData("user_papers","paper_id='".$args[0]."' and user_id='" . $args[2]."'");
						if($changed != 0 && $changed != -1){
							return parent::_response("Removed user from paper",200);
						}elseif($changed != -1){
							return parent::_response("paper does not have that user",404);
						}else{
							return parent::_response("Failed to remove user from paper",500);
						}
					}else{
						return parent::_response("Failed to connect to DB",500);
					}
				}else{
					return parent::_response("Must send a paper id and user id",400);
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
		//Check if person is logged in based on access hash
		//if logged in return their userid
		//if not return 0 / false
		function checkIfLoggedIn($accessHash){
			$conn = new DB();
			if($conn->connect("localhost","finalProject","root","final")){
				
				$fields = array("uid","access");
				$data = $conn->getData("sessions",$fields,null,"access='" .$accessHash. "'");
				
				//user logged in
				if(count($data) !=0){
					return array(
						"user_id"=>$data[0]['uid'],
					);
				}else{
					//not logged in
					return 0;
				}
			}else{
				//Failed to get data from DB
				return -1;
			}
		}
		protected function login($args,$params){
			//Login
			if($this->method=="POST"){
				//return "hello";
				//return var_dump($params);
				if(isset($params['Access'])){
					
					//access hash was passed to us, check if user is logged in already
					$result = $this->checkIfLoggedIn ($params['Access']);
					//$result=$params['Access'];
					if($result != 0 && $result != -1){
						//user already logged in
						return parent::_response("Already logged in",202);
					}elseif($result != -1){
						//user is not logged in, continue logging in
						
					}else{
						//failed to get info from DB
						return parent::_response("Failed to session info",500);
					}
				}
				if(isset($this->request['username']) && isset($this->request['password'])){
					//return {Something:"hello"};
					$conn = new DB();
					if($conn->connect("localhost","finalProject","root","final")){
						//var_dump($this->request);
						$fields = array("user_id","username","password");
						$username = $this->request['username'];
						$data = $conn->getData("users",$fields,null,"username='" .$username. "'");
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