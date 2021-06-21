<?php
namespace api\classes;
/*
$url = "http://recruiter.totaljobs.tjgtest.com/XMLDataPosting/JobLoad1.asp";
$xml = '<FILE>
<ADVERT>
	<ROOT LOADTYPE="A">
  		<JOB 	FEEDID="TEST"
				LIVEDAYS="28"
				UNIQUEJOBNO="20150621MYTEST01"
				DESCRIPTION="Senior Auditor required for City practice."
				TITLE="Senior Auditor"
				REGION="London"
				RATEUNIT="a"
				SALARYMIN="25000"
				SALARYMAX="30000"
				SALARYDESC="30k plus benefits"
				INTERNALJOBREF="REF20050202TEST02"
				INDUSTRYLONGNAME="Accountancy"
				RESPONSEURL="http://www.totalrecruiters.com/"
				POSTCODE="W1T 1JU" />
  		<POSTINGCOMPANY USERNAME="loadtesttj"
						PASSWORD="testonly" />
  		<CONTACT	PHONE="020 1234 9999"
					FAX="020 5678 9999"
					EMAIL="ben@test.com"
					FIRSTNAME="Ben Test" />
  		<ADDITIONALJOBTYPE	INDUSTRY="Accountancy"
							GROUP="Permanent" />
  	</ROOT>
  	</ADVERT>
  </FILE>';
$headers = array(
    "Content-type: text/xml",
    "Content-length: " . strlen($xml),
    "Connection: close",
);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 0);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$data = curl_exec($ch);
echo $data;
if(curl_errno($ch))
    print curl_error($ch);
else
    curl_close($ch);
*/
define("LIVEDAYS_IS_MANDATORY","Please provide live day of vacancy");
define("TITLE_IS_MANDATORY","Please provide vacancy title");
define("DESCRIPTION_IS_MANDATORY","Please provide vacancy description");
define("UNIQUEJOBNO_IS_MANDATORY","Please provide unique job no.");
define("REGION_IS_MANDATORY","Please provide region");
define("SALARY_IS_MANDATORY","Please provide salary");
define("INDUSTRYLONGNAME_IS_MANDATORY","Please provide industry long name");
define("EMAIL_IS_MANDATORY","Please provide email");
define("FIRSTNAME_IS_MANDATORY","Please provide first name");
define("FIRSTNAME_EMAILIS_MANDATORY","Please provide email or firstname");
define("GROUP_IS_MANDATORY","Please provide group");
define("MSG_COMP_DATA_ERROR","An error occured while fetching College information");
define("MSG_USERNAME","Please provide TotalJobs Username.");
define("MSG_PASSWORD","Please provide TotalJobs Password.");
define("MSG_FEEDID","Please provide TotalJobs FeedId.");
define("MSG_TOTALJOBS_ENDPOINT","Please provide total jobs URL");
class totaljobsposting{
	private $jobIDs=array();
	public  $errorMsg=array();
	public  $readErrorMsg=array();
	private $RequestXML=false;
	private $ResponseXML=false;
	private $filename=false;
	private $compData=array();
	private $cronLogID=false;
	private $crmJobID=array();
	private $outboundStatus=array();
	public $JobArray=array();
	private $load_type=false; // A - Add, U - Update, D - Delete
	public $url = false;
	public function __construct(){
		global $obj;
		$this->compData = getCompanyData();
		try{
			$this->initcheck();
		}catch(Exception $e){
			//$this->sendEmail("error",'10001',$e->getMessage());
		}
	}

	private function initcheck(){
		if(!isset($this->compData[0])){
			$this->throwException(LBL_COMP_DATA_ERROR);
		}
		if(isset($this->compData[0]) && isset($this->compData[0]['totaljobs_url']) && empty($this->compData[0]['totaljobs_url'])){
			$this->throwException(MSG_TOTALJOBS_ENDPOINT);
		}else{
			$this->url=$this->compData[0]['totaljobs_url'];
		}
		if(isset($this->compData[0]) && isset($this->compData[0]['totaljobs_username']) && empty($this->compData[0]['totaljobs_username'])){
			$this->throwException(MSG_USERNAME);
		}
		if(isset($this->compData[0]) && isset($this->compData[0]['totaljobs_password']) && empty($this->compData[0]['totaljobs_password'])){
			$this->throwException(MSG_PASSWORD);
		}
		if(isset($this->compData[0]) && isset($this->compData[0]['totaljobs_feedid']) && empty($this->compData[0]['totaljobs_feedid'])){
			$this->throwException(MSG_FEEDID);
		}
	}
	public function PostJobsOnTotalJobs(){
		global $obj;
		$sql="select JobID from JobOrders where totaljobs_publish_required=1 and totaljobs_publish_try_counter<3 and totaljobs_posting_success<>'Published' and PublicPosting='Yes' and LastDate>='".date("Y-m-d")."'";
		$this->show($sql);
		$rs=$obj->select($sql);
		if(count($rs)>0){
			for($i=0;$i<count($rs);$i++){
				array_push($this->jobIDs,$rs[$i]['JobID']);
			}
		}
		if(count($this->jobIDs) > 0){
			try{
				$this->show("Generating XML");
				$this->generateAndPostJobXML();
				$this->sendEmail("jobpublished",'');
			}catch(Exception $e){
				$this->show("Error : ".$e->getMessage());
				$this->RequestXML="";
				$this->errorMsg=array();
				$this->sendEmail("error",$e->getCode(),$e->getMessage());
			}
		}
	}
	private function generateAndPostJobXML(){
		global $CompanyPath;
		if(count($this->jobIDs)>0){
			for($i=0;$i<count($this->jobIDs);$i++){
				if(isset($this->jobIDs[$i]) && is_numeric($this->jobIDs[$i]) && $this->jobIDs[$i]>0){
					try{
						$this->setJobData($this->jobIDs[$i]);
						$this->RequestXML.=$this->buildXML();
						$this->errorMsg[$this->jobIDs[$i]]['vacancytitle']=$this->JobArray['TITLE'];
						$this->errorMsg[$this->jobIDs[$i]]['CRM_JobID']=$this->JobArray['UNIQUEJOBNO'];
					}catch(Exception $e){
						$this->errorMsg[$this->jobIDs[$i]]['status']='Error';
						$this->errorMsg[$this->jobIDs[$i]]['mode']='Error';
						$this->errorMsg[$this->jobIDs[$i]]['message']=$e->getMessage();
						$this->show("Exception....".$e->getMessage());
						continue;
					}
				}
			}
			$this->RequestXML='<FILE>'.$this->RequestXML.'</FILE>';
			$this->show("Request XML :: ".$this->RequestXML);
			$this->ResponseXML=$this->postXMLtoTotalJobs($this->RequestXML);
			$this->show($this->RequestXML);
			$this->show("Response XML".$this->RequestXML);
			$totaljobs_response_description_arr=$this->logVacancyResponse($this->RequestXML,$this->ResponseXML);
			foreach($totaljobs_response_description_arr as $k=>$v){
				$v_arr=explode("_",$v);
				$this->errorMsg[$k]['status']=($v_arr[0])?"True":"Error";
				$this->errorMsg[$k]['message']=$v_arr[1];
				$this->errorMsg[$k]['mode']=$v_arr[2];
			}
		}
	}

	private function setJobData($JobID){
		global $obj;
		if($JobID !== false && is_numeric($JobID) && $JobID>0){
			$sql1="SELECT JobOrders.*,industry.industry_title FROM `JobOrders` LEFT JOIN industry ON JobOrders.industryID=industry.industryID WHERE JobID = '".$JobID."'";
			$rs=$obj->select($sql1);
			$this->show($sql1);
			if(count($rs)>0 && isset($rs[0]['JobID'])){
			    $sql2="select EFV.`ExtraFieldDefinationID`, EFV.`ExtraFieldTitle`,rc.default_field_name, EFV.Value as `value` from ExtraFieldValues EFV, customize_job_form rc, ExtraFieldsDefination EFD where rc.is_extra_field='Yes' and rc.extra_field_id=EFV.ExtraFieldDefinationID and EFV.ExtraFieldDefinationID = EFD.ExtraFieldDefinationID and EFD.ExtraFieldType != 'label' and EFD.EntityType='job' and EFV.EntitiyID='".$JobID."' ";
				$res2=$obj->select($sql2);
				$this->show($sql2);
				if(count($res2)>0){
					for($p=0;$p<count($res2);$p++){
	    				$this->ExtraFields[trim($res2[$p]["default_field_name"])]=trim($res2[$p]["value"]);
					}
				}
				if(isset($rs[0]['ClientID']) && $rs[0]['ClientID']>0){
					$this->getClientInfo($rs[0]['ClientID']);
				}
				$this->JobArray["USERNAME"]=$this->compData[0]['totaljobs_username'];
				$this->JobArray["PASSWORD"]=$this->compData[0]['totaljobs_password'];
				$this->JobArray["FEEDID"]=$this->compData[0]['totaljobs_feedid'];
				if(isset($rs[0]['StartDate']) && $rs[0]['StartDate']!="0000-00-00" && isset($rs[0]['LastDate']) && $rs[0]['LastDate']!="0000-00-00"){
					$this->JobArray["LIVEDAYS"]=$this->xml_escape(floor(abs(strtotime($rs[0]['LastDate'])-strtotime($rs[0]['StartDate']))/(60*60*24)));
				}else{
					$this->throwException(LIVEDAYS_IS_MANDATORY);
				}
				if(isset($rs[0]['CRM_JobID']) && !empty($rs[0]['CRM_JobID'])){
					$this->JobArray["UNIQUEJOBNO"]=$this->xml_escape($rs[0]['CRM_JobID']);
					$this->JobArray["INTERNALJOBREF"]=$this->xml_escape($rs[0]['CRM_JobID']);
				}else{
					$this->throwException(UNIQUEJOBNO_IS_MANDATORY);
				}

				if(isset($rs[0]['DetailDesc']) && !empty($rs[0]['DetailDesc'])){
					$this->JobArray["DESCRIPTION"]=$this->xml_escape(nl2br(strip_tags($rs[0]['DetailDesc'])));
				}else{
					$this->throwException(DESCRIPTION_IS_MANDATORY);
				}
				if(isset($rs[0]['Jobtitle']) && !empty($rs[0]['Jobtitle'])){
					$this->JobArray["TITLE"]=$this->xml_escape($rs[0]['Jobtitle']);
					$this->JobArray["RESPONSEURL"]=$this->getApplyURL($JobID,$rs[0]['Jobtitle']);
				}else{
					$this->throwException(TITLE_IS_MANDATORY);
				}
				if(isset($this->ExtraFields['totaljobs_region']) && !empty($this->ExtraFields['totaljobs_region'])){
					$this->JobArray["REGION"]=$this->xml_escape($this->ExtraFields['totaljobs_region']);
				}else{
					$this->throwException(REGION_IS_MANDATORY);
				}
				if(isset($rs[0]['salary']) && !empty($rs[0]['salary'])){
					$this->JobArray["RATEUNIT"]="a";
					$this->JobArray["SALARYMIN"]=number_format($this->xml_escape($rs[0]['salary']*52), 2);
					$this->JobArray["SALARYMAX"]=number_format($this->xml_escape($rs[0]['salary']*52), 2);
				}else{
					$this->throwException(SALARY_IS_MANDATORY);
				}
				if(isset($this->ExtraFields['Working_Week']) && !empty($this->ExtraFields['Working_Week'])){
					$this->JobArray["SALARYDESC"]=$this->ExtraFields['Working_Week'];
				}
				if(isset($this->ExtraFields['totaljobs_industry']) && !empty($this->ExtraFields['totaljobs_industry'])){
					$this->JobArray["INDUSTRYLONGNAME"]=$this->xml_escape($this->ExtraFields['totaljobs_industry']);
					$this->JobArray["INDUSTRY"]=$this->xml_escape($this->ExtraFields['totaljobs_industry']);
				}else{
					$this->throwException(INDUSTRYLONGNAME_IS_MANDATORY);
				}
				if(isset($rs[0]['post_code']) && !empty($rs[0]['post_code'])){
					$this->JobArray["POSTCODE"]=$rs[0]['post_code'];
				}else{
					if(count($this->Client)>0 && isset($this->Client[0]['Zip']) && !empty($this->Client[0]['Zip'])){
						$this->JobArray["POSTCODE"]=$this->Client[0]['Zip'];
					}
				}
				if(isset($rs[0]['ClientID']) && !empty($rs[0]['ClientID'])){
					$sql_contact="SELECT FirstName,Email FROM `prospect_contact` where ClientID='".$rs[0]['ClientID']."'";
					$this->show($sql_contact);
					$res_contact=$obj->select($sql_contact);
					if(isset($res_contact[0]['Email']) && !empty($res_contact[0]['Email'])){
						$this->JobArray["EMAIL"]=$this->xml_escape($res_contact[0]['Email']);
					}else{
						//$this->JobArray["EMAIL"]=$this->xml_escape("apprenticerecruitment@lifetimetraining.co.uk");
					}
					if(isset($res_contact[0]['FirstName']) && !empty($res_contact[0]['FirstName'])){
						$this->JobArray["FIRSTNAME"]=$this->xml_escape($res_contact[0]['FirstName']);
					}else{
						$this->throwException(FIRSTNAME_IS_MANDATORY);
					}
				}else{
					$this->throwException(FIRSTNAME_EMAIL_IS_MANDATORY);
				}
				if(isset($rs[0]['ReqType']) && !empty($rs[0]['ReqType'])){
					$this->JobArray["GROUP"]=$this->getVacancyType($rs[0]['ReqType']);
				}else{
					$this->throwException(GROUP_IS_MANDATORY);
				}
				$this->JobArray["LOADTYPE"]="A";
				if(isset($rs[0]['totaljobs_posting_success']) && $rs[0]['totaljobs_posting_success']=="updated"){
					$this->JobArray["LOADTYPE"]="U";
				}
				if((isset($rs[0]['totaljobs_posting_success']) && $rs[0]['totaljobs_posting_success']=="True")){
					if((isset($rs[0]['LastDate']) && $rs[0]['LastDate']<date("Y-m-d")) || (isset($rs[0]['PublicPosting']) && $rs[0]['PublicPosting']=="No")){
						$this->JobArray["LOADTYPE"]="D";
					}
				}

			}else{
				$this->throwException("Invalid Vacancy ID.");
			}
		}
	}
	private function getApplyURL($JobID,$title){
		$VacancyURL=shareJobURL("tj","",$JobID,$title);
		$res_url=getShortenURL($VacancyURL);
		if($res_url !== false && isset($res_url["id"]) && !empty($res_url["id"])){
			$apply_url=$res_url["id"];
			return $apply_url;
		}else{
			$apply_url=shareJobURL("tj","",$JobID,$title);
			return $apply_url;
		}
		return false;
	}
	private function getVacancyType($vacancytype){
		//$UJMVacancyType=array("permanent"=>1,"temporary"=>2,"placement"=>3,"apprenticeship/internship"=>4);
		global $obj;
		$sql="select totaljobs_vacancy_type from vacancy_type where LOWER(TRIM(VacancyTypeTitle))='".strtolower(trim($vacancytype))."'";
		$rs=$obj->select($sql);
		$this->show($sql);
		if(is_array($rs) && isset($rs[0]) && isset($rs[0]['totaljobs_vacancy_type'])){
			return $rs[0]['totaljobs_vacancy_type'];
		}
		return false;
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
	private function buildXML(){
		$this->show(print_r($this->JobArray,1));
		$xml='<ADVERT>
				<ROOT LOADTYPE="'.$this->JobArray["LOADTYPE"].'">
			  		<JOB 	FEEDID="'.$this->JobArray["FEEDID"].'"
							LIVEDAYS="'.$this->JobArray["LIVEDAYS"].'"
							UNIQUEJOBNO="'.$this->JobArray["UNIQUEJOBNO"].'"
							DESCRIPTION="'.$this->JobArray["DESCRIPTION"].'"
							TITLE="'.$this->JobArray["TITLE"].'"
							REGION="'.$this->JobArray["REGION"].'"
							RATEUNIT="'.$this->JobArray["RATEUNIT"].'"
							SALARYMIN="'.$this->JobArray["SALARYMIN"].'"
							SALARYMAX="'.$this->JobArray["SALARYMAX"].'"
							SALARYDESC="'.$this->JobArray["SALARYDESC"].'"
							INTERNALJOBREF="'.$this->JobArray["INTERNALJOBREF"].'"
							INDUSTRYLONGNAME="'.$this->JobArray["INDUSTRYLONGNAME"].'"
							RESPONSEURL="'.$this->JobArray["RESPONSEURL"].'"
							POSTCODE="'.$this->JobArray["POSTCODE"].'" />
			  				<POSTINGCOMPANY USERNAME="'.$this->JobArray["USERNAME"].'"
							PASSWORD="'.$this->JobArray["PASSWORD"].'" />
			  				<CONTACT	EMAIL="'.$this->JobArray["EMAIL"].'"
							FIRSTNAME="'.$this->JobArray["FIRSTNAME"].'" />
			  				<ADDITIONALJOBTYPE	INDUSTRY="'.$this->JobArray["INDUSTRY"].'"
							GROUP="'.$this->JobArray["GROUP"].'" />
			  	</ROOT>
			  	</ADVERT>';
		$this->show("Generated XML  ::  ".'<pre>'.htmlspecialchars($xml).'</pre>');
		return $xml;
	}

	private function postXMLtoTotalJobs($xml){
		$url = $this->url;
		$this->show("URL".$url);
		$headers = array(
    					"Content-type: text/xml",
    					"Content-length: " . strlen($xml),
    					"Connection: close",);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 0);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$data = curl_exec($ch);
		$this->show(print_r($data,1));
		if(curl_errno($ch)){
			$this->throwException(curl_error($ch));
		}
    	curl_close($ch);
		return $data;
	}

	private function logVacancyResponse($req,$resp){
		global $obj;
		$this->show("Log Vacancy Function");
		$xml = simplexml_load_string($resp);
		$response=json_decode(json_encode($xml),true);
		$this->show(print_r($response,true));
		$totaljobs_response_description_arr=array();
		if(isset($response["PostingSuccess"])){

			if(isset($response['Errors'])){
				$errors=$response['Errors']['Message'].'<br/>'.$response['Errors']['Reason'].'<br/>';
			}
			for($j=0;$j<count($response["BulkJobLoader"]["root"]["Job"]);$j++){
				$is_published=true;
				$information=$warnings=$link_url=$errors="";
				if(isset($response["BulkJobLoader"]["root"]["Job"][$j]["JobDetail"])){
					for($i=0;$i<count($response["BulkJobLoader"]["root"]["Job"][$j]["JobDetail"]);$i++){
						switch(strtolower($response["BulkJobLoader"]["root"]["Job"][$j]["JobDetail"][$i]["@attributes"]["type"])){
							case 'jobid':
								$job_id_arr=explode("=",$response["BulkJobLoader"]["root"]["Job"][$j]["JobDetail"][$i]["Detail"]);
								$totaljobs_JobID=$job_id_arr[1];
								break;
							case 'information':
								$information.=$response["BulkJobLoader"]["root"]["Job"][$j]["JobDetail"][$i]["Detail"].'<br/>';
								break;
							case 'warning':
								$warnings.=$response["BulkJobLoader"]["root"]["Job"][$j]["JobDetail"][$i]["Detail"].'<br/>';
								break;
							case 'linkurl':
								$link_url=$response["BulkJobLoader"]["root"]["Job"][$j]["JobDetail"][$i]["Detail"];
								break;
							case 'fatal error':
								$errors.=$response["BulkJobLoader"]["root"]["Job"][$j]["JobDetail"][$i]["Detail"].'<br/>';
								$is_published=false;
								break;
						}
					}
					$CRM_JobID=$response["BulkJobLoader"]["root"]["Job"][$j]["@attributes"]["JobID"];
					if($is_published){
						$j++;
					}
				}elseif(isset($response["BulkJobLoader"]["root"]["Job"]["JobDetail"])){
					for($k=0;$k<count($response["BulkJobLoader"]["root"]["Job"]["JobDetail"]);$k++){
						switch(strtolower($response["BulkJobLoader"]["root"]["Job"]["JobDetail"][$k]["@attributes"]["type"])){
							case 'jobid':
								$job_id_arr=explode("=",$response["BulkJobLoader"]["root"]["Job"]["JobDetail"][$k]["Detail"]);
								$totaljobs_JobID=$job_id_arr[1];
								break;
							case 'information':
								$information.=trim($response["BulkJobLoader"]["root"]["Job"]["JobDetail"][$k]["Detail"]).'<br/>';
								break;
							case 'warning':
								$warnings.=trim($response["BulkJobLoader"]["root"]["Job"]["JobDetail"][$k]["Detail"]).'<br/>';
								break;
							case 'linkurl':
								$link_url=trim($response["BulkJobLoader"]["root"]["Job"]["JobDetail"][$k]["Detail"]);
								break;
							case 'fatal error':
								$errors.=trim($response["BulkJobLoader"]["root"]["Job"]["JobDetail"][$k]["Detail"]).'<br/>';
								$is_published=false;
								break;
						}
					}
					$CRM_JobID=@$response["BulkJobLoader"]["root"]["Job"]["@attributes"]["JobID"];
					if($is_published){
						$j++;
					}
				}else{
					$information.=$response["BulkJobLoader"]["root"]["Job"]["JobDetail"]["Detail"].'<br/>';
				}
				$totaljobs_response_description="";
				if(trim($errors) != ""){
					$totaljobs_response_description.="Errors : ".trim($errors).'<br/>';
				}
				if(trim($warnings) != ""){
					$totaljobs_response_description.="Warnings : ".trim($warnings).'<br/>';
				}
				if(trim($information) != ""){
					$totaljobs_response_description.="Information : ".trim($information).'<br/>';
				}
				$para="";
				if(!empty($totaljobs_JobID)){
					$para=" ,totaljobs_published_date='".date("Y-m-d H:i:s")."',totaljobs_id='".$totaljobs_JobID."',totaljobs_posting_success='Published'";
				}else{
					$para=" ,totaljobs_posting_success='',totaljobs_publish_try_counter=totaljobs_publish_try_counter+1";
				}
				$sql_sel="select JobID from JobOrders where CRM_JobID='".$CRM_JobID."'";
				$res_sel=$obj->select($sql_sel);
				$JobID=$res_sel[0]["JobID"];
				$sql="update JobOrders set totaljobs_response_description='".escapeStr($totaljobs_response_description)."' ".$para." where JobID='".$JobID."'";
				$obj->edit($sql);
				$this->show($sql);
				if($this->JobArray["LOADTYPE"]=="A"){
					$mode="Publish";
				}elseif($this->JobArray["LOADTYPE"]=="U"){
					$mode="Renew";
				}else{
					$mode="Remove";
				}
				$sql="insert into chanelAPILog set chanelAPIEntity='TotalJobs', JobID='".$JobID."', CRM_JobID='".$CRM_JobID."',channelJobID='".$totaljobs_JobID."', webserviceURL='".$this->url."', request='".escapeStr($req)."',response='".escapeStr($resp)."',status='".$response["PostingSuccess"]."',mode='".$mode."' ";
				$obj->insert($sql);
				$this->show($sql);


				$totaljobs_response_description_arr[$JobID]=$is_published."_".$totaljobs_response_description."_".$mode;
				$totaljobs_response_description="";
			}
			return $totaljobs_response_description_arr;
		}
	}
	private function xml_escape($s){
		$s1 = html_entity_decode($s, ENT_QUOTES, 'UTF-8');
    	$s1 = htmlspecialchars($s1,ENT_XML1 | ENT_SUBSTITUTE,'UTF-8',false);
		return $s1;
	}
	private function throwException($exceptionText){
		throw new Exception($exceptionText);
	}
	private function sendEmail($action,$case,$errorMessage="",$filename=""){
		return;
		switch($action){
			case "jobpublished":
				$emailBody="<style type='text/css'>p,tr,td{font-family:verdana;font-size:11px;}</style>";
				$emailBody.= "<p>Dear Admin,</p>";
				$emailBody.="<p>Below is the status of the processed Vacancies. </p>";
				$emailBody.="<table cellspacing='2' cellpading='2'><tr><th>Vacancy Title</th><th>Vacancy Reference</th><th>Status</th><th>Description</th></tr>";
				$subject=$this->compData[0]['company_code'].' -  Vacancy Data XML Posted to TotalJobs.';
				for($i=0;$i<count($this->jobIDs);$i++){
					if(isset($this->errorMsg[$this->jobIDs[$i]])){
						$emailBody.="<tr>";
						if(isset($this->errorMsg[$this->jobIDs[$i]]['vacancytitle'])){
							$emailBody.="<td valign='top'> ".$this->errorMsg[$this->jobIDs[$i]]['vacancytitle']."</td>";
						}else{
							$emailBody.="<td valign='top'>&nbsp;</td>";
						}
						if(isset($this->errorMsg[$this->jobIDs[$i]]['CRM_JobID'])){
							$emailBody.="<td valign='top'>".$this->errorMsg[$this->jobIDs[$i]]['CRM_JobID']."</td>";
						}else{
							$emailBody.="<td valign='top'>&nbsp;</td>";
						}
						if(isset($this->errorMsg[$this->jobIDs[$i]]['status'])){
							$status=$this->errorMsg[$this->jobIDs[$i]]['status']=='True'?$this->errorMsg[$this->jobIDs[$i]]['mode']:'Error';
							$emailBody.="<td valign='top'>".$status."</td>";
						}else{
							$emailBody.="<td valign='top'>&nbsp;</td>";
						}
						if(isset($this->errorMsg[$this->jobIDs[$i]]['message'])){
							$emailBody.="<td valign='top'>".$this->errorMsg[$this->jobIDs[$i]]['message']."</td>";
						}else{
							$emailBody.="<td valign='top'>&nbsp;</td>";
						}
					}
				}
				break;
			case 'error':
				$subject=$this->compData[0]['company_code'].' - Job posting failed to post to Total Jobs';
				$emailBody="<style type='text/css'>p{font-family:verdana;font-size:11px;}</style>";
				$emailBody.="<p>Dear Admin,</p>";
				$emailBody.="<p></p>";
				$emailBody.="<p>There is some error while posting vacancies to Total Jobs</a>";
				$emailBody.="<br/><strong>Error</strong> : ".$errorMessage."</p>";
				$emailBody.="<p>Please contact your CRM administrator for more information and resolution.</p>";
				$emailBody.="<p>Regards,<br/>Focus On Business JA Team</p>";
			break;
			case "errorconf":
				$subject=$this->compData[0]['company_code'].' - Invalid Total Jobs Configuration';
				$emailBody="<style type='text/css'>p{font-family:verdana;font-size:11px;}</style>";
				$emailBody.="<p>There is some problem in Total Jobs Configuration</p>";
				$emailBody.="<p>Please follow below steps to add proper configuration.</p>";
				$emailBody.="<p>Please login to administration panel at <a href='".$AppURL."'>".$AppURL."</a>";
				$emailBody.="<br/>After successful login, please go to Manager->Company->Additional Configuration";
				$emailBody.="<br/>Please set up values for all fields mentioned under Total Jobs Configuration.</p>";
			break;
		}
		if(isset($emailBody) && trim($emailBody)!=""){
			$this->show($emailBody);
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			$headers .= 'From: JustApply - API <info@justapply.uk>' . "\r\n";
			$headers .= 'Bcc: jigar@indapoint.com' . "\r\n";
			$to='jaapimail@gmail.com';
			$mail_send=mail($to,$subject,$emailBody,$headers);
			$this->show("mail_send=".$mail_send);
		}
	}

	private function show($str){
		return;
		$str="<span style='font-size:11px;font-family:verdana'>".$str."</span><hr>";
		//flushBuffer();
		ob_implicit_flush(1);
		echo $str;
		@ob_flush();
		@flush();
		ob_start();
	}
}
?>
