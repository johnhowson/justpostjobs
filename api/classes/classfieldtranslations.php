<?php
class fieldtranslations
{
public  $jobsfields=array("fermsapp_description"=>"title",
  "fermsapp_vacancyshortdescription"=>"shortdescription",
  "fermsapp_roleresponsibilities"=>"longdescription",
  "fermsapp_fixedwage"=>"fobfixedwage",
  "fermsapp_longtermcompanyaimsforlearner"=>"futureprospects",
  "fermsapp_workinghours"=>"workingweek",
  "fermsapp_volume"=>"numberOfPositions",
  /*contact information */
  "fermsapp_firstname"=>"companycontactfirstname",
  "fermsapp_lastname"=>"companycontactlastname",
  "fermsapp_emailaddress"=>"companycontactemailaddress",
  /*"mainbusinessphone"=>"maincontactnumber",*/
  "contactphone"=>"companycontactcontactnumber",
    /* company information */
  /*"fermsapp_jasmallemployerwageincentive"=>"",*/
  /*"employeremployername"=>"companyname",*/
  "name"=>"companyname",
/*  "ernnumber"=>"ERN_Number",*/
  /*"employeremployeranonymous"=>"",*/
  "websiteurl"=>"employerwebsiteurl",
  "description"=>"employerdescription",
  "address1_line1"=>"addressline1",
  "address1_line2"=>"addressline2",
  "address1_line3"=>"addressline3",
  "address1_city"=>"town",
  "fermsapp_county"=>"county",
  "address1_postalcode"=>"postcode",
  /*"numberofemployees"=>"",*/
  /* company information */
  "levypayer"=>"levypayer",
  /*"yourcontact"=>"",*/
  "fermsapp_skillsrequired"=>"desiredskills",
  "fermsapp_volume"=>"numberofemployees",
  "fermsapp_idealcandidate"=>"desiredpersonalqualities",
  "fermsapp_qualificationsrequired"=>"desiredqualifications ",
  "fermsapp_realitycheck"=>"thingstoconsider",
  "fermsapp_postcode"=>"postcode",
  /*"fermsapp_importantotherinformation"=>"otherinformation",*/
  "fermsapp_vacancyquestion1"=>"supplementaryquestion1",
  "fermsapp_vacancyquestion2"=>"supplementaryquestion2",
  "fermsapp_disabilityconfidentemployer"=>"isemployerdisabilityconfident",
  /*dates */
  "fermsapp_jaapplicationdeadlinedate"=>"applicationclosingdate",
  /*"fermsapp_anticipatedinterviewdate"=>"",*/
  "fermsapp_anticipatedstartdate"=>"expectedstartdate");

public $oldcrmfields=array(
      "fermsapp_description"=>"fermsapp_jobtitle",
      "fermsapp_roleresponsibilities"=>"fermsapp_vacancyfulldescription",
      "fermsapp_qualificationsrequired"=>"fermsapp_qualificationsrequired",
      "fermsapp_vacancyshortdescription"=>"fermsapp_vacancyshortdescription",
      "fermsapp_importantotherinformation"=>"fermsapp_importantotherinformation",
    /*  "fermsapp_type"=>1,
      "fermsapp_durationtype"=>2,*/
      "fermsapp_vacancyquestion1"=>"fermsapp_vacancyquestion1",
      "fermsapp_vacancyquestion2"=>"fermsapp_vacancyquestion2",
    /* "fermsapp_applicationtype"=>"2",
      "fermsapp_disabilityconfidentemployer"=>false,*/
    /*  "fermsapp_wagetype"=>1,*/
      "fermsapp_jaapplicationdeadlinedate"=>"fermsapp_jaapplicationdeadlinedate",
      "fermsapp_anticipatedstartdate"=>"fermsapp_anticipatedstartdate",
        "fermsapp_anticipatedinterviewdate"=>"fermsapp_anticipatedinterviewdate",
      "fermsapp_longtermcompanyaimsforlearner"=>"fermsapp_longtermcompanyaimsforlearner",
      "fermsapp_skillsrequired"=>"fermsapp_skillsrequired",
      "fermsapp_volume"=>"fermsapp_volume",
      "fermsapp_fixedwage"=>"fermsapp_fixedwage",
    /*  "fermsapp_jasalaryfrequency"=>3,*/
      "fermsapp_postcode"=>"employerpostcode",
      "fermsapp_weeklyworkinghours"=>40,
    /*  "fermsapp_contacttype"=>100000001,*/
      /*"fermsapp_eemployeranonymous"=>false,
      "fermsapp_jasalarytextvalueother"=>"",
      "fermsapp_jaminsalary_base"=>"",
      "fermsapp_payrateperhour"=>"",
      "fermsapp_jaminsalary"=>"",
      "fermsapp_jamaxsalary_base"=>"",
      "fermsapp_locationadditionalinfo"=>"",
      "fermsapp_edsurn"=>"",
      "fermsapp_jasalarytextvalue"=>"",
      "fermsapp_applicationsinstructions"=>"",
      "fermsapp_trainingtobeprovided"=>"", */
      "fermsapp_workinghours"=>"fermsapp_workinghours",
      "fermsapp_realitycheck"=>"fermsapp_realitycheck",
      /*"fermsapp_employerwebsite"=>"",
      "fermsapp_vacancynotes"=>"",
      "fermsapp_trainingtype"=>"",
      "fermsapp_wagetypereason"=>"",
      "fermsapp_employercontactnumber"=>"",
      "fermsapp_otherdepartments"=>"",
      "fermsapp_expectedduration"=>"",
      "fob_noofapplications"=>"",
      "fermsapp_payrateperhour_base"=>"",*/

);
public $crmfields=array(
    "fermsapp_description"=>"title",
    "fermsapp_roleresponsibilities"=>"longDescription",
    "fermsapp_qualificationsrequired"=>"desiredQualifications",
    "fermsapp_vacancyshortdescription"=>"shortDescription",
    "fermsapp_importantotherinformation"=>"fermsapp_importantotherinformation",
    /*  "fermsapp_type"=>1,
     "fermsapp_durationtype"=>2,*/
    "fermsapp_vacancyquestion1"=>"fermsapp_vacancyquestion1",
    "fermsapp_vacancyquestion2"=>"fermsapp_vacancyquestion2",
    /* "fermsapp_applicationtype"=>"2",
     "fermsapp_disabilityconfidentemployer"=>false,*/
    /*  "fermsapp_wagetype"=>1,*/
    "fermsapp_jaapplicationdeadlinedate"=>"fermsapp_jaapplicationdeadlinedate",
    "fermsapp_anticipatedstartdate"=>"fermsapp_anticipatedstartdate",
    "fermsapp_anticipatedinterviewdate"=>"fermsapp_anticipatedinterviewdate",
    "fermsapp_longtermcompanyaimsforlearner"=>"fermsapp_longtermcompanyaimsforlearner",
    "fermsapp_skillsrequired"=>"fermsapp_skillsrequired",
    "fermsapp_volume"=>"fermsapp_volume",
    "fermsapp_fixedwage"=>"fermsapp_fixedwage",
    /*  "fermsapp_jasalaryfrequency"=>3,*/
    "fermsapp_postcode"=>"employerpostcode",
    "fermsapp_weeklyworkinghours"=>"hoursperweek",
    /*  "fermsapp_contacttype"=>100000001,*/
    /*"fermsapp_eemployeranonymous"=>false,
     "fermsapp_jasalarytextvalueother"=>"",
     "fermsapp_jaminsalary_base"=>"",
     "fermsapp_payrateperhour"=>"",
     "fermsapp_jaminsalary"=>"",
     "fermsapp_jamaxsalary_base"=>"",
     "fermsapp_locationadditionalinfo"=>"",
     "fermsapp_edsurn"=>"",
     "fermsapp_jasalarytextvalue"=>"",
     "fermsapp_applicationsinstructions"=>"",
     "fermsapp_trainingtobeprovided"=>"", */
    "fermsapp_workinghours"=>"fermsapp_workinghours",
    "fermsapp_realitycheck"=>"fermsapp_realitycheck",
    /*"fermsapp_employerwebsite"=>"",
     "fermsapp_vacancynotes"=>"",
     "fermsapp_trainingtype"=>"",
     "fermsapp_wagetypereason"=>"",
     "fermsapp_employercontactnumber"=>"",
     "fermsapp_otherdepartments"=>"",
     "fermsapp_expectedduration"=>"",
     "fob_noofapplications"=>"",
     "fermsapp_payrateperhour_base"=>"",*/

);
public $locationfields=array(
    'name'=>"companyname",
    'address1_line1'=>"addressline1",
    'address1_line2'=>"addressline2",
    'address1_line3'=>"addressline3",
        /*"addressline4"=>'',
        "addressline5"=>'', */
    'address1_postalcode'=>"postcode",
    'address1_city'=>"town",
       /* "county"=>'',*/
        "ernnumber"=>"ERN_Number",
        "additionalInformation"=>'',
        'websiteurl'=>"employerWebsiteUrl",
        "description"=>"employerDescription",
        'mainbusinessphone'=>"mainContactNumber",
        "fermsapp_disabilityconfidentemployer"=>"isEmployerDisabilityConfident",
        "levyPayer"=>0

);
public $contact=array(
"contactName"=>"",
"contactEmail"=>"",
"contactMobile"=>"",
"logoURL"=>'',);

/*$fields = array(
    "name",
    "ernnumber",
    "websiteurl",
    "description",
    "address1_line1",
    "address1_line2",
    "address1_line3",
    "address1_city",
    "address1_postalcode",
    "numberofemployees"
);*/
}
