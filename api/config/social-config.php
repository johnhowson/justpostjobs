<?php
/* Facebook Application Credentials*/
/* Product App */
/*$app_name       =  "JustApply";
$AppId			=	'1496588063907450';
$secret       	= 'ab4d83c069f6bff8a824603c07315fbf';*/
/*Test App*/
$app_name       =  "JustApply";
$AppId			=	'1867925230099669';
$secret       	= 'c9d5c3aabdf4e7478c0ecf68c4ca7ba1';


/*Twitter Application Credentials*/
//$cosnumerkey="cK5Vxsr2htb76ovKYAJbjg";
//$twitter_secret="axeOPs2JMzGRpnjS3mVmaDmcLsdUrko2opZWjqTEJY8";

$cosnumerkey="tDOuJeYPaeqTOfnd8aBX5XsB0";
$twitter_secret="4EoYWW7oVm7ydXx1RR63uI4t6lVa3GLye4TcEiCco5olBhojuR";


/*Linkedin Application Credentials*/
/*$linkein_ApiKey = "7521lpos573a9i";
$linkein_secret = "y6WYyXFxMuMbterO";*/
$linkein_ApiKey = "75qy7l9icyh9lx";
$linkein_secret = "W76O3ptyS0xjs32H";

//$linkein_redirect_uri="index.php?mod=manager&op=linkedinauthorization&layout=download";
$linkein_redirect_uri="linkedinreturn.php";


define("PIPL_SEARCH_API_KEY","7wsmcrhkwwje8dukr8gb3nyc");
define("PIPL_SEARCH_URL","http://apis.pipl.com/search/v2/");

define("GOOGLE_API_KEY","AIzaSyDf17zgdphCU8OUwoVZkLx02LxnDCtV384");
define("GOOGLE_ENDPOINT","https://www.googleapis.com/urlshortener/v1");
define("TRIAL_PERIOD","30");
//Insert all activity in master database for table access_activity
$SupportNumber="+44-7989402130";
$SupportEmail="support@justapply.co.uk";

$sandbox=false;
$api_username = $sandbox ? 'nilphpdeveloperbusiness_api1.gmail.com' : 'sales_api1.appliview.com';
$api_password = $sandbox ? '1365671736' : 'HM7FNTQLGV72EBYN';
$api_signature = $sandbox ? 'ArAHMJ-37Wb7mMlLjQoSyGURTJMMAYxYE7NlWh2m1LwUMxERgK.1HFxu' : 'AFcWxV21C7fd0v3bYYYRCpSSRl31Amd2DzA6-PTkrdFXrRC6CAAxVm6f';

define("CANCEL_REGISTRATION_EMAIL","sales@appliview.com");
/* Google Calendar Application Credentials*/
define("GOOGLE_CALANCER_APP_NAME","Google Calendar PHP Starter Application");
define("GOOGLE_APPLICANT_APP_NAME","Google UserInfo PHP Starter Application");
define("GOGLE_CLIENT_ID",'767874959980-8cbj187kjbb9t42jo562q67gnmt28t4b.apps.googleusercontent.com');
define("GOGLE_SECRET_TEXT",'Nv0cyjimSdXwJNrJbPWFXmdO');
define("GOGLE_DEVELOPER_KEY",'AIzaSyDOsznb4JkMOw9JMQPv1HTG-2K3vIQWCvs');
define("BASE_API_URL",$SystemURL."/api/iconnect");
define("INSTALLER_DIR","appinstaller/");
define("INSTALLER_FILENAME","AppliView-Simple Resumes Uploader.exe");
define("INSTANCE_UPLOADER","instanceuploader/");
$configfileText="[config]
CompanyID={companyId}
User={user}
Password={password}
FileType={filetype}
FTPServer={ftpserver}
FTPUser={ftpuser}
FTPPassword={ftppass}
FTPBaseDir={resume_directory}
BaseApiUrl={baseAPIURL}";


define("INSTALLER_APP_CONFIG_FILENAME","config.dta");
define("APP_ALLOWED_FILETYPE","pdf,doc,docx,txt");

//2checkout API
define("APIUSER","ind_developer");
define("APIPASS","API_ind2013#");

//google captcha key
define("GOOGLE_PUBLIC_KEY","6Lds1EwUAAAAAAOiio4p_G-LOFMxobQSMQPAP-ic");
define("GOOGLE_PRIVATE_KEY","6Lds1EwUAAAAAH6BENpJPLjAY1mZZlD72uR9B78Y");


/* AS RChilli API Changed we require to make changes in Webservice URL. Moved config from instance config.php to social-config as this gets included in all instances */
$webserviceUrl="http://appliview1.rchilli.com/RChilliParser/services/RChilliParser?wsdl";////WEb Service URL for Resume Parsing
$webServiceVersion="5.0.0"; //Version of API
$layoutiframe='layout_iframesite.php';

$NASErrorCodes=array(
	'-1'=>'Unknown System Error',
	'-2'=>'SuppliedDataInvalid',
	'-10001'=>'"WorkingWeek" must be 100 characters or less',
	'-10002'=>'"WorkingWeek" is mandatory',
	'-10003'=>'"WeeklyWage" should be atleast �40',
	'-10004'=>'"WeeklyWage" is mandatory',
	'-10005'=>'"VacancyLocationType" is mandatory',
	'-10006'=>'"Title" must be 200 characters or less',
	'-10007'=>'"Title" is mandatory',
	'-10008'=>'"PossibleStartDate" is mandatory',
	'-10009'=>'"ShortDescription" must be 512 characters or less',
	'-10010'=>'"ShortDescription" is mandatory',
	'-10011'=>'"NumberOfPositions" is mandatory for standard vancancies',
	'-10012'=>'"LearningProviderEdsUrn" is mandatory',
	'-10013'=>'"InterviewStartDate" is mandatory',
	'-10014'=>'"LongDescription" is mandatory',
	'-10015'=>'"ApprenticeshipFramework" is mandatory',
	'-10016'=>'"ApprenticeshipFramework" must be 3 characters',
	'-10017'=>'"EmployerWebsite" must be 512 characters or less',
	'-10018'=>'"EmployerExternalApplicationWebsite" must be 512 characters or less',
	'-10019'=>'"EmployerEdsUrn" is mandatory',
	'-10020'=>'"EmployerDescription" must be 8000 characters or less',
	'-10021'=>'"EmployerAnonymousName" must be 510 characters or less',
	'-10022'=>'"ContactName" must be 200 characters or less',
	'-10023'=>'"ClosingDate" is mandatory',
	'-10024'=>'"ApplicationInstructions" must be 8000 characters or less',
	'-10025'=>'"NumberOfVacancies" is mandatory for multi-site vancancies',
	'-10026'=>'"ClosingDate" is Invalid',
	'-10027'=>'"EmployerImage" size must be less than 10K',
	'-10028'=>'"LongDescription" must be 2147483648 characters or less',
	'-10029'=>'"InterviewStartDate" is Invalid',
	'-10030'=>'"PossibleStartDate" is Invalid',
	'-10031'=>'"EmployerExternalApplicationWebsite " in mandatory for offline vancancies',
	'-10032'=>'"EmployerDescription" is mandatory for anonymous employer',
	'-10033'=>'Learning Provider is not authorised for this vacancy',
	'-10034'=>'Vacancy reference number already exists',
	'-10035'=>'Invalid relationship for training provider and employer',
	'-10036'=>'"ApprenticeshipFramework" is invalid',
	'-10037'=>'"County" for standard location is invalid',
	'-10038'=>'"County" for multiple location is invalid',
	'-10039'=>'"County" for standard location is mandatory',
	'-10040'=>'"County" for multiple location is mandatory',
	'-10041'=>'"AddressLine1" for standard location is mandatory',
	'-10042'=>'"AddressLine1" for multiple location is mandatory',
	'-10043'=>'"EmployerImage" is not valid.',
	'-10044'=>'Entered Training Provider and Employer cannot have national vacancy',
	'-10045'=>'"EmployerEdsUrn" is invalid',
	'-10046'=>'"LearningProviderEdsUrn" is invalid',
	'-10047'=>'"PostCode" is mandatory for standard vancancies',
	'-10048'=>'"PostCode" is mandatory for multisite vancancies',
	'-10049'=>'"PostCode" is invalid for standard vancancy',
	'-10050'=>'"PostCode" is invalid for multisite vancancy',
	'-10051'=>'Unsupported HTML data format',
	'-10052'=>'"DisplayRecrutmentAgency" is mandatory',
	'-10053'=>'"SmallEmployerWageIncentive" is mandatory',
	'-10054'=>'"DeliveryOrganisation" does not exist',
	'-10055'=>'"VacancyManager" does not exist',
	'-10056'=>'"ContractOwner" is not authorised for this vacancy',
	'-10057'=>'"VacancyManager" is not authorised for this vacancy',
	'-10058'=>'"DeliveryOrganisation" is not authorised for this vacancy',
	'-10059'=>'"ContractOwnerUKPRN" is mandatory',
	'-10060'=>'"DeliveryProviderEdsUrn" is mandatory',
	'-10061'=>'"VacancyManagerEdsUrn" is mandatory',
	'-10062'=>'"VacancyOwnerEdsUrn" is mandatory',
	'-10063'=>'"LocalAuthority" does not exist',
	'-10064'=>'"Address" is mandatory',
	'-10065'=>'"SiteVacancyDetails" is mandatory',
	'-10066'=>'WageType" is invalid',
	'-20001'=>'Unknown Vacancy Reference',
	'-20002'=>'InvalidVacancyReference',
	'-20003'=>'Invalid Update Value',
	'-20004'=>'You cannot record this number of candidates as successful as the total number of successes is greater than the number of vacancies available for this advert. Either the number of successful candidates reported is incorrect or the number of vacancies for this advert needs to be increased.',
	'-20005'=>'Updates Not Allowed'
);
/*UJM Vacancy Type*/
$ujm_vacancy_types=array("1"=>"Permanent (Employee)","2"=>"Temporary / Contract","3"=>"Placement (Student)","4"=>"Apprenticeship");
/*NGTU Vacancy Type*/
$ngtu_vacancy_types=array("College Courses","College Courses->Level 3","College Courses->Level 2","College Courses->Level 1","College Courses->Entry Level","College Higher Education","College Higher Education->Level 7+","College Higher Education->Level 6","College Higher Education->Level 5","College Higher Education->Level 4","Sponsored Degrees","Sponsored Degrees->Level 5","Sponsored Degrees->Level 6","Sponsored Degrees->Level 7","Traineeships","Gap Year","Gap Year->Volunteer","Employment & Training","Employment & Training->Level 1","Employment & Training->Level 3","Employment & Training->Level 2","Distance Learning","Distance Learning->Level 1","Distance Learning->Level 3","Distance Learning->Level 4","Distance Learning->Level 5","Distance Learning->Level 2","Distance Learning->Level 6","Distance Learning->Level 7","Jobs","Jobs->School Leaver","Jobs->Starting Your Own Business","Jobs->A Level Training Scheme","Jobs->Employability Skills","Jobs->Full Time","Jobs->Part Time","Alternative Courses/Degrees","Work Experience","Apprenticeships","Apprenticeships->Higher Apprenticeship","Apprenticeships->Intermediate Apprenticeship","Apprenticeships->Advanced Apprenticeship");


$NASFrameWorkList=array(
	'454'=>'Accounting',
	'460'=>'Activity Leadership',
	'627'=>'Advanced Diagnostics and Management Principles',
	'517'=>'Advanced Engineering Construction',
	'550'=>'Advanced Manufacturing Engineering',
	'586'=>'Advertising & Marketing Communications',
	'528'=>'Agriculture',
	'439'=>'Animal Care',
	'595'=>'Animal Technology',
	'405'=>'Aviation Ops on the Ground',
	'590'=>'Banking',
	'507'=>'Barbering',
	'422'=>'Beauty Therapy',
	'625'=>'Blacksmithing',
	'452'=>'Bookkeeping',
	'610'=>'Broadcast Production',
	'605'=>'Broadcasting Technology',
	'554'=>'Buliding Energy Maintenance Systems',
	'559'=>'Building Product Industry Occupations',
	'543'=>'Building Services Eng Tech',
	'431'=>'Bus and Coach',
	'490'=>'Business & Administration',
	'620'=>'Business & Professional Administration',
	'581'=>'Business, Innovation and Growth',
	'432'=>'Cabin Crew',
	'556'=>'Campaigning',
	'584'=>'Care Leadership and Management',
	'582'=>'Catering and Professional Chefs',
	'469'=>'Ceramics Manufacturing',
	'445'=>'Children\'s and Young peoples wforce',
	'498'=>'Cleaning and Environmental Services',
	'561'=>'Coaching',
	'546'=>'Combined Manufacturing',
	'415'=>'Commercial Moving',
	'492'=>'Community Arts',
	'617'=>'Community Safety',
	'596'=>'Composite Engineerint',
	'522'=>'Construction Building',
	'520'=>'Construction Civil Engineering',
	'612'=>'Highers in Construction Management',
	'519'=>'Construction Specialist',
	'521'=>'Construction Technical',
	'587'=>'Consumer Electrical and Electronic Products',
	'489'=>'Contact Centre Operations',
	'619'=>'Contact Centre Operations Management',
	'493'=>'Costume and Wardrobe',
	'406'=>'Courts',
	'621'=>'Craft and Technical Roles in Film and Television',
	'624'=>'Creative Craft Practitioner',
	'449'=>'Creative and Digital Media',
	'614'=>'Criminal Investigation',
	'495'=>'Cultural and Heritage Venue Operations',
	'410'=>'Custodial Care',
	'488'=>'Customer Service',
	'497'=>'Design',
	'636'=>'Digital Learning Design',
	'516'=>'Domestic Heating',
	'441'=>'Driving Goods Vehicles',
	'513'=>'Electrotechnical',
	'409'=>'Emergency Fire Service ops',
	'536'=>'Employment Related Services',
	'588'=>'Energy Assessment and Advice',
	'564'=>'Engineering Construction',
	'604'=>'Engineering Environmental Technologies',
	'539'=>'Engineering Manufacture',
	'552'=>'Enterprise',
	'524'=>'Environmental Conservation',
	'511'=>'Equine',
	'462'=>'Exercise and Fitness',
	'607'=>'Explosives, Storage & Maintenance',
	'577'=>'Express Logistics',
	'553'=>'Extractives',
	'501'=>'Facilities Management',
	'526'=>'Farriery',
	'423'=>'Fashion and Textiles',
	'585'=>'Fashion and Textiles: Technical',
	'530'=>'Fencing',
	'568'=>'Fish Husbandry and Fisheries Management',
	'523'=>'Floristry',
	'403'=>'Food and Drink',
	'557'=>'Fundraising',
	'578'=>'Funeral Operations and Services',
	'551'=>'Furniture, Furnishings and interiors',
	'635'=>'Furniture Manufacturing Technician',
	'438'=>'Game and Wildlife Management',
	'502'=>'Glass Industry',
	'508'=>'Hairdressing',
	'478'=>'Allied Health',
	'473'=>'Clinical Healthcare',
	'479'=>'Dental Nursing',
	'476'=>'Emergency Care',
	'474'=>'Healthcare Support Services',
	'475'=>'Maternity and Paediatric Support',
	'471'=>'Optical Retail',
	'470'=>'Pathology Support',
	'477'=>'Perioperative Support',
	'480'=>'Pharmacy Services',
	'602'=>'Health (Assistant Practitioner)',
	'444'=>'Health and Social Care',
	'567'=>'Health (Informatics)',
	'515'=>'Heating and Ventilation',
	'537'=>'HM Forces',
	'527'=>'Horticulture',
	'583'=>'Hospitality',
	'580'=>'Hospitality Management',
	'499'=>'Housing',
	'574'=>'Human Resource Management',
	'611'=>'Information Security',
	'504'=>'Improving Op Perf',
	'589'=>'Insurance',
	'603'=>'Intelligence Analysis',
	'637'=>'Intelligence Operations',
	'622'=>'Interactive Design and Development',
	'413'=>'Int Trade and Logistics',
	'419'=>'IT Applications Specialist',
	'418'=>'IT, Software, Web & Telecoms Professionals',
	'548'=>'Jewellery',
	'629'=>'Jewellery',
	'599'=>'Journalism',
	'506'=>'Lab and Science Tech',
	'525'=>'Landbased Engineering',
	'541'=>'Learning and Development',
	'592'=>'Learning Support',
	'601'=>'Legal Advice',
	'565'=>'Legal Services',
	'466'=>'Leisure Operations and Leisure Management',
	'450'=>'Libraries Records and IM Services',
	'404'=>'Licensed Hospitality',
	'563'=>'Life Scences & Chemical Sciences Professionals',
	'491'=>'Live Events and Promotion',
	'549'=>'Local Taxation and Benefits',
	'571'=>'Locksmithing',
	'442'=>'Logistics Operations',
	'411'=>'Mail Services',
	'487'=>'Management',
	'560'=>'Maritime Occupations',
	'486'=>'Marketing',
	'597'=>'Metal Processing and Allied Operations',
	'593'=>'Mineral Products Technology',
	'606'=>'Multi-skilled Vehicle Collision Repair',
	'496'=>'Music Business',
	'509'=>'Nail Services',
	'426'=>'Nuclear Working',
	'569'=>'Nursing Assistants in a Veterinary Environment',
	'562'=>'Operations and Quality Improvement',
	'464'=>'Outdoor Programmes',
	'430'=>'Passenger Carrying',
	'451'=>'Payroll',
	'448'=>'Photo Imaging',
	'456'=>'Playwork',
	'512'=>'Plumbing & Heating',
	'407'=>'Policing',
	'424'=>'Polymer Processing Operations',
	'600'=>'Power Engineering',
	'542'=>'Print and Printed Packaging',
	'425'=>'Process Manufacturing',
	'503'=>'Production of Coatings',
	'615'=>'Professional Aviation Pilot Practice',
	'591'=>'Professional Development for Work Based Learning Practitioners',
	'575'=>'Professional Services',
	'573'=>'Project Management',
	'500'=>'Property Services',
	'455'=>'Providing Financial Services',
	'453'=>'Providing Mortgage Advice',
	'440'=>'providing Security Services',
	'572'=>'Public Relations',
	'428'=>'Rail Engineeering (Track)',
	'545'=>'Rail Infrastructure',
	'429'=>'Rail services',
	'544'=>'Rail Traction',
	'594'=>'Recruitment',
	'514'=>'Refrig and Air Con',
	'443'=>'Retail',
	'608'=>'Higher in Retail Management Level 4',
	'485'=>'Sales and Telesales',
	'421'=>'Security Systems',
	'416'=>'Signmaking',
	'570'=>'Smart Meter Installations',
	'579'=>'Social Media and Digital Marketing',
	'609'=>'Sound Recording, Engineering and Studio Facilities',
	'510'=>'Spa Therapy',
	'458'=>'Spectator Safety',
	'465'=>'Sporting Excellence',
	'467'=>'Sports Development',
	'566'=>'Supply Chain Management',
	'613'=>'Supporting Teaching and Learning in Physical Education',
	'420'=>'Supporting Teaching and Learning',
	'532'=>'Surveying',
	'505'=>'Sustainable Resource Mgmt',
	'618'=>'Sustainable Resource Operations and Management',
	'639'=>'Tax',
	'494'=>'Technical Theatre',
	'446'=>'The Gas Industry',
	'427'=>'The Power Industry',
	'535'=>'The Water Industry',
	'616'=>'Trade Business Services',
	'412'=>'Traffic Office',
	'408'=>'Travel services',
	'531'=>'Trees & Timber',
	'434'=>'Vehicle Body and Paint',
	'437'=>'Vehicle Fitting',
	'436'=>'Vehicle Maintenance and Repair',
	'433'=>'Vehicle parts',
	'634'=>'Vehicle Restoration',
	'435'=>'Vehicle Sales',
	'529'=>'Vet Nursing',
	'558'=>'Volunteer Management',
	'414'=>'Warehousing and Storage',
	'538'=>'Witness Care',
	'576'=>'Wood & Timber Processing and Merchants Industry',
	'447'=>'Youth Work'
);
$NASFrameWorkType=array(
	'IntermediateLevelApprenticeship'	=>	'Intermediate Level Apprenticeship',
	'AdvancedLevelApprenticeship'		=>	'Advanced Level Apprenticeship',
	'HigherApprenticeship'				=>	'Higher Apprenticeship',
	"Unspecified"						=>	'Not Specified',
	"Traineeship"						=>	'Traineeship'
);
define("BRIEF_DESC_CHARACTERS","120");
$NGTU_NAS_MAPPING=array("IntermediateLevelApprenticeship"=>"Apprenticeships->Intermediate Apprenticeship","AdvancedLevelApprenticeship"=>"Apprenticeships->Advanced Apprenticeship","HigherApprenticeship"=>"Apprenticeships->Higher Apprenticeship");
define("CAREERMAP_API_END_POINT","https://careermap.co.uk/api/v1/");
define("GOOGLE_DISTANCE_MATRIX_API_URL","https://maps.googleapis.com/maps/api/distancematrix/json?");
//define("GOOGLE_DISTANCE_MATRIX_API_KEY","AIzaSyAlJjG54_oH1xz3Eujj5uskdKYEBwnrKPM");
define("GOOGLE_DISTANCE_MATRIX_API_KEY","AIzaSyBRNZoTjuZLiVSp7nVkQhqNX8AqSlpd-cQ");

$ChannelCCEmailAddresses=array(
	"NAS"		=>	"nasissues@justapply.co.uk",
	"NGTU"		=>	"ngtuissues@justapply.co.uk",
	"UJM"		=>	"ujmissues@justapply.co.uk",
	"CAREERMAP"	=>	"careermapissues@justapply.co.uk",
	"TOTALJOBS"	=>	"totaljobsissues@justapply.co.uk",
	"FACEBOOK"	=>	"facebookissues@justapply.co.uk",
	"TWITTER"	=>	"twitterissues@justapply.co.uk"
);
if($_SERVER['HTTP_HOST']=='192.168.0.100'){
    define("REED_CLIENT_ID","1418477");
    define("REED_API_TOKEN","4aca7714-61d6-4558-9667-8d1896ba8ca7");
}else{
    define("REED_CLIENT_ID","1190243");
    define("REED_API_TOKEN","a7863b6f-51af-400a-a11b-3469f635f5f1");
}
define("COOKIE_POLICY_URL","http://www.justapply.co.uk/cookie-policy.html");
define("PRIVACY_POLICE_URL","http://www.justapply.co.uk/privacy-policy.html");


$login_acees=array("my_account","job_list","job_list_a","fortune-finder","fortunefinder_a","view_cv","uploadresume","uploadresume_a","changepass","changepass_a","change_email","change_email_a","vacancy_withdraw","vacancy_withdraw_a","set_security_questions_answers");



?>
