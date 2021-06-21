<?php
class sendtoCRM{
  public function sendCandidateDatetoCRM($resumeId,$jobId=0,$upd=false){
  	global $obj,$CompanyPath,$resume_folder_name,$resume_download_folder_name,$arrResumeSource,$resume_parsing_code,$AppAbsolutePath,$AppURL,$aws,$awsCofigAry,$CDN_URL,$gdpr_questions_ids;
  	$emailBody="";
  	$compData=getCompanyData();
  	createLogFile("Function to Send Applicant Details to CRM Called.");
  	if($jobId != 0){
  		$sql="select j.CRM_JobID as externalJobId,r.*,jrr.question1,jrr.question2,jrr.answer1,jrr.answer2,jrr.Job_Related_ResumeID as Job_Related_ResumeID, jrr.applied as resume_source,jrr.createdOn as created, jrr.travel_times from `resume` r left join `Job_Related_Resume` jrr on jrr.resumeID=r.resumeID left join `JobOrders` j on j.JobID=jrr.jobID where r.resumeID='".$resumeId."' and jrr.jobID='".$jobId."'";
  	}else{
  		$sql="select *,createdOn as `created` from resume where resumeID='".$resumeId."'";
  	}
  	$res=$obj->select($sql);
  	//Getting ExtraFields
  	$sql="select EFV.`ExtraFieldDefinationID`, EFV.`ExtraFieldTitle`,rc.default_field_name, EFV.Value from ExtraFieldValues EFV, regformconfig rc, ExtraFieldsDefination EFD where rc.is_require='True' and rc.is_extra_field='Yes' and rc.extra_field_id=EFV.ExtraFieldDefinationID and EFV.ExtraFieldDefinationID = EFD.ExtraFieldDefinationID and EFD.ExtraFieldType != 'label' and EFD.EntityType='candidate' and EFV.EntitiyID='".$resumeId."'";
  	$rs_extra=$obj->select($sql);
  	$ExtraFields=array();
  	$personalInfoFields=array('areyouinfull-timeeducation','whatareyourstrengthsegtimemanagementorganisingetc','whatpersonalskillswouldyouliketoimproveegtimemanagementskill','whatareyourhobbiesinterestsorachievements','whydoyouwanttoworkinthissector','whatskillsdoyouhavewhichyoubelievewillmakeyoutherightperson','Ethnicity','pleaseconfirmyoure-mailaddress','enternewaccountpassword','re-enterpassword','iwantyoutokeepmyaccountopenfortwelvemonths','iamhappytoreceiveotherinterestinginformation');
  	if(is_array($rs_extra) && count($rs_extra)>0){
  		for($i=0;$i<count($rs_extra);$i++){
  			$ExtraFields[$rs_extra[$i]['default_field_name']]['value']=$rs_extra[$i]['Value'];
  			$ExtraFields[$rs_extra[$i]['default_field_name']]['id']=$rs_extra[$i]['ExtraFieldDefinationID'];
  			$ExtraFields[$rs_extra[$i]['default_field_name']]['title']=$rs_extra[$i]['ExtraFieldTitle'];
  		}
  	}
  	createLogFile("Fetching Records \r\n".$sql);
  	$Job_Related_ResumeID=@$res[0]['Job_Related_ResumeID'];
  	$externalJobId=@$res[0]['externalJobId'];
  	$first_name=$res[0]['FirstName'];
  	$last_name=$res[0]['LastName'];
  	$email=$res[0]['Email'];
  	$phone=$res[0]['Phone'];
  	$mobile=$res[0]['Mobile'];
  	$day_contact=$res[0]['Day_contact'];
  	$address1=$res[0]['Address1'];
  	$address2=$res[0]['Address2'];
  	$current_city=$res[0]['Current_city'];
  	$current_state=$res[0]['Current_state'];
  	$current_country=$res[0]['Current_country'];
  	$zipcode=$res[0]['zipcode'];
  	$date_of_birth=$res[0]['Date_of_birth'];
  	$exp_date_of_birth=explode("-",$date_of_birth);
  	$birth_year=$exp_date_of_birth[0];
  	$birth_month=$exp_date_of_birth[1];
  	$birth_day=$exp_date_of_birth[2];
  	$createdYear=date('Y',strtotime($res[0]['created']));
  	$createdMonth=date('m',strtotime($res[0]['created']));
  	$createdDay=date('d',strtotime($res[0]['created']));
  	$resume_source=($jobId != 0)?$res[0]['resume_source']:$res[0]['resume_source_code'];
      $candidate_registration_form=$res[0]['candidate_registration_form'];
  	$full_time_education=$res[0]['full_time_education'];
  	$hold_an_hnd_degree=$res[0]['hold_an_hnd_degree'];
  	$work_30_hours_per_week=$res[0]['work_30_hours_per_week'];
      $travel_times=unserialize($res[0]['travel_times']);
  	$sql="SELECT resume_source_id,resume_source_title FROM resume_source WHERE resume_source_code='".$resume_source."'";
  	$data=$obj->select($sql);
  	$resume_source_id=$data[0]['resume_source_title'];
  	$SourceOfApplication=$data[0]['resume_source_title'];
  	$resumeFile1=$res[0]['ResumeFile'];
  	$resumeFileContent='';
  	$resumeFileContent1='';
  	if(!empty($resumeFile1)){
  		if($aws===true){
  				include_once($AppAbsolutePath.'StdFunctions/aws_functions.php');
  				$_url=$CDN_URL.$compData[0]['company_code']."/".$resume_folder_name.$resume_download_folder_name.rawurlencode($resumeFile1);
  				$contents1=@file_get_contents($_url);
  				if($contents1 !== false){
  					$resumeFileContent1=base64_encode($contents1);
  				}else{
  					$resumeFile1="";
  				}
  		}else{
  			$filepath=$CompanyPath.$resume_folder_name.$resume_download_folder_name.$resumeFile1;
  			if(file_exists($filepath)){
  				$handle = fopen($filepath, "r");
  				$contents1 = fread($handle, filesize($filepath));
  				$resumeFileContent1=base64_encode($contents1);
  			}
  		}
  	}
  	if(isset($jobId) && $jobId>0 && defined('SEND_PDF_AS_BINARY_TO_CRM') && SEND_PDF_AS_BINARY_TO_CRM===true){
  		$filenm=sendApplicationasPDF($resumeId,$jobId);
  		if(isset($filenm['path']) && !is_dir($filenm['path']) && file_exists($filenm['path'])){
  			$handle = fopen($filenm['path'], "r");
  			$contents = fread($handle, filesize($filenm['path']));
  			$resumeFileContent=$contents;
  			$resumeFileContent=base64_encode($resumeFileContent);
  			$resumeFile=$filenm['file_name'];
  		}
  	}
  	$jobQuestionssection="";
  	if($jobId != 0){
  		if(isset($res[0]['question1']) && !empty($res[0]['question1']) && isset($res[0]['answer1']) && !empty($res[0]['answer1'])){
  			$jobQuestionssection.="<Question1>";
  			$jobQuestionssection.="<QuestionText transform='base64'>";
  			$encodedQuestion1=base64_encode(xml_escape($res[0]['question1']));
  			$jobQuestionssection.=$encodedQuestion1;
  			$jobQuestionssection.="</QuestionText>";
  			$jobQuestionssection.="<Answer transform='base64'>";
  			$jobAnswer1=base64_encode(xml_escape($res[0]['answer1']));
  			$jobQuestionssection.=$jobAnswer1;
  			$jobQuestionssection.="</Answer>";
  			$jobQuestionssection.="</Question1>";
  		}
  		if(isset($res[0]['question2']) && !empty($res[0]['question2']) && isset($res[0]['answer2']) && !empty($res[0]['answer2'])){
  			$jobQuestionssection.="<Question2>";
  			$jobQuestionssection.="<QuestionText transform='base64'>";
  			$encodedQuestion2=base64_encode(xml_escape($res[0]['question2']));
  			$jobQuestionssection.=$encodedQuestion2;
  			$jobQuestionssection.="</QuestionText>";
  			$jobQuestionssection.="<Answer transform='base64'>";
  			$jobAnswer2=base64_encode(xml_escape($res[0]['answer2']));
  			$jobQuestionssection.=$jobAnswer2;
  			$jobQuestionssection.="</Answer>";
  			$jobQuestionssection.="</Question2>";
  		}
  	}
  	$sql_url="select CRM_Webservice_URL from company";
  	$rs_url=$obj->select($sql_url);
  	if(is_array($rs_url) && isset($rs_url[0]['CRM_Webservice_URL']) && trim($rs_url[0]['CRM_Webservice_URL'])!=""){
  		$webserviceUrl=$rs_url[0]['CRM_Webservice_URL'];
  		createLogFile("Generating Authentication XML ");
  		include_once($AppAbsolutePath.$resume_parsing_code."lib/nusoap.php");
  		$token=authenticateCRM($webserviceUrl,$emailBody);
  		createLogFile("Authentication Token Received...".$token);
  		$soap=getSOAPEnvelop($token,"ReceiveApplicants");
  		$xml="	<ApplicationFeed>
  					<Application ID='".@$Job_Related_ResumeID."' SourceOfApplication='".xml_escape($SourceOfApplication)."' SourceID='".$resume_source_id."' LastModified='".date('Ymdhis')."' ApplicationFormID='".@$jobId."' PreScreenScore='100' TalentPoolScore='69' CandidateRegistrationForm='".$candidate_registration_form."'>
  						<Keys>
  							<ApplicantID Origin='internal' ID='".$resumeId."' />
  							<VacancyID Origin='external' ID='".@$externalJobId."' />
  							<VacancyID Origin='internal' ID='".@$jobId."' />
  							<Company ID='11603'>23</Company>
  						</Keys>
  						<Applicant>
  							<PersonalInfo>
  								<Title>".xml_escape(@$ExtraFields['title']['value'])."</Title>
  								<Firstname>".xml_escape($first_name)."</Firstname>
  								<Surname>".xml_escape($last_name)."</Surname>
  								<Nationality ISOCode='' Assignment='first' HideAnswer='true'>".xml_escape($current_country)."</Nationality>
  								<DateOfBirth HideAnswer='true'>
  									<Date>
  										<Day>".xml_escape($birth_day)."</Day>
  										<Month>".xml_escape($birth_month)."</Month>
  										<Year>".xml_escape($birth_year)."</Year>
  									</Date>
  								</DateOfBirth>
  								<Ethnicity>".xml_escape(@$ExtraFields['Ethnicity']['value'])."</Ethnicity>
  								<Question1>".xml_escape((strtolower($full_time_education)=='yes'?"true":"false"))."</Question1>
  								<Question2>".xml_escape(@$ExtraFields['whatareyourstrengthsegtimemanagementorganisingetc']['value'])."</Question2>
  								<Question3>".xml_escape(@$ExtraFields['whatpersonalskillswouldyouliketoimproveegtimemanagementskill']['value'])."</Question3>
  								<Question4>".xml_escape(@$ExtraFields['whatareyourhobbiesinterestsorachievements']['value'])."</Question4>
  								<Question5>".xml_escape(@$ExtraFields['whydoyouwanttoworkinthissector']['value'])."</Question5>
  								<Question6>".xml_escape(@$ExtraFields['whatskillsdoyouhavewhichyoubelievewillmakeyoutherightperson']['value'])."</Question6>
  							</PersonalInfo>
  							<ContactInfo>
  								<Address>
  									<Address1>".xml_escape($address1)."</Address1>
  									<Address2>".xml_escape($address2)."</Address2>
  									<Town>".xml_escape($current_city)."</Town>
  									<County>".xml_escape($current_state)."</County>
  									<Postcode>".xml_escape($zipcode)."</Postcode>
  									<Country>".xml_escape($current_country)."</Country>
  								</Address>
  								<EmailAddress>".xml_escape($email)."</EmailAddress>
  								<PhoneNumbers>
  									<Voice Type='daytime'>
  										<TelNumber>".xml_escape($day_contact)."</TelNumber>
  									</Voice>
  									<Voice Type='evening'>
  										<TelNumber>".xml_escape($phone)."</TelNumber>
  									</Voice>
  									<Voice Type='mobile'>
  										<TelNumber>".xml_escape($mobile)."</TelNumber>
  									</Voice>
  								</PhoneNumbers>
  								<ContactAtWork></ContactAtWork>
  							</ContactInfo>
  						</Applicant>
  						<ApplicationDetails>
  							<AppliedOn>
  								<Date>
  									<Day>".xml_escape($createdDay)."</Day>
  									<Month>".xml_escape($createdMonth)."</Month>
  									<Year>".xml_escape($createdYear)."</Year>
  								</Date>
  							</AppliedOn>";
  							if(defined("GOOGLE_DISTANCE_MATRIX") && GOOGLE_DISTANCE_MATRIX==true){
  								$xml.=" <traveltimes>
  											<cardistance>".(isset($travel_times['driving']['distance']) && !empty($travel_times['driving']['distance'])?xml_escape($travel_times['driving']['distance']):0)."</cardistance>
  											<cartraveltime>".(isset($travel_times['driving']['traveltime']) && !empty($travel_times['driving']['traveltime'])?xml_escape($travel_times['driving']['traveltime']):0)."</cartraveltime>
  											<walkdistance>".(isset($travel_times['walking']['distance']) && !empty($travel_times['walking']['distance'])?xml_escape($travel_times['walking']['distance']):0)."</walkdistance>
  											<walktraveltime>".(isset($travel_times['walking']['traveltime']) && !empty($travel_times['walking']['traveltime'])?xml_escape($travel_times['walking']['traveltime']):0)."</walktraveltime>
  											<cycledistance>".(isset($travel_times['bicycling']['distance']) && !empty($travel_times['bicycling']['distance'])?xml_escape($travel_times['bicycling']['distance']):0)."</cycledistance>
  											<cycletraveltime>".(isset($travel_times['bicycling']['traveltime']) && !empty($travel_times['bicycling']['traveltime'])?xml_escape($travel_times['bicycling']['traveltime']):0)."</cycletraveltime>
  											<busdistance>".(isset($travel_times['bus']['distance']) && !empty($travel_times['bus']['distance'])?xml_escape($travel_times['bus']['distance']):0)."</busdistance>
  											<bustraveltime>".(isset($travel_times['bus']['traveltime']) && !empty($travel_times['bus']['traveltime'])?xml_escape($travel_times['bus']['traveltime']):0)."</bustraveltime>
  											<traindistance>".(isset($travel_times['train']['distance']) && !empty($travel_times['train']['distance'])?xml_escape($travel_times['train']['distance']):0)."</traindistance>
  											<traintraveltime>".(isset($travel_times['train']['traveltime']) && !empty($travel_times['train']['traveltime'])?xml_escape($travel_times['train']['traveltime']):0)."</traintraveltime>
  										</traveltimes>";
  								}
                          $xml.="".$jobQuestionssection."
  							<ApplicationForm>";
  								foreach($ExtraFields as $key=>$Field){
  									if(!in_array($key,$personalInfoFields)){
  										$QText=base64_encode(xml_escape($Field['title']));
  										$Fieldvalbase64=($Field['value']!='')?base64_encode(xml_escape($Field['value'])):'';
  										$xml.="<Question QuestionID='".$Field['id']."' SectionID='' Type='Questions' NotForScreening='false'>";
  										$xml.="<QuestionText transform='base64'>".$QText."</QuestionText>";
  										$xml.="<Answer transform='base64'>".$Fieldvalbase64."</Answer>";
  										$xml.="</Question>";
  									}
  								}
  								/* Added by Jigar as we need to pass these fields as 162 and 163 extra fields.*/
  								$QText=base64_encode(xml_escape(ARE_YOU_LOOKING_FOR_FULL_TIME_POSITION));
  								$Fieldvalbase64=($work_30_hours_per_week!='')?base64_encode(xml_escape($work_30_hours_per_week)):'';
  								$xml.="<Question QuestionID='162' SectionID='' Type='Questions' NotForScreening='false'>";
  								$xml.="<QuestionText transform='base64'>".$QText."</QuestionText>";
  								$xml.="<Answer transform='base64'>".$Fieldvalbase64."</Answer>";
  								$xml.="</Question>";

  								$QText=base64_encode(xml_escape(DO_YOU_HAVE_GCSE_ENGLISH_MATH));
  								$Fieldvalbase64=($hold_an_hnd_degree!='')?base64_encode(xml_escape($hold_an_hnd_degree)):'';
  								$xml.="<Question QuestionID='163' SectionID='' Type='Questions' NotForScreening='false'>";
  								$xml.="<QuestionText transform='base64'>".$QText."</QuestionText>";
  								$xml.="<Answer transform='base64'>".$Fieldvalbase64."</Answer>";
  								$xml.="</Question>";

                                  /* Added by Dipen for GDPR 165 and 166 extra field ids. START */
                                  if(defined('GDPR_QUESTIONS_ANSWERS') && GDPR_QUESTIONS_ANSWERS==true){
                                      $gdpr_timestamp_ids='';
                                      foreach($gdpr_questions_ids as $gdpr_que_id){
                                          $sql_que_text="SELECT gdpr.`id`, efd.`ExtraFieldTitle` , gdpr.`value` , gdpr.`created_at`
                                              FROM `ExtraFieldsDefination` efd
                                              JOIN gdpr_timestamp gdpr ON gdpr.extra_field_id = efd.`ExtraFieldDefinationID`
                                              WHERE efd.`ExtraFieldDefinationID`='".$gdpr_que_id."'
                                              AND gdpr.resumeID='".$resumeId."'
                                              ORDER BY gdpr.`id` DESC
                                              LIMIT 0,1";
                                          $res_que_text=$obj->select($sql_que_text);
                                          $QText=base64_encode(xml_escape($res_que_text[0]['ExtraFieldTitle']));
                                          $Fieldvalbase64=base64_encode(xml_escape($res_que_text[0]['value']));
                                          $timestamp=date('d/m/Y H:i:s',strtotime($res_que_text[0]['created_at']));
                                          $xml.="<Question QuestionID='".$gdpr_que_id."' SectionID='' Type='Questions' NotForScreening='false' timestamp='".$timestamp."'>";
                                          $xml.="<QuestionText transform='base64'>".$QText."</QuestionText>";
                                          $xml.="<Answer transform='base64'>".$Fieldvalbase64."</Answer>";
                                          $xml.="</Question>";
                                          $gdpr_timestamp_ids.=$gdpr_timestamp_ids==''?$res_que_text[0]['id']:",".$res_que_text[0]['id']; // will be used in update query '$sql_upd3'
                                      }
                                  }
                                  /* Added by Dipen for GDPR 165 and 166 extra field ids. END */

  								// select experience info from Resume_Experience
  								$sql_experience="select * from Resume_Experience where resumeID='".$resumeId."' ORDER BY ResumeExpID ASC";
  								$res_experience=$obj->select($sql_experience);
  								if($res_experience){
  									$xml.="<WorkHistory>";
  									foreach($res_experience as $kexp=>$vexp){
  										$expFromyear=explode("-",$vexp['FromYear']);
  										$expToyear=explode("-",$vexp['ToYear']);
  										$xml.="<WorkHistoryDetails>
  													<EmploymentHstoryId>".xml_escape($vexp['ResumeExpID'])."</EmploymentHstoryId>
  													<JobTitle>".xml_escape($vexp['JobTitle'])."</JobTitle>
  													<Level>".xml_escape($vexp['Level'])."</Level>
  													<CompanyName>".xml_escape($vexp['CompanyName'])."</CompanyName>
  													<Address1>".xml_escape($vexp['CompanyAddress1'])."</Address1>
  													<Address2>".xml_escape($vexp['CompanyAddress2'])."</Address2>
  													<Address3></Address3>
  													<Town>".xml_escape($vexp['city'])."</Town>
  													<Postcode>".xml_escape($vexp['postcode'])."</Postcode>
  													<Country>".xml_escape($vexp['country'])."</Country>
  													<County>".xml_escape($vexp['state'])."</County>
  													<Category></Category>
  													<Industry>".xml_escape($vexp['Industry'])."</Industry>
  													<KeyResponsibilities transform='base64'>".base64_encode(xml_escape($vexp['KeyResponsibilities']))."</KeyResponsibilities>
  													<MainAchievements transform='base64'>".base64_encode(xml_escape($vexp['MainAchievements']))."</MainAchievements>
  													<ReasonForLeaving>".xml_escape($vexp['ReasonForLeaving'])."</ReasonForLeaving>
  													<FinalSalary>".xml_escape($vexp['FinalSalary'])."</FinalSalary>
  													<StartMonth>".xml_escape(($expFromyear[0]!=""?date('m', strtotime($expFromyear[0])):""))."</StartMonth>
  													<StartYear>".xml_escape($expFromyear[1])."</StartYear>
  													<EndMonth>".htmlentities(($expToyear[0]!=""?date('m', strtotime($expToyear[0])):""))."</EndMonth>
  													<EndYear>".xml_escape($expToyear[1])."</EndYear>
  													<Sector>".xml_escape($vexp['JobSector'])."</Sector>
  													<IsVoluntaryWork>".xml_escape($vexp['was_this_work_voluntary'])."</IsVoluntaryWork>
  													<IsApprenticeshipRole>".xml_escape($vexp['was_this_role'])."</IsApprenticeshipRole>
  													<IsApprenticeshipCompleted>".xml_escape($vexp['role_complete'])."</IsApprenticeshipCompleted>
  												</WorkHistoryDetails>";
  									}
  									$xml.="</WorkHistory>";
  								}
  								$sql_qualification="select * from Resume_qualification where resumeID='".$resumeId."' ORDER BY ResQualificationID ASC";
  								$res_qualification=$obj->select($sql_qualification);
  								if($res_qualification){
  									$xml.="<Qualifications>";
  									foreach($res_qualification as $qkey=>$qval){
  										$gradeVal=(isset($qval['grade_type']) && $qval['grade_type']=='predicted')?strtolower($qval['grade_type']):"";
  										$xml.="<Qualification>
  													<QualificationID>".xml_escape($qval['ResQualificationID'])."</QualificationID>
  													<Subject>".xml_escape($qval['Course_title'])."</Subject>
  													<QualificationType>".xml_escape($qval['qualification'])."</QualificationType>
  													<Grade>".xml_escape($qval['Grade'])." ".$gradeVal."</Grade>
  													<Establishment>".xml_escape($qval['Establishment'])."</Establishment>
  													<ToYear>".xml_escape($qval['ToYear'])."</ToYear>
  													<Other>".xml_escape($qval['other_qualification'])."</Other>
  												</Qualification>";
  									}
  									$xml.="		</Qualifications>";
  								}
  								$xml.="</ApplicationForm>
  									<ApplicationDocuments>
  										<File filename='".$resumeFile."' content-type='application/octet-stream'>".$resumeFileContent."</File>
  										<File filename='".$resumeFile1."' content-type='application/octet-stream'>".$resumeFileContent1."</File>
  									</ApplicationDocuments>
  							</ApplicationDetails>
  						</Application>
  				</ApplicationFeed>";
  				$xml=str_replace("{SOAP_BODY}",$xml,$soap);
                  //echo $xml;exit;
  				$emailBody.="sending Candidate ADD Request\r\n\r\n======================================================================================\r\n\r\n";
  				//Making Call To Webservice
  				$output=makeCall($webserviceUrl,$xml,"ReceiveApplicants",$emailBody);
  				if($upd){
  					$subject='LOG - Candidate Details updated. Information sent to CRM';
  				}else{
  					$subject='LOG - New Candidate Submitted from JA to CRM';
  				}
  				sendErrEmail($subject,$emailBody);
  				$error=sendCRMErrorEmail($output,$resumeId,$Job_Related_ResumeID);
  				if(!$error){
  					$sql_upd1="update resume set send_candidate_data_to_CRM='No' where resumeID='".$resumeId."'";
  					$obj->edit($sql_upd1);
  					if($jobId>0){
  						$sql_upd2="update Job_Related_Resume set send_candidate_data_to_CRM='No' where resumeID='".$resumeId."' and JobID='".$jobId."'";
  						$obj->edit($sql_upd2);
  					}
                      if(defined(SEND_TO_CRM_THRU_CRON) && SEND_TO_CRM_THRU_CRON=="true"){
                          $sql_ci="select count(*) cnt from `candidate_industry` where candidates_id = '".$resumeId."'";
                          $res_ci=$obj->select($sql_ci);
                          if($res_ci[0]['cnt']>0){
                              sendFortuneFinderToCRM(1,$resumeId);
                          }else{
                              sendFortuneFinderToCRM(1,$resumeId,1);
                          }
                          $sql_cpa="select count(*) cnt from `candidate_programme_area` where candidates_id = '".$resumeId."'";
                          $res_cpa=$obj->select($sql_cpa);
                          if($res_cpa[0]['cnt']>0){
                              sendFortuneFinderToCRM(3,$resumeId);
                          }else{
                              sendFortuneFinderToCRM(3,$resumeId,1);
                          }
                      }
  				}
  				$filename=date("ymd_his").".html";
  				$handle=fopen($AppAbsolutePath."ErrorLogs/".$filename,"w+");
  				fwrite($handle,$emailBody);
  				fclose($handle);
  			}else{
  				$subject='LOG - Webservice URL not configured';
  				$emailBody="<p>Not able to find Webservice URL in Company Settings.</p>";
  				$emailBody.="<p>Please follow below steps to configure webservice URL.</p>";
  				$emailBody.="<p>Please login to administration panel at <a href='".$AppURL."'>".$AppURL."</a>";
  				$emailBody.="<br/>After successful login, please go to Manager->Company->Additional Configuration";
  				$emailBody.="<br/>Please set up value for CRM Webservice URL</p>";
  				sendErrEmail($subject,$emailBody);
  			}
  			return $output;
  }



}
