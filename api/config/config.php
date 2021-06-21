<?php
if(isset($_SERVER['SCRIPT_NAME'])){
	$app_main_dir = rtrim(dirname($_SERVER['SCRIPT_NAME']),'/\\');
	define('_APP_MAIN_DIR', $app_main_dir);
}else{
	die('[config.php] Cannot determine APP_MAIN_DIR, please set manual and comment this line');
}
if(isset($_SERVER['HTTP_HOST']) && isset($_SERVER['SERVER_PORT'])){
	if($_SERVER['SERVER_PORT'] == 80){
		define ('BASE_URL', 'https://' . $_SERVER['HTTP_HOST'] . _APP_MAIN_DIR . '/');
	}else{
		define ('BASE_URL', 'https://' . $_SERVER['HTTP_HOST'].':'.$_SERVER['SERVER_PORT'] . _APP_MAIN_DIR . '/');
	}
}else{
	die('[config.php] Cannot determine BASE_URL, please set manual and comment this line');
}
$newUrl = str_replace('/', '\/', _APP_MAIN_DIR);
$pattern = '/'.$newUrl.'/';
$_url = preg_replace($pattern, '', $_SERVER['REQUEST_URI'], 1);
$_tmp = explode('?', $_url);
$_url = $_tmp[0];
if ($_url = explode('/', $_url)){
	foreach ($_url as $tag){
		if ($tag){
			$_app_info['params'][] = $tag;
		}
	}
}
$page = (isset($_app_info['params'][0]) ? $_app_info['params'][0] : '');
$id = (isset($_app_info['params'][1]) ? $_app_info['params'][1] : 0);
$flag = 0;
$user_agent = $_SERVER['HTTP_USER_AGENT'];
if(!isset($_SERVER['HTTP_REFERER'])) {
	$_SERVER['HTTP_REFERER'] = '';
}
define("SOURCE_DATABASE_KEY","eyJEQl9IT1NUIjoianVzdGFwcGx5LmMyNGZ2NTFkeXA4bi5ldS13ZXN0LTEucmRzLmFtYXpvbmF3cy5jb20iLCJEQl9VU0VSIjoicm9vdCIsIkRCX1BBU1MiOiJKdXNUYXBwbHkyMDE0JCMiLCJEQl9OQU1FIjoic3lzdGVtZGIifQ==");
$RolesArray = array("1"=>"Super Admin",
					/*"2"=>"Company Admin",
					"3"=>"Department Head",
					"4"=>"Team Leader",*/ /**AV Comment**/
					"5"=>"Employee",
					"6"=>"Recruiter",
					"7"=>"HR Manger",
					"8"=>"Interviewer",
					"9"=>"Candidates",
					"10"=>"Client");
$RolesArrayCustomerSide = array("6"=>"Recruiter","7"=>"HR Manger","8"=>"Interviewer","9"=>"Candidates","10"=>"Client");

$SystemAdminRoles = array("1"=>"Super Admin",
					"7"=>"Client HR-Manager",
					"6"=>"Client Recruiter");
define("SYSTEM_SUPER_ADMIN_ROLE_ID","1");
define("SYSTEM_CLIENT_MANAGER_ID","7");
define("SYSTEM_CLIENT_RECRUITER_ID","6");
/** USED IN JOB POST & SENDING MAIL AFTER POSTING JOB */
define("SUPER_ADMIN_ROLE_ID","1");
define("COMPANY_ADMIN_ROLE_ID","2");
define("DEPT_ADMIN_ROLE_ID","3");
define("TEAM_LEAD_ROLE_ID","4");
define("COORDINATOR_ID","5");
define("RECRUITER_ID","6");
define("HR_MANAGER_ID","7");
define("INTERVIEWER_ID","8");
define("CLIENT_MANAGER_ID","9");
define("CLIENT_ID","10");
$cfg["dir"]["config"]="configs/";
$cfg["dir"]["cron"]="cron/";
define("READ_RESUME_FOLDER","read_resume_folder.php");
define("PARSE_RESUME","parse_resume.php");
define("SITE_CONFIG_FILE_NAME","site_config.php");
define("CONFIG_FILE_NAME","config.php");
$InstanceDirectoryRootPath=$AppAbsolutePath;
?>
