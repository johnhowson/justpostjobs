<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
$file_us_root="/var/www/vhosts/ariadnedesigns.com/justapp.ariadnedesigns.com";
require_once $file_us_root ."/users/init.php";
require_once $file_us_root ."/api/classes/apifunctions.php";
require_once $file_us_root ."/core/classsettings.php";
require_once $file_us_root ."/core/functions.php";
if(isset($file_us_root))
{
    require_once $file_us_root .'/pages/candidateexperience.php';
    require_once $file_us_root .'/pages/candidatequalification.php';
}else{
    require_once '../pages/candidateexperience.php';
    require_once '../pages/candidatequalification.php';
}
$db = DB::getInstance();
$settings=new settings();
$settings->usersettings();
	use api\classes;
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

saveLog("making call");
$personid=$argv[1];
$jobappid=$argv[2];
$sql="select xml from ja_crmSpool where personid='$personid' and jobappid='$jobappid'";
$xx=$db->query($sql)->first();
$xml=$xx->xml;
$emailBody="";
$webserviceUrl=$settings->crmurl;
$emailBody.="sending Candidate ADD Request\r\n\r\n======================================================================================\r\n\r\n";
//Making Call To Webservice
$subject='LOG - Candidate Details updated. Information sent to CRM';
$output=makeCall($webserviceUrl,$xml,"ReceiveApplicants",$emailBody);
saveLog("sent xml:" .$output);

sendErrEmail($subject,$emailBody);
