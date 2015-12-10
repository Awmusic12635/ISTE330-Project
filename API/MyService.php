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
		        $str .= $keyspace[random_int(0, $max)];
		    }
		    return $str;
		}
		protected function login($args,$params){
			//return "hello";
			//Login
			if(count($args) ==0 && $this->method=="POST"){
				if(isset($this->request['user']) && isset($this->request['password'])){
					$conn = new DB();
	
					$conn->connect("localhost","finalProject","root","final");
					$fields = array("user_id","username","password");
					$data = $conn->getData("users",$fields," where username="+$this->request['username']);
					if($data[0]["password"]==$this->request['password']){
						$access = random_str(50);
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
					return parent::_response("User or password not supplied",400);
				}
				
			}else{
				return parent::_response("Incorrect login format or nothing sent",400);
			}
		}
		protected function paper($args,$params){
			//Paper
			if(count($args) ==0 && $this->method=="GET"){
				//Paper?Title=something
				if(isset($params["Title"])){
					$filtered_paper_list= array();
					foreach($this->papers_list as $paper_id => $paper){
						if(strpos($paper["Title"], $params["Title"]) !== false){
							//echo "hi";
							$filtered_paper_list[$paper_id]=$paper;
						}
					}
					return $filtered_paper_list;	
				}else{
					return $this->papers_list;	
				}
			}
			//Paper/{id}
			if(count($args) ==1 && $this->method=="GET"){
				return $this->papers_list[$args[0]];
			}
		}
		protected function services($args){
			if($args[0] == "Beers"){
				array_shift($args);
				return $this->beers($args);
			}else{
				$method_names = array(
					"Array getServices()",
					"Array getBeers()".
					"String getCheapest()",
					"String getCostliest()",
					"String getBeer()",
				);	
				return json_encode($method_names);
			}
		}
		/*protected function beers($args){
			if(count($args) ==0 && $this->method=="GET"){
				// /Beers
				$name_list = $this->getNames();
				
				return json_encode($name_list);
			}else if (count($args) == 1 && $this->method=="GET"){
				//Beers/Cheapest
				if($args[0] == "Cheapest"){
					return json_encode($this->getCheapest());
				}
				//Beers/Costliest
				else if ($args[0] == "Costliest"){
					return json_encode($this->getCostliest());
				}
				//Beers/{name}
				else{
					$price="";
					if(array_key_exists($args[0],$this->beers_list)){
						$price = $this->beers_list[$args[0]];	
						return json_encode($price);
					}
					return parent::_response("Requested Resource Doesn't Exist",404);
				}
			}
		}*/
	}// end of class
	//create the service
	try{
		$API = new MyService($_REQUEST['request'],$_SERVER_['HTTP_ORIGIN']);
		echo $API->processAPI();
			
		
	}catch(Exception $e){
		echo json_encode(Array("Error"=>$e->getMessage()));
	}
?>