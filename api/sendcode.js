// JScript source code
var authenticationToken = null;
var JustApplyUsername = null;
var JustApplyPassword = null;
var apiURL = null;
//apiURL = "https://ar.justapply.uk/api/iconnect/";
method_employer_add = "addEmployer";
method_edit_employer = "editEmployer";
method_add_job = "addJob";
method_edit_job = "editJob";
method_auth = "authenticate";
access_token = "";
employer_id = 0;
vacancy_id = 0;
ERN = null;
EMPID = null;
var Flag = 0;

function showLoadingMessage() {
    //tdAreas.style.display = 'none';
    var newdiv = document.createElement('div');
    newdiv.setAttribute('id', "msgDiv");
    newdiv.valign = "middle";
    newdiv.align = "center";
    var divInnerHTML = "<table height='100%' width='100%' style='cursor:wait'>";
    divInnerHTML += "<tr>";
    divInnerHTML += "<td valign='middle' align='center'>";
    divInnerHTML += "<img alt='' src='/_imgs/AdvFind/progress.gif'/>";
    divInnerHTML += "<div/><b>Adding Vacancy…</b>";
    divInnerHTML += "</td></tr></table>";
    newdiv.innerHTML = divInnerHTML;
    newdiv.style.background = '#FFFFFF';
    newdiv.style.fontSize = "15px";
    newdiv.style.zIndex = "1010";
    newdiv.style.width = document.body.clientWidth;
    newdiv.style.height = document.body.clientHeight;
    newdiv.style.position = 'absolute';
    document.body.insertBefore(newdiv, document.body.firstChild);
    document.all.msgDiv.style.visibility = 'visible';
}

function GetAuthenticationInfo() {
    debugger;

    try {
        // debugger;
        //        var lookupObject = Xrm.Page.getAttribute("ownerid");
        //        if (lookupObject != null) {
        //            var lookUpObjectValue = lookupObject.getValue();
        //            if ((lookUpObjectValue != null)) {
        //                var lookupid = lookUpObjectValue[0].id;
        //                //get owner
        var entity = "fermsapp_configurations";

        var ret = retrieveMultipleSyncPostVacancy(entity, "fermsapp_name eq 'Just Apply'", "fermsapp_justapplyurl,fermsapp_username,fermsapp_password");
        if (ret != null && ret[0]) {

            if (ret[0].fermsapp_username != null && ret[0].fermsapp_password != null && ret[0].fermsapp_justapplyurl != null) {
                JustApplyUsername = ret[0].fermsapp_username;
                JustApplyPassword = ret[0].fermsapp_password;
                apiURL = ret[0].fermsapp_justapplyurl;
                return true;
            }
            else {
                return false;
            }
        }

    }
    catch (err) {
        document.all.msgDiv.style.visibility = 'hidden';
        Flag = 0;
        alert(err.Message);
    }
    return false;
}

function getEMPIDFromAccount(formContext) {
    debugger;

    try {
        var formContext = formContext;
        //var lookupObject = Xrm.Page.getAttribute("fermsapp_organisationid");
        var lookupObject = formContext.getAttribute("fermsapp_organisationid");
        if (lookupObject != null) {
            var lookUpObjectValue = lookupObject.getValue();

            if ((lookUpObjectValue != null)) {
                var lookupid = lookUpObjectValue[0].id;
                //get owner
                var entity = "AccountSet";
                //var fields = "*";
                //var filter = "AccountId eq guid'" + lookupid + "'";

                var ret = retrieveSyncRecord(entity, lookupid);

                if (ret != null) {
                    if (ret.fermsapp_Justapplyemployerid != null) {
                        EMPID = ret.fermsapp_Justapplyemployerid;
                        return ret.fermsapp_Justapplyemployerid;
                    }
                    else
                        return null;
                }
            }

        }
    }
    catch (err) {
        document.all.msgDiv.style.visibility = 'hidden';
        Flag = 0;
        alert(err.Message);
    }
    return null;
}

getAccessToken = function (formContext) {
    debugger;

    // alert(Xrm.Page.getAttribute("fermsapp_nasframeworktype").getText());
    //var test=Xrm.Page.getAttribute("fermsapp_nasframeworktype").getText();
    // debugger;
    if (Flag == 1) {
        alert("Please Wait Vacancy Add/Update is in Process or refesh the Page");
        return false;
    }
    else {

        Flag = 1;
        if (document.all.msgDiv != null || document.all.msgDiv != undefined) {
            document.all.msgDiv.style.visibility = 'visible';
        }
        else { showLoadingMessage(); }
    }
    ////////
    try {
        //if (Xrm.Page.data.entity.getIsDirty()) {
        if (formContext.data.entity.getIsDirty()) {


            alert("There are unsaved changes on the form. Please save the form before posting the Vacancy.");
            document.all.msgDiv.style.visibility = 'hidden';
            Flag = 0;
            return;
        }

        var _AuthInforec = GetAuthenticationInfo();

        if (!_AuthInforec) {
            alert("Please Specify User name and Password in Configuration Entity");
            document.all.msgDiv.style.visibility = 'hidden';
            Flag = 0;
            return false;
        }

        EMPID = getEMPIDFromAccount(formContext);
        if (EMPID == null) {
            alert("Please Add Employer to Just Apply");
            document.all.msgDiv.style.visibility = 'hidden';
            Flag = 0;
            return false;
        }


        var authdata = { "username": JustApplyUsername, "password": JustApplyPassword };
        // jQuery.support.cors = true;
        // jQuery.ajax({
        $.ajax({
            type: "POST",
            async: false,
            url: apiURL + method_auth,
            data: "data=" + JSON.stringify(authdata),
            dataType: 'jsonp',
            crossDomain: true,
            success: function (responsedata) {
                //var arr = responsedata.success;
                try {
                    debugger;
                    authenticationToken = responsedata.token;
                    //  alert(authenticationToken);
                    //if (Xrm.Page.getAttribute("fermsapp_referencenumber").getValue() != null)
                    if (formContext.getAttribute("fermsapp_referencenumber").getValue() != null)
                        editJob(formContext);
                    else
                        addJob(formContext);
                    Flag = 0;
                    document.all.msgDiv.style.visibility = 'hidden';
                }
                catch (e) {
                    document.all.msgDiv.style.visibility = 'hidden';
                    Flag = 0;
                    alert(e.Message);
                }

            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                debugger;
                alert("Error: ");
                Flag = 0;
            }
        });
    }

    catch (e) {
        document.all.msgDiv.style.visibility = 'hidden';
        Flag = 0;
        alert(e.Message);
    }
    Flag = 0;

    // editJob();
    //    $.ajax({
    //        type: "POST",
    //        url: apiURL + method_auth,
    //        data: "data=" + JSON.stringify(authdata),
    //        dataType: 'jsonp',
    //        success: function (responsedata) {
    //            authenticationToken = responsedata.token;
    //        }
    //    });
}

function AddVacancies(executionContext) {
    debugger;
    var formContext=null;
    if(executionContext.getFormContext){formContext = executionContext.getFormContext(); }
    else{formContext=executionContext}
    var checkNullAtrributeArray = ["ownerid",
                                     "fermsapp_type",
                                     "fermsapp_postcode",
                                     "fermsapp_description",
                                     "fermsapp_anticipatedstartdate",
                                     "fermsapp_roleresponsibilities",
                                     //"fermsapp_jacountry",
                                     "fermsapp_jaapplicationdeadlinedate",
                                     //"fermsapp_jajoblevel",
                                     "fermsapp_jacategory",
                                     "fermsapp_jaindustry",
                                     "fermsapp_jajobreference",
                                     "fermsapp_roleresponsibilities",
                                     "fermsapp_skillsrequired",
                                     "fermsapp_jaindustry",
                                     "fermsapp_jacategory",
                                     "fermsapp_volume",
                                     "fermsapp_workinghours",
                                     "fermsapp_jaapplicationdeadlinedate",
                                     "fermsapp_anticipatedstartdate"];

    var CheckNullforNas = ["fermsapp_vacancyshortdescription",
                            "fermsapp_roleresponsibilities",
                            "fermsapp_address1",
                            "fermsapp_towncity",
							"fermsapp_disabilityconfidentemployer",
							"fermsapp_trainingtype",
                            "fermsapp_nasframeworkcode"];



    var _Val = validateNull(formContext,checkNullAtrributeArray);

    var CheckNullForUJM = ["fermsapp_soc", "fermsapp_ujmrecruitinternationally", "fermsapp_ujmstate"];
    var CheckNullForNGTU = ["fermsapp_ngtusector", "fermsapp_nationwide"];
    var CheckNullForTotalJobs = ["fermsapp_totaljobs_industry", "fermsapp_totaljobs_region"];
    var CheckNullForReed = ["fermsapp_reedsector", "fermsapp_reedsubsector", "fermsapp_reedcredittype"];
	var CheckNullForCareerMap = ["fermsapp_careermap_category", "fermsapp_careermap_level", "fob_careermap_jobtype", "fob_careermap_salary"];

    if (!_Val) {
        return;
    }

    //if (Xrm.Page.getAttribute("fermsapp_ujm").getValue()) {
    if (formContext.getAttribute("fermsapp_ujm").getValue()) {

        if (!validateNull(formContext,CheckNullForUJM)) {
            return;
        }
    }

	//if (Xrm.Page.getAttribute("fermsapp_reedchannel").getValue()) {
    if (formContext.getAttribute("fermsapp_reedchannel").getValue()) {

        if (!validateNull(formContext,CheckNullForReed)) {
            return;
        }
    }

    //if (Xrm.Page.getAttribute("fermsapp_ngtu").getValue()) {
    if (formContext.getAttribute("fermsapp_ngtu").getValue()) {

        if (!validateNull(formContext,CheckNullForNGTU)) {
            return;
        }
    }

    //if (Xrm.Page.getAttribute("fermsapp_totaljobs").getValue()) {
    if (formContext.getAttribute("fermsapp_totaljobs").getValue()) {
        if (!validateNull(formContext,CheckNullForTotalJobs)) {
            return;
        }
    }

 //if (Xrm.Page.getAttribute("fermsapp_careermap").getValue()) {
    if (formContext.getAttribute("fermsapp_careermap").getValue()) {

        if (!validateNull(formContext,CheckNullForCareerMap)) {
            return;
        }
    }

    //if (Xrm.Page.getAttribute("fermsapp_nas").getValue()) {
    if (formContext.getAttribute("fermsapp_nas").getValue()) {
        if (!validateNull(formContext,CheckNullforNas))  {
            return;
        }
		//else if (!NASTitleValidations())
		//{
		//	alert("Vacancy Title must contain the word " + "'"+ "apprentice" + "'"+ " or "+ "'" + "apprenticeship" + "'");
		//	return;
		//}

	}

    // GetValuesForNas();
    //GetValuesForNas(CheckNullforNas);

    getAccessToken(formContext);
}

function validateNull(formContext,checkNullAtrributeArray) {
    try {
        for (i = 0; i < checkNullAtrributeArray.length; i++) {
            //if (Xrm.Page.getAttribute(checkNullAtrributeArray[i]).getValue() == null) {
			if (formContext.getAttribute(checkNullAtrributeArray[i])) {
            if (formContext.getAttribute(checkNullAtrributeArray[i]).getValue() == null) {
                //alert(Xrm.Page.getControl(checkNullAtrributeArray[i]).getLabel() + " : Mandatory Value Missing");
                alert(formContext.getControl(checkNullAtrributeArray[i]).getLabel() + " : Mandatory Value Missing");
                //Xrm.Page.getControl(checkNullAtrributeArray[i]).setFocus();
                formContext.getControl(checkNullAtrributeArray[i]).setFocus();
                return false;
            }
			}

        }
        return true;
    }
    catch (e) {
        alert(e.Message);
    }

}
//function NASTitleValidations() {
//	debugger;
//    try {
//		   var apprenticeshipType = GetTextForOptionSet("fermsapp_type");
//		     if ( apprenticeshipType == "Apprenticeship")
//			 {

//               var test = Xrm.Page.getAttribute("fermsapp_description").getValue();
//               if (test.includes("apprentice") || test.includes("apprenticeship")) {
//                return true;
//			  }
//			   else {

//		               return false;
//                    }

//	 }
//         else {

//		          return true;
//             }
//    }
//    catch (e) {
//        alert(e.Message);
//    }

//}
function convertIfNull(formContext,Attribute) {
    try {
        //var Attributevalue = Xrm.Page.getAttribute(Attribute).getValue();
		var Attributevalue=null;
        //var dateValue = Xrm.Page.getAttribute(dtAttribute).getValue();
		if(formContext.getAttribute(Attribute)){
			Attributevalue = formContext.getAttribute(Attribute).getValue();
		}
        if (Attributevalue == null)
            return "";
        else return Attributevalue;
    }
    catch (e) {
        alert(e.Message);
    }
}


////////////////////

function ConvertDateFormat(formContext,dtAttribute) {
    try {
		var dateValue=null;
        //var dateValue = Xrm.Page.getAttribute(dtAttribute).getValue();
		if(formContext.getAttribute(dtAttribute)){
			dateValue = formContext.getAttribute(dtAttribute).getValue();
		}
        if (dateValue == null)
            return null

        var year = dateValue.getFullYear() + "";
        var month = (dateValue.getMonth() + 1) + "";
        var day = dateValue.getDate() + "";
        return day + "/" + month + "/" + year;
    }
    catch (e) {
        alert(e.Message);
    }
}


function GetAllChannelsValue(formContext) {
    try {
		var channels = "Career Site,";
        debugger;

		if ( formContext.getAttribute("fermsapp_nas").getValue())
		{
			channels = channels + "NAS,";

		}
		if ( formContext.getAttribute("fermsapp_ngtu").getValue())
		{
			channels = channels + "NGTU,";

		}
		if ( formContext.getAttribute("fermsapp_indeed").getValue())
		{
			channels = channels + "indeed,";

		}
		if ( formContext.getAttribute("fermsapp_ujm").getValue())
		{
			channels = channels + "UJM,";

		}
        if ( formContext.getAttribute("fermsapp_careermap").getValue())
		{
			channels = channels + "careermap,";

		}
       if ( formContext.getAttribute("fermsapp_twitter").getValue())
		{
			channels = channels + "Twitter,";

		}
         if ( formContext.getAttribute("fermsapp_facebook").getValue())
		{
			channels = channels + "Facebook,";

		}
         if ( formContext.getAttribute("fermsapp_totaljobs").getValue())
		{
			channels = channels + "TOTALJOBS,";

		}
         if ( formContext.getAttribute("fermsapp_reedchannel").getValue())
		{
			channels = channels + "reed,";

		}

        return channels;
        // return "No";
    }
    catch (e) {
        alert(e.Message);
    }
}

function GetBooleanText(formContext,attrbute) {
    try {
        //var _boolvalue = Xrm.Page.getAttribute(attrbute).getValue();
		var _boolvalue=null;
		if(formContext.getAttribute(attrbute) && formContext.getAttribute(attrbute).getValue()){
			_boolvalue = formContext.getAttribute(attrbute).getValue();
		}
        if (_boolvalue != null && _boolvalue)
            return "Yes";
        else return "No";
    }
    catch (e) {
        alert(e.Message);
    }
}

function GetTextForOptionSet(formContext,Opt) {
    try {

        if (formContext.getAttribute(Opt) && formContext.getAttribute(Opt).getValue() != null){
			if(formContext.getAttribute(Opt).getText!=null){return String(formContext.getAttribute(Opt).getText());}
			else{return String(formContext.getAttribute(Opt).getValue());}
		}

        else return "";
    }
    catch (e) {
        alert(e.Message);
    }
}
function GetValueForOptionSet(formContext,Opt) {
    try {

        if (formContext.getAttribute(Opt) && formContext.getAttribute(Opt).getValue() != null)
            return formContext.getAttribute(Opt).getValue();
        else return "";
    }
    catch (e) {
        alert(e.Message);
    }
}
addJob = function (formContext) {

    //"Framework_Type": "IntermediateLevelApprenticeship",
    debugger;

    try {


        var jobdata = {
            "token": authenticationToken,
            "CRM_JobID": formContext.getAttribute("fermsapp_jajobreference").getValue(),

            "Jobtitle": convertIfNull(formContext,"fermsapp_description"),
             "post_code": convertIfNull(formContext,"fermsapp_postcode"),
            "BriefDesc": convertIfNull(formContext,"fermsapp_vacancyshortdescription"),
            "DetailDesc": convertIfNull(formContext,"fermsapp_roleresponsibilities"),
            "MainSkill": convertIfNull(formContext,"fermsapp_skillsrequired"),
            "industry": GetTextForOptionSet(formContext,"fermsapp_jaindustry"),
            "category": GetTextForOptionSet(formContext,"fermsapp_jacategory"),
            "JobOpening": convertIfNull(formContext,"fermsapp_volume"),
            "Closing_Date": ConvertDateFormat(formContext,"fermsapp_jaapplicationdeadlinedate"),
            //"Interview_Start_Date": ConvertDateFormat("fermsapp_anticipatedinterviewdate"),
            "Possible_Start_Date": ConvertDateFormat(formContext,"fermsapp_anticipatedstartdate"),
            "Weekly_Wage": convertIfNull(formContext,"fermsapp_fixedwage"),
            "Working_Week": convertIfNull(formContext,"fermsapp_workinghours"),
            "Future_Prospects_Description": convertIfNull(formContext,"fermsapp_longtermcompanyaimsforlearner"),
            "Vacancy_Location": GetTextForOptionSet(formContext,"fermsapp_locationtype"),
            "Qualification_Required": convertIfNull(formContext,"fermsapp_qualificationsrequired"),
            "Important_Other_Information": convertIfNull(formContext,"fermsapp_importantotherinformation"),
            //"Reality_Check": convertIfNull("fermsapp_realitycheck"),
            //"Framework_Type": GetTextForOptionSet("fermsapp_nasframeworktype"),
			"disability_confident_employer": GetTextForOptionSet(formContext,"fermsapp_disabilityconfidentemployer"),
			"wage_type": GetValueForOptionSet(formContext,"fermsapp_wagetype"),
			"wage_type_reason": convertIfNull(formContext,"fermsapp_wagetypereason"),
			"min_wage": convertIfNull(formContext,"fermsapp_jaminsalary"),
			"max_salary": convertIfNull(formContext,"fermsapp_jamaxsalary"),
			"training_type": GetTextForOptionSet(formContext,"fermsapp_trainingtype"),
			"duration_type": GetTextForOptionSet(formContext,"fermsapp_durationtype"),
            "Vacancy_Submitted_By": convertIfNull(formContext,"fermsapp_vacancysubmittedby"),
            "Question_One": convertIfNull(formContext,"fermsapp_vacancyquestion1"),
            "Question_Two": convertIfNull(formContext,"fermsapp_vacancyquestion2"),
            "NAS_Framework": convertIfNull(formContext,"fermsapp_nasframeworkcode"),
            "Display_On_Carrier_Site": "Yes",
            "Applications_Instructions": convertIfNull(formContext,"fermsapp_applicationsinstructions"),
            //"Small_Employer_Wage_Incentive": GetBooleanText(formContext,"fermsapp_jasmallemployerwageincentive"),
            "Training_To_Be_Provided": convertIfNull(formContext,"fermsapp_trainingtobeprovided"),
            "Expected_Duration": convertIfNull(formContext,"fermsapp_expectedduration"),
            //"Employer_Anonymous": GetBooleanText(formContext,"fermsapp_eemployeranonymous"),
            //"Employer_Anonymous_Name": convertIfNull(formContext,"fermsapp_employeranonymousname"),
            "Chanel": ChannelTrim(formContext),
            "City": convertIfNull(formContext,"fermsapp_towncity"),
            "state": convertIfNull(formContext,"fermsapp_county"),
            //"country": GetlookupValue(formContext,"fermsapp_jacountry"),
            "country": "United Kingdom",
			"CourseTitle": GetlookupValue(formContext,"fermsapp_nasframeworkproduct"),
            "hours": GetTextForOptionSet(formContext,"fermsapp_jahoursstate"),
            "vacancy_type": GetTextForOptionSet(formContext,"fermsapp_type"),

            "ContactName": GetlookupValue(formContext,"ownerid"),
            "Employer_Description": convertIfNull(formContext,"fermsapp_employerdescription"),
            "JustApplyEmployerID": EMPID,
            "sector": GetTextForOptionSet(formContext,"fermsapp_jaindustry"),
            "Street1": convertIfNull(formContext,"fermsapp_address1"),
            "Street2": convertIfNull(formContext,"fermsapp_address2"),
            "Street3": convertIfNull(formContext,"fermsapp_address3"),
            "Website_URL": convertIfNull(formContext,"fermsapp_employerwebsite"),
            "salaryfrequency": GetTextForOptionSet(formContext,"fermsapp_jasalaryfrequency"),
            "Working_Hours": (convertIfNull(formContext,"fermsapp_weeklyworkinghours")).toString(),
            "ngtu_sectors": GetTextForOptionSet(formContext,"fermsapp_ngtusector"),
            "Personal_Qualities": convertIfNull(formContext,"fermsapp_idealcandidate"),
            "UJM_SOC": IfChannelSelected(formContext,"UJM", "fermsapp_soc"),
            "UJM_region": GetTextForOptionSet(formContext,"fermsapp_ujmstate"),
            "recruitinternationally": GetBooleanText(formContext,"fermsapp_ujmrecruitinternationally"),
            "Nationwide": GetBooleanText(formContext,"fermsapp_nationwide"),
            "Totaljob_Industry": GetTextForOptionSet(formContext,"fermsapp_totaljobs_industry"),
            "Total_Job_Region": GetTextForOptionSet(formContext,"fermsapp_totaljobs_region"),
			"reed_sector": GetValueForOptionSet(formContext,"fermsapp_reedsector"),
			"reed_sub_sector": GetValueForOptionSet(formContext,"fermsapp_reedsubsector"),
			"reed_credit_type": GetValueForOptionSet(formContext,"fermsapp_reedcredittype"),
            "careermap_category": GetValueForOptionSet(formContext,"fermsapp_careermap_category"),
            "careermap_jobtype": GetValueForOptionSet(formContext,"fob_careermap_jobtype"),
            "careermap_salary": GetValueForOptionSet(formContext,"fob_careermap_salary"),
            "careermap_level": GetValueForOptionSet(formContext,"fermsapp_careermap_level")

        }


        var encodejobdataadd =
          {


              "token": jobdata.token.toString().replace(/(\r\n|\r|\n)/g, '<br />'),
              "CRM_JobID": jobdata.CRM_JobID,

              "Jobtitle": jobdata.Jobtitle.replace(/(\r\n|\r|\n)/g, '<br />'),
              "post_code": jobdata.post_code.replace(/(\r\n|\r|\n)/g, '<br />'),
              "BriefDesc": jobdata.BriefDesc.replace(/(\r\n|\r|\n)/g, '<br />'),
              "DetailDesc": jobdata.DetailDesc.replace(/(\r\n|\r|\n)/g, '<br />'),
              "MainSkill": jobdata.MainSkill.replace(/(\r\n|\r|\n)/g, '<br />'),
              "industry": jobdata.industry.replace(/(\r\n|\r|\n)/g, '<br />'),
              "category": jobdata.category.replace(/(\r\n|\r|\n)/g, '<br />'),
              "JobOpening": jobdata.JobOpening,
              "Closing_Date": jobdata.Closing_Date.replace(/(\r\n|\r|\n)/g, '<br />'),
              //"Interview_Start_Date": jobdata.Interview_Start_Date.replace(/(\r\n|\r|\n)/g, '<br />'),
              "Possible_Start_Date": jobdata.Possible_Start_Date.replace(/(\r\n|\r|\n)/g, '<br />'),
              "Weekly_Wage": jobdata.Weekly_Wage,
              "Working_Week": jobdata.Working_Week,
              "Future_Prospects_Description": jobdata.Future_Prospects_Description.replace(/(\r\n|\r|\n)/g, '<br />'),
              "Vacancy_Location": jobdata.Vacancy_Location.replace(/(\r\n|\r|\n)/g, '<br />'),
              "Qualification_Required": jobdata.Qualification_Required.replace(/(\r\n|\r|\n)/g, '<br />'),
              "Important_Other_Information": jobdata.Important_Other_Information.replace(/(\r\n|\r|\n)/g, '<br />'),
              //"Reality_Check": jobdata.Reality_Check.replace(/(\r\n|\r|\n)/g, '<br />'),
              //"Framework_Type": jobdata.Framework_Type.replace(/(\r\n|\r|\n)/g, '<br />'),
			  "disability_confident_employer": String(jobdata.disability_confident_employer).replace(/(\r\n|\r|\n)/g, '<br />'),
			  "wage_type": jobdata.wage_type,
			  "wage_type_reason": jobdata.wage_type_reason,
			  "min_wage": jobdata.min_wage,
			  "max_salary": jobdata.max_salary,
			  "training_type": jobdata.training_type.replace(/(\r\n|\r|\n)/g, '<br />'),
			  "duration_type": jobdata.duration_type.replace(/(\r\n|\r|\n)/g, '<br />'),
              "Vacancy_Submitted_By": jobdata.Vacancy_Submitted_By.replace(/(\r\n|\r|\n)/g, '<br />'),
              "Question_One": jobdata.Question_One.replace(/(\r\n|\r|\n)/g, '<br />'),
              "Question_Two": jobdata.Question_Two.replace(/(\r\n|\r|\n)/g, '<br />'),
              "NAS_Framework": jobdata.NAS_Framework.replace(/(\r\n|\r|\n)/g, '<br />'),
              "Display_On_Carrier_Site": "Yes",
              "Applications_Instructions": jobdata.Applications_Instructions.replace(/(\r\n|\r|\n)/g, '<br />'),
              //"Small_Employer_Wage_Incentive": jobdata.Small_Employer_Wage_Incentive.replace(/(\r\n|\r|\n)/g, '<br />'),
              "Training_To_Be_Provided": jobdata.Training_To_Be_Provided.replace(/(\r\n|\r|\n)/g, '<br />'),
              "Expected_Duration": jobdata.Expected_Duration.toString().replace(/(\r\n|\r|\n)/g, '<br />'),
              //"Employer_Anonymous": jobdata.Employer_Anonymous.replace(/(\r\n|\r|\n)/g, '<br />'),
              //"Employer_Anonymous_Name": jobdata.Employer_Anonymous_Name.replace(/(\r\n|\r|\n)/g, '<br />'),

              "Chanel": jobdata.Chanel.replace(/(\r\n|\r|\n)/g, '<br />'),

              "City": jobdata.City.replace(/(\r\n|\r|\n)/g, '<br />'),
              "state": jobdata.state.replace(/(\r\n|\r|\n)/g, '<br />'),
              //"country": jobdata.country.replace(/(\r\n|\r|\n)/g, '<br />'),
              "country": "United Kingdom",//As per Michel it will always be UK.
			  "CourseTitle": jobdata.CourseTitle.replace(/(\r\n|\r|\n)/g, '<br />'),
              "hours": jobdata.hours,
              "vacancy_type": jobdata.vacancy_type.replace(/(\r\n|\r|\n)/g, '<br />'),

              "ContactName": jobdata.ContactName.replace(/(\r\n|\r|\n)/g, '<br />'),
              "Employer_Description": jobdata.Employer_Description.replace(/(\r\n|\r|\n)/g, '<br />'),
              "JustApplyEmployerID": jobdata.JustApplyEmployerID,
              "sector": jobdata.sector.replace(/(\r\n|\r|\n)/g, '<br />'),
              "Street1": jobdata.Street1.replace(/(\r\n|\r|\n)/g, '<br />'),
              "Street2": jobdata.Street2.replace(/(\r\n|\r|\n)/g, '<br />'),
              "Street3": jobdata.Street3.replace(/(\r\n|\r|\n)/g, '<br />'),
              "Website_URL": jobdata.Website_URL.replace(/(\r\n|\r|\n)/g, '<br />'),
              "salaryfrequency": jobdata.salaryfrequency.replace(/(\r\n|\r|\n)/g, '<br />'),
              "Working_Hours": (jobdata.Working_Hours.replace(/(\r\n|\r|\n)/g, '<br />')).toString(),
              "ngtu_sectors": jobdata.ngtu_sectors.replace(/(\r\n|\r|\n)/g, '<br />'),
              "Personal_Qualities": jobdata.Personal_Qualities.replace(/(\r\n|\r|\n)/g, '<br />'),
              "UJM_SOC": jobdata.UJM_SOC,
              "UJM_region": jobdata.UJM_region.replace(/(\r\n|\r|\n)/g, '<br />'),
              "recruitinternationally": jobdata.recruitinternationally.replace(/(\r\n|\r|\n)/g, '<br />'),
              "Nationwide": jobdata.Nationwide.replace(/(\r\n|\r|\n)/g, '<br />'),
              "Totaljob_Industry": jobdata.Totaljob_Industry.replace(/(\r\n|\r|\n)/g, '<br />'),
              "Total_Job_Region": jobdata.Total_Job_Region.replace(/(\r\n|\r|\n)/g, '<br />'),
			  "careermap_category": jobdata.careermap_category,
			  "careermap_jobtype": jobdata.careermap_jobtype,
			  "careermap_salary": jobdata.careermap_salary,
			  "careermap_level": jobdata.careermap_level,
				"reed_sector": jobdata.reed_sector,
				"reed_sub_sector": jobdata.reed_sub_sector,
				"reed_credit_type": jobdata.reed_credit_type
          }


        $.support.cors = true;
        // jQuery.ajax({
        $.ajax({
            type: "POST",
            async: false,
            data: "data=" + encodeURIComponent(JSON.stringify(jobdata)),
            //data: { data: jobdata },
            dataType: 'json',
            ContentType: "application/json; charset=utf-8",
            Accept: "application/json; charset=utf-8",

            // dataType: 'json',
            //            beforeSend: function (xhr) {
            //                xhr.setRequestHeader("Content-Type", "application/json");
            //                xhr.setRequestHeader("Accept", "text/json");
            //            },
            crossDomain: true,
            url: apiURL + method_add_job,
            //data: "data=" + encodeURIComponent(JSON.stringify(jobdata)),
            success: function (responsedata) {
                try {
                    if (responsedata.status != "false") {
                        debugger;
                        var vacancy_id = responsedata.results[0].justApplyRefId;
                        var vacancy_url = responsedata.results[0].vacancy_url;


                        var Nasrefernce = "";
                        if (responsedata.results != undefined && responsedata.results != null && responsedata.results[0].NAS_ReferenceNumber != undefined) {
                            Nasrefernce = responsedata.results[0].NAS_ReferenceNumber;
                        }
                        //  alert(vacancy_id);
                        //
						if (responsedata.results != undefined && responsedata.results != null && responsedata.results[0].NAS_Error != undefined) {
                         NasError = responsedata.results[0].NAS_Error;
                        alert("NAS Posting Failied - Error :" + NasError);
						var date = new Date().toLocaleDateString("en-UK");
						var errorMessage = date + " : " + NasError;
						formContext.getAttribute("fermsapp_naserror").setValue(errorMessage);
						formContext.getAttribute("fermsapp_naserror").setSubmitMode("always");
                    }



                        formContext.getAttribute("fermsapp_referencenumber").setSubmitMode("always");
                        formContext.getAttribute("fermsapp_nasreference").setSubmitMode("always");
                        formContext.getAttribute("fermsapp_vacancyurl").setSubmitMode("always");
                        formContext.getAttribute("fermsapp_postedtojustapply").setSubmitMode("always");
                        formContext.getAttribute("fermsapp_postedtonas").setSubmitMode("always");
                        formContext.getAttribute("fermsapp_edsurn").setSubmitMode("always");
                        //
                        formContext.getAttribute("fermsapp_referencenumber").setValue(vacancy_id);

                        //  Xrm.Page.getAttribute("fermsapp_vacancyurl").setValue(vacancy_url.replace('hit.justapply.uk/', 'hittraining.justapply.co.uk/ViewVacancy.aspx?'));
                        formContext.getAttribute("fermsapp_vacancyurl").setValue(vacancy_url);
                        formContext.getAttribute("fermsapp_nasreference").setValue(Nasrefernce);
                        //var currentdate = new Date();
                        //var year = currentdate.getFullYear() + "";
                        //var month = (currentdate.getMonth() + 1) + "";
                        //var day = currentdate.getDate() + "";
                        //var dateFormat1 = day + "-" + month + "-" + year;
                        //var dateFormat1 = new Date(day, month, year);
                        //var dateFormat1 = new Date(currentdate.getMonth() + 1 + "/" + today.getDate() + "/" + today.getFullYear());
                        formContext.getAttribute("fermsapp_postedtojustapply").setValue(new Date());


                        var getNasRefValue = formContext.getAttribute("fermsapp_nasreference").getValue();
                        if (getNasRefValue != null) {

                            formContext.getAttribute("fermsapp_postedtonas").setValue(new Date());
                        }
                        alert(responsedata.msg);
                        formContext.data.save();
                        //Xrm.Page.data.entity.save();

                    } else {
                        alert(responsedata.msg);
                    }
                    Flag = 0;
                    document.all.msgDiv.style.visibility = 'hidden';
                }
                catch (e) {
                    document.all.msgDiv.style.visibility = 'hidden';
                    Flag = 0;
                    alert(e.Message);
                }
            }
        });
    }
    catch (e) {
        document.all.msgDiv.style.visibility = 'hidden';
        Flag = 0;
        alert(e.Message);
    }
    Flag = 0;
}
//$.ajax({
//    type: "POST",
//    url: apiURL + method_add_job,
//    data: "data=" + JSON.stringify(jobdata),
//    dataType: 'jsonp',
//    success: function (responsedata) {
//        if (responsedata.status != "false") {
//            vacancy_id = responsedata.results[0].justApplyRefId;
//        } else {
//            alert(responsedata.msg)
//        }
//    }
//});

function editJob(formContext) {
    debugger;
    // if (authenticationToken == null)
    //{ getAccessTokenForSiverLight(); }
    try {
        var jobdata = {
            "justApplyRefId": convertIfNull(formContext,"fermsapp_referencenumber"),
            "token": authenticationToken,
            "CRM_JobID": formContext.getAttribute("fermsapp_jajobreference").getValue(),

            "Jobtitle": convertIfNull(formContext,"fermsapp_description"),
            "post_code": convertIfNull(formContext,"fermsapp_postcode"),
            "BriefDesc": convertIfNull(formContext,"fermsapp_vacancyshortdescription"),
            "DetailDesc": convertIfNull(formContext,"fermsapp_roleresponsibilities"),
            "MainSkill": convertIfNull(formContext,"fermsapp_skillsrequired"),
            "industry": GetTextForOptionSet(formContext,"fermsapp_jaindustry"),
            "category": GetTextForOptionSet(formContext,"fermsapp_jacategory"),
            "JobOpening": convertIfNull(formContext,"fermsapp_volume"),
            "Closing_Date": ConvertDateFormat(formContext,"fermsapp_jaapplicationdeadlinedate"),
            //"Interview_Start_Date": ConvertDateFormat("fermsapp_anticipatedinterviewdate"),
            "Possible_Start_Date": ConvertDateFormat(formContext,"fermsapp_anticipatedstartdate"),
            "Weekly_Wage": convertIfNull(formContext,"fermsapp_fixedwage"),
            "Working_Week": convertIfNull(formContext,"fermsapp_workinghours"),
            "Future_Prospects_Description": convertIfNull(formContext,"fermsapp_longtermcompanyaimsforlearner"),
            "Vacancy_Location": GetTextForOptionSet(formContext,"fermsapp_locationtype"),
            "Qualification_Required": convertIfNull(formContext,"fermsapp_qualificationsrequired"),
            "Important_Other_Information": convertIfNull(formContext,"fermsapp_importantotherinformation"),
            //"Reality_Check": convertIfNull("fermsapp_realitycheck"),
            //"Framework_Type": GetTextForOptionSet("fermsapp_nasframeworktype"),
			"disability_confident_employer": GetTextForOptionSet(formContext,"fermsapp_disabilityconfidentemployer"),
			"wage_type": GetValueForOptionSet(formContext,"fermsapp_wagetype"),
			"wage_type_reason": convertIfNull(formContext,"fermsapp_wagetypereason"),
			"min_wage": convertIfNull(formContext,"fermsapp_jaminsalary"),
			"max_salary": convertIfNull(formContext,"fermsapp_jamaxsalary"),
			"training_type": GetTextForOptionSet(formContext,"fermsapp_trainingtype"),
			"duration_type": GetTextForOptionSet(formContext,"fermsapp_durationtype"),
            "Vacancy_Submitted_By": convertIfNull(formContext,"fermsapp_vacancysubmittedby"),
            "Question_One": convertIfNull(formContext,"fermsapp_vacancyquestion1"),
            "Question_Two": convertIfNull(formContext,"fermsapp_vacancyquestion2"),
            "NAS_Framework": convertIfNull(formContext,"fermsapp_nasframeworkcode"),
            "Display_On_Carrier_Site": "Yes",
            "Applications_Instructions": convertIfNull(formContext,"fermsapp_applicationsinstructions"),
            //"Small_Employer_Wage_Incentive": GetBooleanText(formContext,"fermsapp_jasmallemployerwageincentive"),
            "Training_To_Be_Provided": convertIfNull(formContext,"fermsapp_trainingtobeprovided"),
            "Expected_Duration": convertIfNull(formContext,"fermsapp_expectedduration"),
            //"Employer_Anonymous": GetBooleanText(formContext,"fermsapp_eemployeranonymous"),
            //"Employer_Anonymous_Name": convertIfNull(formContext,"fermsapp_employeranonymousname"),

            "Chanel": ChannelTrim(formContext),

            "City": convertIfNull(formContext,"fermsapp_towncity"),
            "state": convertIfNull(formContext,"fermsapp_county"),
            //"country": GetlookupValue(formContext,"fermsapp_jacountry"),
            "country": "United Kingdom",
		    "CourseTitle": GetlookupValue(formContext,"fermsapp_nasframeworkproduct"),
            "hours": GetTextForOptionSet(formContext,"fermsapp_jahoursstate"),
            "vacancy_type": GetTextForOptionSet(formContext,"fermsapp_type"),

            "ContactName": GetlookupValue(formContext,"ownerid"),
            "Employer_Description": convertIfNull(formContext,"fermsapp_employerdescription"),
            "JustApplyEmployerID": EMPID,
            "sector": GetTextForOptionSet(formContext,"fermsapp_jaindustry"),
            "Street1": convertIfNull(formContext,"fermsapp_address1"),
            "Street2": convertIfNull(formContext,"fermsapp_address2"),
            "Street3": convertIfNull(formContext,"fermsapp_address3"),
            "Website_URL": convertIfNull(formContext,"fermsapp_employerwebsite"),
            "salaryfrequency": GetTextForOptionSet(formContext,"fermsapp_jasalaryfrequency"),
            "Working_Hours": (convertIfNull(formContext,"fermsapp_weeklyworkinghours")).toString(),
            "ngtu_sectors": GetTextForOptionSet(formContext,"fermsapp_ngtusector"),
            "Personal_Qualities": convertIfNull(formContext,"fermsapp_idealcandidate"),
            "UJM_SOC": IfChannelSelected(formContext,"UJM", "fermsapp_soc"),
            "UJM_region": GetTextForOptionSet(formContext,"fermsapp_ujmstate"),
            "recruitinternationally": GetBooleanText(formContext,"fermsapp_ujmrecruitinternationally"),
            "Nationwide": GetBooleanText(formContext,"fermsapp_nationwide"),
            "Totaljob_Industry": GetTextForOptionSet(formContext,"fermsapp_totaljobs_industry"),
            "Total_Job_Region": GetTextForOptionSet(formContext,"fermsapp_totaljobs_region"),
			"reed_sector": GetValueForOptionSet(formContext,"fermsapp_reedsector"),
			"reed_sub_sector": GetValueForOptionSet(formContext,"fermsapp_reedsubsector"),
			"reed_credit_type": GetValueForOptionSet(formContext,"fermsapp_reedcredittype"),
            "careermap_category": GetValueForOptionSet(formContext,"fermsapp_careermap_category"),
            "careermap_jobtype": GetValueForOptionSet(formContext,"fob_careermap_jobtype"),
            "careermap_salary": GetValueForOptionSet(formContext,"fob_careermap_salary"),
            "careermap_level": GetValueForOptionSet(formContext,"fermsapp_careermap_level")
        }



        var encodejobdata =
            {
                "justApplyRefId": jobdata.justApplyRefId,
                "token": jobdata.token.replace(/(\r\n|\r|\n)/g, '<br />'),
                "CRM_JobID": jobdata.CRM_JobID,

                "Jobtitle": jobdata.Jobtitle.replace(/(\r\n|\r|\n)/g, '<br />'),
                 "post_code": jobdata.post_code.replace(/(\r\n|\r|\n)/g, '<br />'),
                "BriefDesc": jobdata.BriefDesc.replace(/(\r\n|\r|\n)/g, '<br />'),
                "DetailDesc": jobdata.DetailDesc.replace(/(\r\n|\r|\n)/g, '<br />'),
                "MainSkill": jobdata.MainSkill.replace(/(\r\n|\r|\n)/g, '<br />'),
                "industry": jobdata.industry.replace(/(\r\n|\r|\n)/g, '<br />'),
                "category": jobdata.category.replace(/(\r\n|\r|\n)/g, '<br />'),
                "JobOpening": jobdata.JobOpening,
                "Closing_Date": jobdata.Closing_Date.replace(/(\r\n|\r|\n)/g, '<br />'),
                //"Interview_Start_Date": jobdata.Interview_Start_Date.replace(/(\r\n|\r|\n)/g, '<br />'),
                "Possible_Start_Date": jobdata.Possible_Start_Date.replace(/(\r\n|\r|\n)/g, '<br />'),
                "Weekly_Wage": jobdata.Weekly_Wage,
                "Working_Week": jobdata.Working_Week,
                "Future_Prospects_Description": jobdata.Future_Prospects_Description.replace(/(\r\n|\r|\n)/g, '<br />'),
                "Vacancy_Location": jobdata.Vacancy_Location.replace(/(\r\n|\r|\n)/g, '<br />'),
                "Qualification_Required": jobdata.Qualification_Required.replace(/(\r\n|\r|\n)/g, '<br />'),
                "Important_Other_Information": jobdata.Important_Other_Information.replace(/(\r\n|\r|\n)/g, '<br />'),
                //"Reality_Check": jobdata.Reality_Check.replace(/(\r\n|\r|\n)/g, '<br />'),
                //"Framework_Type": jobdata.Framework_Type.replace(/(\r\n|\r|\n)/g, '<br />'),
				"disability_confident_employer": jobdata.disability_confident_employer.replace(/(\r\n|\r|\n)/g, '<br />'),
			    "wage_type": jobdata.wage_type,
			    "wage_type_reason": jobdata.wage_type_reason,
			    "max_salary": jobdata.max_salary,
			    "min_wage": jobdata.min_wage,
			    "training_type": jobdata.training_type.replace(/(\r\n|\r|\n)/g, '<br />'),
			    "duration_type": jobdata.duration_type.replace(/(\r\n|\r|\n)/g, '<br />'),
                "Vacancy_Submitted_By": jobdata.Vacancy_Submitted_By.replace(/(\r\n|\r|\n)/g, '<br />'),
                "Question_One": jobdata.Question_One.replace(/(\r\n|\r|\n)/g, '<br />'),
                "Question_Two": jobdata.Question_Two.replace(/(\r\n|\r|\n)/g, '<br />'),
                "NAS_Framework": jobdata.NAS_Framework.replace(/(\r\n|\r|\n)/g, '<br />'),
                "Display_On_Carrier_Site": "Yes",
                "Applications_Instructions": jobdata.Applications_Instructions.replace(/(\r\n|\r|\n)/g, '<br />'),
                //"Small_Employer_Wage_Incentive": jobdata.Small_Employer_Wage_Incentive.replace(/(\r\n|\r|\n)/g, '<br />'),
                "Training_To_Be_Provided": jobdata.Training_To_Be_Provided.replace(/(\r\n|\r|\n)/g, '<br />'),
                "Expected_Duration": jobdata.Expected_Duration.toString().replace(/(\r\n|\r|\n)/g, '<br />'),
                //"Employer_Anonymous": jobdata.Employer_Anonymous.replace(/(\r\n|\r|\n)/g, '<br />'),
                //"Employer_Anonymous_Name": jobdata.Employer_Anonymous_Name.replace(/(\r\n|\r|\n)/g, '<br />'),

                "Chanel": jobdata.Chanel.replace(/(\r\n|\r|\n)/g, '<br />'),

                "City": jobdata.City.replace(/(\r\n|\r|\n)/g, '<br />'),
                "state": jobdata.state.replace(/(\r\n|\r|\n)/g, '<br />'),
                //"country": jobdata.country.replace(/(\r\n|\r|\n)/g, '<br />'),
                "country": "United Kingdom",
				 "CourseTitle": jobdata.CourseTitle.replace(/(\r\n|\r|\n)/g, '<br />'),
                "hours": jobdata.hours,
                "vacancy_type": jobdata.vacancy_type.replace(/(\r\n|\r|\n)/g, '<br />'),

                "ContactName": jobdata.ContactName.replace(/(\r\n|\r|\n)/g, '<br />'),
                "Employer_Description": jobdata.Employer_Description.replace(/(\r\n|\r|\n)/g, '<br />'),
                "JustApplyEmployerID": jobdata.JustApplyEmployerID,
                "sector": jobdata.sector.replace(/(\r\n|\r|\n)/g, '<br />'),
                "Street1": jobdata.Street1.replace(/(\r\n|\r|\n)/g, '<br />'),
                "Street2": jobdata.Street2.replace(/(\r\n|\r|\n)/g, '<br />'),
                "Street3": jobdata.Street3.replace(/(\r\n|\r|\n)/g, '<br />'),
                "Website_URL": jobdata.Website_URL.replace(/(\r\n|\r|\n)/g, '<br />'),
                "salaryfrequency": jobdata.salaryfrequency.replace(/(\r\n|\r|\n)/g, '<br />'),
                "Working_Hours": (jobdata.Working_Hours.replace(/(\r\n|\r|\n)/g, '<br />')).toString(),
                "ngtu_sectors": jobdata.ngtu_sectors.replace(/(\r\n|\r|\n)/g, '<br />'),
                "Personal_Qualities": jobdata.Personal_Qualities.replace(/(\r\n|\r|\n)/g, '<br />'),
                "UJM_SOC": jobdata.UJM_SOC,
                "UJM_region": jobdata.UJM_region.replace(/(\r\n|\r|\n)/g, '<br />'),
                "recruitinternationally": jobdata.recruitinternationally.replace(/(\r\n|\r|\n)/g, '<br />'),
                "Nationwide": jobdata.Nationwide.replace(/(\r\n|\r|\n)/g, '<br />'),
                "Totaljob_Industry": jobdata.Totaljob_Industry.replace(/(\r\n|\r|\n)/g, '<br />'),
                "Total_Job_Region": jobdata.Total_Job_Region.replace(/(\r\n|\r|\n)/g, '<br />'),
				"careermap_category": jobdata.careermap_category,
				"careermap_jobtype": jobdata.careermap_jobtype,
				"careermap_salary": jobdata.careermap_salary,
				"careermap_level": jobdata.careermap_level,
				"reed_sector": jobdata.reed_sector,
				"reed_sub_sector": jobdata.reed_sub_sector,
				"reed_credit_type": jobdata.reed_credit_type

            }
        $.support.cors = true;
        $.ajax({
            type: "POST",
            url: apiURL + method_edit_job,
            data: "data=" + encodeURIComponent(JSON.stringify(jobdata)),
            //data: { data: jobdata },
            dataType: 'json',
            "Content-Type": "application/json; charset=utf-8",
            Accept: "application/json",

            crossDomain: true,
            async: false,
            //            beforeSend: function (xhr) {
            //                xhr.setRequestHeader("Content-Type", "application/json");
            //                xhr.setRequestHeader("Accept", "text/json");
            //            },
            success: function (responsedata) {
                if (responsedata.status != "false") {

                    var Nasrefernce="";
					var NasError="";
                    if (responsedata.results != undefined && responsedata.results != null && responsedata.results[0].NAS_ReferenceNumber != undefined) {
                         Nasrefernce = responsedata.results[0].NAS_ReferenceNumber;
                        alert("Nas Ref :" + Nasrefernce);

                    }
					if (responsedata.results != undefined && responsedata.results != null && responsedata.results[0].NAS_Error != undefined) {
                         NasError = responsedata.results[0].NAS_Error;

						 alert("NAS Posting Failied - Error :" + NasError);
						var date = new Date().toLocaleDateString("en-UK");
						var errorMessage = date + " : " + NasError;
						formContext.getAttribute("fermsapp_naserror").setValue(errorMessage);
						formContext.getAttribute("fermsapp_naserror").setSubmitMode("always");
                    }
                        formContext.getAttribute("fermsapp_nasreference").setSubmitMode("always");
                        formContext.getAttribute("fermsapp_nasreference").setValue(Nasrefernce);
                        //document.getElementById("header_fermsapp_nasreference_d").childNodes[0].innerText = Nasrefernce;
                        formContext.getAttribute("fermsapp_postedtojustapply").setSubmitMode("always");
                    //    var currentdate = new Date();
                    //    var year = currentdate.getFullYear() + "";
                    //    var month = (currentdate.getMonth() + 1) + "";
                    //    var day = currentdate.getDate() + "";
                    //var dateFormat1 = day + "-" + month + "-" + year;
                        //var dateFormat1 = new Date(day, month, year);
                       // var dateFormat1 = new Date(currentdate.getMonth() + 1 + "/" + today.getDate() + "/" + today.getFullYear());
                       formContext.getAttribute("fermsapp_postedtojustapply").setValue(new Date());
                        var posttoja = formContext.getAttribute("fermsapp_postedtojustapply").getValue();
                        var getNasRefValue = formContext.getAttribute("fermsapp_nasreference").getValue();
                        if (getNasRefValue != null) {
                            var postedToNAS = formContext.getAttribute("fermsapp_postedtonas").getValue();
                            if (postedToNAS == null) {
                                formContext.getAttribute("fermsapp_postedtonas").setSubmitMode("always");
                                formContext.getAttribute("fermsapp_postedtonas").setValue(new Date());
                                //var postedToNASDate = Xrm.Page.getAttribute("fermsapp_postedtonas").getValue();
                                //var postedToNASDate_header = formattedDate(postedToNASDate);
                                // document.getElementById("header_fermsapp_postedtonas_d").childNodes[0].innerText = postedToNASDate_header;
                            }
                        }
                        alert(responsedata.msg);
                        formContext.data.save();
                }
                else {
                    alert(responsedata.msg);
                }
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                debugger;
                Flag = 0;
                //alert("Error: Edit.Ajax");
            }
        });

    }
    catch (e) {
        document.all.msgDiv.style.visibility = 'hidden';
        Flag = 0;
        alert("Error : " + e.Message);
    }
    document.all.msgDiv.style.visibility = 'hidden';
    Flag = 0;
}

function createRequestObjectForAPI() {
    try {
        var xmlhttp;
        if (window.XMLHttpRequest) {
            // code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        }
        else {
            // code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }

        return xmlhttp;
    }
    catch (err) {
        alert('createRequestObject: ' + err.description);
    }
}

///////get country value//// state
function GetlookupValue(formContext,lookupval) {

    debugger;
    // var lookupObject = Xrm.Page.getAttribute("fermsapp_jacountry");
    var lookupObject = formContext.getAttribute(lookupval);
    if (lookupObject != null) {
        var lookUpObjectValue = lookupObject.getValue();
        if ((lookUpObjectValue != null)) {
            var lookupname = lookUpObjectValue[0].name;
            return lookupname;
        }
        else {
            return "";
        }

    }
    else {
        return "";
    }

}

function retrieveMultipleSyncPostVacancy(entity, filter, fields) {
    try {
        let globalContext = Xrm.Utility.getGlobalContext();
        var req = createRequestObject();
        var oDataSelect = globalContext.getClientUrl()  + "/api/data/v9.1/" + entity + "?$select=" + fields + "&$filter=" + filter;
        req.open('GET', oDataSelect, false);
        req.setRequestHeader("OData-MaxVersion", "4.0");
        req.setRequestHeader("OData-Version", "4.0");
        req.setRequestHeader("Accept", "application/json");
        req.setRequestHeader("Content-Type", "application/json; charset=utf-8");
        req.send();


        if (JSON.parse(req.responseText) && JSON.parse(req.responseText).value) {
            return JSON.parse(req.responseText).value;
        }
        else {
            return null;
        }
    }
    catch (err) {
        alert('retrieveMultipleSync For PostVacancy: ' + err.description);
    }
}


function retrieveSyncRecord(entity, id) {
    try {
        var globalContext = Xrm.Utility.getGlobalContext();
        var serverUrl=globalContext.getClientUrl();
        //var context = Xrm.Page.context;
        //var serverUrl= context.getClientUrl();
        var req = createRequestObjectForAPI();
        var oDataSelect = serverUrl + "/XRMServices/2011/OrganizationData.svc/" + entity + "(guid'" + id + "')";
        req.open('GET', oDataSelect, false);
        req.setRequestHeader("Accept", "application/json");
        req.setRequestHeader("Content-Type", "application/json; charset=utf-8");
        req.send();

        if (JSON.parse(req.responseText).d !== undefined) {
            //a   alert(JSON.parse(req.responseText).d);
            return JSON.parse(req.responseText).d;
        }
        else {
            return null;
        }
    }
    catch (err) {
        alert('retrieveSync: ' + err.description);
    }
}



function createRequestObjectForAPI() {
    try {
        var xmlhttp;
        if (window.XMLHttpRequest) {
            // code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        }
        else {
            // code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }

        return xmlhttp;
    }
    catch (err) {
        alert('createRequestObject: ' + err.description);
    }
}

function fillVacancyData(executionContext) {
        var formContext=null;
    if(executionContext.getFormContext){formContext = executionContext.getFormContext(); }
    else{formContext=executionContext}
    //debugger;
    //  debugger;
    var jobdata = {
        "justApplyRefId": formContext.getAttribute("fermsapp_referencenumber").getValue(),
        "token": authenticationToken
    }

    GetAuthenticationInfo();
    //  getEMPIDFromAccount();
    //        jobdata.JustApplyEmployerID = EMPID,
    jobdata.UserName = JustApplyUsername;
    jobdata.Password = JustApplyPassword;

    return jobdata;

} // JScript source code// JScript source code


////get channels and remove last char ,
function ChannelTrim(formContext) {
    var phrase = GetAllChannelsValue(formContext);
    if (phrase != null) {
        var rephrase = "";
        if (phrase != null && phrase.length > 1) {
            rephrase = phrase.substring(0, phrase.length - 1);
        }
        return rephrase;
    }
} // JScript source code


///If any channeld selected then only it will retun value the fields affecting it
function IfChannelSelected(formContext,ChannelName, fieldname) {
    if (formContext.getAttribute("fermsapp_ujm")) {
        return convertIfNull(formContext,fieldname);
    }
    else {
        return "";
    }
}
function formattedDate(date) {
    var d = new Date(date || Date.now()),
       day = '' + d.getDate(),
         month = '' + (d.getMonth() + 1),
        year = d.getFullYear();

    if (month.length < 2) month = '0' + month;
    if (day.length < 2) day = '0' + day;

    return [day, month, year].join('/');
} // JScript source code

// JavaScript source code
