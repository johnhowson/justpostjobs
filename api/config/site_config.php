<?php
/*
Purpose			:	configuration file.
File Name		:	site_config.php
Cerated By		:	Jigar Dave
Created Date	:	28 September, 2005.
Module Name		:	Global
@author			:	Jigar Dave.
*/
$countrynames = array("Afghanistan","Albania","Algeria","American Samoa","Andorra","Angola","Anguilla","Antarctica","Antigua and Barbuda","Argentina","Armenia","Aruba","Australia","Austria",
"Azerbaijan","Bahamas","Bahrain","Bangladesh","Barbados","Belarus","Belgium","Belize","Benin","Bermuda","Bhutan","Bolivia","Bosnia and Herzegovina","Botswana","Bouvet Island","Brazil","British Indian Ocean Territory","Brunei Darussalam","Bulgaria","Burkina Faso","Burundi","Cambodia","Cameroon","Canada","Cape Verde",
"Cayman Islands","Central African Republic","Chad","Chile","China","Christmas Island","Cocos (Keeling) Islands","Colombia","Comoros","Congo","Congo, The Democratic Republic of the","Cook Islands","Costa Rica","Cote D'Ivoire","Croatia","Cuba","Cyprus","Czech Republic","Denmark","Djibouti","Dominica","Dominican Republic","Ecuador","Egypt","El Salvador",
"Equatorial Guinea","Eritrea","Estonia","Ethiopia","Falkland Islands (Malvinas)","Faroe Islands","Fiji","Finland","France","French Guiana","French Polynesia","French Southern Territories","Gabon","Gambia","Georgia","Germany","Ghana","Gibraltar","Greece","Greenland","Grenada","Guadeloupe","Guam","Guatemala","Guernsey",
"Guinea","Guinea-Bissau","Guyana","Haiti","Heard Island and McDonald Islands","Holy See (Vatican City State)","Honduras","Hong Kong","Hungary","Iceland","India","Indonesia","Iran, Islamic Republic of","Iraq","Ireland","Isle of Man","Israel","Italy","Jamaica","Japan","Jersey",
"Jordan","Kazakhstan","Kenya","Kiribati","Korea, Democratic People's Republic of","Korea, Republic of","Kuwait","Kyrgyzstan","Laos People's Democratic Republic","Latvia","Lebanon","Lesotho","Liberia","Libyan Arab Jamahiriya","Liechtenstein","Lithuania","Luxembourg","Macao","Macedonia, The former Yugoslav Republic of","Madagascar","Malawi","Malaysia","Maldives","Mali",
"Malta","Marshall Islands","Martinique","Mauritania","Mauritius","Mayotte","Mexico","Micronesia, Federated States of","Moldova, Republic of","Monaco","Mongolia","Montserrat","Morocco","Mozambique","Myanmar","Namibia","Nauru","Nepal","Netherlands","Netherlands Antilles","New Caledonia","New Zealand","Nicaragua","Niger","Nigeria",
"Niue","Norfolk Island","Northern Mariana Islands","Norway","Oman","Pakistan","Palau","Palestinian Territory, Occupied","Panama","Papua New Guinea","Paraguay","Peru","Philippines","Pitcairn","Poland","Portugal","Puerto Rico","Qatar","Reunion","Romania",
"Russian Federation","Rwanda","Saint Helena","Saint Kitts and Nevis","Saint Lucia","Saint Pierre and Miquelon",
"Saint Vincent and the Grenadines","Samoa","San Marino","Sao Tome and Principe","Saudi Arabia","Senegal","Serbia and Montenegro","Seychelles","Sierra Leone","Singapore","Slovakia","Slovenia","Solomon Islands","Somalia","South Africa","South Georgia and the South Sandwich Islands","Spain","Sri Lanka","Sudan","Suriname","SValbard and Jan Mayen","Swaziland","Sweden","Switzerland","Syrian Arab Republic",
"Taiwan, Province of China","Tajikistan","Tanzania, United Republic of","Thailand","Timor-Leste","Togo","Tokelau",
"Tonga","Trinidad and Tobago","Tunisia","Turkey","Turkmenistan","Turks and Caicos Islands","Tuvalu","Uganda","Ukraine","United Arab Emirates","United Kingdom","United States","United States Minor Outlying Islands","Uruguay","Uzbekistan","Vanuatu","Venezuela","Viet Nam","Virgin Islands, British","Virgin Islands, U.S.","Wallis and Futuna","Western Sahara","Yemen","Zambia","Zimbabwe");
$image_type = Array("image/jpeg" , "image/pjpeg", "image/jpg");
$DEFAULT_COUNTRY = "United Kingdom";
$logo_image_height = 292;
$logo_image_width = 112;
$option_image_height=60;
$option_image_width=200;
$option_image_show_height=100;
$option_image_show_width=100;
$PDFpersonvaViewLogoFile="logo.jpg"; //only jpeg allowed
$HeaderLeftText="HR Application";
$HeaderCenterText="";
$HeaderRightText="Report Date/Time: ".date("m/d/Y - g:i A T");//date("D M j G:i:s T Y")
$companylogoimagepath = "images/companylogo";
$FotterLeft="HR Application";
$FotterCenterText="(C) ".date("Y")." HR Application";
$FotterRightText=date("D M j G:i:s T Y"); // Example:-Sat Mar 10 15:16:08 MST 2001
$Phone_number="";
$newadminnotificationsubject="A new {ENTITY} has been added";
$Month_Array = array('01'=>"January",
					'02'=>"February",
					'03'=>"March",
					'04'=>"April",
					'05'=>"May",
					'06'=>"June",
					'07'=>"July",
					'08'=>"August",
					'09'=>"September",
					'10'=>"October",
					'11'=>"November",
					'12'=>"December");
$Month_Array_Short = array('01'=>"Jan",
					'02'=>"Feb",
					'03'=>"Mar",
					'04'=>"Apr",
					'05'=>"May",
					'06'=>"Jun",
					'07'=>"Jul",
					'08'=>"Aug",
					'09'=>"Sept",
					'10'=>"Oct",
					'11'=>"Nov",
					'12'=>"Dec");
$importFileDir="upload/";


$arrayReportList = array('1' => "Summary report",
					'2' => "Prints by user report",
					'3' => "Prints by printer report",
					'4' => "Prints by segment report",
					'5' => "Max/Min stats report");


$comboBackground=array("#ffffff","#ffff9f","#000066","#660000","#006600","#6666ff");
$comboForeground=array("#000000","#660000","#ffffff","#ffffff","#ffffff","#ffffff");
$fieldvalueforother="__Other__val";
$ReferredByArray=Array("Advertisement"=>"Advertisement","Affiliate/Partner"=>"Affiliate/Partner",
				"Article"=>"Article","Associate"=>"Associate","Called In"=>"Called In",
				"Customer Referral"=>"Customer Referral","Direct Mail"=>"Direct Mail",
				"E-Mail Campaign"=>"E-Mail Campaign","Event/Seminar"=>"Event/Seminar",
				"Friend"=>"Friend","Phone Book"=>"Phone Book","Search Engine"=>"Search Engine",
				"Trade Show"=>"Trade Show","Website"=>"Website","Word Of Mouth"=>"Word Of Mouth",
				"Other"=>"Other");


$RolesArray = array("1"=>"Super Admin",
					/*"2"=>"Company Admin",
					"3"=>"Department Head",
					"4"=>"Team Leader",*/ /**AV Comment**/
					"5"=>"Employee",
					"6"=>"Recruiter",
					"7"=>"HR Manger",
					"8"=>"Interviewer",
					"9"=>"Candidates");

$SystemAdminRoles = array("1"=>"Super Admin",
					"7"=>"Client HR-Manager",
					"6"=>"Client Recruiter");
$SYSTEM_SUPER_ADMIN_ROLE_ID = "1";
$SYSTEM_CLIENT_MANAGER_ID = "7";
$SYSTEM_CLIENT_RECRUITER_ID = "6";
/** USED IN JOB POST & SENDING MAIL AFTER POSTING JOB */
$SUPER_ADMIN_ROLE_ID = "1";
$COMPANY_ADMIN_ROLE_ID = "2";
$DEPT_ADMIN_ROLE_ID = "3";
$TEAM_LEAD_ROLE_ID = "4";
$COORDINATOR_ID = "5";
$RECRUITER_ID = "6";
$HR_MANAGER_ID = "7";
$INTERVIEWER_ID = "8";
$CLIENT_MANAGER_ID="9";

$JobTypeArray = Array("External"=>"External","Internal"=>"Internal");
$RequirementTypeArray = Array("Hire"=>"Hire","Contract"=>"Contract");
$JobStatusArray = Array("Waiting"=>"Waiting",
						"Live"=>"Live",
						"Pending for Approved"=>"Pending for Approved",
						"Approved By HOD"=>"Approved By HOD",
						"Approved by HR"=>"Approved by HR",
						"Interviews"=>"Interviews",
						"closed"=>"closed");
$DEFAULT_JOBSTATUS = "Waiting";
$START_YEAR=date("Y");
$END_YEAR = $START_YEAR+10;
$MonthArr = Array("01"=>"January","02"=>"February","03"=>"March","04"=>"April",
					"05"=>"May","06"=>"June","07"=>"July","08"=>"August",
					"09"=>"September","10"=>"October","11"=>"November",
					"12"=>"December");
$VisaTypeArray=Array("H1"=>"H1","H2"=>"H2");

$DegreeTypeArray =Array("Diploma"=>"Diploma", "Degree"=>"Degree", "PG"=>"Post Graduate","Doctorate"=>"Doctorate");
$Designation_Array = Array("Developer"=>"Developer","Designer"=>"Designer","Web Developer"=>"Web Developer","Programmer"=>"Programmer","Project Leader"=>"Project Leader","Team Leader"=>"Team Leader");


$CommunicationTypeArray = Array('Discussion','Email','Interview','Personal Contact','Phone Call');
$EntityTypeArray = Array('Job', 'Resume', 'Interview');
$YesNoArray = Array('Yes','No');
$OpenCloseArray = Array('Open','Close');

$ReferralStatusArr = Array("Request-Received"=>"Request-Received",
					"Email-Sent"=>"Email-Sent",
					"Application-Received"=>"Application-Received",
					"Interview"=>"Interview",
					"Selected"=>"Selected",
					"Approved"=>"Approved");

$ReportModuleTitle = "Interview Assessment Sheet";
$DEFAULT_RATING_OPTIONS=5;
$frequency_array=array('Daily'=>'Daily',
						'Weekly'=>'Weekly',
						'Monthly'=>'Monthly');

$Search_Result_Criteria = Array(
								"Industry"=>"0",
								"Business Area"=>"1",
								"Experience"=>"2",
								/*"Technology"=>"3",
								"Programming Language"=>"4",
								"Database"=>"5",
								"Operating System"=>"6",*//**AV Comment**/
								"Qualification"=>"7",
								"Skill"=>"8");
$InterviewTypeArray = Array("phone"=>"phone","personal"=>"personal","other"=>"other");

/** DO NOT CHANGE THE KEY OF STATUS ARRAY */
$InterviewStatusArray = Array("WAIT"=>"Waiting",
						"WFIC"=>"Waiting for Interviewer confirmation",
						"CBI"=>"Confirmed by Interviewer",
						"CBC"=>"Interview Schedule Confirmed By Candidate",
						"FBI"=>"Feedback by Interviewer",
						"FBC"=>"Feedback by candidate",
						"FBHR"=>"Feedback by HR waiting for Candidate confirmation",
						"SIC"=>"Schedule Interview by Candidate",
						"SICF"=>"Schedule Interview by Confirmed",
						"SICP"=>"Schedule Interview by Completed");
/** Please do not remove below Mail IDs*/
$JOBMAILID=1;
$REFERRAL_MAIL_ID=2;
$INTERVIEW_MAIL_ID=3;
$INTERVIEW_CONFIRMATION_MAIL_ID=4;
$CONFIRM_INTERVIEW_MAIL_ID=5;
$EMPLOYEE_REFERRAL_DETAILS_ID=6;
$HRMANAGER_REFERRAL_DETAILS_ID=7;
$REFERRED_DETAILS_ID=8;
$SUBMIT_RESUME_ID=9;
$WindowH=200;
$WindowW=200;
$WindowT=10;
$WindowL=100;

$COMPANY_NOTIFY_EMAIL_ID=10;

/** Please do not remove below Mail IDs*/
$weekDayArray=Array("Sunday"=>"Sunday",
					"Monday"=>"Monday",
					"Tuesday"=>"Tuesday",
					"Wednesday"=>"Wednesday",
					"Thursday"=>"Thursday",
					"Friday"=>"Friday",
					"Saturday"=>"Saturday");
$gradeArray=Array("01"=>"01",
					"02"=>"02",
					"03"=>"03",
					"04"=>"04",
					"05"=>"05",
					"06"=>"06",
					"07"=>"07",
					"08"=>"08",
					"09"=>"09",
					"10"=>"10");
$ResumeUploadDir = "resume/";
$font_name=array("Arial","Helvetica","Sans-Serif","Tahoma","Verdana");
$start_font_size = 7;
$end_font_size=14;
$font_weight = array("Normal","Bold","Bolder","Lighter");
$text_decoration=array("None","Underline","Overline","line-through");
$arrRolesAbrivation=array("Super Admin"=>"SA","Company Admin"=>"CA","Department Head"=>"DH","Team Leader"=>"TL","Employee"=>"E","Recuriter"=>"R","HR Manager"=>"HM","Interviewer"=>"I","Client Manager"=>"CM");
$resume_size=2*1024*1024;
$arrEmailRole=array($SUPER_ADMIN_ROLE_ID=>"Administrators",$HR_MANAGER_ID=>"Hr_managers",$RECRUITER_ID=>"Recruiters",$INTERVIEWER_ID=>"Interviewers","Candidates"=>"Candidates");
$MAX_EMAIL_PER_EXECUTION=10;
$MAX_DOMAIN_EMAIL_ALLOWED=3;
$arrCandidateApplicationStatus=array('Open'=>'Open','Close'=>'Close','Hold'=>'Hold','Selected'=>'Selected','Rejected'=>'Not Selected');
$arrDefaultResumeFormat=array("EE68NJKFF72QV524YFJU"=>"Australia","AK9SQTB248TXG73Q73D5"=>"France","MYHNTRY6U5FF8GR3HAVF"=>"India","D4BGRZCVPF4UWUMZEYPD"=>"Ireland","YYFJRKHGZT6AVUZR9WQE"=>"Netherlands","GM6EK7ZGSR6JF7GM6QNG"=>"Singapore","2HQZ2URZ3C2E9AE87YY3"=>"UK","SRW7P97B75EKS5SE27EM"=>"USA Canada");
$arrResymeParsing=array("Yes"=>"Yes","No"=>"No");
/** array for displaying no of qualification **/
$NoOfQualification=1;
$NoOfExperince=1;
$tempResumeUploadDir = "temp_resume/";
$templateVaraibleList=array('Template_1'=>array("[NAME]"=>"Name Of Email Reciepant","[JOB_CONTENT]"=>"Full Job Detail","[CHANGE_STATUS_LINK]"=>"Change Status Link","[APPWEBMASTER]"=>"Application WebMaster Name","[JOB_STATUS_HISTORY]"=>"Job Status History"),
														'Template_3'=>array("[NAME]"=>"Name Of Email Reciepant","[INTERVIEW_CONTENT]"=>"Full Inteview Detail","[APPWEBMASTER]"=>"Application WebMaster Name","[INTERVIEW_DETAIL_LINK]"=>"Link for Interview Detail"),
														'Template_4'=>array("[CANDIDATE_NAME]"=>"Candidate Name","[JOB_TITLE]"=>"Job Title","[DESIGNATION]"=>"Designation","[INTERVIEW_DATE]"=>"Interview Date","[INTERVIEW_TIME]"=>"Interview Time","[LOCATION]"=>"Location","[INTERVIEW_TYPE]"=>"Interview Type","[INSTRUCTION]"=>"Instruction","[OTHER_INFO]"=>"Other Information","[CONFIRM_INTERVIEW_LINK]"=>"Confirm Interview Link"),
														'Template_5'=>array("[CANDIDATE_NAME]"=>"Candidate Name","[JOB_TITLE]"=>"Job Title","[DESIGNATION]"=>"Designation","[INTERVIEW_DATE]"=>"Interview Date","[INTERVIEW_TIME]"=>"Interview Time","[LOCATION]"=>"Location","[INTERVIEW_TYPE]"=>"Interview Type","[INSTRUCTION]"=>"Instruction","[OTHER_INFO]"=>"Other Information","[CANDIDATE_CONFIRM_INTERVIEW_LINK]"=>"Confirm Interview Link at Candidate Side"),
														"Template_9"=>array("[NAME]"=>"Name Of Email Reciepant","[CONTENT]"=>"Job Detail","[CHANGE_STATUS_LINK]"=>"Change Status Link","[APPWEBMASTER]"=>"Application Web Master Name","[JOB_TITLE]"=>"Job Title","[SUBMIT_RESUME_LINK]"=>"Submit Resume Link"),
														"Template_10"=>array("{COMPANY_NAME}"=>"Company Name","{USER_NAME}"=>"User Name","{PASSWORD}"=>"Password"),
														"Template_25"=>array("[NAME]"=>"Name Of Email Reciepant","[JOB_CONTENT]"=>"Full Job Detail","[JOB_STATUS_HISTORY]"=>"Job Status History","[APPWEBMASTER]"=>"Application Web Master Name"),
														"Template_26"=>array("[NAME]"=>"Name Of Email Reciepant","[RESUME_CONTENT]"=>"Full Resume Detail","[APPWEBMASTER]"=>"Application Web Master Name"),
														"Template_27"=>array("[NAME]"=>"Name Of Email Reciepant","[JOB_CONTENT]"=>"Full Job Detail","[APPWEBMASTER]"=>"Application Web Master Name"),
														"Template_28"=>array("[NAME]"=>"Name Of Email Reciepant","[JOB_CONTENT]"=>"Full Job Detail","[INTERVIEW_DETAIL]"=>"Interview Detail","[UPDATED_STATUTS]"=>"Latest Applicant Status","[INTERVIEW_STATUS_HISTORY]"=>"Interview Status History","[APPWEBMASTER]"=>"Application Web Master Name"),
														"Template_29"=>array("[NAME]"=>"Name Of Email Reciepant","[JOB_CONTENT]"=>"Full Job Detail","[APPWEBMASTER]"=>"Application Web Master Name"),
														"Template_30"=>array("[NAME]"=>"Name Of Email Reciepant","[JOB_CONTENT]"=>"Full Job Detail","[APPWEBMASTER]"=>"Application Web Master Name"),
														"Template_31"=>array("[NAME]"=>"Name Of Email Reciepant","[JOB_CONTENT]"=>"Full Job Detail","[APPWEBMASTER]"=>"Application Web Master Name"),
														"Template_32"=>array("[NAME]"=>"Name Of Email Reciepant","[EMAIL]"=>"Candidate Email","[USERNAME]"=>"Username","[PASSWORD]"=>"Password","[APPWEBMASTER]"=>"Application Web Master Name"),
														"Template_33"=>array("[NAME]"=>"Name Of Email Reciepant","[JOB_CONTENT]"=>"Full Job Detail","[APPWEBMASTER]"=>"Application Web Master Name"),
														"Template_35"=>array("[NAME]"=>"Receipant Name","[EMAIL]"=>"Receipant Email","[JOB_TITLE]"=>"Job Title","[JOB_DETAIL_TELL_AFRIEND]"=>"Job Detail for a friend","[JOB_DETAIL_LINK]"=>"Job Detail Link","[SENDER_NAME]"=>"Sender Name","[SENDER_EMAIL]"=>"Sender Email Address")
														);
$EmailBlast_templateArray=array("{FirstName}"=>"Candidate First Name","{LastName}"=>"Candidate Last Name","{JobTitle}"=>"Job Title","{JobStatus}"=>"Job Status");
$dateFormat=array("Y-M-d","d-M-Y","M-d-Y","m-d-Y","d-m-Y");

$arrResumeMatched=array("flagExpertise"=>"gray",
									 "flagQualification"=>"green",
									 "flagExp"=>"blue");
/**Rating Varaible**/
$maxRatingPoint=5;
$RatingPointHint=" '','','','','' ";
$csvExportDir="CSVExport/";
$DemoMode=true;
$DemoEmail="jigar@indapoint.com";
$TemplateLable=array("Template_1"=>"Template used when new job is added or existing job is updated",
"Template_4"=>"Template used when interviewer send interview schedule to applicant",
"Template_3"=>"Template is used to send interview schedule to Interviewer",
"Template_5"=>"Template is used when applicatnt confirm the interiview schedule",
"Template_9"=>"Template is used when applicant register from publicpost page",
"Template_10"=>"Template is used to send company admin login detail when we create a new company",
"Template_25"=>"Template is used when user change the status of Job",
"Template_26"=>"Template is used when applicant is map with any job",
"Template_27"=>"Template is used when applicnat successfully apply the job.This is confirmation mail to applicant",
"Template_28"=>"Template is used when interview status changes",
"Template_29"=>"Template is used when candidate is selected for job",
"Template_30"=>"Template is used when candidate is not selected for job",
"Template_31"=>"Template is used when candidate is on hold for job",
"Template_32"=>"Template is used to send login id and password detail to newly created candidate",
"Template_33"=>"Template is used to send a job detail to recruiter when we map new recruiter to job",
"Template_35"=>"Template is used to send a job detail to Friend"
);
$RecruiterModule=true;
$LiveJobStatusID=2;
$EntityType=array("job"=>"Job","candidate"=>"Candidate");
$ExtraFieldType=Array("text"=>"text","textarea"=>"textarea","number"=>"number","currency"=>"currency","date"=>"date","list"=>"list","yesno"=>"yesno");
$EntityTypeValidation=array("mandatory"=>"mandatory");
/** CVS Upload Directory used for resume Import Module**/
$CSVResumeUpload="cvsresumeupload/";
$CSVDupCandidateArray=array("skip"=>"Skip","overwrite"=>"Overwrite");
$CSVUploadEncodingSupport=array('UTF-8'=>"UTF-8",'Big5'=>"Big5",'EUC-JP'=>"EUC-JP",'EUC-KR'=>"EUC-KR",'GB2312'=>"GB2312",'ISO-2022-JP'=>"ISO-2022-JP",'ISO-8859-1'=>"ISO-8859-1",'KOI8-R'=>"KOI8-R",'Shift_JIS'=>"Shift_JIS",'US-ASCII'=>"US-ASCII",'WINDOWS-1251'=>"WINDOWS-1251");
$tmpCSVResumeUpload="tmpCSVResumeUpload/";

$CvsMappingBasicArray=array("FirstName"=>"First Name","LastName"=>"Last Name","introduction"=>"Introduction","Address1"=>"Address1","Address2"=>"Address2","Current_city"=>"City","Current_state"=>"State","Current_country"=>"Country","Date_of_birth"=>"Date Of Birth","Gender"=>"Gender","Expertise_area"=>"Skill Set","Experience_years"=>"Experience","industryID"=>"Industry","BusinessAreaID"=>"Functional area","Email"=>"Email","Phone"=>"Phone","Mobile"=>"Mobile","Current_company"=>"Current employer","Current_designation"=>"Current Designation","current_location"=>"Current Location","prefered_location"=>"Prefered Location","Expected_salary"=>"Expected Salary","Current_salary"=>"Current Salary","ResumeFile"=>"Resume File Name");

$CvsMappingEducationArray=array("Qualification1"=>"Qualification 1","PassingYear1"=>"Passing Year 1","Institute1"=>"Institute 1","Degree1"=>"Degree 1","Specialization1"=>"Specialization 1","Grade1"=>"Grade 1",
"Qualification2"=>"Qualification 2","PassingYear2"=>"Passing Year 2","Institute2"=>"Institute 2","Degree2"=>"Degree 2","Specialization2"=>"Specialization 2","Grade2"=>"Grade 2",
"Qualification3"=>"Qualification 3","PassingYear3"=>"Passing Year 3","Institute3"=>"Institute 3","Degree3"=>"Degree 3","Specialization3"=>"Specialization 3","Grade3"=>"Grade 3",);

$CvsMappingExperienceArray=array("CompanyName1"=>"Company Name 1","FromYear1"=>"From Year 1","ToYear1"=>"To Year 1","Designation1"=>"Designation 1","JobProfile1"=>"JobProfile 1",
"CompanyName2"=>"Company Name 2","FromYear2"=>"From Year 2","ToYear2"=>"To Year 2","Designation2"=>"Designation 2","JobProfile2"=>"JobProfile 2",
"CompanyName3"=>"Company Name 3","FromYear3"=>"From Year 3","ToYear3"=>"To Year 3","Designation3"=>"Designation 3","JobProfile3"=>"JobProfile 3",);

$CVSMappingProjectArray=array("ProjectTitle1"=>"Title 1" ,"ProjectDuration1"=>"Duration(In Months) 1","ProjectDesc1"=>"Project Description 1",
"ProjectTitle2"=>"Title 2" ,"ProjectDuration2"=>"Duration(In Months) 2","ProjectDesc2"=>"Project Description 2",
"ProjectTitle3"=>"Title 3" ,"ProjectDuration3"=>"Duration(In Months) 3","ProjectDesc3"=>"Project Description 3");

$CVSMappingMandatory=array("FirstName"=>"FirstName","LastName"=>"LastName","Email"=>"Email","Mobile"=>"Mobile","Experience_years"=>"Experience_years","Expertise_area"=>"Expertise_area");

$CSVSupoortedDate=array("m.d.y"=>"m.d.y","m-d-y"=>"m-d-y","m/d/y"=>"m/d/y","m d y"=>"m d y",
						"m.d.Y"=>"m.d.Y","m-d-Y"=>"m-d-Y","m/d/Y"=>"m/d/Y","m d Y"=>"m d Y","d.M.y"=>"d.M.y","d-M-y"=>"d-M-y","d/M/y"=>"d/M/y","d M y"=>"d M y",
						"d.M.Y"=>"d.M.Y","d-M-Y"=>"d-M-Y","d/M/Y"=>"d/M/Y","d M Y"=>"d M Y","d.F.Y"=>"d.F.Y","d-F-Y"=>"d-F-Y","d/F/Y"=>"d/F/Y","d F Y"=>"d F Y","d.F.y"=>"d.F.y","d-F-y"=>"d-F-y","d/F/y"=>"d/F/y","d F y"=>"d F y","M.d.y"=>"M.d.y","M-d-y"=>"M-d-y","M/d/y"=>"M/d/y","M d y"=>"M d y",
						"M.d.Y"=>"M.d.Y","M-d-Y"=>"M-d-Y","M/d/Y"=>"M/d/Y","M d Y"=>"M d Y",
						"F.d.Y"=>"F.d.Y","F-d-Y"=>"F-d-Y","F/d/Y"=>"F/d/Y","F d Y"=>"F d Y");

$CSVJobBasicArray=array("Jobtitle"=>"Job Title","Designation"=>"Designation","BriefDesc"=>"Brief Description","DetailDesc"=>"Detail Description","BusinessAreaID"=>"Functional Area","City"=>"City","State"=>"State","Country"=>"Country","contact_name"=>"Contact Name","contact_email"=>"Contact Email","contact_phone"=>"Phone No");
$CSVJobReqArray=array("qualificationID"=>"Qualification(s)","ReqType"=>"Requirement Type","MainSkill"=>"Main Skill","MinExp"=>"Minimum Experience","MaxExp"=>"Maximum Experience","OtherSkill"=>"Other Skill","StartDate"=>"Start Date","LastDate"=>"Last Date","JobOpening"=>"No of Opening");
$CVSMappingMandatory_Job=array("Jobtitle"=>"Jobtitle","BusinessAreaID"=>"BusinessAreaID"); // REMOVED "JobOpening"=>"JobOpening" AS PER SIR'S INSTRUCTION
/*----------------------------------------------------------------------------------------------------------------------------------------------------------*/
$arrTimeZoneGMT=array("-10:00"=>"Honolulu GMT -10",
							"-09:00"=>"Anchorage GMT -9",
							"-08:00"=>"San Francisco GMT -8",
							"-07:00"=>"Edmonton/Denver GMT -7",
							"-06:00"=>"Chicago/Mexico City/Santiago GMT -6",
							"-05:00"=>"New York/Bogota GMT -5",
							"-04:00"=>"Halifax/Puerto Rico/Caracas GMT -4",
							"-03:00"=>"Rio/Buenos Aires GMT -3",
							"+00:00"=>"London/Casablanca/Dakar GMT 0",
							"+01:00"=>"Stockholm/Geneva/Algiers/Lagos GMT +1",
							"+02:00"=>"Helsinki/Athens/Cairo/Capetown GMT +2",
							"+03:00"=>"Moscow/Riyadh/Nairobi GMT +3",
							"+04:30"=>"Kabul GMT +4.5",
							"+05:00"=>"Islamabad GMT +5",
							"+05:30"=>"New Delhi GMT +5.5",
							"+07:00"=>"Bankok/Jakarta GMT +7",
							"+08:00"=>"Beijing/Singapore/Perth GMT +8",
							"+09:00"=>"Tokyo/Seoul GMT +9",
							"+10:00"=>"Vladivostok/Sydney GMT +10",
							"+12:00"=>"Wellington GMT +12");
$Email_resorce_code="SOURCE_CODE_2";
$language_folder="language/";
$defalut_language_folder="english/";
$language_file_name="text.php";
$default_characterset="UTF-8";



/*============================================***************************========================================
									Below settings are used in new instance creation steps.
============================================***************************========================================*/
//define("SOURCE_DATABASE_KEY","eyJEQl9IT1NUIjoibG9jYWxob3N0IiwiREJfVVNFUiI6InJvb3QiLCJEQl9QQVNTIjoiQUtFcWlhWWR3dWdEM1MiLCJEQl9OQU1FIjoiYXBwbGl2aWVfaW5zdGFuY2VtYXN0ZXJzb3VyY2UifQ=="); //Source Database Details.
//define("SOURCE_INSTANCE_STRUCTURE_DIR","master_instance/"); // Directory name from where we require to copy files and directories to create new company instance.
//List of Directories which require full permission (777).
$ListOfDirForFullPermission=array("ErrorLogs","Logs","pdfreports","resume","resumes","temp_resume","tmpCSVResumeUpload","images/companylogo","resumes/download_resumes","resumes/upload_resumes");
//Configuration Variables.
$REPLACE_VARS=array("DB_VARS"=>array("{HOST_SERVER}","{DB_HOST}","{DB_USERNAME}","{DB_PASSWORD}","{DB_NAME}"),"CONFIG_VARS"=>array("{INSTANCE_NAME}","{INSTANCE_EMAIL}"),"HTACCESS_VARS"=>array("{INSTANCE_NAME}"));
//List of Master tables which has initial data and requires to copy with data for new instance.
$masterTablesWithData=array("applicant_status","config_master","country","desgination_master","industry","JobPostStatusMaster","language","qualification_master","resume_source","site_config","static_pages","static_text","technology","TemplatesMaster");
define("LINUX_GROUP_NAME","apache"); //Specify group name to change group permission on copied Instance directory.
define("LINUX_USER_NAME","jigar");	//Specify User name to change group permission on copied Instance directory.
//define("CONFIG_FILE_DIR","/configs/");
//define("CONFIG_FILE_NAME","config.php"); //Configuration file location.
//define("DB_FILE_NAME","dbinfo.php");
define("HTACCESS_FILE_SRC","htaccess.txt");//.htaccess file name
define("HTACCESS_FILE_NAME",".htaccess");//.htaccess file name
define("DEFAULT_DB_HOST","localhost");
//define("DEFAULT_HOST_SERVER","192.168.0.100");
define("SITE_ADMIN_CP_INFO","eyJET01BSU4iOiJ3d3cuYXBwbGl2aWV3Lm5ldCIsIlVTRVIiOiJhcHBsaXZpZSIsIlBBU1MiOiJBcHBsaSRWaWV3MjAxMyMiLCJQT1JUIjoiMjA4MiJ9");
define("SITE_PREFIX","applivie_");
define("MYCLASS_FILE_NAME","myclass.php");
define("CRON_DIR","/cron/");
define("SYSTEM_MASTER_DB","applivie_customersystemdb");
$cronscriptArray=array("daily_crone.php"=>array("day"=>"*","hour"=>"0","minute"=>"0","month"=>"*","weekday"=>"*") ,//daily at Midnight
						"half_an_hour.php"=>
								array("day"=>"*","hour"=>"*","minute"=>"*/5","month"=>"*","weekday"=>"*"), // Schedule Reminder Cron Script - Every Five Minutes
						"hour_cron.php"=>
							array("day"=>"*","hour"=>"*","minute"=>"0","month"=>"*","weekday"=>"*"), //Every Hour
						"email_read.php"=>
							array("day"=>"*","hour"=>"0","minute"=>"0","month"=>"*","weekday"=>"*"), //Daily at midnight
						"parse_resume.php"=>
							array("day"=>"*","hour"=>"*","minute"=>"0,30","month"=>"*","weekday"=>"*"), //Every 30 minutes

						"read_resume_folder.php"=>
							array("day"=>"*","hour"=>"*","minute"=>"0","month"=>"*","weekday"=>"*") //Every Hour
					);
$supportEmail="jigar@indapoint.com";
define("EMAIL_FROM_NAME","AppliView - Sales");
define("EMAIL_FROM","sales@appliview.net");
define("EMAIL_SUBJECT","Welcome to AppliView - Innovating Recruitment");
/*===================================================End==========================================================*/
///
//To Set Demo request instatnce values
define("DEMO_PERIOD","7");
define('DEMO_USER_NAME',"demov2");
define('DEMO_CLIENT_USER_NAME',"clients@appliview.com");
define('DEMO_CANDIDATE_USER_NAME',"michael@yahoo.com");
$DemoUserArray=array("Adminfirstname"=>array("Demo","Lula","Cheryl","Jackie"),
	"Adminlastname"=>array("User","Thomas","Ansell","Smith"),
	"adminusername"=>array("demov2","lula123","cheryl123","Jackie"),
	"adminemail"=>array("demov2@appliview.com","lula@applicant-tracking-system.org","cheryl@applicant-tracking-system.org","jackie@applicant-tracking-system.org"),
	"roleid"=>array("1","7","6","8"),
	"roleName"=>array("Super Admin","HR Manager","Recruiter","Interviewer")
	);

define('DEMO_INSTANCE_URL',"http://goo.gl/v8KeB");
define('DEMO_ADMIN_SHORTURL','http://goo.gl/pWkG7');
define('DEMO_CANDIDATE_SHORTURL','http://goo.gl/kajWk');
define('DEMO_CLIENT_SHORTURL','http://goo.gl/kIaj9');



define('DEMO_FROM_NAME','AppliView-Sales');
define('DEMO_FROM_ADDRESS','sales@appliview.com');
define('DEMO_REQUEST_SUB','AppliView-Demo account');

$sessprefix="adminsess_";

define("SECURITY_CREDENTIALS","eyJhdXRodXNlciI6ImF1dG9pbnN0YW5jZSIsImF1dGhwYXNzIjoicm9ib2NvcDcxOTUifQ==");

$county = array("Bedfordshire","Berkshire","Buckinghamshire","Cambridgeshire","Cheshire","Cornwall","Cumbria","Derbyshire","Devon","Dorset","Durham","East Riding of Yorkshire","East Sussex","Essex","Gloucestershire","Greater Manchester","Hampshire","Herefordshire","Hertfordshire","Isle of Wight","Kent","Lancashire","Leicestershire","Lincolnshire","London","Merseyside","Norfolk","North Yorkshire","Northamptonshire","Northumberland","Nottinghamshire","Oxfordshire","Shropshire","Somerset","South Yorkshire","Staffordshire","Suffolk","Surrey","Tyne and Wear","Warwickshire","West Midlands","West Sussex","West Yorkshire","Wiltshire","Worcestershire","Rutland");
?>
