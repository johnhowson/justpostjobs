<?php
namespace api\classes;
require_once("class_nasposting.php");
use api\classes\postVacancyToNAS;
/**
* a set of common tools for the api library
* @author John Howson
* @since 22/02/2021
*/
class apitools{

  /** registerInAPILog
  * add a log entry for api insert referenced by job id
  * @author John Howson
  * @since 22-0-2021
  * @param $jobid
  * @param $request
  * @param $response
  * @param $status
  **/
  public function registerInAPILog($JobID,$request,$response,$status){
      global $db;
    //$sql="update JobOrders set `careermap_request`='".escapeStr($request)."', `careermap_response`='".escapeStr($request)."' where JobID='".$JobID."'";
    $fields=array("jobid"=>$JobID,"apiname"=>"careermap","message"=>"request: " .$request,"response"=>$response,"reference"=>$status,"dateadded"=>date('Y-m-d H:i:s'));
    $table="ja_apiLog";
    $rs=$db->insert($table,$fields);
  }
  function postToNAS($jobid){
  	global $settings;
  	$p=new postVacancyToNAS($jobid);
  	$result=$p->publishToNAS();
  	if($result["Status"]=="Error"){
  		$result["Error"]=implode(",",$result["Error"]);
  	}
  	//$p->show("Result Returned from Function :".print_r($result,1));
  	return  $result;
  }

}
