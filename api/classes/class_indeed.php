<?php
namespace api\classes;
class indeed{
  function getIndeedXMLFeed(){
      global $ApplicationName,$AppURL,$obj;
      $sql="SELECT JobID, Jobtitle, StartDate, CRM_JobID, c.ClientID, c.CompanyName, j.City, j.State, j.Country, j.post_code, BriefDesc, DetailDesc, salary, hours, i.industryID, i.industry_title, MainSkill, OtherSkill, c.Details
          FROM JobOrders j
          LEFT JOIN `client` c ON j.ClientID = c.ClientID
          LEFT JOIN `industry` i ON j.industryID = i.industryID
          WHERE indeed_publish_required=1 AND PublicPosting = 'Yes'and LastDate>='".date("Y-m-d")."'";
  	$rs=$obj->select($sql);
      $cnt=count($rs);
      $xml='';
  	if($cnt>0 && $rs){
          $xml='<?xml version="1.0" encoding="utf-8"?>
              <source>
                  <publisher>'.$ApplicationName.'</publisher>
                  <publisherurl>'.$AppURL.'</publisherurl>
                  <lastBuildDate>'.gmdate('D, d M Y H:i:s e').'</lastBuildDate>';
          for($j=0;$j<$cnt;$j++){
              $sql_ext="SELECT `default_field_name`,`Value` FROM `ExtraFieldValues` efv join customize_job_form cjf on efv.ExtraFieldDefinationID=cjf.extra_field_id where cjf.default_field_name in('Employer_Description','salaryfrequency','Personal_Qualities','Working_Week','Training_To_Be_Provided','Qualification_Required','Future_Prospects_Description','Important_Other_Information','Reality_Check') and efv.EntityType='job' and efv.EntitiyID='".$rs[$j]['JobID']."'";
              $res_ext=$obj->select($sql_ext);
              $cnt_ext=count($res_ext);
              for($e=0;$e<$cnt_ext;$e++){
                  $rs_ext[trim($res_ext[$e]["default_field_name"])]=trim($res_ext[$e]["Value"]);
              }
              $url=generateSEOURL("",$rs[$j]['JobID'],$rs[$j]['Jobtitle'],"","",false,true)."?s=ind";
              $salary="£".$rs[$j]['salary']." ".$rs_ext['salaryfrequency'];
              $xml.='<job>
                      <title><![CDATA['.xml_escape($rs[$j]['Jobtitle']).']]></title>
                      <date><![CDATA['.gmdate('D, d M Y H:i:s e',strtotime($rs[$j]['StartDate'])).']]></date>
                      <referencenumber><![CDATA['.xml_escape($rs[$j]['CRM_JobID']).']]></referencenumber>
  		    <url><![CDATA['.xml_escape($url).']]></url>
  		    <company><![CDATA[ InteQual ]]></company>
                      <city><![CDATA['.xml_escape($rs[$j]['City']).']]></city>
                      <state><![CDATA['.xml_escape($rs[$j]['State']).']]></state>
                      <country><![CDATA['.xml_escape($rs[$j]['Country']).']]></country>
                      <postalcode><![CDATA['.xml_escape($rs[$j]['post_code']).']]></postalcode>';
                      /*<description><![CDATA['.xml_escape($rs[$j]['BriefDesc']).']]></description>*/
                  $xml.='<description><![CDATA[';
                  if($rs_ext['Employer_Description']!=""){
                      $xml.='<strong>About the employer:</strong><p>'.nl2br(xml_escape($rs_ext['Employer_Description'])).'</p>';
                  }
                  if($rs[$j]['BriefDesc']!=""){
                      $xml.='<strong>Brief overview of the role:</strong><p>'.nl2br(xml_escape($rs[$j]['BriefDesc'])).'</p>';
                  }
                  if($rs[$j]['salary']!=""){
                      $xml.='<strong>Salary:</strong><p>'.xml_escape($salary).'</p>';
                  }
                  if($rs_ext['Working_Week']!=""){
                      $xml.='<strong>Working week:</strong><p>'.nl2br(xml_escape($rs_ext['Working_Week'])).'</p>';
  		}
                  if($rs_ext['JobOpening']!=""){
                      $xml.='<strong>Positions available:</strong><p>'.xml_escape($rs[$j]['JobOpening']).'</p>';
                  }
                  if($rs[$j]['DetailDesc']!=""){
                      $xml.='<strong>Vacancy description:</strong><p>'.nl2br(xml_escape($rs[$j]['DetailDesc'])).'</p>';
                  }
                  $xml.='<strong>Requirements and prospects</strong>';
                  if($rs_ext['Qualification_Required']!=""){
                      $xml.='<strong>Qualifications Required:</strong><p>'.nl2br(xml_escape($rs_ext['Qualification_Required'])).'</p>';
                  }
                  if($rs[$j]['MainSkill']!=""){
                      $xml.='<strong>Desired skills:</strong><p>'.nl2br(xml_escape($rs[$j]['MainSkill'])).'</p>';
                  }
                  if($rs[$j]['OtherSkill']!=""){
                      $xml.='<p>'.xml_escape($rs[$j]['OtherSkill']).'</p>';
                  }
                  if($rs_ext['Personal_Qualities']!=""){
                      $xml.='<strong>Personal Qualities:</strong><p>'.nl2br(xml_escape($rs_ext['Personal_Qualities'])).'</p>';
                  }
                  if($rs_ext['Training_To_Be_Provided']!=""){
                      $xml.='<strong>Training to be provided:</strong><p>'.nl2br(xml_escape($rs_ext['Training_To_Be_Provided'])).'</p>';
                  }
                  if($rs_ext['Future_Prospects_Description']!=""){
                      $xml.='<strong>Future Prospects:</strong><p>'.nl2br(xml_escape($rs_ext['Future_Prospects_Description'])).'</p>';
                  }
                  if($rs_ext['Other Information']!=""){
                      $xml.='<strong>Other Information:</strong><p>'.nl2br(xml_escape($rs_ext['Important_Other_Information'])).'</p>';
                  }
                  if($rs_ext['Reality_Check']!=""){
                      $xml.='<strong>Things to Consider:</strong><p>'.nl2br(xml_escape($rs_ext['Reality_Check'])).'</p>';
                  }
                  $xml.=']]></description>';
                      /*<salary><![CDATA['.xml_escape($salary.']]></salary>';
                      <education><![CDATA['.xml_escape($rs[$j]['']).'Bachelors]]></education>*/
              $xml.='<jobtype><![CDATA['.xml_escape(str_replace(" ","",strtolower($rs[$j]['hours']))).']]></jobtype>
                      <category><![CDATA['.xml_escape($rs[$j]['industry_title']).']]></category>';
                      /*<experience><![CDATA['.xml_escape($rs[$j]['']).'5+ years]]></experience>*/
              $xml.='</job>';
          }
          $xml.='</source>';
      }
      return $xml;
  }


}
