<?php
namespace api\classes;
define("SYSTEM_ERROR","Sorry, there is some problem while processing your request. Please try again."); //General Error.
define("INVALID_REQUEST","Bad Request");	//	When system did not find required parameter in the request.
define("ERR_INVALID_USERNAME","Invalid User Name.");	//When username is blank or not able to pass rule checking
define("ERR_INVALID_PASSWORD","Invalid Password.");		//When Password is blank or not able to pass rule checking
define("ERR_INVALID_COMPANYID","Invalid CompanyID");	//When companyID is blank or not able to pass rule checking
define("ERR_INVALID_CONFIGURATION","Invalid Configuration");	//When
define("ERR_INVALID_FUNCTION_CALL","Sorry, requested page is not available."); //When request is made for invalid function.
define("ERR_INVALID_USENAME_OR_PASS","Invalid Username/Password."); //When system not able to authenticate with provided username/password.
define("ERR_ROLES","Invalid Username/Password."); //When role is not assigned for provided username/password.
define("NO_JOBS_AVAILABLE","Sorry, Jobs not Found");//When no Jobs available.
define("NO_APPLICANT_STATUS_FOUND","Sorry, Applicant Status not Found.");//When Applicant Status not found in system.
define("NO_RESUME_SOURCE_FOUND","Sorry, Resume Source not Found."); //When Resume source not found from the system.
define("INVALID_TOKEN","Invalid or exipired Token");	//When passed expired token.
define("AUTHENTICATION_REQUIRED","Authentication Required.");	//When request is made without authentication.
define("ERR_SAVE_DATA","Sorry, there is some problem while saving data"); 	//When there is some error while saving schedule
define("RESUME_SOURCE_REQUIRED","Please select resume source.");	// When user has not selected resume source.
define("RESUME_SCHEDULE_ERROR","Sorry, there is some problem while creating resume parsing schedule."); // When system is not able to create schedule
define("RESUME_DIRECTORY_NOT_FOUND","Sorry, there is some problem while creating resume parsing schedule. Resume Directory not Found."); // When resume directory not created on server.
define("MSG_RESUME_SCHEDULE_CREATED","Resume parsing schedule has been created successfully. You will be notified by email once uploaded resumes get parsed."); //When resume schedule created.
define("MSG_RESUME_SCHEDULE_NO_FILE_FOUND","Sorry, unable to find resume file(s) in specified directory."); // When resume files are not there in specified directory.
define("MSG_IS_REQUIRED_FIELD","is required field.");
define("MSG_IS_BOOLEAN_FIELD","is boolean field.");
define("MSG_IS_INTEGER_FIELD","is integer field.");
define("MSG_IS_NOT_A_VALID_EMAIL_FORMAT","is not a valid email format.");
define("MSG_JOB_ADDED_SUCCESSFULLY","Vacancy added successfully.");
define("MSG_SHOULD_FROM","shoud from");
define("MSG_RESUME_LISTING","Resumes litsing.");
define("MSG_NO_RECORD_FOUND","No record found.");
define("MSG_IS_DECIMAL_FIELD","is decimal field and should be atleast 40.");
define("MSG_INVALID_POST_CODE","Invalid post code.");
define("MSG_INVALID_COUNTY","Invalid county.");
define("MSG_THERE_IS_NO_JOB_TO_SUBMITE_AT_NAS","There is no job to submit at NAS");
define("MSG_DATE_SHOULD_FUTUER"," should be future date");
define("INVALID_JUSTAPPLY_REF_ID","Invalid Just Apply Vacancy Reference ID");
define("INVALID_ERN_NUMBER","Employer with specified ERN Number does not exists in Just Apply.");
//Employer
define("EMPLOYER_NAME_REQUUIRED","Please provide employer name.");
define("STREET_1_REQUIRED","Please provide Street Address 1");
define("ZIPCODE_REQUIRED","Please provide Zip/Postal Code.");
define("CITY_IS_REQUIRED","Please provide value for City.");
define("COUNTY_IS_REQUIRED","Please provide value for County.");
define("ERN_NUMBER_REQUIRED","Please provide ERN Number.");
define("EMPLOYER_CONTACT_FIRSTNAME","Please provide Employer Contact First Name.");
define("EMPLOYER_CONTACT_LASTNAME","Please provide Employer Contact Last Name.");
define("EMPLOYER_CONTACT_EMAIL","Please provide Employer Contact Email.");
define("EMPLOYER_CONTACT_PASSWORD","Please provide Employer Password.");
define("JUST_APPLY_REF_ID_EMPLOYER","Invalid Just Apply Employer ID");
define("MSG_EMPLOYER_ADDED_SUCCESSFULLY","Employer inserted successfully");
define("MSG_EMPLOYER_RECORD_UPDATED_SUCCESSFULLY","Employer record updated successfully.");
define("MSG_ERN_ALREADY_EXISTS","Employer with this ERN already Exists.");
define("MSG_ERN_ALREADY_EXISTS_WITH_OTHER","Employer with this ERN already Exists.");
define("INVALID_INDUSTRY","Specified sector not found in Just Apply.");
define("INVALID_CATEGORY","Specified Category not found in Just Apply.");
define("MSG_JOB_UPDATED_SUCCESSFULLY","Vacancy has been updated successfully.");
define("INVALID_VACANCY_TYPE","Invalid Vacancy Type.");
define("INVALID_HOURS_VALUE","Please specify proper hours value.");
define("INVALID_COUNTRY","Please provide valid country name.");
define("MSG_SELECT_INDUSTRY","Please provide value of Sector Field.");
define("MSG_SELECT_CATEGORY","Please provide value of Category Field.");
define("MSG_CRM_JOB_ID_BLANK","Please provide value of CRM ID Field.");
define("MSG_JOB_TITLE_BLANK","Please provide value of Vacancy Title Field.");
define("MSG_BRIEF_DESC_BLANK","Please provide value of Brief Description Field.");
define("MSG_DETAIL_DESC_BLANK","Please provide value of Detail Description Field.");
define("MSG_MAINSKILL_BLANK","Please provide value of Skill Required Field.");
define("MSG_JOBOPENING_BLANK","Please provide number of Job Openings");
define("MSG_JOBOPENING_VALID","Invalid value for Job Openings. It should be Integer.");
//define("LBL_LINKEDIN_NOT_AUTHORIZED","You need authorized your LinkedIn account to publish Vacancies.");
//define("LBL_FB_NOT_AUTHORIZED","You need authorized Facebook account to publish Vacancies.");
//define("LBL_TWITTER_NOT_AUTHORIZED","You need authorized Twitter account to publish Vacancies.");
define("LBL_INVALID_EMPLOYER_REF_ID","Invalid Just Apply Employer Reference ID");
define("MSG_CLIENT_ID_REQUIRED_TO_POST_JOB_TO_NAS","Just Apply Employer ID is needed to post vacancies to the NAS. Please provide Just Apply Employer ID and try again.");
define("MSG_ERNNUMBER_REQUIRED_TO_POST_JOB_TO_NAS","Employer ERN Number is needed to post vacancies to the NAS. Please add Employer ERN Number and try again.");
define("MSG_DISPLAY_CAREER_SITE_REQ","Please provide value for Display on Career site");
define("MSG_INVALID_VALUE","Invalid value for Display on Career Site.");
define("MSG_WEEK_WAGE","Please provide value for Weekly wage.");
define("MSG_NGTU_SECTORS_MANDATORY","Please provide value for NGTU sectors.");
define("MSG_NATION_WIDE_MANDATORY","Please provide value for Nationwide.");
define("MSG_TOTALJOBS_INDUSTRY_MANDATORY","Please provide value for Totaljobs Industry.");
define("MSG_TOTALJOBS_REGION_MANDATORY","Please provide value for Totaljobs Region.");
define("LBL_FB_POST_DELETED","Facebook post deleted successfully.");
define("LBL_FB_POST_UPDATED","Facebook post updated successfully.");
define("MSG_CAREERMAP_CATEGORY_MANDATORY","Please provide value for Career Map Category.");
define("MSG_CAREERMAP_LEVEL_MANDATORY","Please provide value for Career Map Level.");
define("MSG_COLLEGE_ACCOUNT","Please specify NAS college account.");
define("MSG_VACANCY_ALREADY_EXISTS","Sorry, the vacancy has the same CRM ID '{crmid}' as a vacancy that has already been published to JustApply with a JustApply reference of {id}.");
define("MSG_CANDIDATE_UPDATED_SUCCESSFULLY","Candidate has been updated successfully.");
define("MSG_IS_INTEGER_AND_REQUIRED_FIELD","is integer and required field.");
define("MSG_RESUME_STATUS_UPDATED_SUCCESSFULLY","Application status has been updated successfully.");
define("EMPLOYER_LOGO_DIRECTORY","companylogos");
define("GOOGLE_DISTANCE_MATRIX",false);
