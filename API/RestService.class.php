<?php
	abstract class RestService {
		protected $method = ''; //GET, POST, PUT or DELETE
		protected $endpoint = ''; //the requested resource e.g. /files
		protected $verb = ''; //optional additional descriptor about the endoint
		protected $args = Array(); //any add'l URI components after the endpoint
					//verb have been removed. 
	    protected $file = Null; //stores the input of the PUT request
	    
	    public function __construct($request) {
		    //var_dump($request);
	    	$this->args = explode('/', rtrim($request,'/'));
	    	$this->endpoint = array_shift($this->args);
	    	/*if (array_key_exists(0,$this->args) && !is_numeric($this->args[0])) {
	    		$this->verb = array_shift($this->args);
	    	}*/
	    	
	    	$this->method = $_SERVER['REQUEST_METHOD'];
	    	if ($this->method == 'POST' &&
	    		array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
	    			
	    		if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {
	    			$this->method = 'DELETE';
	    		} else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT') {
	    			$this->method = 'PUT';
	    		} else {
	    			throw new Exception('Unexpected Header');
	    		}
	    	}
	    	
	    	switch($this->method) {
	    		case 'DELETE':
	    		case 'POST':
	    			$this->request = $this->_cleanInputs($_POST);
	    			//file_get_contents("php://input")
	    			$this->request = array_merge($this->request,$this->_cleanInputs($_GET));
	    			break;
	    		case 'GET':
	    			$this->request = $this->_cleanInputs($_GET);
	    			unset($this->request['request']);
	    			//var_dump($this->request);
	    			break;
	    		case 'PUT':
	    			$this->request = $this->_cleanInputs($_GET);
	    			//make this actually usable
	    			parse_str(file_get_contents("php://input"),$this->file);
	    			break;
	    		default:
	    			$this->_response('Invalid Method',405);
	    			break;
	    	} //switch
	    	
	    } //constructor
	    
	    //public method for the service's API to determine if concrete class
	    //implements a method for the endpoint that is requested and if so
	    //calls that method otherwise returns 404 response
	    public function processAPI() {
	    	if ( (int)method_exists($this,$this->endpoint) > 0) {
	    		return $this->_response($this->{$this->endpoint}($this->args,$this->request));
	    	} 
	    	return $this->_response("No Endpoint: $this->endpoint",404);
	    }
	    
	    //handles response to client
	    protected function _response($data, $status = 200) {
	    //var_dump($data,$status);die();
	    	header("HTTP/1.1 $status ".$this->_requestStatus($status));
	    	header('Content-Type: application/json');
	    	if ($status != 200) die();
	    	return json_encode($data);
	    }
	    
	    //sanitization
	    private function _cleanInputs($data) {
		    //var_dump($data);
	    	$clean_input = array();
	    	if (is_array($data)) {
	    		foreach ($data as $k => $v) {
	    			$clean_input[$k] = $this->_cleanInputs($v);
	    		}
	    	} else {
	    		$clean_input = trim(strip_tags($data));
	    	}
	    	return $clean_input;
	    }
	    private function _requestStatus($code) {
	    	$status = array(
	    		200 => 'OK',
	    		404 => 'Not Found',
	    		405 => 'Method Not Allowed',
	    		500 => 'Internal Server Error'
	    	);
	    	return ($status[$code])?$status[$code] : $status[500];
	    }
	} //class
?>