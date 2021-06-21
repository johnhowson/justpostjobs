<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
	require_once '../users/init.php';
	ob_start();
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, HEAD');
	header('Access-Control-Max-Age: 1000');

	/* 
	OLD JUSTAPPLY CODE
	$handle=fopen("requestlog_".date("Y-m-d-h-i-s").".txt","w+");
	fwrite($handle,print_r($_REQUEST,1));
	fclose($handle);
	require_once('../configs/config.php');
	require_once("../configs/dbinfo.php");
	require_once($CompanyPath."cron/myclass.php");
	$obj=new myclass($_USERNAME,$_PASS,$_HOST,$_DATABASE);
	$cmpSQL="select * from company LIMIT 0,1";
	$cmpDATA=$obj->select($cmpSQL);
	$CurrentCompanyTimeZone = $cmpDATA[0]["timezone"];
	*/
$AppAbsolutePath = $_SERVER['DOCUMENT_ROOT'] ."/";
	require_once '../core/classaddjob.php';
	require_once '../core/classcandidate.php';
	require_once '../core/classsuperadmin.php';
	include_once '../core/functions.php';
		$db = DB::getInstance();
	$settings=new settings();
	$page=new page();
	$settings->usersettings();
	$SystemURL=$_SERVER['HTTP_HOST'];
	require_once "config/social-config.php";
	require_once "config.php";
	require_once "config/site_config.php";
	//require_once($AppAbsolutePath."configs/dbinfo.php");
	require ("classes/facebook_new.php");
	require ("../v2/classes/twitteroauth.php");
	require ("../v2/classes/simplelinkedin.class.php");
	require ("classes/class_nasposting.php");
	require ("classes/apifunctions.php");

	require_once "classes/forminputvalidator.cls.php";
	require_once 'classes/class.webservice.php';
	//require_once 'api/classes/class.dbclass.php';
	require_once 'classes/constants.php';
	use api\classes;
	//$databasearray=array("DB_HOST"=>$_MASTER_HOST,"DB_USER"=>$_MASTER_USERNAME,"DB_PASS"=>$_MASTER_PASS,"DB_NAME"=>$_MASTER_DATABASE);
	//$dbkey=base64_encode(json_encode($databasearray));
	//define("SOURCE_DATABASE_KEY",$dbkey);
	$RequirementTypeArray =getRequirementType();
	$workingHours=array("Full Time"=>"Full Time","Part Time"=>"Part Time");
	switch(urlencode($page)){
		case 'iconnect':
				require_once 'webservice.php';
				$flag = 1;
		break;
	}
	if ($flag == 0){
		exit;
	}
