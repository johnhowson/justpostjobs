<?php
namespace api\classes;
$WageType=array("1"=>"CustomWageFixed","2"=>"CustomWageRange","3"=>"NationalMinimumWage","4"=>"ApprenticeshipMinimumWage","5"=>"Unwaged","6"=>"CompetitiveSalary","7"=>"ToBeSpecified");
/*define("N_NAS_SANDBOX_URL","https://apis.apprenticeships.sfa.bis.gov.uk/manage-vacancies-sandbox/v1/apprenticeships");
define("N_NAS_LIVE_URL","https://apis.apprenticeships.sfa.bis.gov.uk/manage-vacancies/v1/apprenticeships");*/

/* NAS API URL changed on 07 July 2020 as per Skype chat with Michael*/
define("N_NAS_SANDBOX_URL","https://apis.apprenticeships.education.gov.uk/manage-vacancies-sandbox/v1/apprenticeships");
define("N_NAS_LIVE_URL","https://apis.apprenticeships.education.gov.uk/manage-vacancies/v1/apprenticeships");

class postVacancyToNAS{
	private $JobID=false;
	private $ExternalSystemId=false;
	private $ContractedProvideUkprn=false;
	private $EmployerEdsUrn=false;
	private $PublicKey=false;
	private $db=false;
	private $JobArray=array();
	private $fieldValidationRule=array();
	private $Client=false;
	private $closedate=false;
	private $startdate=false;
	private $Errors=array();
	private $hasError=false;
	private $output=false;
	private $request=false;
	private $vacancyReference=false;
	private $publishStatus=false;
	private $NASErrorCode=false;
	private $CRM_JobID=false;
	public function __construct($JobID=false){
		global $db,$cmpDATA,$settings;
		if(isset($settings->nasmode) && !empty($settings->nasmode)){
			if($settings->nasmode=="Test"){
				define("NAS_API_MODE","sandbox");
			}else{
				define("NAS_API_MODE","Live");
			}
		}else{
			define("NAS_API_MODE","sandbox");
		}
		if(is_object($db)){
			$this->db=$db;
		}
		if(!empty($JobID) && $JobID >0){
			$this->JobID=$JobID;
		}
	}
	public function publishToNAS(){
		try{
			$this->setJobData();
		}catch(Exception $e){
			$this->show($e->getMessage());
			return array("Status"=>"Error","Error"=>array($e->getMessage()));
		}
		try{
			$this->validateVacancyData();
			if($this->hasError){
				$this->show($this->Errors);
				$this->sendNASPostingErrorEmail();
				return array("Status"=>"Error","Error"=>$this->Errors);
			}
		}catch(Exception $e){
			array_push($this->Errors,$e->getMessage());
			$this->show($this->Errors);
			$this->sendNASPostingErrorEmail();
			return array("Status"=>"Error","Error"=>array($e->getMessage()));
		}
		try{
			$this->show("Sending Request");
			$this->sendRequest();
			if($this->hasError){
				$this->show($this->Errors);
				$this->sendNASPostingErrorEmail();
				return array("Status"=>"Error","Error"=>$this->Errors);
			}else{
				$arr=array("Status"=>"Success","ReferenceNumber"=>$this->vacancyReference);
				$this->show("Returning Response");
				$this->show(print_r($arr,1));
				return $arr;
			}
		}catch(Exception $e){
			array_push($this->Errors,$e->getMessage());
			$this->show($this->Errors);
			$this->sendNASPostingErrorEmail();
			return array("Status"=>"Error","Error"=>array($e->getMessage()));
		}
	}
	private function getWageType($currentVal){
		global $WageType;
		if(isset($WageType[$currentVal]) && !empty($WageType[$currentVal])){
			return $WageType[$currentVal];
		}
		return 'CustomWageFixed';
	}
	private function throwException($exceptionText,$code=0){
		throw new Exception($exceptionText,$code);
	}
	private function validateField($validationType,$fieldName,$validationMsg,$fieldLength=0){
		foreach($validationType as $key=>$value){
			switch($value){
				case "required":
					if(!isset($this->JobArray[$fieldName]) || trim($this->JobArray[$fieldName]) == ""){
						$this->throwException($validationMsg[$value]);
					}
				break;
				case "maxlength":
					if(isset($this->JobArray[$fieldName]) && strlen($this->JobArray[$fieldName]) > $fieldLength){
						$this->throwException($validationMsg[$value]);
					}
				break;
				case "numeric":
					if(isset($this->JobArray[$fieldName]) && !is_numeric($this->JobArray[$fieldName])){
						$this->throwException($validationMsg[$value]);
					}
				break;
			}
		}
	}
	private function validateVacancyData(){


		//exit;
		define("ERR_INVALID_TITLE","'Title' should not be empty.");
		define("ERR_INVALID_TITLE_MAXLEN","'Title' should not exceed 100 characters.");
		define("ERR_INVALID_BRIEF","'Short description' should not be empty.");
		define("ERR_INVALID_BRIEF_MAXLEN","'Short description' should exceed 350 characters.");
		define("ERR_INVALID_DESC","'Long description' should not be empty.");
		define("ERR_INVALID_DESC_MAXLEN","'Long description' should not exceed 4000 characters.");
		define("ERR_INVALID_APP_CLOSE_DATE","Invalid Application closing date");
		define("ERR_INVALID_EXP_START_DATE","Invalid Expected start date");
		define("ERR_CLOSING_DATE_START_DATE","'Expected Start Date' must be after the specified application closing date.");
		define("ERR_INVALID_WORKING_DATE","Invalid Working week");
		define("ERR_INVALID_WAGE_TYPE_REASON","Invalid Wage type reason");
		define("ERR_INVALID_SKILLS","'Desired skills' should not be empty.");
		define("ERR_INVALID_SKILLS_MAXLEN","'Desired skills' should not exceed 4000 characters.");
		define("ERR_INVALID_PERSONAL_QUALITIES","'Desired personal qualities' should not be empty.");
		define("ERR_INVALID_PERSONAL_QUALITIES_MAXLEN","'Desired personal qualities' should exceed 4000 characters.");
		define("ERR_INVALID_QUALI","'Desired qualifications' should not be empty.");
		define("ERR_INVALID_QUALI_MAXLEN","'Desired qualifications' should not exceed 4000 characters.");
		define("ERR_INVALID_FUTURE_PROSPECT","'Future prospects' should not be empty.");
		define("ERR_INVALID_FUTURE_PROSPECT_MAXLEN","'Future prospects' should not exceed 4000 characters.");
		define("ERR_INVALID_TRAINING_PROVIDED","'Training to be provided' should not be empty.");
		define("ERR_INVALID_TRAINING_PROVIDED_MAXLEN","'Training to be provided' should not exceed 4000 characters.");
		define("ERR_INVALID_APPLICATION_METHOD","Invalid Application method");
		define("ERR_INVALID_THINGS_TO_CONSIDER","'Things to Consider' should not exceed 4000 characters.");
		define("ERR_INVALID_URL","Invalid External application url");
		define("ERR_INVALID_APP_INSTRUCT","'External Application Instructions' should not exceed 4000 characters.");
		define("ERR_INVALID_DURATION_TYPE","Invalid Duration type");
		define("ERR_INVALID_START_DATE","Invalid Expected start date");
		define("ERR_INVALID_EMPLOYER_DESCRIPTION","'Employer Description' should not be empty.");
		define("ERR_INVALID_EMPLOYER_DESCRIPTION_MAXLEN","'Employer Description; should not exceed 4000 characters");
		define("ERR_INVALID_CONTACT_NAME","'Contact Name' should not exceed 100 characters.");
		define("ERR_INVALID_CONTACT_EMAIL","'Contact Email' should not exceed 100 characters.");
		define("ERR_ADDRESS_LINE1","'Address Line 1' should not be empty");
		define("ERR_ADDRESS_LINE1_MAXLEN","'Address Line 1' should not exceed 300 characters.");
		define("ERR_INVALID_TOWN","'Town' Should not be empty.");
		define("ERR_INVALID_POSTCODE","'Postcode' Should not be empty.");
		define("ERR_EXPECTED_DURATION","'Expected Duration' must be a minimum of 1 year, 12 months or 52 weeks depending on the value of DurationType selected");
		define("ERR_WORKING_WEEK","'Working Week' should not be empty.");
		define("ERR_WORKING_WEEK_MAXLEN","'Working Week' should not exceed 250 characters.");
		define("ERR_HOURS_PER_WEEK","'Hours per week' should not be empty.");
		define("ERR_HOURS_PER_WEEK_NUMBER","'Hours per week' should be nmueric");
		define("ERR_HOURS_PER_WEEK_RANGE","'Hours per week' must be between 16 and 48 inclusive");
		define("ERR_INVALID_LOCATION_TYPE","'Location Type' should not be empty.");
		define("ERR_INVALID_NUMBER_OF_POSITION","'Number of Positions' should not be empty.");
		define("ERR_INVALID_NUMBER_OF_POSITION_NUM","'Number of Positions' should be numeric.");
		define("ERR_INVALID_EMPLOYER_EDS_URN","'Employer Eds Urn' should not be empty.");
		define("ERR_INVALID_PROVIDER_EDS_URN","'Provider Site Eds Urn' should not be empty.");
		define("ERR_INVALID_TRAINING_TYPE","'Training Type' should not be empty.");
		define("ERR_INVALID_TRAINING_TYPE_VAL","Invalid value for 'Training Type'");
		define("ERR_INVALID_TRAINING_CODE","'Training code' should not be empty.");
		define("ERR_INVALID_TRAINING_CODE_VAL","Invalid value for 'Training code'");
		define("ERR_INVALID_EMPLOYER_DISABILITY_CONFIDENT","'Is Employer Disability Confident' must not be empty.");

		try{
			$this->validateWageTypeFieldsandAmendRequest();
		}catch(Exception $e){
			array_push($this->Errors,$e->getMessage());
			$this->hasError=true;
		}
		$this->show(print_r($this->JobArray,true));
		$this->fieldValidationRule=array(
			array(
				"vtype"=>array("required","maxlength"),
				"field"=>"title",
				"message"=>array("required"=>ERR_INVALID_TITLE,"maxlength"=>ERR_INVALID_TITLE_MAXLEN),
				"maxlength"=>100
			),
			array(
				"vtype"=>array("required","maxlength"),
				"field"=>"shortDescription",
				"message"=>array("required"=>ERR_INVALID_BRIEF,"maxlength"=>ERR_INVALID_BRIEF_MAXLEN),
				"maxlength"=>350
			),
			array(
				"vtype"=>array("required","maxlength"),
				"field"=>"longDescription",
				"message"=>array("required"=>ERR_INVALID_DESC,"maxlength"=>ERR_INVALID_DESC_MAXLEN),
				"maxlength"=>4000
			),
			array(
				"vtype"=>array("required","maxlength"),
				"field"=>"desiredSkills",
				"message"=>array("required"=>ERR_INVALID_SKILLS,"maxlength"=>ERR_INVALID_SKILLS_MAXLEN),
				"maxlength"=>4000
			),
			array(
				"vtype"=>array("required","maxlength"),
				"field"=>"desiredQualifications",
				"message"=>array("required"=>ERR_INVALID_QUALI,"maxlength"=>ERR_INVALID_QUALI_MAXLEN),
				"maxlength"=>4000
			),
			array(
				"vtype"=>array("required","maxlength"),
				"field"=>"desiredPersonalQualities",
				"message"=>array("required"=>ERR_INVALID_PERSONAL_QUALITIES,"maxlength"=>ERR_INVALID_PERSONAL_QUALITIES_MAXLEN),
				"maxlength"=>4000
			),
			array(
				"vtype"=>array("required","maxlength"),
				"field"=>"futureProspects",
				"message"=>array("required"=>ERR_INVALID_FUTURE_PROSPECT,"maxlength"=>ERR_INVALID_FUTURE_PROSPECT_MAXLEN),
				"maxlength"=>4000
			),
			array(
				"vtype"=>array("required","maxlength"),
				"field"=>"trainingToBeProvided",
				"message"=>array("required"=>ERR_INVALID_TRAINING_PROVIDED,"maxlength"=>ERR_INVALID_TRAINING_PROVIDED_MAXLEN),
				"maxlength"=>4000
			),
			array(
				"vtype"=>array("maxlength"),
				"field"=>"thingsToConsider",
				"message"=>array("maxlength"=>ERR_INVALID_THINGS_TO_CONSIDER),
				"maxlength"=>4000
			),
			array(
				"vtype"=>array("required"),
				"field"=>"applicationMethod",
				"message"=>array("required"=>ERR_INVALID_APPLICATION_METHOD)
			),
			array(
				"vtype"=>array("required"),
				"field"=>"applicationClosingDate",
				"message"=>array("required"=>ERR_INVALID_APP_CLOSE_DATE)
			),
			array(
				"vtype"=>array("required"),
				"field"=>"durationType",
				"message"=>array("required"=>ERR_INVALID_DURATION_TYPE)
			),
			array(
				"vtype"=>array("required"),
				"field"=>"expectedStartDate",
				"message"=>array("required"=>ERR_INVALID_START_DATE)
			),
			array(
				"vtype"=>array("required","maxlength"),
				"field"=>"employerDescription",
				"message"=>array("required"=>ERR_INVALID_EMPLOYER_DESCRIPTION,"maxlength"=>ERR_INVALID_EMPLOYER_DESCRIPTION_MAXLEN),
				"maxlength"=>4000
			),
			array(
				"vtype"=>array("maxlength"),
				"field"=>"contactName",
				"message"=>array("maxlength"=>ERR_INVALID_CONTACT_NAME)
				,"maxlength"=>100
			),
			array(
				"vtype"=>array("maxlength"),
				"field"=>"contactEmail",
				"message"=>array("maxlength"=>ERR_INVALID_CONTACT_EMAIL),
				"maxlength"=>100
			),
			array(
				"vtype"=>array("required","maxlength"),
				"field"=>"workingWeek",
				"message"=>array("required"=>ERR_WORKING_WEEK,"maxlength"=>ERR_WORKING_WEEK_MAXLEN),
				"maxlength"=>250
			),
			array(
				"vtype"=>array("required","numeric"),
				"field"=>"hoursPerWeek",
				"message"=>array("required"=>ERR_HOURS_PER_WEEK,"numeric"=>ERR_HOURS_PER_WEEK_NUMBER),
				"maxlength"=>250
			),
			array(
				"vtype"=>array("required"),
				"field"=>"locationType",
				"message"=>array("required"=>ERR_INVALID_LOCATION_TYPE)
			),
			array(
				"vtype"=>array("required","numeric"),
				"field"=>"numberOfPositions",
				"message"=>array("required"=>ERR_INVALID_NUMBER_OF_POSITION,"numeric"=>ERR_INVALID_NUMBER_OF_POSITION_NUM)
			),
			array(
				"vtype"=>array("required"),
				"field"=>"employerEdsUrn",
				"message"=>array("required"=>ERR_INVALID_EMPLOYER_EDS_URN)
			),
			array(
				"vtype"=>array("required"),
				"field"=>"providerSiteEdsUrn",
				"message"=>array("required"=>ERR_INVALID_PROVIDER_EDS_URN)
			),
			array(
				"vtype"=>array("required"),
				"field"=>"trainingType",
				"message"=>array("required"=>ERR_INVALID_TRAINING_TYPE)
			),
			array(
				"vtype"=>array("required"),
				"field"=>"trainingCode",
				"message"=>array("required"=>ERR_INVALID_TRAINING_CODE)
			),
			array(
				"vtype"=>array("required"),
				"field"=>"isEmployerDisabilityConfident",
				"message"=>array("required"=>ERR_INVALID_EMPLOYER_DISABILITY_CONFIDENT)
			),
		);
		if(strtolower(trim($this->JobArray["applicationMethod"]))=="offline"){
			array_push(
				$this->fieldValidationRule,
				array(
					"vtype"=>array("required"),
					"field"=>"externalApplicationUrl",
					"message"=>array("required"=>ERR_INVALID_URL)
				)
			);
			array_push(
				$this->fieldValidationRule,
					array(
						"vtype"=>array("maxlength"),
						"field"=>"externalApplicationInstructions",
						"message"=>array("maxlength"=>ERR_INVALID_APP_INSTRUCT),
						"maxlength"=>4000
					)
				);
		}

		foreach($this->fieldValidationRule as $key=>$value){
			try{
				$this->validateField($value["vtype"],$value["field"],$value["message"],@$value["maxlength"]);
			}catch(Exception $e){
				array_push($this->Errors,$e->getMessage());
				$this->hasError=true;
				//break;
			}
		}
		if(strtolower(trim($this->JobArray["locationType"]))=="otherlocation"){
			if(!isset($this->JobArray["location"]["addressLine1"]) || empty($this->JobArray["location"]["addressLine1"])){
				array_push($this->Errors,ERR_ADDRESS_LINE1);
			}
			if(isset($this->JobArray["location"]["addressLine1"]) && strlen($this->JobArray["location"]["addressLine1"])>300){
				array_push($this->Errors,ERR_ADDRESS_LINE1_MAXLEN);
			}
			if(!isset($this->JobArray["location"]["town"]) || empty($this->JobArray["location"]["town"])){
				array_push($this->Errors,ERR_ADDRESS_LINE1_MAXLEN);
			}
			if(!isset($this->JobArray["location"]["postcode"]) || empty($this->JobArray["location"]["postcode"])){
				array_push($this->Errors,ERR_INVALID_POSTCODE);
			}
		}
		if($this->JobArray['hoursPerWeek']<16 || $this->JobArray['hoursPerWeek']>48){
			array_push($this->Errors,ERR_HOURS_PER_WEEK_RANGE);
		}
		if($this->closedate > $this->startdate){
			array_push($this->Errors,ERR_CLOSING_DATE_START_DATE);
		}
		switch(strtolower(trim($this->JobArray['durationType']))){
			case "weeks":
				if($this->JobArray['expectedDuration']<52){
					array_push($this->Errors,ERR_EXPECTED_DURATION);
				}
			break;
			case "months":
				if($this->JobArray['expectedDuration']<12){
					array_push($this->Errors,ERR_EXPECTED_DURATION);
				}
			break;
			case "years":
				if($this->JobArray['expectedDuration']<1){
					array_push($this->Errors,ERR_EXPECTED_DURATION);
				}
			break;
		}
		if(isset($this->JobArray['trainingType']) && !in_array(strtolower(trim($this->JobArray['trainingType'])),array("framework","standard"))){
			array_push($this->Errors,ERR_INVALID_TRAINING_TYPE_VAL);
		}
		if(isset($this->JobArray['trainingType']) && strtolower(trim($this->JobArray['trainingType']))=="standard"){
			if(!is_numeric($this->JobArray['trainingCode'])){
				array_push($this->Errors,ERR_INVALID_TRAINING_CODE_VAL);
			}
			if($this->JobArray['trainingCode']>9999){
				array_push($this->Errors,ERR_INVALID_TRAINING_CODE_VAL);
			}
		}
	}
	private function getApplyURL($title){
		$VacancyURL=shareJobURL("nas","",$this->JobID,$title);
		$apply_url=shareJobURL("nas","",$this->JobID,$title);
		return $apply_url;

	}
	private function clean($val)
	{
		return isset($val)?$this->xml_escape($val):"";
	}
	private function setJobData(){
		global $db,$VacancyURL;
		//	$this->CRM_JobID									=	$rs[0]['CRM_JobID'];
		if($this->JobID !== false && is_numeric($this->JobID) && $this->JobID>0){
		 	$attributes=(array) new Job;
			$renderjob=new renderjob();
			$location=$attributes["location"];
	    $wage=$attributes["wage"];
	    // location data is not in the jobs table
			$renderjob->setwage($wage);
	    unset($attributes["location"]);
	    unset($attributes["wage"]);
	    $fields=array_keys($attributes);
	    $sql=makeselect($fields, "ja_jobs", "jobid", $this->JobID);
			$job = $db->query($sql)->first();
			$jobid=$job->jobid;
			$wageid=$job->WageID;
			unset($job->jobid);
			unset($job->WageID);
			$renderjob->setlocation($location);
			$joblocation=$renderjob->getlocation($job->companyid);
			//print_r($joblocation);
			foreach($job as $key=>$value)
			{
				$this->JobArray[$key]=$this->clean($value);
			}


				$location=$joblocation[0];
				$isEmployerDisabilityConfident=$location->isEmployerDisabilityConfident;
				$employerWebsiteUrl=$location->employerWebsiteUrl;
				$employerDescription=$location->employerDescription;
				unset($location->isEmployerDisabilityConfident);
				unset($location->employerWebsiteUrl);
				unset($location->employerDescription);
				/*	echo "<pre>";
			print_r($location);
					echo "</pre>";*/
			if(strtolower($this->JobArray["locationType"])=="otherlocation")
			{
				foreach ($location as $key => $value) {
					$this->JobArray["location"][$key]=$this->xml_escape($value);
				}
			}
			$wagearray=$renderjob->getwage($wageid);
			$wagedata=$wagearray[0];
			unset($this->JobArray["companyid"]);
			unset($this->JobArray["specialismid"]);
			unset($this->JobArray["salary"]);
			unset($this->JobArray["postcode"]);
			unset($this->JobArray["jobtype"]);
			$this->JobArray["wageType"]=$wagearray[2];
			$this->JobArray["fixedWage"]=$wagedata->Amount;
			$this->JobArray["wageTypeReason"]	=$wagedata->WageReason;
			$this->JobArray["minWage"]=	$wagedata->AmountMin;
			$this->JobArray["maxWage"]=$wagedata->AmountMax;
			$this->JobArray["wageUnit"]	=$renderjob->getWageFrequency($wagedata->WageFrequencyID);
			if(strtolower(trim($this->JobArray["applicationMethod"]))=="offline"){
				$this->JobArray["externalApplicationUrl"] =	$VacancyURL;
				$this->JobArray["externalApplicationInstructions"]	=	$this->clean($this->JobArray["externalApplicationInstructions"]);
			}
			$this->JobArray["EmployerEdsUrn"]=EmployerEdsUrn;
			$this->JobArray["providerSiteEdsUrn"]=VacancyOwnerEdsUrn;
			$this->JobArray["isEmployerDisabilityConfident"]= ($isEmployerDisabilityConfident==1)?true:false;
			$this->JobArray["employerWebsiteUrl"]=$employerWebsiteUrl;
			$this->JobArray["employerDescription"]=$employerDescription;
		/*	echo "<pre>";
			print_r($this->JobArray);
				echo "</pre>";
			die();*/
	}else{
			$this->throwException("Invalid Vacancy ID.");
		}

	}

	private function validateWageTypeFieldsandAmendRequest(){
		if(!isset($this->JobArray["wageType"]) || $this->JobArray["wageType"]==""){
			$this->throwException("Invalid Wage Type.");
		}
		switch($this->JobArray["wageType"]){
			case "CustomWageFixed":
				if(isset($this->JobArray["wageTypeReason"])){
					unset($this->JobArray["wageTypeReason"]);
				}
				if(isset($this->JobArray["minWage"])){
					unset($this->JobArray["minWage"]);
				}
				if(isset($this->JobArray["maxWage"])){
					unset($this->JobArray["maxWage"]);
				}
				if(isset($this->JobArray["wageUnit"]) && $this->JobArray["wageUnit"]=="NotApplicable"){
					$this->throwException("Invalid Wage Unit.");
					return;
				}
				if(!isset($this->JobArray['fixedWage']) || empty($this->JobArray['fixedWage'])){
					$this->throwException("Invalid Wage.");
					return;
				}
			break;
			case "CustomWageRange":
				if(isset($this->JobArray["wageTypeReason"])){
					unset($this->JobArray["wageTypeReason"]);
				}
				if(isset($this->JobArray["fixedWage"])){
					unset($this->JobArray["fixedWage"]);
				}
				if(isset($this->JobArray["wageUnit"]) && $this->JobArray["wageUnit"]=="NotApplicable"){
					$this->throwException("Invalid Wage Unit.");
				}
				if(!isset($this->JobArray["minWage"]) || empty($this->JobArray["minWage"])){
					$this->throwException("Invalid Minimum Wage.");
				}
				if(!isset($this->JobArray["maxWage"]) || empty($this->JobArray["maxWage"])){
					$this->throwException("Invalid Maximum Wage.");
				}
				if($this->JobArray["maxWage"]<$this->JobArray["minWage"]){
					$this->throwException("Maximum Wage must be greater than minimum wage.");
				}
			break;
			case "NationalMinimumWage":
			case "ApprenticeshipMinimumWage":
				if(isset($this->JobArray["wageTypeReason"])){
					unset($this->JobArray["wageTypeReason"]);
				}
				if(isset($this->JobArray["fixedWage"])){
					unset($this->JobArray["fixedWage"]);
				}
				if(isset($this->JobArray["minWage"])){
					unset($this->JobArray["minWage"]);
				}
				if(isset($this->JobArray["maxWage"])){
					unset($this->JobArray["maxWage"]);
				}
				if(isset($this->JobArray["wageUnit"]) && strtolower(trim($this->JobArray["wageUnit"]))!="notapplicable"){
					$this->throwException("Invalid Wage Unit.");
				}
			break;
			case "Unwaged":
			case "CompetitiveSalary":
			case "ToBeSpecified":
				if(isset($this->JobArray["fixedWage"])){
					unset($this->JobArray["fixedWage"]);
				}
				if(isset($this->JobArray["minWage"])){
					unset($this->JobArray["minWage"]);
				}
				if(isset($this->JobArray["maxWage"])){
					unset($this->JobArray["maxWage"]);
				}
				if(isset($this->JobArray["wageUnit"]) && strtolower(trim($this->JobArray["wageUnit"]))!="notapplicable"){
					$this->throwException("Invalid Wage Unit.");
				}
				if(!isset($this->JobArray["wageTypeReason"]) || strlen($this->JobArray["wageTypeReason"])<=0){
					$this->throwException(ERR_INVALID_WAGE_TYPE_REASON);
				}
			break;
		}
	}
	private function getClientInfo($ClientID){
		global $obj;
		if(is_numeric($ClientID) && $ClientID>0){
			$sql="select * from client where ClientID='".$ClientID."'";
			$rs=$obj->select($sql);
			$this->show($sql);
			if(isset($rs[0]) && isset($rs[0]['ClientID']) && $rs[0]['ClientID']==$ClientID){
				$this->Client=$rs;
			}
		}
	}
	private function xml_escape($s){
		/*if($this->isJson === false){
			return $s;
			$s1 = html_entity_decode($s, ENT_QUOTES, 'UTF-8');
			$s1 = htmlspecialchars($s1,ENT_XML1 | ENT_SUBSTITUTE,'UTF-8',false);
    		return $s1;
    	}else{
			return $s;
		}*/
		return utf8_encode($s);
	}
	public function show($str){
		$handle=fopen($_SERVER['DOCUMENT_ROOT'] ."/reports/nas_error".date("Y_m_d_h_i_s").".txt","a+");
		fwrite($handle,print_r($str,1));
		fwrite($handle,"\r\n");
		fclose($handle);
		return;
		$str="<span style='font-size:11px;font-family:verdana'>".$str."</span><hr>";
		//flushBuffer();
		ob_implicit_flush(1);
		echo $str;
		@ob_flush();
		@flush();
		ob_start();
	}
	private function prepareHeader(){
		return array(
			"Content-Type:application/json",
		    "Ocp-Apim-Subscription-Key: ".API_KEY,
		);
	}
	private function sendRequest(){
		$headers=$this->prepareHeader();
		$this->request=json_encode($this->JobArray);
		$this->show("request \r\n");
		$this->show($this->request);
		$ch=curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER,  $headers);
		if(NAS_API_MODE == "sandbox"){
			curl_setopt($ch, CURLOPT_URL,N_NAS_SANDBOX_URL);
		}else{
			curl_setopt($ch, CURLOPT_URL,N_NAS_LIVE_URL);
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:47.0) Gecko/20100101 Firefox/47.0');
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$this->request);
		$this->output = curl_exec($ch);
		$this->show("Output \r\n");
		$this->show($this->output );
		$this->show(print_r(curl_getinfo($ch),1));
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$this->show("HTTP CODE : \r\n");
		$this->show($httpcode);
		curl_close($ch);
		if($httpcode == 200){
			$this->processOutput();
		}else{
			$this->hasError=true;
			$this->processErrors($httpcode);
		}
		$this->updateJobOrder();
	}
	private function processOutput(){
		$outputArr=json_decode($this->output,true);
		$this->vacancyReference=$outputArr["vacancyReferenceNumber"];
		$this->publishStatus='Success';
	}
	private function processErrors($httpcode){
		$outputarray=json_decode($this->output,true);
		$this->publishStatus='Failure';
		$comma="";
		switch($httpcode){
			case "401":
				if(isset($outputarray['message'])){
					$this->hasError=true;
					array_push($this->Errors,$outputarray['message']);
				}
			break;
			case "400":
				if(isset($outputarray['requestErrors']) && is_array($outputarray['requestErrors'])){
					for($i=0;$i<count($outputarray['requestErrors']);$i++){
						$errorDescription="";
						if(isset($outputarray['requestErrors'][$i]["errorCode"]) && !empty($outputarray['requestErrors'][$i]["errorCode"])){
							$errorDescription.=$outputarray['requestErrors'][$i]["errorCode"];
							$this->NASErrorCode=$comma.$outputarray['requestErrors'][$i]["errorCode"];
							$comma=",";
						}
						if(isset($outputarray['requestErrors'][$i]["errorMessage"]) && !empty($outputarray['requestErrors'][$i]["errorMessage"])){
							$errorDescription.=" - ".$outputarray['requestErrors'][$i]["errorMessage"];
						}
						if(!empty($errorDescription)){
							array_push($this->Errors,$errorDescription);
						}
					}
				}
			break;
		}
	}
	private function updateJobOrder(){
		global $db;
		$message="request: " .filter_var($this->request,FILTER_SANITIZE_STRING);
		$response=filter_var($this->output,FILTER_SANITIZE_STRING);
		$reference=filter_var($this->vacancyReference,FILTER_SANITIZE_STRING);
		//$this->JobID
		$iserror=0;
		if($this->NASErrorCode!==false || $this->publishStatus=="Failure"){
			$iserror=1;
		}
		$fields=array("jobid"=>$this->JobID,"apiname"=>"nas","message"=>$message,"response"=>$response,"reference"=>$reference,
		"errorcode"=>$this->NASErrorCode,"iserror"=>$iserror,"nasstatus"=>$this->publishStatus,"dateadded"=>date("Y-m-d H:i:s"));

		$result=$db->insert("ja_apiLog",$fields);
		if($db->error())
		{
			saveLog("A database error occured in NAS module: " .$db->errorString());
		}
		//$sql_nas_update="update `JobOrders` set NAS_request='".escape_string($this->request)."',NAS_response='".escape_string($this->output)."',NAS_VacancyId='".escape_string($this->vacancyReference)."',NAS_Status='".escape_string($this->publishStatus)."',NAS_ReferenceNumber='".escape_string($this->vacancyReference)."', NAS_ErrorCode='".escape_string($this->NASErrorCode)."' where JobID='".$this->JobID."'";
		//$this->show($sql_nas_update);
		//$this->db->edit($sql_nas_update);
	}
	private function sendNASPostingErrorEmail(){
		global $settings,$SurveyTitle;
		$surveyid=getformidforpage("naspostingerror");
			$EmailBody=getemail($surveyid);
			$Subject=$SurveyTitle;
			$errorDescription=implode("<br>",$this->Errors);
			$Subject=str_replace("{companycode}",ucfirst(strtolower($settings->nameofprovider)),$Subject);
			$search=array("{JOBTITLE}","{VACANCYREFERENCE}","{ERRORDESCRIPTION}");
			$replace=array($this->JobArray['title'],$this->CRM_JobID,"<br/>".$errorDescription);
			$EmailBody=str_replace($search,$replace,$EmailBody);
			$style='<style type="text/css">p{font-family:verdana;font-size:12px;}</style>';
			$to=$settings->emailfromaddress;
			$this->show($EmailBody);
			$opts = array(
				'email' => $settings->emailfromaddress,
				'name'  => $settings->emailfromname
			);
			email($to,$Subject,$style.$EmailBody,$opts);

	}
}
