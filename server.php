<?php

//Server access headers defined
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With');
header('Access-Control-Allow-Credentials: true');

//Require Files
require_once "config/productConfig.php";
require_once "database/class.dbConnection.php";
require_once "lib/common/class.commonFunction.php";
require_once "lib/common/class.jwt.php";

class serverController {

	var $angularRequest;
	var $modulePath;
	var $module;
	var $angularResponse;
	var $commonObj;
	var $jwtDecodeStatus;

	function __construct($angularRequest) 
	{
		$this->commonObj = new commonFunction();
		$this->jwtObj = new jwtToken();
		dbConnection::connectDB();
		$this->angularRequest = json_decode($this->commonObj->decryption($angularRequest),1);
		$this->angularResponse = $this->angularRequest;
		$this->modulePath = MODULES_PATH;
		$this->module = array();
		$this->jwtDecodeStatus = false;
	}

	function controller() {

		$this->module = $this->commonObj->readJsonConfigurations('modules', $this->angularRequest['module'], 'module_name');
		$this->module['action'] = $this->angularRequest['action'];

		$this->checkJwtValidation();

		if($this->jwtDecodeStatus === true) {
		
			require_once $this->module['file_path'];
			$moduleObj = new $this->module['class_name'];
			$moduleObj->input = $this->angularRequest['requestData'];
			$moduleObj->action = $this->angularRequest['action'];
			$moduleObj->executeModule();
			
			$this->jwtObj->prepareJwtToken($moduleObj->output['emailId']);
			$moduleObj->output['tokendata'] = $this->angularRequest['responseData']['emailId'];
			$moduleObj->output['token'] = $this->jwtObj->encodeJwtToken();

			$this->angularResponse['responseData'] = $moduleObj->getModuleOutput();

		} else {

			$this->angularResponse['responseData']['token'] = "EMPTY";

		}

		$this->output();
		
	}

	function checkJwtValidation() {

		//if(($this->module['module_name'] != 'login') && ($this->module['module_name'] != 'userProfile' && $this->module['action'] != 'showprofile')) {
		if(isset($this->angularRequest['token'])) {			

			$jwtData = $this->jwtObj->decodeJwtToken($this->angularRequest['token']);
			if(isset($jwtData['emailId'])) {
				$this->angularRequest['requestData']['emailId'] = $jwtData['emailId'];	
				$this->jwtDecodeStatus = true;
			} else {
				$this->jwtDecodeStatus = false;
			}

		} else {

			$this->jwtDecodeStatus = ($this->module['module_name'] == 'login') ? true : false ;

		}

	}

	function getModule($moduleName) {
		$allModules = json_decode(file_get_contents($this->modulePath),1);
		$moduleNames = array_column($allModules,'module_name');
		$moduleIdex = array_search($moduleName,$moduleNames);
		return $allModules[$moduleIdex];
	}

	function output() {
		$angularResponse["response"] = $this->commonObj->encryption(json_encode($this->angularResponse));
		header("Access-Control-Allow-Origin: *");
		header("Content-Type: application/json");
		echo json_encode($angularResponse);
	}

}

$scObj = new serverController(json_decode(file_get_contents('php://input'),1));
$scObj->controller();

?>
