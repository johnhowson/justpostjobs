<?php
namespace api\classes;
require_once("classapitools.php");

use candidate;
use candidateexperience;
use candidatequalification;
class sendtoCRM{
  /** class created from a function by Jigar Dave
 *  and updated week beginning amended by John Howson
 *  @since 19/02/2021
  **/
  public $errors;
  public $usedefaultpath=true;
  /**
  * get the soap envelope for WebServices
  * @author John Howson from function by Jigar Dave
  * @since 21/02/2021
  * @param $token
  * @param $methodName // eg receive applicants
  **/
  private function getSOAPEnvelop($token,$methodName){
    global $template;
    $template->addData(array("token"=>$token,"methodName"=>$methodName));
  	if($methodName != "UpdateApplicationStatus"){
      $soapEnvelope=$template->render("soapenvelope");
  	}else{
      $soapEnvelope=$template->render("soapenveolopeupdatestatus");
  	}
  	return 	$soapEnvelope;
  }
  private function getCRMJobID($jobid)
  {
    global $db;
    $sql="select CRM_JobID from ja_jobs where jobid='$jobid'";
    $jj=$db->query($sql)->first();
    return $jj->CRM_JobID;
  }
  /**
   * get data from user table
   * should also be in user object
   * @param int $us_userid
   * @return array
   * @since 5/03/21
   */
  private function getuserdata($us_userid)
  {
    global $db;
    $fields=array("email","fname","lname","username");
    $table="users";
    $where="id";
    $sql=makeselect($fields,$table,$where,$us_userid);
    $user=$db->query($sql)->first();
    return $user;
  }
  /**
  * authenticate against CRM
  * @param $webserviceUrl // the url of the WebService
  * @param $emailBody // the body of the request
  **/
  function authenticateCRM($webserviceUrl,&$emailBody){
  	//global $AppAbsolutePath,$resume_parsing_code;
    global $template;
  	//include_once("nusoap.php");
  	$xml=$template->render('authrequest');
  	$emailBody.="Sending Authentication Request\r\n\r\n=========================================================================\r\n";
    $output=$this->makeCall($webserviceUrl,$xml,"AuthenticateUser",$emailBody);
    $search=array(
  	'<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">',
  	'<s:Body xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">',
  	'<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">',
  	'<soap:Body>',
  	'</soap:Body>',
  	'</soap:Envelope>',
  	'</s:Body>',
  	'</s:Envelope>'
  	);
  	$replace=array("","","","","","","","","","");
  	$output1=str_replace($search,$replace,$output);
  	$output_xml=simplexml_load_string($output1);
  	$token= $output_xml->AuthenticateUserResult;
  	return $token;
  }
  /** return questions in an array
   * JH
   * @since 21/2/2021
   * @param string $formname
   * @return array
   */
  private function loadquestions($formname)
  {
    global $db;
    $questionarray=array();
    $surveyid=getformidforpage($formname);
    $sql="select FieldName,Question,QuestionID from CentreFormQuestions Where SurveyID='$surveyid'";
    $rr=$db->query($sql);
    $results=$rr->results();
    foreach ($results as $item) {
      $questionarray[$item->FieldName]=$item->Question;
    }
    return $questionarray;
  }
  /**
  * send data to crm re candidate applications
  * @param $personid // the id of the candidate
  * @param $jobis // the id of the job
  * @param $upd // is this an updated record
  * @author Jigar Dave
  * modified 22/02/2021 by John Howson
  **/
  public function sendCandidateDatatoCRM($personid,$jobid=0,$upd=false){
  	global $db,$user,$template,$settings;
    $error=false;
  	$emailBody="";
  	//$compData=getCompanyData();
    $candidate=new candidate();
    $fields = array(
        "ja_person"=>array_keys((array)$candidate),
        "ja_jobApplications"=>array("id","question1","question2","answer1","answer2","dateAdded"),
    );
    $orderby = array(
        "ja_jobApplications.dateAdded"
    );
    $SourceOfApplication="JustApply V5.0";
     $fromstatement = "ja_jobApplications INNER JOIN ja_person on ja_jobApplications.personid=ja_person.personid";
    $sql=makeselectjoin($fields, $fromstatement, array("ja_jobApplications.jobid"=>array("val"=>$jobid,"eval"=>"="),"ja_person.personid"=>array("val"=>$personid,"eval"=>"=")),0, $orderby);
    $res=$db->query($sql);
    $cnt_res=$res->count();
    if($res->count()>0)
    {
      $candidatedata=$res->first();
      $user=$this->getuserdata($candidatedata->us_userid);
    }else{
      //abort
      $this->errors="no data found";
      saveLog("empty result in sending data to CRM: \n" .$sql,"sendCandidateDatatoCRM");
      return false;
    }
        $title="";
        if($candidatedata->gender=="Male")
        {
          $title="Mr";
        }
        if($candidatedata->gender=="Female")
        {
          $title="Ms";
        }
  	//saveLog("Function to Send Applicant Details to CRM Called.");
    $country="United Kingdom";
  	$externalJobId=$this->getCRMJobID($jobid);
  /*	if(isset($jobid) && $jobid>0 && defined('SEND_PDF_AS_BINARY_TO_CRM') && SEND_PDF_AS_BINARY_TO_CRM===true){

    	$filenm=sendApplicationasPDF($personid,$jobid);
  		if(isset($filenm['path']) && !is_dir($filenm['path']) && file_exists($filenm['path'])){
  			$handle = fopen($filenm['path'], "r");
  			$contents = fread($handle, filesize($filenm['path']));
  			$resumeFileContent=$contents;
  			$resumeFileContent=base64_encode($resumeFileContent);
  			$resumeFile=$filenm['file_name'];
  		}
  	}*/
  	$jobQuestionssection="";
  	if($jobid > 0){
      $template->addData(array("question1"=>$candidatedata->question1,"question2"=>$candidatedata->question2,"answer1"=>$candidatedata->answer1,"answer2"=>$candidatedata->answer2));
  		if(isset($candidatedata->question1) && !empty($candidatedata->question1) && isset($candidatedata->answer1) && !empty($candidatedata->answer1)){
        $jobQuestionssection=$template->render("question1");
  		}
  		if(isset($candidatedata->question2) && !empty($candidatedata->question1) && isset($candidatedata->answer2) && !empty($candidatedata->answer2)){
  			$jobQuestionssection=$template->render("question2");
  		}
  	}
    $webserviceUrl=$settings->crmurl;
  	if(strlen($webserviceUrl)>0){
      $exp=new candidateexperience();
      $quals=new candidatequalification();
      $filepath=$_SERVER['DOCUMENT_ROOT'] ."/cv_uploads/" .$candidatedata->cvfilename;
      $res_experience=$exp->loadexperience($personid);
      $res_qualification=$quals->loadqualifcations($personid);
      $registration3=$this->loadquestions("registration3");
      $template->addData(array("candidatedata"=>$candidatedata,"personid"=>$personid,
      "title"=>$title,"user"=>$user,"country"=>$country,"registration3"=>$registration3,"registrationform"=>$settings->registrationform,
      "jobQuestionssection"=>$jobQuestionssection,"SourceOfApplication"=>$SourceOfApplication,
      "res_experience"=>$res_experience,"res_qualification"=>$res_qualification,"filepath"=>$filepath,"jobid"=>$jobid,"externalJobId"=>$externalJobId));
  		$token=$this->authenticateCRM($webserviceUrl,$emailBody);
      // potentially allow a custom path
      $xml=$template->render("profilexml");
      $soap=$this->getSOAPEnvelop($token,"ReceiveApplicants");
      $fields=array("personid"=>$personid,"jobappid"=>$candidatedata->id,"xml"=>$xml);
      $table="ja_crmSpool";
      $db->insert($table,$fields);
  				$xml=str_replace("{SOAP_BODY}",$xml,$soap);
              //    echo $xml;exit;
  				$emailBody.="sending Candidate ADD Request\r\n\r\n======================================================================================\r\n\r\n";
  				//Making Call To Webservice
  				  $output=$this->makeCall($webserviceUrl,$xml,"ReceiveApplicants",$emailBody);
          //$result=exec("php /var/www/vhosts/ariadnedesigns.com/justapp.ariadnedesigns.com/api/sendtocrmexec.php $personid $candidatedata->id",$output,$return_val);
  				if($upd){
  					$subject='LOG - Candidate Details updated. Information sent to CRM';
  				}else{
  					$subject='LOG - New Candidate Submitted from JA to CRM';
  				}
  				sendErrEmail($subject,$emailBody);
  				//$error=sendCRMErrorEmail($output,$personid,$Job_Related_ResumeID);
  				if(!$error){
  					$sql_upd1="update ja_person set senttocrm='1',senttocrmDate='" .date('Y-m-d H:i:s') ."' where personid='".$personid."'";
  					$db->query($sql_upd1);
  					if($jobid>0){
  						$sql_upd2="update ja_jobApplications set senttocrm='1',senttocrmDate='" .date('Y-m-d H:i:s') ."' where personid='".$personid."' and jobid='".$jobid."'";
  						$db->query($sql_upd2);
  					}
                          $sql_ci="select count(*) cnt from ja_personSector where personid= '".$personid."'";
                          $res_ci=$db->query($sql_ci)->first();
                          if($res_ci->cnt>0){
                              $this->sendSectorsToCRM($personid);
                          }else{
                              $this->sendSectorsToCRM($personid,true);
                          }

  				}
          saveLog("API sent data to CRM: " .$emailBody);
  				//$filename=date("ymd_his").".html";
  				//$handle=fopen($AppAbsolutePath."ErrorLogs/".$filename,"w+");
  				//fwrite($handle,$emailBody);
  				//fclose($handle);
  			}else{
  				$subject='LOG - Webservice URL not configured';
  				$emailBody="<p>Not able to find the web-service URL in settings.</p>";
  				$emailBody.="<p>Please follow below steps to configure webservice URL.</p>";
  				$emailBody.="<p>Please login to administration panel at <a href='/controlpanel.php'>Control Panel</a>";
  				$emailBody.="<br/>After successful login, please go to Settings";
  				$emailBody.="<br/>Please set up value for CRM Webservice URL</p>";
  				sendErrEmail($subject,$emailBody);
  			}
  			return $output;
  }
  /**
  * originally sendFortuneFinderToCRM renamed by JH
  * @param $case // changes the SQL but not needed under JA5
  */
  function sendSectorsToCRM($personid,$isBlank=false){
  	global $db,$settings;
    if(!isset($settings->crmurl))return false;
    $webserviceUrl=$settings->crmurl;
  	$data="";
  	$callWebService=false;
  	$emailBody="";
    $elements="";
  	//createLogFile("Sending Fortune Finder Selection to CRM for Applicant ID : ".$applicantId);
  	if(strlen($webserviceUrl)>0){
  		//createLogFile("WebService URL is : ".$webserviceUrl);
  			$sql="select sectorName from ja_sector inner join ja_personSector on ja_sector.sectorid=ja_personSector.sectorid where ja_personSector.personid=$personid";
  			$XMLCase="Sector";
  				$rs=$db->query($sql);
  				if($rs->count()>0 && !$isBlank){
  					$results=$rs->results();
  					foreach ($results as $item) {
              $elements.="<element>".htmlentities($item->sectorName)."</element>";
            }
  					if($elements != ""){
  						$data="<addElement>".$elements."</addElement>";
  						$XMLBody="<Applicant>
  										<JustApplyRefID>".$personid."</JustApplyRefID>
  										<elementname>".$XMLCase."</elementname>";
  						$XMLBody.=		$data;
  						$XMLBody.="</Applicant>";
  						$callWebService=true;

  				}
        }else{
          $XMLBody="<Applicant>
    						<JustApplyRefID>".$personid."</JustApplyRefID>
    						<elementname>".$XMLCase."</elementname>
    						<addElement>
    						<element />
    						</addElement>
    						</Applicant>";
        }
  			//createLogFile("Calling Authentication Webservice from Fortune Finder Function");
  			$token=$this->authenticateCRM($webserviceUrl,$emailBody);
  			saveLog("Authentication Token Received for Sectors...".$token);
  			$soap=getSOAPEnvelop($token,"addSectors");
  			$xml=str_replace("{SOAP_BODY}",$XMLBody,$soap);
  			//createLogFile("Final XML".$xml);

  			$this->makeCall($webserviceUrl,$xml,"addSectors",$emailBody);
  			$subject='LOG -  '.$XMLCase.' Selection Information Sent To CRM';
  			sendErrEmail($subject,$emailBody);
        saveLog("Sent XML ".$xml);

  }
}

  function makeCall($webserviceUrl,$xml,$methodname,&$emailBody){
  	$output="";
  	$webserviceUrl1=$webserviceUrl."?op=".$methodname;
  	$emailBody.="Request URL : ".$webserviceUrl1."\r\n\r\n";
  	$emailBody.=wordwrap($xml,1000)."\r\n\r\n ===============================================================================================================================================================\r\n";
    $ch1 = curl_init($webserviceUrl1);
  	curl_setopt($ch1, CURLOPT_SSL_VERIFYHOST, 2);
  	curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, 0);
  	curl_setopt($ch1, CURLOPT_POST, 1);
  	//curl_setopt($ch1, CURLOPT_HTTPHEADER,array('Content-Type:text/xml','SOAPAction:http://tempuri.org/'.$methodname));
  	curl_setopt($ch1, CURLOPT_HTTPHEADER,array('Content-Type:text/xml','SOAPAction:http://tempuri.org/IService/'.$methodname));
  	curl_setopt($ch1, CURLOPT_POSTFIELDS, $xml);
  	curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);
  	$output = curl_exec($ch1);
  	$info = curl_getinfo($ch1);
  	curl_close($ch1);
  	$emailBody.="Output\r\n\r\n===================================================================================================================================================\r\n";
  	$emailBody.=$output;
  	$emailBody.="\r\n\r\n";
  	return $output;
  }

}
