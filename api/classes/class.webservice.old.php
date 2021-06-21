<?php
require_once("Rest.inc.php");
//require_once($AppAbsolutePath."language/english/text.php");
class webservice extends REST{
	private $email="";
	private $password="";
	public $data = "";
	public $apiKey="";
	public $authkey="";
	public $errorlog_array=array();
	public $requestmethod="";
	public $membertype="";
	public $pkyId="";
	public $timestamp="";
	public $session_id="";
	protected $timestamp_threshold = 1800000;
	private $sourceDBdetails=false;
	private $instanceDBObj=false;
	protected $sourceDBObj=false;
	public  $token="";
	private $currentCompanyDBName=false;
	private $companyInstancePath=false;
	private $companycode=false;
	private $resumeDirectory=false;
	private $resumerelativedirectory="";
	private $ConfigVars=array();
	private $addJobCallConvertToEdit=false;
	public function __construct(){
		if($_SERVER['REQUEST_METHOD']=="GET"){
		//	$error=array('status' => "false", "msg" => "Method Not Allowed");
		//	$this->response($this->json($error),200);
		}
		parent::__construct();
		try{
			$this->checkRequestParam();
		}catch(Exception $e){
			$this->sendFalseResponse(200,$e->getMessage());
		}
		try{
			$this->initCheck();
			$this->_masterDBObj=$this->sourceDBObj;
		}catch(Exception $e){
			$this->sendFalseResponse(200,$e->getMessage());
		}
		try{
			$this->setDatabaseObject();
		}catch(Exception $e){
			$this->sendFalseResponse(200,$e->getMessage());
		}
		$this->setConfigData();

	}
	public function processApi($id){
		$func = strtolower(trim(str_replace("/","",$id)));
		$this->requestmethod=$func;
		if((int)method_exists($this,$func) > 0){
			$this->$func();
			exit;
		}else{
			$this->sendFalseResponse(404,ERR_INVALID_FUNCTION_CALL);
		}
	}
	private function decodeRequest(){
		$jsonStr=$this->parseJSON($this->_request["data"]);
		$JsonReqObj=json_decode($jsonStr);
		return $JsonReqObj;
	}
	public function authenticate(){
		global $RolesArray,$InstanceDirectoryRootPath;
		$JsonReqObj=$this->decodeRequest();//json_decode($this->_request["data"]);
		$RequireFields=array("username"=>array("data"=>$JsonReqObj->username,"maxlength"=>"20","datatype"=>"string","errormsg"=>ERR_INVALID_USERNAME),"password"=>array("data"=>$JsonReqObj->password,"maxlength"=>"20","datatype"=>"string","errormsg"=>ERR_INVALID_PASSWORD));
		$fields=$this->validateRequestParameters($RequireFields);
		if(is_array($fields)){
			try{
				$this->setDatabaseObject();
			}catch(Exception $e){
				$this->sendFalseResponse(200, $e->getMessage());
			}
			if($this->instanceDBObj !== false){
				$sql="select * from companyadmin where adminusername  ='" . $fields["username"]["data"] . "' and (adminpassword='".md5($fields["password"]["data"])."' or adminpassword='".$fields["password"]["data"]."') and status = 'Active'";
				try{
					$res=$this->instanceDBObj->select($sql);
				}catch(Exception $e){
					$this->sendFalseResponse(200, ERR_INVALID_USENAME_OR_PASS);
				}
				if(count($res)>0 && isset($res[0]['companyadminID']) && $res[0]['companyadminID']>0){
					$RoleSql = "select role_id from emp_roles where companyadminID='".$res[0]['companyadminID']."'";
					try{
						$RoleRes = $this->instanceDBObj->select($RoleSql);
					}catch(Exception $e){
						$this->sendFalseResponse(200, ERR_ROLES);
					}
					if(count($RoleRes)>0){
						for($i=0;$i<count($RoleRes);$i++){
							$roleArray[$i] = $RoleRes[$i]['role_id'];
						}
						if (in_array("1",$roleArray)){ // super admin
							$role = $RolesArray[1];
							$accessno = 1;
						}else if (in_array("2",$roleArray)){ // company admin
							$role = $RolesArray[2];
							$accessno = 1;
						}else if (in_array("7",$roleArray)){ // HR Manager
							$role = $RolesArray[7];
							$accessno = 7;
						}else if (in_array("3",$roleArray)){ // Department admin (Division admin)
							$role = $RolesArray[3];
							$accessno = 3;
						}else if (in_array("4",$roleArray)){ // Team admin (unit admin)
							$role = $RolesArray[4];
							$accessno = 4;
						}else if (in_array("5",$roleArray)){ // Employee
							$role = $RolesArray[5];
							$accessno = 5;
						}else if (in_array("6",$roleArray)){ // Recuriter
							$role = $RolesArray[6];
							$accessno = 6;
						}else if (in_array("8",$roleArray)){ // Interviewer
							$role = $RolesArray[8];
							$accessno = 8;
						}
					}else{
						$this->sendFalseResponse(200, ERR_INVALID_USENAME_OR_PASS);
					}
					$mres['token']		=	md5(uniqid(rand(), true));
					$mres['session_id']	=	$res[0]['companyadminID'];
					$expireson=date("Y-m-d H:i:s",time() + 30*60);
					$instanceRootDirectory="";
					if($this->companycode != ""){
						$instanceRootDirectory=(file_exists($InstanceDirectoryRootPath.$this->companycode) && is_dir($InstanceDirectoryRootPath.$this->companycode))?$InstanceDirectoryRootPath.$this->companycode."/":"";
					}
					$sql="insert into jobapi_accesslog (`username`, `password`, `token`, `session_id`, `role`, `accessno`, `expireson`, `createdon`,`companyId`,`companydirectorypath`) values ('".$fields["username"]["data"]."','".$fields["password"]["data"]."','".$mres['token']."','".$mres['session_id']."','".$role."','".$accessno."','".$expireson."','".date("Y-m-d H:i:s")."','".$res[0]['companyadminID']."','".$instanceRootDirectory."')";
					$this->sourceDBObj->insert($sql);
					try{
						$this->response($this->json($mres), 200);
					}catch(Exception $e){
						//Do nothing as we require to send response.
					}
					$this->response($this->json($mres),200);
				}else{
					$this->sendFalseResponse(200, ERR_INVALID_USENAME_OR_PASS);
				}
			}else{
				$this->sendFalseResponse(200, SYSTEM_ERROR);
			}
		}
	}
	private function validateRequestParameters($RequireFields){
		$fields=array();
		try{
			$fields=$this->valiadateRequest($RequireFields);
		}catch(Exception $e){
			$this->sendFalseResponse(200,$e->getMessage());
		}
		return $fields;
	}
	private function checkRequestParam(){
		if(!isset($this->_request["data"]) || !$this->isJson($this->_request["data"])){
			$this->throwException(INVALID_REQUEST);
			return false;
		}
	}
	private function initCheck(){
		//Checking initial configuration. All require constants and variable are available or not.
		if(!defined("SOURCE_DATABASE_KEY")){
			$this->throwException(SYSTEM_ERROR);
			return false;
		}
		if(trim(SOURCE_DATABASE_KEY)==""){
			$this->throwException(SYSTEM_ERROR);
			return false;
		}
		if(!isset($this->_request["data"]) || !$this->isJson($this->_request["data"])){
			$this->throwException(SYSTEM_ERROR);
			return false;
		}
		$this->getSourceDatabaseDetails();
	}
	private function getSourceDatabaseDetails(){
		if(!defined("SOURCE_DATABASE_KEY")){
			$this->throwException(SYSTEM_ERROR);
			return false;
		}
		$this->sourceDBdetails=json_decode(base64_decode(SOURCE_DATABASE_KEY));
		if($this->sourceDBdetails !==false && is_object($this->sourceDBdetails)){
			if(empty($this->sourceDBdetails->DB_HOST)){
				$this->throwException(SYSTEM_ERROR);
				return false;
			}
			if(empty($this->sourceDBdetails->DB_USER)){
				$this->throwException(SYSTEM_ERROR);
				return false;
			}
			if(empty($this->sourceDBdetails->DB_PASS)){
				$this->throwException(SYSTEM_ERROR);
				return false;
			}
			if(empty($this->sourceDBdetails->DB_NAME)){
				$this->throwException(SYSTEM_ERROR);
				return false;
			}
			$this->sourceDBObj=new dbclass($this->sourceDBdetails->DB_USER,$this->sourceDBdetails->DB_PASS,$this->sourceDBdetails->DB_HOST,$this->sourceDBdetails->DB_NAME);
		}else{
			$this->throwException(SYSTEM_ERROR);
			return false;
		}
	}
	private function getEmployerId($ERN_Number){
		$sql="select ClientID,count(ClientID) as tot from client where ERN_Number='".$ERN_Number."'";
		$rs=$this->instanceDBObj->select($sql);
		if($rs && isset($rs[0]['tot']) && $rs[0]['tot']==1 && isset($rs[0]['ClientID']) && $rs[0]['ClientID']>0){
			return $rs[0]['ClientID'];
		}
		return false;
	}
	private function validateEmployerRefID($id){
		if(!empty($id) && is_numeric($id) && $id>0){
			$sql="select count(*) as tot from client where ClientID='".$id."'";
			$rs=$this->instanceDBObj->select($sql);
			if(isset($rs[0]['tot']) && $rs[0]['tot']>0){
				return true;
			}
		}
		return false;
	}
	private function setDatabaseObject(){
        global $CompanyPath;
        try{
            include($CompanyPath."configs/dbinfo.php");
            $this->instanceDBObj=new dbclass($_USERNAME,$_PASS,$_HOST,$_DATABASE);
			$this->setCompanyDetails();
        }catch(Exception $e){
            $this->throwException(SYSTEM_ERROR);
            return false;
        }
	}
	private function setCompanyDetails(){
		if($this->instanceDBObj !== false){
			$sql="select * from company";
			try{
				$rs=$this->instanceDBObj->select($sql);
				if($rs && isset($rs[0]['company_code'])){
					$this->companycode=$rs[0]['company_code'];
				}

			}catch(exception $e){
				$this->throwException(SYSTEM_ERROR);
            	return false;
			}
		}
	}
	private function valiadateRequest($RequireValidationForKeys){
		$formInputValidator=new FormInputValidator();
		$errorKey=true;
		foreach($RequireValidationForKeys as $key=>$value){
			if(isset($RequireValidationForKeys[$key]["data"]) && isset($RequireValidationForKeys[$key]["maxlength"]) && isset($RequireValidationForKeys[$key]["datatype"])){
				$formInputValidator->setInputData($RequireValidationForKeys[$key]["data"]);
				$formInputValidator->setMaxLength($RequireValidationForKeys[$key]["maxlength"]);
				$formInputValidator->setDataType($RequireValidationForKeys[$key]["datatype"]);
				if($RequireValidationForKeys[$key]["datatype"]=="date" && isset($RequireValidationForKeys[$key]["format"])){
					$formInputValidator->setDateFormat($RequireValidationForKeys[$key]["format"]);
				}
				$data=$formInputValidator->validateData();
				if($data !== false){
					$RequireValidationForKeys[$key]["data"]=$data;
				}else{
					$errorKey=$RequireValidationForKeys[$key]["errormsg"];
					break;
				}
			}
		}
		if($errorKey !== true){
			$this->throwException($errorKey);
			return;
		}
		return $RequireValidationForKeys;
	}
	private function setCompanyDatabaseDetails($companyId){
		if($this->sourceDBObj !== false){
			$sql="select * from company where companyID=".$companyId;
			try{
				$rs=$this->sourceDBObj->select($sql);
			}catch(Exception $e){
				$this->throwException(SYSTEM_ERROR);
				return false;
			}
			if(isset($rs[0]['reg_cancelled']) && $rs[0]['reg_cancelled']=="yes"){
				if(isset($rs[0]['expiry_date']) && $rs[0]['expiry_date'] < date("Y-m-d")){
					$this->throwException(SYSTEM_ERROR);
					return false;
				}
			}
			if(count($rs)>0 && isset($rs[0]['db_user']) && !empty($rs[0]['db_user']) && isset($rs[0]['db_pass']) && !empty($rs[0]['db_pass']) && isset($rs[0]['db_name']) && !empty($rs[0]['db_name'])  && isset($rs[0]['db_server']) && !empty($rs[0]['db_server'])){
				try{
					$this->instanceDBObj= new dbclass($rs[0]['db_user'],$rs[0]['db_pass'],$rs[0]['db_server'],$rs[0]['db_name']);
					$this->companycode=$rs[0]['company_code'];
				}catch(Exception $e){
					$this->throwException(SYSTEM_ERROR);
				}
			}
		}else{
			$this->throwException(SYSTEM_ERROR);
		}
	}
	private function parseRequestandSetSession(){
        $JsonReqObj=$this->decodeRequest();
        $token = (isset($JsonReqObj->token) && trim($JsonReqObj->token)!="")?$JsonReqObj->token:"";
        if(trim($token)!=""){

            $RequireFields=array("token"=>array("data"=>$JsonReqObj->token,"maxlength"=>"100","datatype"=>"string","errormsg"=>INVALID_TOKEN));
            $this->validateRequestParameters($RequireFields);
            $this->setSession($token);
        }else{
            $this->sendFalseResponse(200, INVALID_TOKEN);
        }
    }
    private function setSession($token){
        $sql="select * from jobapi_accesslog where token='".$token."' and expireson>'".date("Y-m-d H:i:s")."'";
        try{
            $rs=$this->sourceDBObj->select($sql);
        }catch(Exception $e){
	        return false;
        }
        if(count($rs)){
            $_SESSION['sess_companyId']=$rs[0]['companyId'];
            $_SESSION['sess_companyadmin']=$rs[0]['session_id'];
            $_SESSION['sess_userid']=$rs[0]['session_id'];
            $_SESSION['sess_role']=$rs[0]['role'];
            $_SESSION['sess_accessno']=$rs[0]['accessno'];
            $_SESSION['sess_token']=$rs[0]['token'];
            $this->companyInstancePath=$rs[0]['companydirectorypath'];
        }else{
            $this->sendFalseResponse(200, INVALID_TOKEN);
        }
    }
	public function validateEmployerData($act){
		$RequireFields_arr=array();
		$retArray=array();
		$JsonReqObj=$this->decodeRequest();;
		$retArray['employer_name']=isset($JsonReqObj->employer_name)?$JsonReqObj->employer_name:"";
		$retArray['street_1']=isset($JsonReqObj->street_1)?$JsonReqObj->street_1:"";
		$retArray['street_2']=isset($JsonReqObj->street_2)?$JsonReqObj->street_2:"";
		$retArray['street_3']=isset($JsonReqObj->street_3)?$JsonReqObj->street_3:"";
		$retArray['county']=isset($JsonReqObj->county)?$JsonReqObj->county:"";
		$retArray['city']=isset($JsonReqObj->city)?$JsonReqObj->city:"";
		$retArray['zipcode']=isset($JsonReqObj->zipcode)?$JsonReqObj->zipcode:"";
		$retArray['phone']=isset($JsonReqObj->phone)?$JsonReqObj->phone:"";
		$retArray['fax']=isset($JsonReqObj->fax)?$JsonReqObj->fax:"";
		$retArray['employer_description']=isset($JsonReqObj->employer_description)?$JsonReqObj->employer_description:"";
		$retArray['emplouyer_website']=isset($JsonReqObj->emplouyer_website)?$JsonReqObj->emplouyer_website:"";
		$retArray['emoloyer_ern_number']=isset($JsonReqObj->emoloyer_ern_number)?$JsonReqObj->emoloyer_ern_number:"";
		$retArray["logo"]=isset($JsonReqObj->logo)?$JsonReqObj->logo:"";
		if($act=="Add"){
			/*if(trim($retArray['emoloyer_ern_number']) == ""){
				$this->sendFalseResponse(200, ERN_NUMBER_REQUIRED);
			}*/
			if(isset($retArray['emoloyer_ern_number']) && !empty($retArray['emoloyer_ern_number'])){
			$employerId=$this->getEmployerId($retArray['emoloyer_ern_number']);
			if($employerId!==false){
				$this->sendFalseResponse(200, MSG_ERN_ALREADY_EXISTS);
			}
			}
			$retArray['firstname']=isset($JsonReqObj->firstname)?$JsonReqObj->firstname:"";
			$retArray['lastname']=isset($JsonReqObj->lastname)?$JsonReqObj->lastname:"";
			$retArray['email']=isset($JsonReqObj->email)?$JsonReqObj->email:"";
			$retArray['password']=isset($JsonReqObj->password)?$JsonReqObj->password:"";
			$RequireFields_arr=array(
				"firstname"=>array("data"=>$retArray['firstname'],"maxlength"=>"255","datatype"=>"string","errormsg"=>EMPLOYER_CONTACT_FIRSTNAME),
				"lastname"=>array("data"=>$retArray['lastname'],"maxlength"=>"255","datatype"=>"string","errormsg"=>EMPLOYER_CONTACT_LASTNAME),

			);
			/*	"email"=>array("data"=>$retArray['email'],"maxlength"=>"255","datatype"=>"string","errormsg"=>EMPLOYER_CONTACT_EMAIL),
				"password"=>array("data"=>$retArray['password'],"maxlength"=>"255","datatype"=>"string","errormsg"=>EMPLOYER_CONTACT_PASSWORD)*/
		}
		if($act=="Edit"){
			$retArray['JustApplyEmployerID']=isset($JsonReqObj->JustApplyEmployerID)?$JsonReqObj->JustApplyEmployerID:"";
			if(trim($retArray['JustApplyEmployerID'])=="" || !is_numeric((int)$retArray['JustApplyEmployerID']) || $retArray['JustApplyEmployerID']<=0){
				$this->sendFalseResponse(200, JUST_APPLY_REF_ID_EMPLOYER);
			}
			if(isset($retArray['emoloyer_ern_number']) && !empty($retArray['emoloyer_ern_number'])){
			$employerId=$this->getEmployerId($retArray['emoloyer_ern_number']);
			if($employerId!==false && $employerId!==$retArray['JustApplyEmployerID']){
				$this->sendFalseResponse(200, MSG_ERN_ALREADY_EXISTS);
			}
		}
		}
	//	"county"=>array("data"=>$retArray['county'],"maxlength"=>"255","datatype"=>"string","errormsg"=>"County ".MSG_IS_REQUIRED_FIELD),
		$RequireFields=array_merge(
			array(
				"employer_name"=>array("data"=>$retArray['employer_name'],"maxlength"=>"255","datatype"=>"string","errormsg"=>EMPLOYER_NAME_REQUUIRED),
				"street_1"=>array("data"=>$retArray['employer_name'],"maxlength"=>"255","datatype"=>"string","errormsg"=>STREET_1_REQUIRED),
				"city"=>array("data"=>$retArray['city'],"maxlength"=>"255","datatype"=>"string","errormsg"=>"City ".MSG_IS_REQUIRED_FIELD),
				"zipcode"=>array("data"=>$retArray['zipcode'],"maxlength"=>"255","datatype"=>"string","errormsg"=>ZIPCODE_REQUIRED),
			)
			,$RequireFields_arr
		);
		$this->validateRequestParameters($RequireFields);
		return  $retArray;
	}
	private function saveEmployerLogo($logoBinaryStr,$EmployerID,$EmployerComapyName){
		global $CompanyPath;
		/* We will receive logo in base64 encoded format and we need to decode it and need to save it in employerlogo directory. This directory will be in root of the company directory.*/
		if($logoBinaryStr != "" && is_numeric($EmployerID) && $EmployerID > 0 && trim($EmployerComapyName) != ""){
			$logodecodedStr=base64_decode(str_replace("data:image/jpeg;base64,","",rawurldecode($logoBinaryStr)));
			$EmployerNameForFileName=preg_replace("/[^A-Za-z0-9]+/", "-", strtolower($EmployerComapyName));
			$logoFileName=$EmployerID."-".$EmployerNameForFileName.".jpg";
			$SavePath=$CompanyPath.EMPLOYER_LOGO_DIRECTORY;
			$handle=fopen($SavePath.$logoFileName,"w");
			fwrite($handle,$logodecodedStr);
			fclose($handle);
			chmod($SavePath.$logoFileName,0777);
			$sql="update client set logo='".$logoFileName."' WHERE ClientID='".$EmployerID."'";
			$this->instanceDBObj->edit($sql);
		}
	}
	public function addEmployer(){
		global $AppAbsolutePath;
	    $this->parseRequestandSetSession();
        if(isset($_SESSION['sess_companyId']) && $_SESSION['sess_companyId']>0 && isset($_SESSION['sess_companyadmin']) && !empty($_SESSION['sess_companyadmin'])){
            if(isset($_SESSION['sess_token']) && isset($_SESSION['sess_userid'])){
				$retArray=$this->validateEmployerData("Add");
				extract($retArray);
				try{
					$sSql = "Insert into client set ";
					$sSql = $sSql."
							CompanyName='".mysql_real_escape_string(@$employer_name)."',
							Street='".mysql_real_escape_string(@$street_1)."',
							Street2='".mysql_real_escape_string(@$street_2)."',
							Street3='".mysql_real_escape_string(@$street_3)."',
							county='".mysql_real_escape_string(@$county)."',
							City='".mysql_real_escape_string(@$city)."',
							Zip='".mysql_real_escape_string(@$zipcode)."',
							Phone='".mysql_real_escape_string(@$phone)."',
							Fax='".mysql_real_escape_string(@$fax)."',
							Details='".mysql_real_escape_string(@$employer_description)."',
							Website='".mysql_real_escape_string(@$emplouyer_website)."',
							ERN_Number='".mysql_real_escape_string(@$emoloyer_ern_number)."',
							isPrivate='no',
							CountryID='United Kingdom',
							CreatedOn='".date("Y-m-d H:i:s")."',
							CreatedBy='".$_SESSION["sess_userid"]."'";

					 $lastInsertedId=$this->instanceDBObj->insert($sSql);
				 	if($lastInsertedId>0){
				 		if(isset($logo) && !empty($logo)){
				 			$this->saveEmployerLogo($logo,$lastInsertedId,$employer_name);
				 		}
						$emailSQL=(isset($email) && !empty($email))?",Email='".mysql_real_escape_string($email)."'":"";
						$passwordSQL=(isset($password) && !empty($password))?",password='".md5($password)."'":"";
						 	$sql = "insert into prospect_contact set ";
							$sql .= "ClientID='".$lastInsertedId."',
								FirstName='".mysql_real_escape_string(@$firstname)."',
								LastName='".mysql_real_escape_string(@$lastname)."',
								Mobile='".mysql_real_escape_string(@$mobile)."',
								CreatedBy='".@$_SESSION["sess_userid"]."'".$emailSQL.$passwordSQL;
						$this->instanceDBObj->insert($sql);
			 		}
					$results=array(array("JustApplyEmployerID"=>$lastInsertedId));
                    $success=array("status"=>"success","msg"=>MSG_EMPLOYER_ADDED_SUCCESSFULLY,"results"=>$results);
                    $this->response($this->json($success), 200);
				}catch(Exception $e){
					 $this->sendFalseResponse(200, $e->getMessage());
				}
			}
		}
	}
	public function editEmployer(){
		global $AppAbsolutePath;
	    $this->parseRequestandSetSession();
        if(isset($_SESSION['sess_companyId']) && $_SESSION['sess_companyId']>0 && isset($_SESSION['sess_companyadmin']) && !empty($_SESSION['sess_companyadmin'])){
            if(isset($_SESSION['sess_token']) && isset($_SESSION['sess_userid'])){
				$retArray=$this->validateEmployerData("Edit");
				extract($retArray);
				try{
					$sSql = "update client set ";
					$sSql = $sSql."
							CompanyName='".mysql_real_escape_string(@$employer_name)."',
							Street='".mysql_real_escape_string(@$street_1)."',
							Street2='".mysql_real_escape_string(@$street_2)."',
							Street3='".mysql_real_escape_string(@$street_3)."',
							county='".mysql_real_escape_string(@$county)."',
							City='".mysql_real_escape_string(@$city)."',
							Zip='".mysql_real_escape_string(@$zipcode)."',
							Phone='".mysql_real_escape_string(@$phone)."',
							Fax='".mysql_real_escape_string(@$fax)."',
							Details='".mysql_real_escape_string(@$employer_description)."',
							Website='".mysql_real_escape_string(@$emplouyer_website)."',
							ERN_Number='".mysql_real_escape_string(@$emoloyer_ern_number)."',
							LastUpdateBy='".$_SESSION["sess_userid"]."'
							WHERE ClientID='".$JustApplyEmployerID."'";
							$this->instanceDBObj->edit($sSql);
					if(isset($logo) && !empty($logo)){
				 		$this->saveEmployerLogo($logo,$JustApplyEmployerID,$employer_name);
				 	}
							$results=array(array("JustApplyEmployerID"=>$JustApplyEmployerID));
							$success=array("status"=>"success","msg"=>MSG_EMPLOYER_RECORD_UPDATED_SUCCESSFULLY,"results"=>$results);
							$this->response($this->json($success), 200);

				}catch(Exception $e){
					 $this->sendFalseResponse(200, $e->getMessage());
				}
			}
		}
	}
	private function validateJustApplyRefID($id){
		$sql="select count(*) as tot from JobOrders where JobID='".$id."'";
		$rs=$this->instanceDBObj->select($sql);
		if($rs[0]['tot']>0){
			return true;
		}
		return false;
	}
	private function setConfigData(){
		$sql="select * from site_config LIMIT 0,1";
		$rs=$this->instanceDBObj->select($sql);
		if(count($rs)>0){
			foreach($rs as $key=>$value){
				if(!is_numeric($key)){
					$this->ConfigVars[$key]=$value;
				}
			}
		}

	}
	public function validateJobData($act){
		global $RequirementTypeArray, $workingHours,$countrynames,$collegeNASAccountParams;
		$RequireFields_arr=array();
		$retArray=array();
		$NASChannelSelected=false;
		$NGTUChannelSelected=false;
		$TOTALJOBSChannelSelected=false;
		$CarrerMapChannelSelected=false;
		$JsonReqObj=$this->decodeRequest();
		$justApplyRefId="";
		$retArray['ClientID']=0;
		$retArray['CRM_JobID']=isset($JsonReqObj->CRM_JobID)?$JsonReqObj->CRM_JobID:"";

		if(trim($retArray['CRM_JobID'])!=""){
			$rsunique=$this->isunique($retArray['CRM_JobID']);
			if($rsunique["status"]=== false){
				if(isset($rsunique["JobID"]) && $rsunique["JobID"]>0){
					$this->addJobCallConvertToEdit=true;
					$justApplyRefId=$rsunique["JobID"];
					$act="edit";
				}else{
					$msg=str_replace(array("{id}","{crmid}"),array("",$retArray['CRM_JobID']),MSG_VACANCY_ALREADY_EXISTS);
					$this->sendFalseResponse(200, $msg);
				}
			}
		}
		if($act=="edit"){
			$retArray['justApplyRefId']=isset($JsonReqObj->justApplyRefId)?$JsonReqObj->justApplyRefId:$justApplyRefId;
			if(isset($justApplyRefId) && !empty($justApplyRefId) && $retArray['justApplyRefId']!=$justApplyRefId && $this->addJobCallConvertToEdit===true){
				$retArray['justApplyRefId']=$justApplyRefId;
			}
			if(!is_numeric($retArray['justApplyRefId']) || empty($retArray['justApplyRefId'])){
				$this->sendFalseResponse(200, INVALID_JUSTAPPLY_REF_ID);
			}
			if(!$this->validateJustApplyRefID($retArray['justApplyRefId'])){
				$this->sendFalseResponse(200, INVALID_JUSTAPPLY_REF_ID);
			}
		}
		/*$Employer_ERN_Number=isset($JsonReqObj->emoloyer_ern_number)?$JsonReqObj->emoloyer_ern_number:"";
		if(trim($Employer_ERN_Number) != ""){
			$ClinetID=$this->getEmployerId(trim($Employer_ERN_Number));
			if(!$ClinetID){
				$this->sendFalseResponse(200, INVALID_ERN_NUMBER);
			}
			$retArray['ClientID']=$ClinetID;
		}*/
		if(isset($JsonReqObj->Chanel)){
			$retArray['Chanel']=$JsonReqObj->Chanel;
			$extra_field_arr[]="Chanel";
			$channelArray=explode(",",$retArray['Chanel']);
			if(is_array($channelArray)){
				$channelArray=array_map("strtolower",$channelArray);
				$channelArray=array_map("trim",$channelArray);
				if(in_array("nas",$channelArray)){
					$NASChannelSelected=true;
				}
				if(defined("MULTIPLE_NAS_ACCOUNTS") && MULTIPLE_NAS_ACCOUNTS===true){
					if(!isset($JsonReqObj->College) || empty($JsonReqObj->College)){
						$this->sendFalseResponse(200, MSG_COLLEGE_ACCOUNT);
					}else{
						$retArray['College']=trim(strtolower($JsonReqObj->College));
						if(!in_array($retArray['College'],$collegeNASAccountParams)){
							$this->sendFalseResponse(200, MSG_COLLEGE_ACCOUNT);
						}
						$retArray["NAS_ACCOUNT_KEY"]=array_search($retArray['College'],$collegeNASAccountParams);
					}
				}
				if(in_array("ngtu",$channelArray)){
					$NGTUChannelSelected=true;
				}
				if(in_array("totaljobs",$channelArray)){
					$TOTALJOBSChannelSelected=true;
				}
				if(in_array("careermap",$channelArray)){
					$CarrerMapChannelSelected=true;
				}
			}
		}
		$JustApplyEmployerID=isset($JsonReqObj->JustApplyEmployerID)?$JsonReqObj->JustApplyEmployerID:"";
		if(!empty($JustApplyEmployerID)){
			if($this->validateEmployerRefID($JustApplyEmployerID)){
				$retArray['ClientID']=trim($JustApplyEmployerID);
			}else{
				$this->sendFalseResponse(200, LBL_INVALID_EMPLOYER_REF_ID);
			}
		}

		$retArray['Jobtitle']=isset($JsonReqObj->Jobtitle)?$JsonReqObj->Jobtitle:"";
		$retArray['BriefDesc']=isset($JsonReqObj->BriefDesc)?$JsonReqObj->BriefDesc:"";
		$retArray['DetailDesc']=isset($JsonReqObj->DetailDesc)?$JsonReqObj->DetailDesc:"";
		$retArray['vacancy_type']=isset($JsonReqObj->vacancy_type)?$JsonReqObj->vacancy_type:"";
		$retArray['JobOpening']=isset($JsonReqObj->vacancy_type)?$JsonReqObj->JobOpening:"";

		$retArray['MainSkill']=isset($JsonReqObj->MainSkill)?$JsonReqObj->MainSkill:"";
		$retArray['contact_phone']=isset($JsonReqObj->contact_phone)?$JsonReqObj->contact_phone:"";

		$retArray['hours']=isset($JsonReqObj->hours)?$JsonReqObj->hours:"";
		$retArray['City']=isset($JsonReqObj->City)?$JsonReqObj->City:"";
		$retArray['state']=isset($JsonReqObj->state)?$JsonReqObj->state:"";
		$retArray['country']=isset($JsonReqObj->country)?$JsonReqObj->country:"";
		$retArray['post_code']=isset($JsonReqObj->post_code)?$JsonReqObj->post_code:"";
		$industryName=isset($JsonReqObj->industry)?$JsonReqObj->industry:"";
		$BusinessTitle=isset($JsonReqObj->category)?$JsonReqObj->category:"";
		$retArray['industryID']=false;
		if(trim($industryName)!=""){
			$retArray['industryID']=$this->getIndustryId($industryName);
		}
		/*$retArray['BusinessAreaID']=false;
		if(trim($BusinessTitle)!=""){
			$retArray['BusinessAreaID']=$this->getFunctionalAreaId($BusinessTitle);
		}	*/
		$ountryarr=array_map('strtolower',$countrynames);
		$reqtypearr=array_map('strtolower',$RequirementTypeArray);
		$reqtypearr=array_map('trim',$reqtypearr);
		$hoursarr=array_map('strtolower',$workingHours);

		if($act=="add"){
			if(trim($retArray['CRM_JobID'])==""){
				$this->sendFalseResponse(200, MSG_CRM_JOB_ID_BLANK);
			}

			if(trim($retArray['Jobtitle'])==""){
				$this->sendFalseResponse(200, MSG_JOB_TITLE_BLANK);
			}
			if(trim($retArray['BriefDesc'])==""){
				$this->sendFalseResponse(200, MSG_BRIEF_DESC_BLANK);
			}
			if(trim($retArray['DetailDesc'])==""){
				$this->sendFalseResponse(200, MSG_DETAIL_DESC_BLANK);
			}
			if(trim($retArray['MainSkill'])==""){
				$this->sendFalseResponse(200, MSG_MAINSKILL_BLANK);
			}
			if(trim($retArray['JobOpening'])==""){
				$this->sendFalseResponse(200, MSG_JOBOPENING_BLANK);
			}
			if(!is_numeric($retArray['JobOpening']) || $retArray['JobOpening']<=0){
				$this->sendFalseResponse(200, MSG_JOBOPENING_VALID);
			}
			if(trim($industryName)==""){
				$this->sendFalseResponse(200, MSG_SELECT_INDUSTRY);
			}
			if($retArray['industryID']===false){
				$this->sendFalseResponse(200, INVALID_INDUSTRY);
			}
			/*if(trim($BusinessTitle)==""){
				$this->sendFalseResponse(200, MSG_SELECT_CATEGORY);
			}*/
			/*if($retArray['BusinessAreaID']===false){
				$this->sendFalseResponse(200, INVALID_CATEGORY);
			}*/
		}

		if(!in_array(strtolower(trim($retArray['vacancy_type'])),$reqtypearr)){
			$this->sendFalseResponse(200, INVALID_VACANCY_TYPE);
		}
		if(!in_array(strtolower(trim($retArray['country'])),$ountryarr)){
			$this->sendFalseResponse(200, INVALID_COUNTRY);
		}
		if(!in_array(strtolower(trim($retArray['hours'])),$hoursarr)){
			$this->sendFalseResponse(200, INVALID_HOURS_VALUE);
		}

		if(isset($JsonReqObj->Closing_Date)){
			$retArray['Closing_Date']=$JsonReqObj->Closing_Date;
		}
		if(isset($JsonReqObj->Interview_Start_Date)){
			$retArray['Interview_Start_Date']=$JsonReqObj->Interview_Start_Date;
			$extra_field_arr[]="Interview_Start_Date";
		}
		if(isset($JsonReqObj->Possible_Start_Date)){
			$retArray['Possible_Start_Date']=$JsonReqObj->Possible_Start_Date;
		}
		if(isset($JsonReqObj->Weekly_Wage)){
			$retArray['Weekly_Wage']=(double)$JsonReqObj->Weekly_Wage;
			/*if($NASChannelSelected){
				$RequireFields_arr=array_merge($RequireFields_arr,array("Weekly_Wage"=>array("data"=>$retArray['Weekly_Wage'],"maxlength"=>"10","datatype"=>"double","errormsg"=>MSG_WEEK_WAGE)));
				if(strtolower(trim($retArray['vacancy_type']))!="traineeship"){
					if($retArray['Weekly_Wage']<40){
						$this->sendFalseResponse(200,"Weekly_Wage ".MSG_IS_DECIMAL_FIELD);
					}
				}
			}*/
		}else{
			if($NASChannelSelected){
				if(strtolower(trim($retArray['vacancy_type']))!="traineeship"){
					$this->sendFalseResponse(200,MSG_WEEK_WAGE);
				}
			}
		}
		if(isset($JsonReqObj->Working_Week)){
			$retArray['Working_Week']=$JsonReqObj->Working_Week;
			$extra_field_arr[]="Working_Week";
		}
		if(isset($JsonReqObj->Future_Prospects_Description)){
			$retArray['Future_Prospects_Description']=$JsonReqObj->Future_Prospects_Description;
			$extra_field_arr[]="Future_Prospects_Description";
		}
		if(isset($JsonReqObj->Vacancy_Location)){
			$retArray['vacancylocation']=$JsonReqObj->Vacancy_Location;
			$extra_field_arr[]="vacancylocation";
		}

		if(isset($JsonReqObj->Qualification_Required)){
			$retArray['Qualification_Required']=$JsonReqObj->Qualification_Required;
			$extra_field_arr[]="Qualification_Required";
		}
		if(isset($JsonReqObj->Personal_Qualities)){
			$retArray['Personal_Qualities']=$JsonReqObj->Personal_Qualities;
			$extra_field_arr[]="Personal_Qualities";
		}
		if(isset($JsonReqObj->Important_Other_Information)){
			$retArray['Important_Other_Information']=$JsonReqObj->Important_Other_Information;
			$extra_field_arr[]="Important_Other_Information";
		}
		if(isset($JsonReqObj->Reality_Check)){
			$retArray['Reality_Check']=$JsonReqObj->Reality_Check;
			$extra_field_arr[]="Reality_Check";
		}
		if(isset($JsonReqObj->Framework_Type)){
			$retArray['Framework_Type']=$JsonReqObj->Framework_Type;
			if(trim($retArray['Framework_Type']) != ""){
				if($retArray['Framework_Type']!="Unspecified" && $retArray['Framework_Type']!="IntermediateLevelApprenticeship" && $retArray['Framework_Type']!="AdvancedLevelApprenticeship" && $retArray['Framework_Type']!="HigherApprenticeship" && $retArray['Framework_Type']!="Traineeship"){
					$this->sendFalseResponse(200,"Framework_Type ".MSG_SHOULD_FROM." (Unspecified or IntermediateLevelApprenticeship or AdvancedLevelApprenticeship or HigherApprenticeship or Traineeship)");
				}
			}
			$extra_field_arr[]="Framework_Type";
		}
		if(isset($JsonReqObj->Vacancy_Submitted_By)){
			$retArray['Vacancy_Submitted_By']=$JsonReqObj->Vacancy_Submitted_By;
			$extra_field_arr[]="Vacancy_Submitted_By";
		}
		if(isset($JsonReqObj->Question_One)){
			$retArray['Question_One']=$JsonReqObj->Question_One;
			$extra_field_arr[]="Question_One";
		}
		if(isset($JsonReqObj->Question_Two)){
			$retArray['Question_Two']=$JsonReqObj->Question_Two;
			$extra_field_arr[]="Question_Two";
		}
		if(isset($JsonReqObj->Candidate_Known)){
			$retArray['Candidate_Known']=$JsonReqObj->Candidate_Known;
			$RequireFields_arr=array_merge($RequireFields_arr,array("Candidate_Known"=>array("data"=>$retArray['Candidate_Known'],"maxlength"=>"5","datatype"=>"bool","errormsg"=>"Candidate_Known ".MSG_IS_BOOLEAN_FIELD)));
			$Candidate_Known=($retArray['Candidate_Known']=="true")?"on":"";
			$extra_field_arr[]="Candidate_Known";
		}
		if(isset($JsonReqObj->NAS_Framework)){
			$retArray['NAS_Framework']=$JsonReqObj->NAS_Framework;
			$extra_field_arr[]="NAS_Framework";
		}
		if(isset($JsonReqObj->CourseTitle)){
			$retArray['CourseTitle']=$JsonReqObj->CourseTitle;
			$extra_field_arr[]="CourseTitle";
		}

		if($NASChannelSelected){
			if(!isset($retArray['ClientID']) || $retArray['ClientID']<=0){
				$this->sendFalseResponse(200, MSG_CLIENT_ID_REQUIRED_TO_POST_JOB_TO_NAS);
			}
			if(isset($retArray['ClientID']) && $retArray['ClientID'] > 0){
				$sql="select * from client where ClientID='".$retArray['ClientID']."'";
				$crs=$this->instanceDBObj->select($sql);
				if(!isset($crs[0]['ERN_Number']) || trim($crs[0]['ERN_Number']) == ""){
					$this->sendFalseResponse(200, MSG_ERNNUMBER_REQUIRED_TO_POST_JOB_TO_NAS);
				}else{
					$retArray["ERN_Number"]=$crs[0]['ERN_Number'];
				}
			}
		}
		if(isset($JsonReqObj->Expected_Duration)){
			$retArray['Expected_Duration']=$JsonReqObj->Expected_Duration;
			$extra_field_arr[]="Expected_Duration";
		}
		if(isset($JsonReqObj->Training_To_Be_Provided)){
			$retArray['Training_To_Be_Provided']=$JsonReqObj->Training_To_Be_Provided;
			$extra_field_arr[]="Training_To_Be_Provided";
		}
		if(isset($JsonReqObj->Applications_Instructions)){
			$retArray['Applications_Instructions']=$JsonReqObj->Applications_Instructions;
			$extra_field_arr[]="Applications_Instructions";
		}
		if(isset($JsonReqObj->Small_Employer_Wage_Incentive)){
			$retArray['Small_Employer_Wage_Incentive']=$JsonReqObj->Small_Employer_Wage_Incentive;
			$extra_field_arr[]="Small_Employer_Wage_Incentive";
		}
		if(isset($JsonReqObj->Display_On_Carrier_Site)){
			$retArray['PublicPosting']=$JsonReqObj->Display_On_Carrier_Site;
		}
		if(isset($JsonReqObj->Employer_Anonymous)){
			$retArray['Employer_Anonymous']=$JsonReqObj->Employer_Anonymous;
			$extra_field_arr[]="Employer_Anonymous";
		}
		if(isset($JsonReqObj->Employer_Anonymous_Name)){
			$retArray['Employer_Anonymous_Name']=$JsonReqObj->Employer_Anonymous_Name;
			$extra_field_arr[]="Employer_Anonymous_Name";
		}
		if(isset($JsonReqObj->ngtu_sectors)){
			if(is_array($JsonReqObj->ngtu_sectors)){
				$ngtusectorval=implode(",",$JsonReqObj->ngtu_sectors);
			}else{
				$ngtusectorval=$JsonReqObj->ngtu_sectors;
			}
			$retArray['ngtu_sectors']=$ngtusectorval;
			$extra_field_arr[]="ngtu_sectors";
		}

		if($NGTUChannelSelected && (!isset($retArray['ngtu_sectors']) || empty($retArray['ngtu_sectors']))){
			$this->sendFalseResponse(200,MSG_NGTU_SECTORS_MANDATORY);
		}

		if(isset($JsonReqObj->totaljobs_industry)){
			$retArray['totaljobs_industry']=$JsonReqObj->totaljobs_industry;
			$extra_field_arr[]="totaljobs_industry";
		}
		if(isset($JsonReqObj->careermap_category)){
			$retArray['careermap_category']=$JsonReqObj->careermap_category;
			$extra_field_arr[]="careermap_category";
		}
		if(isset($JsonReqObj->careermap_level)){
			$retArray['careermap_level']=$JsonReqObj->careermap_level;
			$extra_field_arr[]="careermap_level";
		}
		if($CarrerMapChannelSelected){
			if(!isset($retArray['careermap_category']) || trim($retArray['careermap_category'])==""){
				$this->sendFalseResponse(200,MSG_CAREERMAP_CATEGORY_MANDATORY);
			}
			if(!isset($retArray['careermap_level']) || trim($retArray['careermap_level'])==""){
				$this->sendFalseResponse(200,MSG_CAREERMAP_LEVEL_MANDATORY);
			}
			if(!isset($retArray['post_code']) || trim($retArray['post_code'])==""){
				$this->sendFalseResponse(200,ZIPCODE_REQUIRED);
			}
		}
		if($TOTALJOBSChannelSelected && (!isset($retArray['totaljobs_industry']) || empty($retArray['totaljobs_industry']))){
			$this->sendFalseResponse(200,MSG_TOTALJOBS_INDUSTRY_MANDATORY);
		}

		if(isset($JsonReqObj->totaljobs_region)){
			$retArray['totaljobs_region']=$JsonReqObj->totaljobs_region;
			$extra_field_arr[]="totaljobs_region";
		}

		if($TOTALJOBSChannelSelected && (!isset($retArray['totaljobs_region']) || empty($retArray['totaljobs_region']))){
			$this->sendFalseResponse(200,MSG_TOTALJOBS_REGION_MANDATORY);
		}

		if(isset($JsonReqObj->Nationwide)){
			$retArray['Nationwide']=$JsonReqObj->Nationwide;
			$extra_field_arr[]="Nationwide";
		}
		if($NGTUChannelSelected && (!isset($retArray['Nationwide']) || empty($retArray['Nationwide']))){
			$this->sendFalseResponse(200,MSG_NATION_WIDE_MANDATORY);
		}
		$Job_extraFields=$this->getExtraFields();
		if(is_array($Job_extraFields) && count($Job_extraFields)){
			foreach($Job_extraFields as $key=>$value){
				if(isset($value["default_field_name"]) && !empty($value["default_field_name"]) && !in_array($value["default_field_name"],$extra_field_arr)){
					if(isset($JsonReqObj->{$value["default_field_name"]})){
						$retArray[$value["default_field_name"]]=$JsonReqObj->{$value["default_field_name"]};
						$extra_field_arr[]=$value["default_field_name"];
						if($value["validate"]=="Yes"){
							$RequireFields_arr=array_merge($RequireFields_arr,array($value["default_field_name"]=>array("data"=>$retArray[$value["default_field_name"]],"maxlength"=>"255","datatype"=>"text","errormsg"=>$value["default_field_name"]." ".MSG_IS_REQUIRED_FIELD)));
						}
					}/*else if($value["validate"]="Yes"){
						$this->sendFalseResponse(200, $value["default_field_name"]." ".MSG_IS_REQUIRED_FIELD);
					}*/
				}
			}
		}
		$RequireFields=array_merge($RequireFields_arr,array(
			"CRM_JobID"=>array("data"=>$retArray['CRM_JobID'],"maxlength"=>"255","datatype"=>"string","errormsg"=>"CRM_JobID ".MSG_IS_REQUIRED_FIELD),
			"Jobtitle"=>array("data"=>$retArray['Jobtitle'],"maxlength"=>"255","datatype"=>"string","errormsg"=>"Jobtitle ".MSG_IS_REQUIRED_FIELD),
			"JobOpening"=>array("data"=>$retArray['JobOpening'],"maxlength"=>"2","datatype"=>"int","errormsg"=>"JobOpening ".MSG_IS_INTEGER_FIELD),
			"Closing_Date"=>array("data"=>@$retArray['Closing_Date'],"maxlength"=>"25","datatype"=>"date","errormsg"=>"Closing_Date ".MSG_IS_REQUIRED_FIELD." (d/m/Y)","format"=>"d/m/y"),
			"Closing_Date"=>array("data"=>@$retArray['Closing_Date'],"maxlength"=>"25","datatype"=>"futureDate","errormsg"=>"Closing_Date".MSG_DATE_SHOULD_FUTUER,"format"=>"d/m/y"),
			"Interview_Start_Date"=>array("data"=>@$retArray['Interview_Start_Date'],"maxlength"=>"25","datatype"=>"date","errormsg"=>"Interview_Start_Date ".MSG_IS_REQUIRED_FIELD." (d/m/Y)","format"=>"d/m/y"),
			"Interview_Start_Date"=>array("data"=>@$retArray['Interview_Start_Date'],"maxlength"=>"25","datatype"=>"futuredate","errormsg"=>"Interview_Start_Date".MSG_DATE_SHOULD_FUTUER,"format"=>"d/m/y"),
			"Possible_Start_Date"=>array("data"=>@$retArray['Possible_Start_Date'],"maxlength"=>"25","datatype"=>"date","errormsg"=>"Possible_Start_Date ".MSG_IS_REQUIRED_FIELD." (d/m/Y)"),"format"=>"d/m/y")
		);
		$retArray['extra_field_arr']=$extra_field_arr;
		$this->validateRequestParameters($RequireFields);
		return $retArray;
	}
	private function getExtraFields(){
		global $obj;
		$sql="SELECT cjf.* FROM `ExtraFieldsDefination` efd,customize_job_form cjf where efd.ExtraFieldDefinationID=cjf.extra_field_id and efd.EntityType='job' and cjf.is_extra_field='Yes' and cjf.extra_field_id != 0 and ExtraFieldType != 'label'";
		$rs=$this->instanceDBObj->select($sql);
		return $rs;
	}
	private function editExistingJob($retArray){
		global $AppAbsolutePath,$CompanyPath,$AppURL,$AppId,$secret,$cosnumerkey, $twitter_secret,$linkein_ApiKey, $linkein_secret,$collegeNASAccountParams;
		global $collegeNASAccountParams;

		extract($retArray);
		try{
			$LastDate="";
			$StartDate="";
			if(isset($Closing_Date)){
				$Closedtarr=explode("/",$Closing_Date);
				$LastDate=$Closedtarr[2]."-".$Closedtarr[1]."-".$Closedtarr[0];
			}
			if(isset($Possible_Start_Date)){
				$stdtarr=explode("/",$Possible_Start_Date);
				$StartDate=$stdtarr[2]."-".$stdtarr[1]."-".$stdtarr[0];
			}
			$extraSQL="";
			if(defined('MULTIPLE_NAS_ACCOUNTS') && MULTIPLE_NAS_ACCOUNTS===true){
				$extraSQL=", `college` = '".$College."'";
			}
			$sql="update `JobOrders` set
				`Jobtitle`			=	'" .mysql_real_escape_string($Jobtitle)."',
				`BriefDesc`			= 	'".mysql_real_escape_string($BriefDesc)."',
				`DetailDesc`		= 	'".mysql_real_escape_string($DetailDesc)."',
				`MainSkill`			= 	'".mysql_real_escape_string($MainSkill)."',
				`BusinessAreaID`	=	'".$BusinessAreaID."',
				`industryID`		=	'".$industryID."',
				`JobOpening`		= 	'".$JobOpening."',
				`City`				=	'".mysql_real_escape_string($City)."',
				`contact_phone`		=	'".mysql_real_escape_string($contact_phone)."',
				`industryID`		=	'".$industryID."',
				`BusinessAreaID`	=	'".$BusinessAreaID."',
				`PublicPosting`		=	'".$PublicPosting."',
				`CRM_JobID`			=	'".$CRM_JobID."',
				`salary`			=	'".$Weekly_Wage."',
				`ReqType`			=	'".$vacancy_type."',
				`hours`				=	'".$hours."',
				`State`				=	'".$state."',
				`Country`			=	'".$country."',
				`post_code`			=	'".@$post_code."',
				`LastDate`			=	'".$LastDate."',
				`StartDate`			=	'".$StartDate."',
				`row_added_by`		= 	'API',
				`ClientID`			=	'".$ClientID."' ".$extraSQL."
				where	JobID		=	'".$justApplyRefId."'";
				$this->instanceDBObj->edit($sql);
                $this->addExtraFieldValues($justApplyRefId, $extra_field_arr, "Edit",$retArray);
                if(defined('PROXIMITY_SEARCH_ENABLED') && PROXIMITY_SEARCH_ENABLED=="true"){
                    $this->setLatLanOfJob($justApplyRefId, $City, $state, $country, $post_code, $retArray, $ClientID);
                }
				$vacancyURL=generateSEOURL("",$justApplyRefId,mysql_real_escape_string($Jobtitle),"","",false,true);
				$resultArr["justApplyRefId"]=$justApplyRefId;
				$resultArr["vacancy_url"]=$vacancyURL;
    	        $ReferenceNumber="";
				$NGTURetVal="";

				if(isset($Chanel) && $Chanel != ""){
					$obj=$this->instanceDBObj;
					$channelArray=explode(",",$Chanel);
					$compData = getCompanyData();
					for($c=0;$c<count($channelArray);$c++){
						switch($channelArray[$c]){
							case "NAS":
								$extra_params=array();
								if(!$this->isJobPublishedToNAS($justApplyRefId)){
									$extra_params['channelParam']='nas';
									if(defined('MULTIPLE_NAS_ACCOUNTS') && MULTIPLE_NAS_ACCOUNTS===true){
										switch($NAS_ACCOUNT_KEY){
											case "0": //oxford college
												$sql_NAS="select `external_system_id`, `public_key`, `contracted_provide_ukprn`, `vacancy_owner_edsurn`, `employer_eds_urn` from company";
												$extra_params['channelParam']="nasoc";
											break;
											case "1": //reading college
												$sql_NAS="select `reading_external_system_id` as `external_system_id`, `reading_public_key` as `public_key`, `reading_contracted_provide_ukprn` as `contracted_provide_ukprn`, `reading_vacancy_owner_edsurn` as `vacancy_owner_edsurn` from company";
												$extra_params['channelParam']="nasrc";
											break;
											case "2": //berkshire college of agriculture
												$sql_NAS="select `agriculture_external_system_id` as `external_system_id`, `agriculture_public_key` as `public_key`, `agriculture_contracted_provide_ukprn` as `contracted_provide_ukprn`, `agriculture_vacancy_owner_edsurn` as `vacancy_owner_edsurn` from company";
												$extra_params['channelParam']="nasbca";
											break;
										}
									}else{
										$sql_NAS="select `external_system_id`, `public_key`, `contracted_provide_ukprn`, `vacancy_owner_edsurn`, `employer_eds_urn` from company";
									}
									$rs_NAS=$this->instanceDBObj->select($sql_NAS);
									$ExternalSystemId			=	(isset($rs_NAS[0]['external_system_id']) && trim($rs_NAS[0]['external_system_id'])!="")?$rs_NAS[0]['external_system_id']:"";
									$PublicKey					=	(isset($rs_NAS[0]['public_key']) && trim($rs_NAS[0]['public_key'])!="")?$rs_NAS[0]['public_key']:"";
									$ContractedProvideUkprn		=	(isset($rs_NAS[0]['contracted_provide_ukprn']) && trim($rs_NAS[0]['contracted_provide_ukprn'])!="")?$rs_NAS[0]['contracted_provide_ukprn']:"";
									$VacancyOwnerEdsUrn			=	(isset($rs_NAS[0]['vacancy_owner_edsurn']) && trim($rs_NAS[0]['vacancy_owner_edsurn'])!="")?$rs_NAS[0]['vacancy_owner_edsurn']:"";
									//$EmployerEdsUrn				=	(isset($rs_NAS[0]['employer_eds_urn']) && trim($rs_NAS[0]['employer_eds_urn'])!="")?$rs_NAS[0]['employer_eds_urn']:"";
									$EmployerEdsUrn				=	(isset($ERN_Number) && trim($ERN_Number)!="")?$ERN_Number:"";
									if(isset($ExternalSystemId) && trim($ExternalSystemId) != "" && isset($PublicKey) && !empty($PublicKey) && isset($ContractedProvideUkprn) && !empty($ContractedProvideUkprn) && isset($VacancyOwnerEdsUrn) && !empty($VacancyOwnerEdsUrn) && isset($EmployerEdsUrn) && !empty($EmployerEdsUrn)){
										define("ExternalSystemId",$ExternalSystemId);
				                        define("PublicKey",$PublicKey);
				                        define("ContractedProvideUkprn",$ContractedProvideUkprn);
				                        define("VacancyOwnerEdsUrn",$VacancyOwnerEdsUrn);
				                        define("EmployerEdsUrn",$EmployerEdsUrn);

										if(isset($Employer_Anonymous) && !empty($Employer_Anonymous)){
											$extra_params["Employer_Anonymous"]=$Employer_Anonymous;
										}
										if(isset($Employer_Anonymous_Name) && !empty($Employer_Anonymous_Name)){
											$extra_params["Employer_Anonymous_Name"]=$Employer_Anonymous_Name;
										}
										if(isset($Reality_Check) && !empty($Reality_Check)){
											$extra_params["RealityCheck"]=$Reality_Check;
										}
										if(isset($NAS_Framework) && !empty($NAS_Framework)){
											$extra_params["NAS_Framework"]=$NAS_Framework;
										}
										if(isset($Training_To_Be_Provided) && !empty($Training_To_Be_Provided)){
											$extra_params["Training_To_Be_Provided"]=$Training_To_Be_Provided;
										}
										if(isset($Expected_Duration) && !empty($Expected_Duration)){
											$extra_params["Expected_Duration"]=$Expected_Duration;
										}
										if(isset($Small_Employer_Wage_Incentive) && !empty($Small_Employer_Wage_Incentive)){
											$extra_params["Small_Employer_Wage_Incentive"]=(strtolower(trim($Small_Employer_Wage_Incentive))=="yes")?1:0;
										}
										$arr=postToNAS(array($justApplyRefId),$obj,$extra_params);
										if($arr["Status"]=="Success"){
											$ReferenceNumber=$arr["ReferenceNumber"];
											$resultArr["NAS_ReferenceNumber"]=$ReferenceNumber;
										}else{
											$resultArr["NAS_Error"]=$arr["Error"];
										}
									}else{
										$arr["Status"]="Failed";
										$arr["Error"]="Invalid NAS Configuration";
										$subject='LOG - Invalid NAS Configuration';
										$emailBody="<style type='text/css'>p{font-family:verdana;font-size:11px;}</style>";
										$emailBody.="<p>There is some problem in NAS Configuration</p>";
										$emailBody.="<p>Please follow below steps to add proper configuration.</p>";
										$emailBody.="<p>Please login to administration panel at <a href='".$AppURL."'>".$AppURL."</a>";
										$emailBody.="<br/>After successful login, please go to Manager->Company->Additional Configuration";
										$emailBody.="<br/>Please set up values for all fields mentioned under NAS Configuration.</p>";
										sendErrEmail($subject,$emailBody);
									}
								}
							break;
							case "NGTU":
								$action=($this->isJobPublishedToNGTU($justApplyRefId)===true)?"edit":"add";
								$sql_ngtu="select ngtu_access_token from company";
								$rs_NGTU=$this->instanceDBObj->select($sql_ngtu);
								$NGTU_Access_Token=(isset($rs_NGTU[0]['ngtu_access_token']) && trim($rs_NGTU[0]['ngtu_access_token'])!="")?$rs_NGTU[0]['ngtu_access_token']:"";
								if(isset($NGTU_Access_Token) && trim($NGTU_Access_Token) !="" ){
									$NGTURetVal=postToNGTU($justApplyRefId,$NGTU_Access_Token,$action,$obj);
									$resultArr["NGTU_Result"]=$NGTURetVal;
								}else{
									$subject='LOG - '.$this->companycode.' Invalid NGTU Configuration';
									$emailBody="<style type='text/css'>p{font-family:verdana;font-size:11px;}</style>";
									$emailBody.="<p>There is some problem in NGTU Configuration</p>";
									$emailBody.="<p>Please follow below steps to add proper configuration.</p>";
									$emailBody.="<p>Please login to administration panel at <a href='".$AppURL."'>".$AppURL."</a>";
									$emailBody.="<br/>After successful login, please go to Manager->Company->Additional Configuration";
									$emailBody.="<br/>Please set up values for all fields mentioned under NGTU Configuration.</p>";
									sendErrEmail($subject,$emailBody);
								}
							break;
							case "UJM":
								if(isset($justApplyRefId) && is_numeric($justApplyRefId) && $justApplyRefId > 0){
									$sql="update JobOrders set ujm_publish_required=1, publish_try_counter=0,ujm_published_status='updated' where JobID='".$justApplyRefId."'";
									$rs=$obj->edit($sql);
								}
							break;
							case "TOTALJOBS":
								if(isset($justApplyRefId) && is_numeric($justApplyRefId) && $justApplyRefId > 0){
									$sql="update JobOrders set totaljobs_publish_required=1, totaljobs_publish_try_counter=0, totaljobs_posting_success='updated' where JobID='".$justApplyRefId."'";
									$rs=$obj->edit($sql);
								}
							break;
							case "Facebook":
								$result=$this->FacebookPublish($compData,$justApplyRefId,$CRM_JobID,true);
								if($result['status']===true){
									$resultArr["FB"]=$result['msg'];
								}else{
									$resultArr["FB_Error"]=$result['msg'];
								}
							break;
							case "Twitter":
								$result=$this->twitterPublish($compData,$justApplyRefId,$CRM_JobID,true);
								if($result['status']===true){
									$resultArr["TW"]=$result['msg'];
								}else{
									$resultArr["TW_Error"]=$result['msg'];
								}
							break;
							case "careermap":
								if(isset($justApplyRefId) && is_numeric($justApplyRefId) && $justApplyRefId > 0){
									if(!isset($compData[0]['careermap_username']) || trim($compData[0]['careermap_username'])=="" || !isset($compData[0]['careermap_password']) || trim($compData[0]['careermap_password'])==""){
										$CareerMapRetVal["CAREERMAP_ID"]=0;
										$CareerMapRetVal["CareerMap_Message"]="Invalid Career Map Configuration";
										$subject='LOG - Invalid Career Map Configuration';
										$emailBody="<style type='text/css'>p{font-family:verdana;font-size:11px;}</style>";
										$emailBody.="<p>There is some problem in Career Map Configuration</p>";
										$emailBody.="<p>Please follow below steps to add proper configuration.</p>";
										$emailBody.="<p>Please login to administration panel at <a href='".$AppURL."'>".$AppURL."</a>";
										$emailBody.="<br/>After successful login, please go to Manager->Company->Additional Configuration";
										$emailBody.="<br/>Please set up values for all fields mentioned under Career Map Configuration.</p>";
										sendErrEmail($subject,$emailBody);
									}else{
										$CareerMapRetVal=postToCareerMap($justApplyRefId,$compData[0]['careermap_username'],$compData[0]['careermap_password'],"edit",$obj);
										$resultArr["CAREERMAP_Result"]=$CareerMapRetVal;
									}
								}
							break;
                            case "reed":
                                $action=($this->isJobPublishedToReed($justApplyRefId)===true)?"edit":"add";
								$sql_reed="select reed_username, reed_client_id, reed_api_token, reed_posting_key from company";
								$res_reed=$this->instanceDBObj->select($sql_reed);
								$reed_username=(isset($res_reed[0]['reed_username']) && trim($res_reed[0]['reed_username'])!="")?$res_reed[0]['reed_username']:"";
                                //$reed_client_id=(isset($res_reed[0]['reed_client_id']) && trim($res_reed[0]['reed_client_id'])!="")?$res_reed[0]['reed_client_id']:"";
                                //$reed_api_token=(isset($res_reed[0]['reed_api_token']) && trim($res_reed[0]['reed_api_token'])!="")?$res_reed[0]['reed_api_token']:"";
                                $reed_posting_key=(isset($res_reed[0]['reed_posting_key']) && trim($res_reed[0]['reed_posting_key'])!="")?$res_reed[0]['reed_posting_key']:"";
								if(isset($reed_username) && trim($reed_username)!="" && isset($reed_posting_key) && trim($reed_posting_key)!=""){
                                    $resultArr["Reed_Result"]=postJobToReed($justApplyRefId,$action,$reed_username,@$reed_client_id,@$reed_api_token,$reed_posting_key);
								}else{
									$subject='LOG - '.$this->companycode.' Invalid Reed Configuration';
									$emailBody="<style type='text/css'>p{font-family:verdana;font-size:11px;}</style>";
									$emailBody.="<p>There is some problem in Reee Configuration</p>";
									$emailBody.="<p>Please follow below steps to add proper configuration.</p>";
									$emailBody.="<p>Please login to administration panel at <a href='".$AppURL."'>".$AppURL."</a>";
									$emailBody.="<br/>After successful login, please go to Manager->Company->Additional Configuration";
									$emailBody.="<br/>Please set up values for all fields mentioned under Reed Configuration.</p>";
									sendErrEmail($subject,$emailBody);
								}
							break;
                            case "Hospitality Jobs UK":
                                if(isset($justApplyRefId) && is_numeric($justApplyRefId) && $justApplyRefId > 0){
                                    $sql="update JobOrders set HJUK_publish_required=1 where JobID='".$justApplyRefId."'";
                                    $rs=$obj->edit($sql);
									try{
										$hjukobj = new postVacancyToHJUK($justApplyRefId);
										$ret=$hjukobj->processRequest();
										$resultArr["HJUK"]=$ret;
									}catch(Exception $e){
										$resultArr["HJUK"]=$e->getMessage();
									}
                                }
                            break;
                            case "indeed":
                                if(isset($justApplyRefId) && is_numeric($justApplyRefId) && $justApplyRefId > 0){
                                    $sql="update JobOrders set indeed_publish_required=1 where JobID='".$justApplyRefId."'";
                                    $rs=$obj->edit($sql);
                                }
                            break;
						}
					}
					if(isset($resultArr)){
						$results=array($resultArr);
					}
				}
				if(isset($results) && is_array($results)){
					$success=array("status"=>"success","msg"=>MSG_JOB_UPDATED_SUCCESSFULLY,"results"=>$results);
				}else{
					$success=array("status"=>"success","msg"=>MSG_JOB_UPDATED_SUCCESSFULLY);
				}
				$this->response($this->json($success), 200);
		}catch(Exception $e){
			 $this->sendFalseResponse(200, $e->getMessage());
		}
	}
	public function editJob(){
		global $AppAbsolutePath,$CompanyPath,$AppURL,$AppId,$secret,$cosnumerkey, $twitter_secret,$linkein_ApiKey, $linkein_secret,$collegeNASAccountParams;
		global $collegeNASAccountParams;
		$this->parseRequestandSetSession();
		if(isset($_SESSION['sess_companyId']) && $_SESSION['sess_companyId']>0 && isset($_SESSION['sess_companyadmin']) && !empty($_SESSION['sess_companyadmin'])){
		 	if(isset($_SESSION['sess_token']) && isset($_SESSION['sess_userid'])){
				$retArray=$this->validateJobData("edit");
				$this->editExistingJob($retArray);
			}
		}
	}
	private function addExtraFieldValues($EntitiyID,$extra_field_arr,$act,$retArray){
		$cnt_extra_field_arr=count($extra_field_arr);
		extract($retArray);
		for($ef=0;$ef<$cnt_extra_field_arr;$ef++){
			$extra_field_name=$extra_field_arr[$ef];
			$ExtraFieldValuesID=0;
			$ExtraFieldTitle="";
			$value=isset($$extra_field_name)?$$extra_field_name:"";
			$sql_sel="SELECT field_title,extra_field_id FROM `customize_job_form` where default_field_name='".$extra_field_arr[$ef]."'";
			$res_sel=$this->instanceDBObj->select($sql_sel);
			if($act=="Edit"){
				$sql1="select ExtraFieldValuesID, ExtraFieldTitle from ExtraFieldValues where ExtraFieldDefinationID='".$res_sel[0]["extra_field_id"]."' and EntitiyID='".$EntitiyID."'";
				$rsval=$this->instanceDBObj->select($sql1);
				if($rsval && isset($rsval[0]['ExtraFieldValuesID']) && $rsval[0]['ExtraFieldValuesID']>0){
					$ExtraFieldValuesID=$rsval[0]['ExtraFieldValuesID'];
					$ExtraFieldTitle=$rsval[0]['ExtraFieldTitle'];
				}
			}
			if($ExtraFieldValuesID>0){
				$sql = "update ExtraFieldValues set ";
				$sql .= "	Value='".escapeStr($value)."',
								LastUpdateBy='".@$_SESSION["sess_userid"]."'
								where ExtraFieldValuesID='".$ExtraFieldValuesID."'";

				$this->instanceDBObj->edit($sql);
			}else{
				$sql_ins2="INSERT INTO `ExtraFieldValues`(`ExtraFieldDefinationID`, `ExtraFieldTitle`, `EntityType`, `EntitiyID`, `Value`, `row_added_by`, `LastUpdateOn`) VALUES ('".$res_sel[0]["extra_field_id"]."', '".escapeStr($res_sel[0]["field_title"])."', 'job', '".$EntitiyID."', '".escapeStr($value)."', 'API','".date("Y-m-d H:i:s")."')";
				$this->instanceDBObj->insert($sql_ins2);
			}
		}
	}
	public function isJobPublishedToNAS($jobID){
		$sql="select NAS_ReferenceNumber from JobOrders where JobID='".$jobID."'";
		try{
			$rs=$this->instanceDBObj->select($sql);
		}catch(Exception $e){

		}
		if(isset($rs[0]) && isset($rs[0]['NAS_ReferenceNumber']) && $rs[0]['NAS_ReferenceNumber'] > 0){
			return true;
		}
		return false;
	}
	private function isJobPublishedToNGTU($jobID){
		$sql="select ngtu_publish_status from JobOrders where JobID='".$jobID."'";
		try{
			$rs=$this->instanceDBObj->select($sql);
		}catch(Exception $e){

		}
		if(isset($rs[0]) && isset($rs[0]['ngtu_publish_status']) && $rs[0]['ngtu_publish_status'] == 'Published'){
			return true;
		}
		return false;
	}
    private function isJobPublishedToReed($jobID){
		$sql="select `reed_status` from JobOrders where JobID='".$jobID."'";
		try{
			$rs=$this->instanceDBObj->select($sql);
		}catch(Exception $e){

		}
		if(isset($rs[0]) && isset($rs[0]['reed_status']) && $rs[0]['reed_status'] == 'Published'){
			return true;
		}
		return false;
	}
	public function addJob(){
		global $AppAbsolutePath,$CompanyPath,$AppURL,$AppId,$secret,$cosnumerkey, $twitter_secret,$linkein_ApiKey, $linkein_secret,$collegeNASAccountParams;
	    $this->parseRequestandSetSession();
        if(isset($_SESSION['sess_companyId']) && $_SESSION['sess_companyId']>0 && isset($_SESSION['sess_companyadmin']) && !empty($_SESSION['sess_companyadmin'])){
            if(isset($_SESSION['sess_token']) && isset($_SESSION['sess_userid'])){
				$retArray=$this->validateJobData("add");
				if($this->addJobCallConvertToEdit===true){
					$this->editExistingJob($retArray);
					return;
				}
				extract($retArray);
				$PublicPosting=isset($PublicPosting)?$PublicPosting:"No";
                try{
                	$extraSQL="";
					if(defined('MULTIPLE_NAS_ACCOUNTS') && MULTIPLE_NAS_ACCOUNTS===true){
						$extraSQL=", `college` = '".$College."'";
					}
					require_once($AppAbsolutePath."StdFunctions/std_function.php");
					$resultArr=array();
					$Closedtarr=explode("/",$Closing_Date);
					$LastDate=$Closedtarr[2]."-".$Closedtarr[1]."-".$Closedtarr[0];
					$stdtarr=explode("/",$Possible_Start_Date);
					$StartDate=$stdtarr[2]."-".$stdtarr[1]."-".$stdtarr[0];
					$JobStatus=isset($this->ConfigVars["JobStatus"])?$this->ConfigVars["JobStatus"]:1;
					$sql_ins1="insert into JobOrders set
							companyID='".@$_SESSION['sess_companyID']."',
							industryID='".@$industryID."',
							Jobtitle='".escapeStr($Jobtitle)."',
							BriefDesc='".escapeStr(@$BriefDesc)."',
							DetailDesc='".escapeStr(@$DetailDesc)."',
							MainSkill='".escapeStr(@$MainSkill)."',
							City='".escapeStr(@$City)."',
							State='".escapeStr(@$state)."',
							Country='".escapeStr(@$country)."',
							post_code='".@$post_code."',
							ClientID='".@$ClientID."',
							BusinessAreaID='".@$BusinessAreaID."',
							JobType='Internal',
							salary='".@$Weekly_Wage."',
							ReqType='".@$vacancy_type."',
							StartDate='".@$StartDate."',
							LastDate='".@$LastDate."',
							JobOpening='".@$JobOpening."',
							PublicPosting='".$PublicPosting."',
							MediaPosting='".$PublicPosting."',
							JobStatus='".$JobStatus."',
							hours='".@$hours."',
							CRM_JobID='".$CRM_JobID."',
							CreatedBy='".@$_SESSION['sess_userid']."',
							LastUpdateBy='".@$_SESSION['sess_userid']."',
							row_added_by='API',
							CreatedOn='".date("Y-m-d H:i:s")."'".$extraSQL;

                    $EntitiyID=$this->instanceDBObj->insert($sql_ins1);
					if ($EntitiyID > 0){
						$this->addExtraFieldValues($EntitiyID, $extra_field_arr, "Add",$retArray);
                        if(defined('PROXIMITY_SEARCH_ENABLED') && PROXIMITY_SEARCH_ENABLED=="true"){
                            $this->setLatLanOfJob($EntitiyID,$City,@$state,@$country,$post_code,$retArray,$ClientID);
                        }
						/** Insert Job Status into JobPostStatus table */
						$insertStatusSql = "insert into JobPostStatus set
											JobID='".$EntitiyID."',
											UserId='".@$_SESSION['sess_userid']."',
											JobPostStatusID='".$JobStatus."',
											Comments='Initial Job Status',
											CreatedBy='".@$_SESSION['sess_userid']."',
											LastUpdateBy='".@$_SESSION['sess_userid']."',
											CreatedOn=now()";
						$this->instanceDBObj->insert($insertStatusSql);
					$vacancyURL=generateSEOURL("",$EntitiyID,mysql_real_escape_string($Jobtitle),"","",false,true);
					$resultArr["justApplyRefId"]=$EntitiyID;
					$resultArr["vacancy_url"]=$vacancyURL;
                    $ReferenceNumber="";
					$NGTURetVal="";
					if(isset($Chanel) && $Chanel != ""){
						$obj=$this->instanceDBObj;
						$channelArray=explode(",",$Chanel);
						$compData = getCompanyData();
						for($c=0;$c<count($channelArray);$c++){
							switch($channelArray[$c]){
								case "NGTU";
									$sql_ngtu="select ngtu_access_token from company";
									$rs_NGTU=$this->instanceDBObj->select($sql_ngtu);
									$NGTU_Access_Token=(isset($rs_NGTU[0]['ngtu_access_token']) && trim($rs_NGTU[0]['ngtu_access_token'])!="")?$rs_NGTU[0]['ngtu_access_token']:"";
									if(isset($NGTU_Access_Token) && trim($NGTU_Access_Token) !="" ){
										$NGTURetVal=postToNGTU($EntitiyID,$NGTU_Access_Token,"add",$obj);
										$resultArr["NGTU_Result"]=$NGTURetVal;
									}else{
										$subject='LOG - '.$this->companycode.' Invalid NGTU Configuration';
										$emailBody="<style type='text/css'>p{font-family:verdana;font-size:11px;}</style>";
										$emailBody.="<p>There is some problem in NGTU Configuration</p>";
										$emailBody.="<p>Please follow below steps to add proper configuration.</p>";
										$emailBody.="<p>Please login to administration panel at <a href='".$AppURL."'>".$AppURL."</a>";
										$emailBody.="<br/>After successful login, please go to Manager->Company->Additional Configuration";
										$emailBody.="<br/>Please set up values for all fields mentioned under NGTU Configuration.</p>";
										sendErrEmail($subject,$emailBody);
									}
								break;
								case "NAS":
									$extra_params=array();
									$extra_params['channelParam']='nas';
									if(defined('MULTIPLE_NAS_ACCOUNTS') && MULTIPLE_NAS_ACCOUNTS===true){
										switch($NAS_ACCOUNT_KEY){
											case "0": //oxford college
												$sql_NAS="select `external_system_id`, `public_key`, `contracted_provide_ukprn`, `vacancy_owner_edsurn`, `employer_eds_urn` from company";
												$extra_params['channelParam']="nasoc";
											break;
											case "1": //reading college
												$sql_NAS="select `reading_external_system_id` as `external_system_id`, `reading_public_key` as `public_key`, `reading_contracted_provide_ukprn` as `contracted_provide_ukprn`, `reading_vacancy_owner_edsurn` as `vacancy_owner_edsurn` from company";
												$extra_params['channelParam']="nasrc";
											break;
											case "2": //berkshire college of agriculture
												$sql_NAS="select `agriculture_external_system_id` as `external_system_id`, `agriculture_public_key` as `public_key`, `agriculture_contracted_provide_ukprn` as `contracted_provide_ukprn`, `agriculture_vacancy_owner_edsurn` as `vacancy_owner_edsurn` from company";
												$extra_params['channelParam']="nasbca";
											break;
										}
									}else{
										$sql_NAS="select `external_system_id`, `public_key`, `contracted_provide_ukprn`, `vacancy_owner_edsurn`, `employer_eds_urn` from company";
									}
									$rs_NAS=$this->instanceDBObj->select($sql_NAS);
									$ExternalSystemId			=	(isset($rs_NAS[0]['external_system_id']) && trim($rs_NAS[0]['external_system_id'])!="")?$rs_NAS[0]['external_system_id']:"";
									$PublicKey					=	(isset($rs_NAS[0]['public_key']) && trim($rs_NAS[0]['public_key'])!="")?$rs_NAS[0]['public_key']:"";
									$ContractedProvideUkprn		=	(isset($rs_NAS[0]['contracted_provide_ukprn']) && trim($rs_NAS[0]['contracted_provide_ukprn'])!="")?$rs_NAS[0]['contracted_provide_ukprn']:"";
									$VacancyOwnerEdsUrn			=	(isset($rs_NAS[0]['vacancy_owner_edsurn']) && trim($rs_NAS[0]['vacancy_owner_edsurn'])!="")?$rs_NAS[0]['vacancy_owner_edsurn']:"";
									//$EmployerEdsUrn				=	(isset($rs_NAS[0]['employer_eds_urn']) && trim($rs_NAS[0]['employer_eds_urn'])!="")?$rs_NAS[0]['employer_eds_urn']:"";
									$EmployerEdsUrn				=	(isset($ERN_Number) && trim($ERN_Number)!="")?$ERN_Number:"";
									if(isset($ExternalSystemId) && trim($ExternalSystemId) != "" && isset($PublicKey) && !empty($PublicKey) && isset($ContractedProvideUkprn) && !empty($ContractedProvideUkprn) && isset($VacancyOwnerEdsUrn) && !empty($VacancyOwnerEdsUrn) && isset($EmployerEdsUrn) && !empty($EmployerEdsUrn)){
										define("ExternalSystemId",$ExternalSystemId);
				                        define("PublicKey",$PublicKey);
				                        define("ContractedProvideUkprn",$ContractedProvideUkprn);
				                        define("VacancyOwnerEdsUrn",$VacancyOwnerEdsUrn);
				                        define("EmployerEdsUrn",$EmployerEdsUrn);

										if(isset($Employer_Anonymous) && !empty($Employer_Anonymous)){
											$extra_params["Employer_Anonymous"]=$Employer_Anonymous;
										}
										if(isset($Employer_Anonymous_Name) && !empty($Employer_Anonymous_Name)){
											$extra_params["Employer_Anonymous_Name"]=$Employer_Anonymous_Name;
										}
										if(isset($Reality_Check) && !empty($Reality_Check)){
											$extra_params["RealityCheck"]=$Reality_Check;
										}
										if(isset($NAS_Framework) && !empty($NAS_Framework)){
											$extra_params["NAS_Framework"]=$NAS_Framework;
										}
										if(isset($Training_To_Be_Provided) && !empty($Training_To_Be_Provided)){
											$extra_params["Training_To_Be_Provided"]=$Training_To_Be_Provided;
										}
										if(isset($Expected_Duration) && !empty($Expected_Duration)){
											$extra_params["Expected_Duration"]=$Expected_Duration;
										}
										if(isset($Small_Employer_Wage_Incentive) && !empty($Small_Employer_Wage_Incentive)){
											$extra_params["Small_Employer_Wage_Incentive"]=(strtolower(trim($Small_Employer_Wage_Incentive))=="yes")?1:0;
										}
										$arr=postToNAS(array($EntitiyID),$obj,$extra_params);
										if($arr["Status"]=="Success"){
											$ReferenceNumber=$arr["ReferenceNumber"];
											$resultArr["NAS_ReferenceNumber"]=$ReferenceNumber;
										}else{
											$resultArr["NAS_Error"]=$arr["Error"];
										}
									}else{
											$subject='LOG - Invalid NAS Configuration';
											$collegeaccount="";
											if(defined('MULTIPLE_NAS_ACCOUNTS') && MULTIPLE_NAS_ACCOUNTS===true){
												$collegeaccount="for $College";
											}
											//$emailBody="<style type='text/css'>p{font-family:verdana;font-size:11px;}</style>";
											$emailBody.="There is some problem in NAS Configuration $collegeaccount.\r\n";
											$emailBody.="Please follow below steps to add proper configuration.\r\n";
											$emailBody.="Please login to administration panel at $AppURL.\r\n";
											$emailBody.="After successful login, please go to Manager->Company->Additional Configuration.\r\n";
											$emailBody.="Please set up values for all fields mentioned under NAS Configuration.\r\n";
											sendErrEmail($subject,$emailBody);
									}
								break;
								case "Facebook":
									$result=$this->FacebookPublish($compData,$EntitiyID,$CRM_JobID,false);
									if($result['status']===true){
										$resultArr["FB"]=$result['msg'];
									}else{
										$resultArr["FB_Error"]=$result['msg'];
									}
								break;
								case "Twitter":
									$result=$this->twitterPublish($compData,$EntitiyID,$CRM_JobID,false);
									if($result['status']===true){
										$resultArr["TW"]=$result['msg'];
									}else{
										$resultArr["TW_Error"]=$result['msg'];
									}
								break;
								case "LI":
									$compData = getAccessData($compData, "submit_to_linkedin");
									if (isset($compData[0]['linkedin_auth_code']) && trim($compData[0]['linkedin_auth_code']) != "" ){
										$ln = new SimpleLinkedIn($linkein_ApiKey, $linkein_secret);
										$ln -> addScope('rw_nus');
										$ln -> setTokenData($compData[0]['linkedin_auth_code']);
										$postarray = LinkedinPostArray($EntitiyID);
										try {
											$ret = $ln -> fetch('POST', '/v1/people/~/shares', $postarray);
											$resultArr["LI"]=LINKEDIN_POST_SUCCESS;
										} catch(Exception $e) {
											$resultArr["LI_Error"]=LINKEDIN_POST_ERROR;
										}
									}else{
										$resultArr["LI_Error"]=LBL_LINKEDIN_NOT_AUTHORIZED;
									}
								break;
								case "UJM":
									if(isset($EntitiyID) && is_numeric($EntitiyID) && $EntitiyID > 0){
										$sql="update JobOrders set ujm_publish_required=1 where JobID='".$EntitiyID."'";
										$rs=$obj->edit($sql);
									}
								break;
								case "TOTALJOBS":
									if(isset($EntitiyID) && is_numeric($EntitiyID) && $EntitiyID > 0){
										$sql="update JobOrders set totaljobs_publish_required=1, totaljobs_publish_try_counter=0, totaljobs_posting_success='' where JobID='".$EntitiyID."'";
										$rs=$obj->edit($sql);
									}
								break;
								case "careermap":
									if(isset($EntitiyID) && is_numeric($EntitiyID) && $EntitiyID > 0){
										if(!isset($compData[0]['careermap_username']) || trim($compData[0]['careermap_username'])=="" || !isset($compData[0]['careermap_password']) || trim($compData[0]['careermap_password'])==""){
											$CareerMapRetVal["CAREERMAP_ID"]=0;
											$CareerMapRetVal["CareerMap_Message"]="Invalid Career Map Configuration";
											$subject='LOG - Invalid Career Map Configuration';
											$emailBody="<style type='text/css'>p{font-family:verdana;font-size:11px;}</style>";
											$emailBody.="<p>There is some problem in Career Map Configuration</p>";
											$emailBody.="<p>Please follow below steps to add proper configuration.</p>";
											$emailBody.="<p>Please login to administration panel at <a href='".$AppURL."'>".$AppURL."</a>";
											$emailBody.="<br/>After successful login, please go to Manager->Company->Additional Configuration";
											$emailBody.="<br/>Please set up values for all fields mentioned under Career Map Configuration.</p>";
											sendErrEmail($subject,$emailBody);
										}else{
											$CareerMapRetVal=postToCareerMap($EntitiyID,$compData[0]['careermap_username'],$compData[0]['careermap_password'],"add",$obj);
											$resultArr["CAREERMAP_Result"]=$CareerMapRetVal;
										}
									}
								break;
                                case "reed":
                                    $action=($this->isJobPublishedToReed($justApplyRefId)===true)?"edit":"add";
                                    $sql_reed="select reed_username, reed_client_id, reed_api_token, reed_posting_key from company";
                                    $res_reed=$this->instanceDBObj->select($sql_reed);
                                    $reed_username=(isset($res_reed[0]['reed_username']) && trim($res_reed[0]['reed_username'])!="")?$res_reed[0]['reed_username']:"";
                                    //$reed_client_id=(isset($res_reed[0]['reed_client_id']) && trim($res_reed[0]['reed_client_id'])!="")?$res_reed[0]['reed_client_id']:"";
                                    //$reed_api_token=(isset($res_reed[0]['reed_api_token']) && trim($res_reed[0]['reed_api_token'])!="")?$res_reed[0]['reed_api_token']:"";
                                    $reed_posting_key=(isset($res_reed[0]['reed_posting_key']) && trim($res_reed[0]['reed_posting_key'])!="")?$res_reed[0]['reed_posting_key']:"";
                                    if(isset($reed_username) && trim($reed_username)!="" && isset($reed_posting_key) && trim($reed_posting_key)!=""){
                                        $resultArr["Reed_Result"]=postJobToReed($EntitiyID,$action,$reed_username,$reed_client_id,$reed_api_token,$reed_posting_key);
                                    }else{
                                        $subject='LOG - '.$this->companycode.' Invalid Reed Configuration';
                                        $emailBody="<style type='text/css'>p{font-family:verdana;font-size:11px;}</style>";
                                        $emailBody.="<p>There is some problem in Reee Configuration</p>";
                                        $emailBody.="<p>Please follow below steps to add proper configuration.</p>";
                                        $emailBody.="<p>Please login to administration panel at <a href='".$AppURL."'>".$AppURL."</a>";
                                        $emailBody.="<br/>After successful login, please go to Manager->Company->Additional Configuration";
                                        $emailBody.="<br/>Please set up values for all fields mentioned under Reed Configuration.</p>";
                                        sendErrEmail($subject,$emailBody);
                                    }
                                break;
                                case "Hospitality Jobs UK":
									if(isset($EntitiyID) && is_numeric($EntitiyID) && $EntitiyID > 0){
										$sql="update JobOrders set HJUK_publish_required=1 where JobID='".$EntitiyID."'";
										$rs=$obj->edit($sql);
										$hjukobj = new postVacancyToHJUK($EntitiyID);
										$ret=$hjukobj->processRequest();
										$resultArr["HJUK"]=$ret;
									}
								break;
                                case "indeed":
									if(isset($EntitiyID) && is_numeric($EntitiyID) && $EntitiyID > 0){
										$sql="update JobOrders set indeed_publish_required=1 where JobID='".$EntitiyID."'";
										$rs=$obj->edit($sql);
									}
								break;
							}
						}
					}
					$results=array($resultArr);
                    $success=array("status"=>"success","msg"=>MSG_JOB_ADDED_SUCCESSFULLY,"results"=>$results);
                    $this->response($this->json($success), 200);
					}else{
						$this->sendFalseResponse(200, "Sorry, there is some problem while adding Vacancy.");
					}
                }catch(Exception $e){
                    $this->sendFalseResponse(200, $e->getMessage());
                }
            }else{
                $this->sendFalseResponse(200, ERR_SAVE_DATA);
            }
        }else{
            $this->sendFalseResponse(200, INVALID_TOKEN);
        }
    }
    private function prepareChanelResponse($channel,$returnResponse){
    	$retVal=array();
    	switch($channel){
			case "NAS":
				if($returnResponse["Status"]=="Success"){
					$retVal["status"]=1;
					$retVal["vacancyid"]=$returnResponse["ReferenceNumber"];
				}else{
					$retVal["status"]=0;
					$retVal["errormessage"]=$returnResponse["Error"];
				}
			break;
			case "NGTU";
				if(isset($returnResponse["NGTU_ID"]) && $returnResponse["NGTU_ID"]>0){
					$retVal["status"]=1;
					$retVal["vacancyid"]=$returnResponse["NGTU_ID"];
				}else{
					$retVal["status"]=0;
					$retVal["errormessage"]=$returnResponse["NGTU_Message"];
				}
			break;
			case "Facebook":
				if($returnResponse['status']===true){
					$retVal["status"]=1;
					$retVal["vacancyid"]=$returnResponse["post_id"];
				}else{
					$retVal["status"]=0;
					$retVal["errormessage"]=$returnResponse['msg'];
				}
			break;
			case "Twitter":
				if($returnResponse['status']===true){
					$retVal["status"]=1;
					$retVal["vacancyid"]=$returnResponse['tweetID'];
				}else{
					$retVal["status"]=0;
					$retVal["errormessage"]=$returnResponse['msg'];
				}
			break;
			case "LI":
			break;
			case "careermap":
				if(isset($returnResponse["CAREERMAP_ID"]) && $returnResponse["CAREERMAP_ID"]>0){
					$retVal["status"]=1;
					$retVal["vacancyid"]=$returnResponse["CAREERMAP_ID"];
				}else{
					$retVal["status"]=0;
					$retVal["errormessage"]=$returnResponse["CareerMap_Message"];
				}
			break;
			case "reed":
				if(isset($returnResponse["Reed_ID"]) && $returnResponse["Reed_ID"]>0){
					$retVal["status"]=1;
					$retVal["vacancyid"]=$returnResponse["Reed_ID"];
				}else{
					$retVal["status"]=0;
					$retVal["errormessage"]=$returnResponse["Reed_Message"];
				}
			break;
		}
		return $retVal;
    }
    function setLatLanOfJob($JobID,$city="",$state="",$country="",$post_code="",$retArray=array(),$ClientID=""){
        $job_address="";
        if(isset($retArray['Street1']) && !empty($retArray['Street1'])){
			$job_address.=$retArray['Street1'];
			$plus="+";
		}
		if(isset($retArray['Street2']) && !empty($retArray['Street2'])){
			$job_address.=$plus.$retArray['Street2'];
			$plus="+";
		}
		if(isset($retArray['Street3']) && !empty($retArray['Street3'])){
			$job_address.=$plus.$retArray['Street3'];
			$plus="+";
		}
		if(isset($city) && !empty($city)){
			$job_address.=$plus.$city;
			$plus="+";
		}
        if(isset($post_code) && !empty($post_code)){
			$job_address.=$plus.$post_code;
			$plus="+";
		}else{
            if($ClientID!=""){
                $sql_client_zip="SELECT `Zip` FROM `client` WHERE `ClientID`='".$ClientID."'";
                $res_client_zip=$this->instanceDBObj->select($sql_client_zip);
                $post_code=$res_client_zip[0]["Zip"];
                $job_address.=$plus.$post_code;
                $plus="+";
            }
        }
		if(isset($state) && !empty($state)){
			$job_address.=$plus.$state;
			$plus="+";
		}
		if(isset($country) && !empty($country)){
			$job_address.=$plus.$country;
			$plus="+";
		}
        if($job_address){
            $url="https://maps.googleapis.com/maps/api/geocode/json?address=".urlencode($job_address)."&sensor=false&key=".GOOGLE_DISTANCE_MATRIX_API_KEY."&c‌omponents=country:UK";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            $response = curl_exec($ch);
            curl_close($ch);
            $response_a = json_decode($response);
            if(strtolower($response_a->status)=="ok"){
                $post_code_found=false;
                /* if multiple address found then we match post_code, and match post_code's, lat-long will be saved */
                if($post_code!=""){
                    $cnt_result=count($response_a->results);
                    for($r=0;$r<$cnt_result;$r++){
                        $cnt_add_com=count($response_a->results[$r]->address_components);
                        for($ac=0;$ac<$cnt_add_com;$ac++){
                            if($response_a->results[$r]->address_components[$ac]->types[0]=="postal_code"){
                                if($response_a->results[$r]->address_components[$ac]->short_name==$post_code){
                                    $post_code_found=true;
                                    $lat=$response_a->results[$r]->geometry->location->lat;
                                    $lon=$response_a->results[$r]->geometry->location->lng;
                                    break 2;
                                }
                            }
                        }
                    }
                    if(!$post_code_found){
                        $lat=$response_a->results[0]->geometry->location->lat;
                        $lon=$response_a->results[0]->geometry->location->lng;
                    }
                }else{
                    $lat=$response_a->results[0]->geometry->location->lat;
                    $lon=$response_a->results[0]->geometry->location->lng;
                }
                if($lat!="" && $lon!=""){
                    $sql_upd="UPDATE `JobOrders` set latitude='".$lat."', longitude='".$lon."' where `JobID`='".$JobID."'";
                    $this->instanceDBObj->edit($sql_upd);
                }
            }
        }
    }
    private function isunique($CRM_JobID){
    	global $obj;
		if(trim($CRM_JobID) != ""){
			$sql="select count(CRM_JobID) as tot,IFNULL(JobID,'') as JobID from JobOrders where CRM_JobID='".$CRM_JobID."'";
			try{
				$rs=$this->instanceDBObj->select($sql,true);
				if(isset($rs[0]['tot']) && $rs[0]['tot']>0){
					return array("status"=>false,"JobID"=>$rs[0]['JobID']);
				}
			}catch(Exception $e){
				return array("status"=>false,"JobID"=>0);
			}
		}
		return array("status"=>true,"JobID"=>0);;
    }
	private function updateVacancyFBPostID($JobID,$FBPostID,$PostedOn,$publised_to_FB='Yes'){
		global $obj;
		$sql="update JobOrders set published_to_FB='".$publised_to_FB."',FB_Post_ID='".$FBPostID."',postedon='".$PostedOn."' where JobID='".$JobID."'";
		$obj->edit($sql);
	}
	private function checkFBPublishedStatus($JobID){
		global $obj;
		$sql="select published_to_FB, FB_Post_ID, postedon from JobOrders where JobID='".$JobID."'";
		$rs=$obj->select($sql);
		if($rs && count($rs)){
			return $rs;
		}
		return false;
	}
	private function checkTwitterPublishedStatus($JobID){
		global $obj;
		$sql="select published_to_Twitter, tweet_ID from JobOrders where JobID='".$JobID."'";
		$rs=$obj->select($sql);
		if($rs && count($rs)){
			return $rs;
		}
		return false;
	}
	private function updateVacancyTweetID($JobID,$TweeID,$publised_to_Twitter='Yes'){
		global $obj;
		$sql="update JobOrders set published_to_Twitter='".$publised_to_Twitter."',tweet_ID='".$TweeID."' where JobID='".$JobID."'";
		$obj->edit($sql);
	}
	private function twitterPublish($compData,$EntitiyID,$CRM_JobID,$editCheck=false){
		global $obj,$AppAbsolutePath,$CompanyPath,$AppURL,$cosnumerkey, $twitter_secret;
		$compData1 = getAccessData($compData, "submit_to_twitter");
		$resultArr="";
		if (isset($compData1[0]['twitter_oauth_token']) && isset($compData1[0]['twitter_oauth_token_secret']) && trim($compData1[0]['twitter_oauth_token']) != "" && trim($compData1[0]['twitter_oauth_token_secret']) != "") {
			if($editCheck){
				$twrs=$this->checkTwitterPublishedStatus($EntitiyID);
				if($twrs !== false){
					if(isset($twrs[0]['tweet_ID']) && !empty($twrs[0]['tweet_ID'])){
						$this->removeTweet($EntitiyID, $twrs[0]['tweet_ID'], $compData1);
					}
				}
			}
			try{
				$twitteroauth = new TwitterOAuth($cosnumerkey, $twitter_secret, $compData1[0]['twitter_oauth_token'], $compData1[0]['twitter_oauth_token_secret']);
				$user_info = $twitteroauth -> get('account/verify_credentials');
				$statustext = getTwitterText($EntitiyID);
				$res = $twitteroauth -> post('statuses/update', array('status' => $statustext));
				if (isset($res -> error)) {
					$resultArr["status"]=error;
					$resultArr["msg"]=$res->error;
				} else {
					$tweetID=$res->id;
					$this->updateVacancyTweetID($EntitiyID,$tweetID,"Yes");
					$resultArr["status"]=true;
					$resultArr["tweetID"]=$tweetID;
					$resultArr["msg"]="Success";
				}
			}catch(Exception $e) {
				$resultArr["status"]=false;
				$resultArr["msg"]=$e->getMessage();
			}
		}else{
			$resultArr["status"]=false;
			$resultArr["msg"]=LBL_TWITTER_NOT_AUTHORIZED;
		}
		logChannelResponse("Twitter",$EntitiyID,$CRM_JobID,$tweetID,$mode,"",serialize($statustext),escapeStr($res),$resultArr["status"]);
		return $resultArr;
	}
	private function removeTweet($JobID,$id,$compData1){
		global $obj,$AppAbsolutePath,$CompanyPath,$AppURL,$cosnumerkey, $twitter_secret;
		if (isset($compData1[0]['twitter_oauth_token']) && isset($compData1[0]['twitter_oauth_token_secret']) && trim($compData1[0]['twitter_oauth_token']) != "" && trim($compData1[0]['twitter_oauth_token_secret']) != "" && !empty($id)){
			try{
				$twitteroauth = new TwitterOAuth($cosnumerkey, $twitter_secret, $compData1[0]['twitter_oauth_token'], $compData1[0]['twitter_oauth_token_secret']);
				$user_info = $twitteroauth -> get('account/verify_credentials');
				$res = $twitteroauth -> post('statuses/destroy',array("id"=>$id));
				$this->updateVacancyTweetID($JobID,"","No" );
			}catch(Exception $e) {
					$resultArr["TW_Error"]=$e->getMessage();
			}
		}
	}
	private function FacebookPublish($compData,$EntitiyID,$CRM_JobID,$editCheck=false){
		global $obj,$AppAbsolutePath,$CompanyPath,$AppURL,$AppId,$secret;
		$compData1 = getAccessData($compData, "submit_to_facebook");
		$authCode="";
		$channelPostID=0;
		if (isset($compData1[0]['facebook_auth_code']) && !empty($compData1[0]['facebook_auth_code'])) {
			$authCode=$compData1[0]['facebook_auth_code'];
		}else if(isset($compData[0]['facebook_auth_code']) && !empty($compData[0]['facebook_auth_code'])){
			$authCode=$compData[0]['facebook_auth_code'];
		}
		$FBPostArray = FBPostArray($EntitiyID);
		if($editCheck===true){
			$fbpoststatus=$this->checkFBPublishedStatus($EntitiyID);
			if($fbpoststatus !== false ){
				if(isset($fbpoststatus[0]['published_to_FB']) && $fbpoststatus[0]['published_to_FB']=='Yes' && isset($fbpoststatus[0]['FB_Post_ID']) && !empty($fbpoststatus[0]['FB_Post_ID'])){
					if($authCode != "") {
						$removeret=$this->removeFBPost($authCode,$fbpoststatus[0]['FB_Post_ID'],$fbpoststatus[0]['postedon'],@$compData[0]['fb_page_id']);
						/*$editret=$this->editFBPost($authCode,$fbpoststatus[0]['FB_Post_ID'],$fbpoststatus[0]['postedon'],$FBPostArray);
						if($editret['status']===true){
							$resultArr["status"]=true;
							$resultArr["msg"]="Success";
							return $resultArr; // As post edited successfully, we need to send back. No need to update anything.
						}*/
						if($removeret['status']===true){
							$this->updateVacancyFBPostID($EntitiyID,"","");
						}
					}
				}
			}
		}
		if($authCode != "") {
			if(isset($compData[0]['fb_page_id']) && !empty($compData[0]['fb_page_id'])){
				$ret=$this->publishToFBPage($compData[0]['fb_page_id'], $authCode, $FBPostArray,$compData[0]['fb_userid']);
				if($ret['status'] === true){
					if(isset($ret['post_id']) && !empty($ret['post_id'])){
						$this->updateVacancyFBPostID($EntitiyID,$ret['post_id'],"Page");
						$channelPostID=$ret['post_id'];
					}
					$resultArr["status"]=true;
					$resultArr["msg"]="Success";
				}else{
					$resultArr["status"]=false;
					$resultArr["msg"]=@$ret["msg"];
				}
			}else{
				$ret=$this->publishToFBWall($authCode, $FBPostArray);
				if($ret['status'] === true){
					if(isset($ret['post_id']) && !empty($ret['post_id'])){
						$this->updateVacancyFBPostID($EntitiyID,$ret['post_id'],"Wall");
						$channelPostID=$ret['post_id'];
					}
					$resultArr["status"]=true;
					$resultArr["post_id"]=$ret['post_id'];
					$resultArr["msg"]="Success";
				}else{
					$resultArr["status"]=false;
					$resultArr["msg"]=@$ret["msg"];
				}
			}
		}else{
			$resultArr["status"]=false;
			$resultArr["msg"]=LBL_FB_NOT_AUTHORIZED;
		}
		$mode=(!$editCheck)?"add":"edit";
		$response=(isset($ret) && isset($ret['actual_response']))?serialize($ret['actual_response']):serialize($resultArr);
		logChannelResponse("Facebook",$EntitiyID,$CRM_JobID,$channelPostID,$mode,"",serialize($FBPostArray),escapeStr($response),$resultArr["status"]);
		return $resultArr;
	}
	private function editFBPost($authCode,$postId,$postedon,$FBPostArray){
		global $AppId,$secret;
		$retuArr=array();
		try{
			$facebook = new Facebook( array('appId' => $AppId, 'secret' => $secret, 'cookie' => true));
			$facebook -> setAccessToken($authCode);
			$result=$facebook -> api("/".$postId,"POST",$FBPostArray);
			$retuArr=array("status"=>$result['success'],"msg"=>LBL_FB_POST_UPDATED,"post_id"=>$postId);
		}catch(Exception $e){
			$retuArr=array("status"=>false,"msg"=>$e->getMessage());
		}
		return $retuArr;
	}
	private function removeFBPost($authCode,$postId,$postedon,$userPageId){
		global $AppId,$secret;
		try{
			$facebook = new Facebook( array('appId' => $AppId, 'secret' => $secret, 'cookie' => true));
			$facebook -> setAccessToken($authCode);
			if($postedon == 'Page'){
				$result=$facebook -> api("/me/accounts");
				for($i=0;$i<count($result['data']);$i++){
					if($result['data'][$i]['id']==$userPageId){
						$pageAccessToken=$result['data'][$i]["access_token"];
					}
				}
				if($pageAccessToken!=""){
					$facebook -> setAccessToken($pageAccessToken);
					$result=$facebook -> api($postId,"DELETE");
					return array("status"=>$result['success'],"msg"=>LBL_FB_POST_DELETED);
				}
			}else{
				$result=$facebook -> api("/me/feed/".$postId,"DELETE");
				return array("status"=>$result['success'],"msg"=>LBL_FB_POST_DELETED);
			}
		}catch(Exception $e){
			return array("status"=>false,"msg"=>$e->getMessage());
		}
	}
	private function publishToFBPage($userPageId,$authCode,$FBPostArray,$userId){
		global $AppId,$secret;
		$retArray=array();
		try {
			$pageAccessToken="";
			$post_url = '/' . $userPageId . '/feed';
			$facebook = new Facebook( array('appId' => $AppId, 'secret' => $secret, 'cookie' => true));
			$facebook -> setAccessToken($authCode);
			$result=$facebook -> api("/".$userId."/accounts");
			for($i=0;$i<count($result['data']);$i++){
				if($result['data'][$i]['id']==$userPageId){
					$pageAccessToken=$result['data'][$i]["access_token"];
				}
			}
			if($pageAccessToken!=""){
				$facebook -> setAccessToken($pageAccessToken);
				$result = $facebook -> api($post_url, 'post', $FBPostArray);
				if(isset($result["id"]) && !empty($result["id"])){
					$retArray["post_id"]=$result["id"];
					$retArray["status"]=true;
					$retArray["msg"]="";
					$retArray["actual_response"]=$result;
				}else{
					$retArray["post_id"]=0;
					$retArray["status"]=false;
					$retArray["msg"]="";
					$retArray["actual_response"]=$result;
				}
				return $retArray;

			}else{
				$retArray["post_id"]=0;
				$retArray["status"]=false;
				$retArray["msg"]="Invalid Page Configuration";
			}
		} catch(Exception $e) {
			$retArray["post_id"]=0;
			$retArray["status"]=false;
			$retArray["msg"]=$e -> getMessage();
		}
		return $retArray;
	}
	private function publishToFBWall($authCode,$FBPostArray){
		global $AppId,$secret;
		$retArray=array();
		try {
			$facebook = new Facebook( array('appId' => $AppId, 'secret' => $secret, 'cookie' => true));
			$facebook -> setAccessToken($authCode);
			$result = $facebook -> api('/me/feed/', 'post', $FBPostArray);
			if(isset($result["id"]) && !empty($result["id"])){
				$retArray["post_id"]=$result["id"];
				$retArray["status"]=true;
				$retArray["actual_response"]=$result;
			}else{
				$retArray["post_id"]=0;
				$retArray["status"]=false;
				$retArray["msg"]="";
				$retArray["actual_response"]=$result;
			}
		} catch(Exception $e) {
			$retArray["post_id"]=0;
			$retArray["status"]=false;
			$retArray["msg"]=$e -> getMessage();
		}
		return $retArray;
	}
	public function changeVacancyDisplaySetting(){
		global $obj;
		$this->parseRequestandSetSession();
		if(isset($_SESSION['sess_companyId']) && $_SESSION['sess_companyId']>0 && isset($_SESSION['sess_companyadmin']) && !empty($_SESSION['sess_companyadmin'])){
			if(isset($_SESSION['sess_token']) && isset($_SESSION['sess_userid'])){
				$JsonReqObj=$this->decodeRequest();
				$justApplyRefId=isset($JsonReqObj->justApplyRefId)?$JsonReqObj->justApplyRefId:"";
				if(!is_numeric($justApplyRefId) || empty($justApplyRefId)){
					$this->sendFalseResponse(200, INVALID_JUSTAPPLY_REF_ID);
				}
				if(!$this->validateJustApplyRefID($justApplyRefId)){
					$this->sendFalseResponse(200, INVALID_JUSTAPPLY_REF_ID);
				}
				$pubpostarr=array("yes","no");
				if(isset($JsonReqObj->Display_On_Carrier_Site)){
					$PublicPosting=trim($JsonReqObj->Display_On_Carrier_Site);
					if(!in_array(strtolower(trim($PublicPosting)),$pubpostarr)){
						$this->sendFalseResponse(200, MSG_INVALID_VALUE);
					}
				}else{
					$this->sendFalseResponse(200, MSG_DISPLAY_CAREER_SITE_REQ);
				}
				$PublicPosting=(isset($PublicPosting) && !empty($PublicPosting))?ucfirst(strtolower($PublicPosting)):"No";

				$sql="update JobOrders set PublicPosting='".$PublicPosting."' where JobID='".$justApplyRefId."'";
				$rs=$obj->edit($sql);
				if(strtolower($PublicPosting)=="no"){
					$sql1="SELECT careermap_vacancyID FROM `JobOrders` WHERE JobID = '".$justApplyRefId."'";
					$res1=$obj->select($sql1);
					if(isset($res1[0]['careermap_vacancyID']) && !empty($res1[0]['careermap_vacancyID']) && $res1[0]['careermap_vacancyID']!=null){
						$compData = getCompanyData();
						removeToCareerMap($justApplyRefId,$compData[0]['careermap_username'],$compData[0]['careermap_password'],'delete');
					}
				}
				$success=array("status"=>"success","msg"=>MSG_JOB_UPDATED_SUCCESSFULLY);
				$this->response($this->json($success), 200);
			}
		}
	}
    public function getResumes(){
			global $db;
        $this->parseRequestandSetSession();
      	if(isset($_SESSION['sess_token']) && isset($_SESSION['sess_userid'])){
                $RequireFields_arr=array();
                $JsonReqObj=$this->decodeRequest();;
                $JobID=isset($JsonReqObj->JobID)?$JsonReqObj->JobID:"";
                $RequireFields=array("JobID"=>array("data"=>$JobID,"maxlength"=>"10","datatype"=>"int","errormsg"=>"JobID ".MSG_IS_INTEGER_FIELD));
                $this->validateRequestParameters($RequireFields);
                try{
                    $resume_arr=array();
                    $sql_sel="SELECT r.* FROM `Job_Related_Resume` jrr JOIN resume r ON jrr.resumeID = r.resumeID WHERE jrr.JobID='".$JobID."'";
                    $res=$this->instanceDBObj->select($sql_sel);
                    $cnt_res=count($res);
                    if($cnt_res>0){
                        for($i=0;$i<$cnt_res;$i++){
                            foreach($res[$i] as $key=>$val){
                                if(!is_numeric($key)){
                                    $key_arr[]=$key;
                                    $val_arr[]=$val;
                                }
                            }
                            $resume_arr[]=array_combine($key_arr, $val_arr);
                        }
                        $cnt_resume_arr=count($resume_arr);
                        $success=array("status"=>"success","msg"=>MSG_RESUME_LISTING,"total_records"=>$cnt_resume_arr,"results"=>$resume_arr);
                    }else{
                        $cnt_resume_arr=count($resume_arr);
                        $success=array("status"=>"success","msg"=>MSG_NO_RECORD_FOUND,"total_records"=>$cnt_resume_arr);
                    }
                    $this->response($this->json($success), 200);
                }catch(Exception $e){
                    $this->sendFalseResponse(200, $e->getMessage());
                }
            }else{
                    $this->sendFalseResponse(200, ERR_SAVE_DATA);
            }
        }
    
	private function getIndustryId($industryName){
		$sql="select industryID from industry where LOWER(industry_title)='".mysql_real_escape_string(strtolower(trim($industryName)))."'";
		$rs=$this->instanceDBObj->select($sql);
		if($rs && isset($rs[0]['industryID'])){
			return $rs[0]['industryID'];
		}
		return false;
	}
	private function getFunctionalAreaId($BusinessTitle){
		$sql="select BusinessAreaID from business_area where LOWER(BusinessTitle)='".mysql_real_escape_string(strtolower(trim($BusinessTitle)))."'";
		$rs=$this->instanceDBObj->select($sql);
		if($rs && isset($rs[0]['BusinessAreaID'])){
			return $rs[0]['BusinessAreaID'];
		}
		return false;
	}
    function isPostcode($postcode){
        $postcode = strtoupper(str_replace(' ','',$postcode));
        if(preg_match("/^[A-Z]{1,2}[0-9]{2,3}[A-Z]{2}$/",$postcode) || preg_match("/^[A-Z]{1,2}[0-9]{1}[A-Z]{1}[0-9]{1}[A-Z]{2}$/",$postcode) || preg_match("/^GIR0[A-Z]{2}$/",$postcode))
            return true;
        else
            return false;
    }

    public function activateForSkillsLogin(){
        $this->parseRequestandSetSession();
        if(isset($_SESSION['sess_companyId']) && $_SESSION['sess_companyId']>0 && isset($_SESSION['sess_companyadmin']) && !empty($_SESSION['sess_companyadmin'])){
            if(isset($_SESSION['sess_token']) && isset($_SESSION['sess_userid'])){
                $RequireFields_arr=array();
                $JsonReqObj=$this->decodeRequest();;
                $JustApplyRefID=isset($JsonReqObj->JustApplyRefID)?$JsonReqObj->JustApplyRefID:"";
                $crm_ref_id=isset($JsonReqObj->crm_ref_id)?$JsonReqObj->crm_ref_id:"";
                $flag_for_ForSkill=isset($JsonReqObj->flag_for_ForSkill)?$JsonReqObj->flag_for_ForSkill:"";
                $for_skills_group=isset($JsonReqObj->for_skills_group)?$JsonReqObj->for_skills_group:"";
                $is_ICT_required=isset($JsonReqObj->is_ICT_required)?$JsonReqObj->is_ICT_required:"0";
                $RequireFields=array_merge(array(
                        "JustApplyRefID"=>array("data"=>$JustApplyRefID,"maxlength"=>"10","datatype"=>"int","errormsg"=>"JustApplyRefID ".MSG_IS_INTEGER_AND_REQUIRED_FIELD)));
                $this->validateRequestParameters($RequireFields);
                if($crm_ref_id==""){
                    $this->sendFalseResponse(200,"crm_ref_id ".MSG_IS_REQUIRED_FIELD);
                }
                if($flag_for_ForSkill==""){
                    $this->sendFalseResponse(200,"flag_for_forskill ".MSG_IS_REQUIRED_FIELD);
                }
                try{
                    $sql_upd="UPDATE `resume` SET
                            `crm_ref_id`='".$crm_ref_id."',
                            `flag_for_ForSkill`='".$flag_for_ForSkill."',
                            `for_skills_group`='".$for_skills_group."',
                            `is_ICT_required`='".$is_ICT_required."'
                            WHERE resumeID='".$JustApplyRefID."'";
                    $res_sel=$this->instanceDBObj->edit($sql_upd);
                    $success=array("status"=>"success","msg"=>MSG_CANDIDATE_UPDATED_SUCCESSFULLY);
                    $this->response($this->json($success), 200);
                }catch(Exception $e){
                    $this->sendFalseResponse(200, $e->getMessage());
                }
            }else{
                $this->sendFalseResponse(200, ERR_SAVE_DATA);
            }
        }else{
            $this->sendFalseResponse(200, INVALID_TOKEN);
        }
    }

    public function changeApplicationStatus(){
        $this->parseRequestandSetSession();
        if(isset($_SESSION['sess_companyId']) && $_SESSION['sess_companyId']>0 && isset($_SESSION['sess_companyadmin']) && !empty($_SESSION['sess_companyadmin'])){
            if(isset($_SESSION['sess_token']) && isset($_SESSION['sess_userid'])){
                $RequireFields_arr=array();
                $JsonReqObj=$this->decodeRequest();;
                $ApplicationID=isset($JsonReqObj->ApplicationID)?$JsonReqObj->ApplicationID:"";
                $application_status=isset($JsonReqObj->application_status)?$JsonReqObj->application_status:"";
                $is_update_status='Yes';
                $today=date("Y-m-d H:i:s");
                $RequireFields=array_merge(array(
                    "ApplicationID"=>array("data"=>$ApplicationID,"maxlength"=>"10","datatype"=>"int","errormsg"=>"ApplicationID ".MSG_IS_INTEGER_AND_REQUIRED_FIELD),
                    "application_status"=>array("data"=>$application_status,"maxlength"=>"255","datatype"=>"string","errormsg"=>"application_status ".MSG_IS_REQUIRED_FIELD)));
                $this->validateRequestParameters($RequireFields);
                if(trim($application_status)==""){
                    $this->sendFalseResponse(200,"application_status ".MSG_IS_REQUIRED_FIELD);
                }
                try{
                    $sql_upd="UPDATE `Job_Related_Resume` SET
                            `resume_status`='".$application_status."',
                            `is_update_status`='".$is_update_status."',
                            `last_updated_on`='".$today."'
                            WHERE Job_Related_ResumeID='".$ApplicationID."'";
                    $this->instanceDBObj->edit($sql_upd);
                    $sql_ins="INSERT INTO `Job_Related_Resume_Status_Log` SET
                                `Job_Related_ResumeID`='".$ApplicationID."',
                                `resume_status`='".$application_status."',
                                `created_at`='".$today."',
                                `created_by`='API'";
                    $this->instanceDBObj->insert($sql_ins);
                    $success=array("status"=>"success","msg"=>MSG_RESUME_STATUS_UPDATED_SUCCESSFULLY);
                    $this->response($this->json($success), 200);
                }catch(Exception $e){
                    $this->sendFalseResponse(200, $e->getMessage());
                }
            }else{
                $this->sendFalseResponse(200, ERR_SAVE_DATA);
            }
        }else{
            $this->sendFalseResponse(200, INVALID_TOKEN);
        }
    }

    private function parseJSON($text){
		$text = str_replace("\r\n", "\n", $text);
	    $text = str_replace("\r", "\n", $text);
	    // JSON requires new line characters be escaped
	    $text = str_replace("\n", "\\n", $text);
	    //$text = htmlentities($text);
	    return $text;
	}
	private function isJson($string) {
		$string=$this->parseJSON($string);
		json_decode($string);
		$lastErr=json_last_error();
		if($lastErr != JSON_ERROR_NONE){
			$this->sendFalseResponse(200,json_last_error_msg());
		}
		return true;
	}

	private function json($data){
		if(is_array($data)){
			array_walk_recursive($data,array($this,"utf8encode"));
			return json_encode($data);
		}
	}
	private function utf8encode(&$item, $key){
		$item=utf8_encode($item);
	}
	private function sendFalseResponse($HTTPCode,$message){
		$error=array('status' => "false", "msg" => $message);
		$this->response($this->json($error), $HTTPCode);
	}
	private function throwException($exceptionText){
		throw new Exception($exceptionText);
	}
	function get_error_message($code){
			$status = array(
						1=> 'System Apikey is wrong',
						2 =>'Not valid authentication key',
						3=> 'Records are not availble',
						4=> 'Please Enter Valid Data',
						6=> 'Invalid username or password',
						7=> 'Missing timestamp parameter. The parameter is required',
						8=> 'Login has been expired',
						9=> 'Your emailid is wrong please try again.',
						10=> 'Your emailid or password is wrong please try again.',
						11=> 'Lösenord har skickats till din e-postadress.',
						12=>'Angiven e-post är inte registrerad.',
						13=>"Authorization failed. Invalid username or password",
						14=>"Invalid/expired token",
						15=>"Sorry, there is some problem while saving data",
						16=>"Sorry, there is some problem while processing your request"

						);
			return ($status[$code]);
	}
}

if (!function_exists('json_last_error_msg')) {
    function json_last_error_msg() {
        static $errors = array(
            JSON_ERROR_NONE             => null,
            JSON_ERROR_DEPTH            => 'Maximum stack depth exceeded',
            JSON_ERROR_STATE_MISMATCH   => 'Underflow or the modes mismatch',
            JSON_ERROR_CTRL_CHAR        => 'Unexpected control character found',
            JSON_ERROR_SYNTAX           => 'Syntax error, malformed JSON',
            JSON_ERROR_UTF8             => 'Malformed UTF-8 characters, possibly incorrectly encoded'
        );
        $error = json_last_error();
        return array_key_exists($error, $errors) ? $errors[$error] : "Unknown error ({$error})";
    }
}
?>
