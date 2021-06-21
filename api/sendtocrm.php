<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
ignore_user_abort(true);
/* set to run for 30 mins if needed */
set_time_limit(1800);
require_once "../users/init.php";
include '../vendor/autoload.php';
include_once '../core/classpage.php';
include_once '../core/classaddcandidate.php';
include_once '../core/classapplyforjob.php';
require_once '../core/centreform.inc.php';
require_once "../core/functions.php";
require_once "classes/apifunctions.php";
require_once 'classes/classSendToCRM.php';
if(isset($file_us_root))
{
    require_once $file_us_root .'/pages/candidateexperience.php';
    require_once $file_us_root .'/pages/candidatequalification.php';
}else{
    require_once '../pages/candidateexperience.php';
    require_once '../pages/candidatequalification.php';
}
use api\classes\sendtoCRM;

/* instantiate the database */
$db = DB::getInstance();
$pg = new page();
if (isset($user) && $user->isLoggedIn()) {
  $pg->init();
  $loggedin=true;
  $settings=$pg->settings;
} else {
  $settings=new settings();
  $settings->usersettings();
}
$test=new sendtoCRM();
/**
 *  instantiate the template
 *  allowing for custom path - set sendtoCRM->usedefaultpath to false
*/
$template = gettemplate("../templates",true);
$jobid=isset($_REQUEST['jobid'])?$_REQUEST['jobid']:0;
$personid=isset($_REQUEST['personid'])?$_REQUEST['personid']:0;

if($jobid==0 || $personid==0)
{
  die("incorrect parameters");
}
  $apply=new applyforjob();
$answer1=$_POST['answer1'];
$answer2=$_POST['answer2'];
$surveyid= getformidforpage("employer questions");
$result=$apply->apply($jobid,$personid,$surveyid,$answer1,$answer2);

$jobtitle=$apply->getjobtitle($jobid);
$pg->setjobtitle($jobtitle);
sendemail($pg,"applyjobemail");
$test->sendCandidateDatatoCRM($personid,$jobid);
