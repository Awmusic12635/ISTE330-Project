<?php
	require_once("RestService.class.php");	
	require_once("../DB/DB.php");
	
	class MyService extends RestService{
		//normally we would have a connection to a data store i.e. db
		private $papers_list = array();
				
		public function __construct($request,$origin){
			parent::__construct($request);
			//create dummy data store
			
			$this->papers_list = array(
				"1"=>array(
						"Title"=>"The Best Paper",
						"Author"=>"Alex Wacker",
						"Description"=>"Goes into depth into how this paper is truely the best",
						"Category"=>"Best",
					),
				"2"=>array(
						"Title"=>"The Worst Paper",
						"Author"=>"Not Alex Wacker",
						"Description"=>"Goes into depth into how this paper is truely the worst",
						"Category"=>"Worst",
					),
				"3"=>array(
						"Title"=>"The Coolest Paper",
						"Author"=>"Certainly Alex Wacker",
						"Description"=>"Goes into depth into how this paper is truely the coolest",
						"Category"=>"Cool",
					),
				"4"=>array(
						"Title"=>"The Amazing Paper",
						"Author"=>"Awesome Alex Wacker",
						"Description"=>"Goes into depth into how this paper is truely the most awesome",
						"Category"=>"Awesome",
					),
				"5"=>array(
						"Title"=>"A wisetale",
						"Author"=>"Bob",
						"Description"=>"What could have been be",
						"Category"=>"What?",
					),
				"6"=>array(
						"Title"=>"Chunks",
						"Author"=>"Mitch",
						"Description"=>"Hunk a Chunk of Burning Love",
						"Category"=>"Music",
					),
				"7"=>array(
						"Title"=>"Cats Musical",
						"Author"=>"David",
						"Description"=>"Cats The Musical",
						"Category"=>"Art",
					),
				"8"=>array(
						"Title"=>"Greece Musical",
						"Author"=>"David",
						"Description"=>"Greece The Musical",
						"Category"=>"Art",
					),
				"9"=>array(
						"Title"=>"The state of Greece",
						"Author"=>"Mitch",
						"Description"=>"Well this is a mess",
						"Category"=>"Failures",
					),
				"10"=>array(
						"Title"=>"Russia, leader in peace",
						"Author"=>"David",
						"Description"=>"Russia, leading the way to peace in the world",
						"Category"=>"Facts",
					),
			);
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
				$conn = new DB();
				if($conn->connect("localhost","finalProject","root","final")){
					$columns = array("paper_id","title","abstract","citation","current_people","max_people");
					$data = $conn->getData("papers",$columns,"paper_id='".$args[0]."'");
					return $data;
				}else{
					return parent::_response("Failed to connect to DB",500);
				}
			}
			//Paper/{id} - PUT
			if(count($args) ==1 && $this->method=="PUT"){
				
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