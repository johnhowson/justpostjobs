<?php
function xml_escape($s,$mode='xml'){
	if($mode == 'xml'){
   		$s = html_entity_decode($s, ENT_QUOTES, 'UTF-8');
		$s = htmlspecialchars($s,ENT_XML1 | ENT_IGNORE,'UTF-8',false);
	}else{
		$htmltables=get_html_translation_table(HTML_ENTITIES, ENT_QUOTES);
		$htmltables[chr(128)]=chr(128);
		$htmltables[chr(157)]=chr(157);
		$htmltables[chr(125)]=chr(125);
		$searchval=array();
		$replaceval=array();
		$blankSpace=array(194,157);
		foreach($htmltables as $key=>$value){
			array_push($searchval,$key);
			$ascii=0;
			if(mb_detect_encoding($key)=='UTF-8'){
				$ascii=ordutf8($key,0);
			}else{
				$ascii=ord($key);
			}
			if(in_array($ascii,$blankSpace)){
				$key=' ';
			}
			$replaceString="";
			switch( $key ) {
				case '&':
					$replaceString="&";
				break;
				case "'":
					$replaceString=$key;
				break;
				case '"':
				case '<':
				case '>':
					$replaceString=htmlspecialchars( $key, ENT_QUOTES );
				break;
				default:
					$replaceString="&#".str_pad($ascii, 3, '0', STR_PAD_LEFT).";";
				break;
			}
			array_push($replaceval, $replaceString);
		}
		if(count($searchval)>0 && count($replaceval)>0){
			return str_replace($searchval,utf8_filter($replaceval),$s);
		}
	}
    $s=utf8_filter($s);
	return $s;
}
function utf8_filter($value){
    return preg_replace('/[^[:print:]\r\n]/u', '', mb_convert_encoding($value, 'UTF-8', 'UTF-8'));
}
function getRequirementType(){
	global $db;
	$retarr=array();
	$sql="select  VacancyTypeID, VacancyTypeTitle from ja_vacancyType where status='Active'";
	$rs=$db->query($sql);
	if($rs->count()>0)
	{
		$results=$rs->results();
		foreach ($results as $item) {
			$retarr[$item->VacancyTypeTitle]=$item->VacancyTypeTitle;
		}
	}
	return $retarr;
}
function getERN_Number($companyid)
{
	global $db;
	$sql="select ERN_Number from ja_company where companyid='$companyid'";
	$rs=$db->query($sql);
	if($rs->count()>0)
	{
		$comp=$rs->first();
		return $comp->ERN_Number;
	}
	return 0;
}

function sendErrEmail($subject,  $message){
	global $settings;
	$to_email="";
	if(!isset($settings->emailfromaddress))
	{
		$to_email="support@justapply.co.uk";
	}else{
		$to_email=$settings->emailfromaddress;
	}
	return email($to_email,$subject, $message);
}

function getSOAPEnvelop($token,$methodName){
	if($methodName != "UpdateApplicationStatus"){
		$soapEnvelope="<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:tem=\"http://tempuri.org/\">
							<soapenv:Header>
								<SecuredWebServiceHeader xmlns='http://tempuri.org/'>
									<Username>kaonixuser</Username>
									<Password>P4ssw0rd</Password>
									<AuthenticatedToken>".$token."</AuthenticatedToken>
								</SecuredWebServiceHeader>
							</soapenv:Header>
							<soapenv:Body>
								<tem:".$methodName." xmlns='http://tempuri.org/'>
									<tem:composite>
										<![CDATA[{SOAP_BODY}]]>
									</tem:composite>
								</tem:".$methodName.">
							</soapenv:Body>
						</soapenv:Envelope>";
	}else{
		$soapEnvelope="<soapenv:Envelope xmlns:soapenv=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:tem=\"http://tempuri.org/\">
							<soapenv:Header>
								<SecuredWebServiceHeader xmlns='http://tempuri.org/'>
									<Username>kaonixuser</Username>
									<Password>P4ssw0rd</Password>
									<AuthenticatedToken>".$token."</AuthenticatedToken>
								</SecuredWebServiceHeader>
							</soapenv:Header>
							<soapenv:Body>
								<tem:".$methodName.">
										{SOAP_BODY}
								</tem:".$methodName.">
							</soapenv:Body>
						</soapenv:Envelope>";
	}
	return 	$soapEnvelope;
}
