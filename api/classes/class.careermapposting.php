<?php
namespace api\classes;
require_once("classapitools.php");
class postVacancyToCareerMap{
public $Jobtitle=""; 

    function createXML($JobID,$username,$password,$mode){
        global $obj,$xml;
        $sql1="SELECT JobOrders.*,industry.industry_title FROM `JobOrders` LEFT JOIN industry ON JobOrders.industryID=industry.industryID WHERE JobID = '".$JobID."'";
        $res1=$obj->select($sql1);
        $sql2="SELECT EFV.`ExtraFieldDefinationID`, EFV.`ExtraFieldTitle`,rc.default_field_name, EFV.Value as `value` from ExtraFieldValues EFV, customize_job_form rc, ExtraFieldsDefination EFD where rc.is_extra_field='Yes' and rc.extra_field_id=EFV.ExtraFieldDefinationID and EFV.ExtraFieldDefinationID = EFD.ExtraFieldDefinationID and EFD.ExtraFieldType != 'label' and EFD.EntityType='job' and EFV.EntitiyID='".$JobID."' ";
        $res2=$obj->select($sql2);
        $cnt_res2=count($res2);
        $ExtraField="";
        $ExtraField=array();
        for($p=0;$p<$cnt_res2;$p++){
            $ExtraFields[trim($res2[$p]["default_field_name"])]=trim($res2[$p]["value"]);
        }
        $sql3="SELECT `CompanyName` FROM `client` WHERE `ClientID`='".$res1[0]["ClientID"]."'";
        $res3=$obj->select($sql3);
        $VacancyURL=shareJobURL("cmap","",$JobID,$res1[0]["Jobtitle"]);
        $res_url=getShortenURL($VacancyURL);
        if($res_url !== false && isset($res_url["id"]) && !empty($res_url["id"])){
            $apply_url=$res_url["id"];
        }else{
            $apply_url=generateSEOURL("",$JobID,$res1[0]["Jobtitle"],"","",false,true);
            $apply_url.="?s=cmap";
        }
        $doc  = new DOMDocument('1.0', 'utf-8');
		$doc->formatOutput = true;
		$root=$doc->createElement("post");
		$doc->appendChild($root);
		$root->appendChild($doc->createElement("employer_email",$username));
		$root=$this->createCDataXML($doc,$root,"employer_password",$password);
		if($mode=='edit'){
            if(isset($res1[0]['careermap_vacancyID']) && !empty($res1[0]['careermap_vacancyID']) &&  $res1[0]['careermap_vacancyID']!=null){
            	$root->appendChild($doc->createElement("id",$res1[0]['careermap_vacancyID']));
            }
        }
		$root=$this->createCDataXML($doc,$root,"position_title",$res1[0]['Jobtitle']);
		$root->appendChild($doc->createElement("position_ref",$res1[0]['CRM_JobID']));
		$root->appendChild($doc->createElement("position_job_level",$ExtraFields["careermap_level"]));
		$root->appendChild($doc->createElement("position_company_name",$res3[0]['CompanyName']));
		$root->appendChild($doc->createElement("position_industry",$ExtraFields["careermap_category"]));
		$root->appendChild($doc->createElement("position_job_type",$ExtraFields["careermap_jobtype"]));
		$root->appendChild($doc->createElement("position_salary",$ExtraFields["careermap_salary"]));
		$root->appendChild($doc->createElement("position_country",5257));
		$root->appendChild($doc->createElement("position_city",$res1[0]['City']));
		$root->appendChild($doc->createElement("position_street_address",$this->getAddress($ExtraFields)));
		$root->appendChild($doc->createElement("position_post_code",$res1[0]['post_code']));
		$root->appendChild($doc->createElement("position_send_apply",396));
		$root->appendChild($doc->createElement("position_url",$apply_url));
		$root=$this->createCDataXML($doc,$root,"position_job_description",$this->getDescription($res1,$ExtraFields));
		$root->appendChild($doc->createElement("position_expiration_date",date('d/m/Y',strtotime($res1[0]['LastDate']))));
		$root->appendChild($doc->createElement("position_original_url",$apply_url));
		$xml=$doc->saveXML();
        return $xml;
    }
	private function createCDataXML($doc,$rootElem,$nodeName,$nodeValue){
		$item=$doc->createElement($nodeName);
		$textNode=$doc->createCDATASection($nodeValue);
		$item->appendChild($textNode);
		$rootElem->appendChild($item);
		return $rootElem;
	}
    function getAddress($ExtraFields){
        $street=(isset($ExtraFields['Street1']) && !empty($ExtraFields['Street1']))?$ExtraFields['Street1']:"";
        $street.=(isset($ExtraFields['Street2']) && !empty($ExtraFields['Street2']))?((trim($street)!="")?",".$ExtraFields['Street2']:$ExtraFields['Street2']):"";
        $street.=(isset($ExtraFields['Street3']) && !empty($ExtraFields['Street3']))?((trim($street)!="")?",".$ExtraFields['Street3']:$ExtraFields['Street3']):"";
        return $street;
    }

    function getDescription($rs,$ExtraFields){
        $descriptionHtml="";
        //$descriptionHtml=$this->xml_escape($this->wrapHTML(LBL_NO_OF_POSITION,$rs[0]['JobOpening']));
		//$descriptionHtml.=isset($ExtraFields['Employer_Description']) && !empty($ExtraFields['Employer_Description'])?$this->xml_escape($this->wrapHTML("About Employer",$ExtraFields['Employer_Description'])):"";
		$descriptionHtml.=$this->xml_escape($rs[0]['DetailDesc']);
		/*$descriptionHtml.=$this->xml_escape($this->wrapHTML(LBL_SKILL_REQUIRED,$rs[0]['MainSkill']));
		$descriptionHtml.=isset($ExtraFields['Personal_Qualities']) && !empty($ExtraFields['Personal_Qualities'])?$this->xml_escape($this->wrapHTML(LBL_PERSONAL_QUALITIES,$ExtraFields['Personal_Qualities'])):"";
		$descriptionHtml.=(isset($ExtraFields['Qualification_Required']) && !empty($ExtraFields['Qualification_Required']))?$this->xml_escape($this->wrapHTML(LBL_QUAL_REQ,$ExtraFields['Qualification_Required'])):"";
        $descriptionHtml.=(isset($ExtraFields['Future_Prospects_Description']) && !empty($ExtraFields['Future_Prospects_Description']))?$this->xml_escape($this->wrapHTML(LBL_FUTURE_PROSPECT,$ExtraFields['Future_Prospects_Description'])):"";
		if(isset($ExtraFields['Important_Other_Information']) && !empty($ExtraFields['Important_Other_Information'])){
		    $descriptionHtml.=$this->xml_escape($this->wrapHTML(LBL_OTHER_INFOR,$ExtraFields['Important_Other_Information']));
        }
		$descriptionHtml.=(isset($ExtraFields['Training_To_Be_Provided']) && !empty($ExtraFields['Training_To_Be_Provided']))?$this->xml_escape($this->wrapHTML(LBL_TRAINING_TO_BE_PROVIDED,$ExtraFields['Training_To_Be_Provided'])):"";
		$descriptionHtml.=(isset($ExtraFields["Expected_Duration"]) && !empty($ExtraFields["Expected_Duration"]))?$this->xml_escape($this->wrapHTML('Expected Duration',$ExtraFields["Expected_Duration"])):"";
		$descriptionHtml.=(isset($ExtraFields['Working_Week']) && !empty($ExtraFields['Working_Week']))?$this->wrapHTML(LBL_WORKING_WEEK,$ExtraFields['Working_Week']):"";
		$descriptionHtml.=(isset($ExtraFields['Reality_Check']) && !empty($ExtraFields['Reality_Check']))?$this->wrapHTML(LBL_THINGS_TO_CONSIDER,$ExtraFields['Reality_Check']):"";*/
        return $descriptionHtml;
    }
    function createXMLForRemoveVacancy($JobID,$username,$password){
        global $obj;
        $sql1="SELECT careermap_vacancyID FROM `JobOrders` WHERE JobID = '".$JobID."'";
        $res1=$obj->select($sql1);
		if(isset($res1[0]["careermap_vacancyID"]) && !empty($res1[0]["careermap_vacancyID"])){
			$doc  = new DOMDocument('1.0', 'utf-8');
			$doc->formatOutput = true;
			$root=$doc->createElement("post");
			$doc->appendChild($root);
			$root->appendChild($doc->createElement("id",$res1[0]["careermap_vacancyID"]));
			$root->appendChild($doc->createElement("employer_email",$username));
			$root=$this->createCDataXML($doc,$root,"employer_password",$password);
			$xml=$doc->saveXML();
	        return $xml;
		}
    }

    private function wrapHTML($title,$content){
		$str="<strong>".$title."</strong><p>".$content."</p>";
		return $str;
    }

	private function xml_escape($s){

        $s = strip_tags($s,'<p><br/><br><ol><ul><li><b>');
        $s = html_entity_decode($s, ENT_QUOTES, 'UTF-8');
        $s = htmlspecialchars($s,ENT_XML1 | ENT_IGNORE,'UTF-8',false);
        //$s = str_replace(array('&', '<', '>', '\'', '"'), array('&amp;', '&lt;', '&gt;', '&apos;', '&quot;'), $s);
        //$s = nl2br($s);
		return $s;
	}

    function postToCareerMap($JobID,$careermap_username,$careermap_password,$mode){
        global $obj;
        switch($mode){
            case 'add':
                $url = "https://careermap.co.uk/careers/main/post";
                $xml=$this->createXML($JobID,$careermap_username,$careermap_password,'add');
                break;
            case 'edit':
                $sql1="SELECT careermap_vacancyID FROM `JobOrders` WHERE JobID = '".$JobID."'";
                $res1=$obj->select($sql1);
                if(isset($res1[0]['careermap_vacancyID']) && !empty($res1[0]['careermap_vacancyID']) &&  $res1[0]['careermap_vacancyID']!=null){
                    //$url = "https://careermap.co.uk/careers/main/renew";
                    $url = "https://careermap.co.uk/careers/main/post";
                    $xml=$this->createXML($JobID,$careermap_username,$careermap_password,'edit');
                }else{
                    $url = "https://careermap.co.uk/careers/main/post";
                    $xml=$this->createXML($JobID,$careermap_username,$careermap_password,'add');
                }
                break;
            case 'delete':
                $url = "https://careermap.co.uk/careers/main/remove";
                $xml=$this->createXMLForRemoveVacancy($JobID,$careermap_username,$careermap_password);
                break;
        }
        //echo $url;exit;
        //echo $xml;exit;
        //setting the curl parameters.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        // Following line is compulsary to add as it is:
        curl_setopt($ch, CURLOPT_POSTFIELDS, "data=" . rawurlencode($xml));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
        $data = curl_exec($ch);
        curl_close($ch);
		$this->show($data);
        //convert the XML result into array
        $output = json_decode(json_encode(simplexml_load_string($data)), true);
        $this->show(print_r($output,1));

        $ret_val="";
        $CareerMap_ID="";
        $CareerMap_Status=0;
        $CareerMap_Message="";
        if(isset($output["status"]) && strtolower($output["status"])=='failure'){
            $CareerMap_Message="<br/>Error code:".$output["error_code"]."<br/>Error message:".$output["error_message"];
            $ret_val["CareerMap_Message"]=$CareerMap_Message;
            $ret_val["CAREERMAP_ID"]=0;
            sendCareerMapPostingErrorEmail($CareerMap_Message,$JobID);
        }else{
            if(isset($output["status"]) && strtolower($output["status"])=='success'){
                $sql="update JobOrders set careermap_publish_status='".$output["status"]."', careermap_request='".escapeStr($xml)."', careermap_response='".escapeStr($data)."', careermap_vacancyID='".$output["id"]."',careermap_communication_date='".date("Y-m-d H:i:s")."',careermap_message='".$output["url"]."' where JobID='".$JobID."'";
                $obj->edit($sql);
                $ret_val["CAREERMAP_ID"]=$output["id"];
            }else if(isset($output["status"]) && strtolower($output["status"])=='renew'){
                $sql="update JobOrders set careermap_publish_status='".$output["status"]."', careermap_request='".escapeStr($xml)."', careermap_response='".escapeStr($data)."', careermap_vacancyID='".$output["old_id"]."',careermap_communication_date='".date("Y-m-d H:i:s")."',careermap_message='".$output["exists_message"]."' where JobID='".$JobID."'";
                $obj->edit($sql);
                $ret_val["CAREERMAP_ID"]=$output["old_id"];
            }else{
                $CareerMap_Message="There is some error while posting vacancy to CareerMap. No errors returned from CareerMap";
                $ret_val["CareerMap_Message"]=$CareerMap_Message;
                $ret_val["CAREERMAP_ID"]=0;
                sendCareerMapPostingErrorEmail($CareerMap_Message,$JobID);
            }
        }
        //logChannelResponse("CAREERMAP",$JobID,@$res1[0]['CRM_JobID'],$ret_val["CAREERMAP_ID"],$mode,$url,$xml,$data,$output["status"]);
        $tools=new apitools();
        $tools->registerInAPILog($JobID,$xml,$data,$output["status"]);
        return $ret_val;
    }

    private function show($str){
    	return;
		$str="<span style='font-size:11px;font-family:verdana'>".$str."</span><hr>";
		//flushBuffer();
		ob_implicit_flush(1);
		echo $str;
		@ob_flush();
		@flush();
		ob_start();
	}
}
