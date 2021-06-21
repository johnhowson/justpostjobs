<?php
namespace api\classes;
require_once("classapitools.php");
class posttoreed{
  function postJobToReed($JobID,$action='add',$username,$client_id,$api_token,$posting_key){
      global $obj,$AppURL;
      //$posting_key='8d862341-23f1-490d-8956-5315b9ca5215';
      //$username='dipen@indapoint.com';
      $user_agent = 'ReedAgent';
      //$api_token = '4aca7714-61d6-4558-9667-8d1896ba8ca7';
      //$client_id = '1418477';*/


      /*$username='jerry.lipman@justapply.co.uk';
      //password: password1
      $posting_key='03cbd494-bbf8-44a1-ad8f-a7fee2c9ccf8';
      $client_id = '1190243';
      $api_token = 'a7863b6f-51af-400a-a11b-3469f635f5f1';*/

      if($client_id==""){
          $client_id=REED_CLIENT_ID;
      }
      if($api_token==""){
          $api_token=REED_API_TOKEN;
      }

      $sql1="SELECT * FROM `JobOrders` WHERE JobID = '".$JobID."'";
      $res1=$obj->select($sql1);
      $sql2="select EFV.`ExtraFieldDefinationID`, EFV.`ExtraFieldTitle`,rc.default_field_name, EFV.Value as `value` from ExtraFieldValues EFV, customize_job_form rc, ExtraFieldsDefination EFD where rc.is_extra_field='Yes' and rc.extra_field_id=EFV.ExtraFieldDefinationID and EFV.ExtraFieldDefinationID = EFD.ExtraFieldDefinationID and EFD.ExtraFieldType != 'label' and EFD.EntityType='job' and EFV.EntitiyID='".$JobID."' ";
      $res2=$obj->select($sql2);
      $cnt_res2=count($res2);
      $ExtraField="";
  	$ExtraField=array();
      for($p=0;$p<$cnt_res2;$p++){
      	$ExtraFields[trim($res2[$p]["default_field_name"])]=trim($res2[$p]["value"]);
      }

      //$datediff=strtotime($res1[0]['LastDate'])-strtotime($res1[0]['StartDate']);
      $datediff=strtotime($res1[0]['StartDate'])-strtotime(date("Y-m-d"));
      $ExpiryInDays=round($datediff/(60*60*24));
      $workingHours=$res1[0]['hours']=='Full Time'?"1":"2";

      if(isset($res1[0]['City']) && !empty($res1[0]['City'])){
          if(isset($res1[0]['post_code']) && !empty($res1[0]['post_code'])){
              //$townName=$res1[0]['post_code'];
          }
          if(isset($res1[0]['City']) && !empty($res1[0]['City'])){
              $townName=$res1[0]['City'];
          }
          if(isset($res1[0]['Country']) && !empty($res1[0]['Country'])){
              //$townName.=($townName!="")?",".$res1[0]['Country']:"";
          }
      }else if(isset($res1[0]['ClientID']) && $res1[0]['ClientID']>0){
  		$sql="select * from client where ClientID='".$res1[0]['ClientID']."'";
  		$client=$obj->select($sql);
  		if($client){
              if(isset($client[0]['Zip']) && !empty($client[0]['Zip'])){
  				//$townName=$client[0]['Zip'];
  			}
  			if(isset($client[0]['City']) && !empty($client[0]['City'])){
  				$townName=$client[0]['City'];
  			}
              if(isset($client[0]['county']) && !empty($client[0]['county'])){
  				//$townName.=($townName!="")?",".$client[0]['county']:"";
  			}
  		}
      }

      $externalUrl=$AppURL.$JobID."-".preg_replace("/[^a-zA-Z0-9]+/","_",str_replace(' ', '_',trim($res1[0]['Jobtitle']))).".html?s=reed";

      $body="";
      if(isset($res1[0]["BriefDesc"]) && !empty($res1[0]["BriefDesc"])){
  		$body.="<p><strong>Vacancy Overview:</strong></p>";
  		$body.="<p>";
  		$body.=nl2br(xml_escape($res1[0]["BriefDesc"]));
  		$body.="</p>";
  	}
  	if(isset($ExtraFields["Working_Week"]) && !empty($ExtraFields["Working_Week"])){
  		$body.="<p>";
  		$body.="<strong>Working Week:</strong><br/>";
  		$body.="</p>";
  		$body.="<p>";
  		$body.=nl2br(xml_escape($ExtraFields["Working_Week"]));
  		$body.="</p>";
  	}
  	if(isset($res1[0]["salary"]) && !empty($res1[0]["salary"])){
  		$body.="<p>";
  		$body.="<strong>Weekly Wage:</strong><br/>";
  		$body.="</p>";
  		$body.="<p>";
  		$body.="&pound;".$res1[0]["salary"];
  		$body.="</p>";
  	}
  	if(isset($res1[0]["DetailDesc"]) && !empty($res1[0]["DetailDesc"])){
  		$body.="<p>";
  		$body.="<strong>Detailed Job Description:</strong><br/>";
  		$body.="</p>";
  		$body.="<p>";
  		$body.=nl2br(xml_escape($res1[0]["DetailDesc"]));
  		$body.="</p>";
  	}
  	if(isset($res1[0]["MainSkill"]) && !empty($res1[0]["MainSkill"])){
  		$body.="<p>";
  		$body.="<strong>Skills Required:</strong><br/>";
  		$body.="</p>";
  		$body.="<p>";
  		$body.=nl2br(xml_escape($res1[0]["MainSkill"]));
  		$body.="</p>";
  	}
  	if(isset($ExtraFields["Personal_Qualities"]) && !empty($ExtraFields["Personal_Qualities"])){
  		$body.="<p>";
  		$body.="<strong>Personal Qualities:</strong><br/>";
  		$body.="</p>";
  		$body.="<p>";
  		$body.=nl2br(xml_escape(@$ExtraFields["Personal_Qualities"]));
  		$body.="</p>";
  	}
  	$showHead=true;
  	if(isset($ExtraFields["Important_Other_Information"]) && !empty($ExtraFields["Important_Other_Information"])){
  		$body.="<p>";
  		$body.="<strong>Other Information:</strong><br/>";
  		$body.="</p>";
  		$body.="<p>";
  		$body.=nl2br(xml_escape(@$ExtraFields["Important_Other_Information"]));
  		$body.="</p>";
  		$showHead=false;
  	}
  	if(isset($ExtraFields["Reality_Check"]) && !empty($ExtraFields["Reality_Check"])){
  		if($showHead){
  			$body.="<p>";
  			$body.="<strong>Other Information:</strong><br/>";
  			$body.="</p>";
  		}
  		$body.="<p>";
  		$body.=nl2br(xml_escape(@$ExtraFields["Reality_Check"]));
  		$body.="</p>";
  	}

      $productId=isset($ExtraFields["reed_credit_type"]) && !empty($ExtraFields["reed_credit_type"])?$ExtraFields["reed_credit_type"]:81;
      $parentSectorId=isset($ExtraFields["reed_sector"]) && !empty($ExtraFields["reed_sector"])?$ExtraFields["reed_sector"]:"";
      $jobSectorId=isset($ExtraFields["reed_sub_sector"]) && !empty($ExtraFields["reed_sub_sector"])?$ExtraFields["reed_sub_sector"]:"";

      // Construct job data
      $data = array(
      "username"=>$username,
      "jobType"=>"1", // 1 = Permanent, 2 = Contract, 4 = Temporary
      "workingHours"=>$workingHours, // 1 = Full time, 2 = Part time, 3 = Full or Part time
      "description"=>$body,
      "title"=>$res1[0]['Jobtitle'],
      "townName"=>substr($townName,0,255),
      "postingKey"=>$posting_key,
      "productId"=>$productId, // 1 = Standard job, 2 = Premium job, 3 = Featured job, 4 = Premium+ job, 51 = TempJob+, 79 = IT job, 81 = Free job, 56587 = Energy job, 89655 = Priority job
      "expiryInDays"=>$ExpiryInDays>42?42:$ExpiryInDays, //The number of days the job has to be on the website. Max of 42.
      "isDemo"=>"false",
      "isPublic"=>"true",
      //"isGraduate"=>"true",
      "sendApplicationDigest"=>"false",
      "ownerRef"=>$res1[0]['CRM_JobID'],
      //"profileId"=>"",
      "countyName"=>$res1[0]['State'],
      "countryName"=>$res1[0]['Country'],
      "parentSectorId"=>$parentSectorId,
      "jobSectorId"=>$jobSectorId,
      //"minSalary"=>$res1[0]['salary']-1,
      //"maxSalary"=>$res1[0]['salary'],
      //"Currency"=>"1", //1 = GBP,2 = EUR, 3 = AUD, 8 = USD, 21 = CAD
      //"salaryType"=>, // 1 = Per hour, 2 = Per day, 5 = Per annum (Default)
      //"ote"=>"false",
      //"benefits"=>"false",
      //"proRata"=>"false",
      //"negotiable"=>"true",
      "showSalary"=>"false", //$res1[0]['salary']>0?"true":"false",
      "hiddenSalaryDescription"=>2, // Only used if showSalary is false, 0=Negotiable(Default), 1=Commision only, 2=Competitive
      "emailForApplications"=>$username,
      //"eligibleUkOnly"=>"false",
      "externalUrl"=>$externalUrl,
      "Skills[0]"=>$res1[0]['MainSkill']
      );

      switch($action){
          case 'add':
              $url = 'https://www.reed.co.uk/recruiter/api/1.0/jobs';
              $http_method = 'POST';
              break;
          case 'edit':
              $url = 'https://www.reed.co.uk/recruiter/api/1.0/jobs/update/'.$res1[0]['reed_job_id'];
              $http_method = 'PUT';
              break;
          case 'end':
              $url = 'https://www.reed.co.uk/recruiter/api/1.0/jobs/end/'.$res1[0]['reed_job_id'];
              $http_method = 'PUT';
              break;
          case 'relist':
              $url = 'https://www.reed.co.uk/recruiter/api/1.0/jobs/relist/'.$res1[0]['reed_job_id'];
              $http_method = 'PUT';
              break;
          case 'extend':
              $url = 'https://www.reed.co.uk/recruiter/api/1.0/jobs/extend/'.$res1[0]['reed_job_id'];
              $http_method = 'PUT';
              break;
      }

      $dt = new DateTime('now', new DateTimeZone('UTC'));
      $timestamp = $dt->format('Y-m-d\TH:i:s') . '+00:00';

      // Calculate signature
      $string_to_sign = $http_method . $user_agent . urldecode($url) . parse_url($url, PHP_URL_HOST) . $timestamp;
      $hmac_sha1_hash = hash_hmac('sha1', $string_to_sign, $api_token , true);
      $api_signature = base64_encode($hmac_sha1_hash);

      // Add required headers
      $headers = array();
      $headers[] = 'ContentType: application/x-www-form-urlencoded';
      $headers[] = 'Method: ' . $http_method;
      $headers[] = 'User-Agent: ' . $user_agent;
      $headers[] = 'X-ApiSignature: ' . $api_signature;
      $headers[] = 'X-ApiClientId: ' . $client_id;
      $headers[] = 'X-TimeStamp: ' . $timestamp;

      // Build request using cURL
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
      curl_setopt($ch, CURLOPT_URL, $url);
      if($http_method == 'POST') {
          curl_setopt($ch, CURLOPT_POST, 1);
      }
      if($http_method == 'PUT') {
          curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
      }
      curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
      curl_setopt($ch, CURLOPT_TIMEOUT, 10);

      // Make API call
      $response = curl_exec($ch);
      curl_close($ch);
      //print_r($response);
      $reed_response=json_decode($response,true);
      if(!is_array($reed_response) && strstr($reed_response,'ResponseMessage')){
          $reed_response=json_decode($reed_response,true);
      }
      //print_r($reed_response);exit;
      if(isset($reed_response['JobId']) && !empty($reed_response['JobId'])){
  		$reed_message=$reed_response["ResponseMessage"];
  		$ret_val["Reed_Message"]=$reed_response["ResponseMessage"];
  		$reed_id=$reed_response['JobId'];
  		$reed_status="Published";
  		$ret_val["Reed_ID"]=$reed_response['JobId'];
  		$status_text="Success";
  	}else{
  		if(isset($reed_response["message"]) && !empty($reed_response["message"])){
  			$reed_message=$reed_response["message"];
  		}
  		if(isset($reed_response["modelState"]) && !empty($reed_response["modelState"])){
              $reed_message2=' - ';
              foreach($reed_response["modelState"] as $modelStates){
                  foreach($modelStates as $modelState){
                      $reed_message2.=$reed_message2==' - '?$modelState:" ; ".$modelState;
                  }
              }
              $reed_message.=$reed_message2;
  		}
  		if(trim($reed_message)==""){
  			$reed_message="There is some error while posting vacancy to Reed. Blank response received from Reed";
  		}
  		$ret_val["Reed_Message"]=$reed_message;
  		$ret_val["Reed_ID"]=0;
  		$status_text="Failed";
  		$status="Failed";
          $reed_status="Failed";
          $reed_id=0;
  		sendReedPostingErrorEmail($reed_message,$JobID);
  	}

  	/*if($action=="add"){
  		$sql="update JobOrders set reed_status='".$reed_status."', reed_job_id='".$reed_id."' where JobID='".$JobID."'";
  		$rs=$obj->edit($sql);
  	}*/
    $this->registerInAPILog($JobID,json_encode($data),$response,$status_text);
  	//logChannelResponse("Reed",$JobID,@$res1[0]['CRM_JobID'],@$reed_id,$action,$url,json_encode($data),$response,$status_text);
  	return $ret_val;
  }
  private function registerInAPILog($JobID,$request,$response,$status){
      global $db;
    //$sql="update JobOrders set `careermap_request`='".escapeStr($request)."', `careermap_response`='".escapeStr($request)."' where JobID='".$JobID."'";
    $fields=array("jobid"=>$JobID,"apiname"=>"reed","message"=>"request: " .$request,"response"=>$response,"reference"=>$status,"dateadded"=>date('Y-m-d H:i:s'));
    $table="ja_apiLog";
    $rs=$db->insert($table,$fields);
  }
  function sendReedPostingErrorEmail($errorDescription,$JobID){
  	global $obj;
  	if(isset($JobID) && is_numeric($JobID) && $JobID>0){
  		$sql="select JobID, Jobtitle,CRM_JobID from JobOrders where JobID='".$JobID."'";
  		$rs=$obj->select($sql);
  		if(isset($rs[0]) && isset($rs[0]['JobID']) && is_numeric($rs[0]['JobID']) && $rs[0]['JobID']==$JobID){
  			$compData = getCompanyData();
  			if(trim($errorDescription)!="" && isset($compData[0]['company_code']) && isset($compData[0]['fromaddress'])){
  				//Getting Template
  				$sql="select * from TemplatesMaster where TemplateCode='Template_62'";
  				$template=$obj->select($sql);
  				if(isset($template[0]) && !empty($template[0]["Subject"]) && !empty($template[0]["TemplateContents"])){
  					$EmailBody=$template[0]["TemplateContents"];
  					$Subject=$template[0]["Subject"];
  					$Subject=str_replace("{companycode}",ucfirst(strtolower($compData[0]['company_code'])),$Subject);
  					$search=array("{JobTitle}","{VacancyReference}","{ErrorDescription}");
  					$replace=array($rs[0]['Jobtitle'],$rs[0]['CRM_JobID'],$errorDescription);
  					$EmailBody=str_replace($search,$replace,$EmailBody);
  					$style='<style type="text/css">p{font-family:verdana;font-size:12px;}</style>';
  					$to=$compData[0]['fromaddress'];
  					errmailsend($to, $Subject, $style.$EmailBody,"","",0,false,"Reed");
  				}
  			}
  		}
  	}
  }
}
