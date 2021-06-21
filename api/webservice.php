<?php
	use api\classes\webservice;
$data=json_encode($_REQUEST);
$message="";
foreach ($_REQUEST as $key => $value) {
  $message.="key: $key value: $value \n";
}

$webserviceObj = new webservice;
$webserviceObj->saveLog($message);

$webserviceObj->processApi($id);
