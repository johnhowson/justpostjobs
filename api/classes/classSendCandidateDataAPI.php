<?php
require_once ("Rest.inc.php");
require_once ("classSendtocrmapi.php");
require_once ("apifunctions.php");
use api\classes\REST;

class sendcandidatedatatocrm extends sendtocrmapi
{

    public $jobtype;

    private $fields = array(
        "firstname" => "",
        "lastname" => "",
        "mobilephone" => "",
        "emailaddress1" => "",
        "birthdate" => "",
        "address1_line1" => "",
        "address1_city" => "",
        "address1_line2" => "",
        "address1_stateorprovince" => "",
        "address1_line3" => "",
        "address1_postalcode" => "",
        "fermsapp_fermsapp_holdhnd" => "",
        "fermsapp_unemployed" => "",
        "fermsapp_question1" => "",
        "feees_ukeeapast3years" => "",
        "fermsapp_accountopenfor12months" => "",
        "fermsapp_receiveotherinfo" => "",
        "password" => "",
        "confirmpassword" => "",
        "us_userid" => "",
        "dateadded" => "",
        "lastupdated" => "",
        "candidateprofilecomplete" => ""
    );

    private $data;

    /**
     *
     * @param integer $jobid            
     * @param string $token            
     * @return string
     */
    private function getJobID($jobid, $token)
    {
        global $db;
        $sql = "select CRM_JobID from ja_jobs where jobid='$jobid'";
        $rr = $db->query($sql)->first();
        $CRM_JobID = $rr->CRM_JobID;
        // http_build_query can be used
        $ch = curl_init();
        $url = $this->resource . $this->vacUrl . '?$select=fermsapp_vacanciesid' . "&" . '$filter=fermsapp_jajobreference' . "%20eq%20%27{$CRM_JobID}%27";
        $headers = $this->prepareHeader($token);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        $fields = json_decode($data);
        return $fields->value[0]->fermsapp_vacanciesid;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function getOutputdata()
    {
        return $this->data;
    }

    /**
     * map fields for CRM based on ja_person table
     * 
     * @param object $user            
     * @param object $rr
     *            // candidate
     * @return string[]
     */
    private function mapfields($user, $rr)
    {
        $fields = $this->getFields();
        /**
         * "firstname"=>"","lastname"=>"","mobilephone"=>"","emailaddress1"=>"","birthdate"=>"","address1_line1"=>"",
         * "address1_city"=>"","address1_line2"=>"","address1_stateorprovince"=>"","address1_line3"=>"","address1_postalcode"=>"",
         * "fermsapp_fermsapp_holdhnd"=>"","fermsapp_unemployed"=>"","fermsapp_question1"=>"","feees_ukeeapast3years"=>"",
         * "fermsapp_accountopenfor12months"=>"",
         * "fermsapp_receiveotherinfo"=>"","password"=>"","confirmpassword"=>"",
         * "us_userid"=>"","dateadded"=>"","lastupdated"=>"","candidateprofilecomplete"=>""
         */
        $fields["firstname"] = $user->fname;
        $fields["lastname"] = $user->lname;
        $fields["mobilephone"] = $rr->mobile;
        $fields["address1_line1"] = $rr->address1;
        $fields["address1_city"] = $rr->towncity;
        $fields["address1_line2"] = $rr->address2;
        $fields["address1_stateorprovince"] = $rr->county;
        $fields["address1_line3"] = $rr->address3;
        $fields["address1_postalcode"] = $rr->postcode;
        $date2 = new DateTime($rr->dateofbirth);
        $fields["birthdate"] = $date2->format('Y-m-d');
        $fields["fermsapp_fermsapp_holdhnd"] = $rr->holdhndordegree;
        $fields["fermsapp_unemployed"] = $rr->supplementaryquestion1;
        $fields["fermsapp_question1"] = $rr->fulltimeeducation;
        $fields["feees_ukeeapast3years"] = $rr->livedinukthreeyrs;
        $fields["fermsapp_accountopenfor12months"] = $rr->keepaccountopen;
        $fields["fermsapp_receiveotherinfo"] = $rr->interestinginformation;
        unset($fields["password"]);
        unset($fields["confirmpassword"]);
        unset($fields["us_userid"]);
        unset($fields["dateadded"]);
        unset($fields["lastupdated"]);
        unset($fields["candidateprofilecomplete"]);
        return $fields;
    }

    /**
     * add log entries for the submission
     * 
     * @param string $personid            
     * @param string $CRMcontactID            
     * @param string $CRM_JobID            
     * @param string $longCRM_JobID            
     * @param int $us_userid            
     * @param object $user            
     */
    private function addlogentries($personid,$jobid, $CRMcontactID, $longCRM_JobID, $us_userid, $user)
    {
        $message = "sent a $this->jobtype application to CRM.\n ";
        $message .= "Details personid $personid contact id: $CRMcontactID jobid: $jobid Long CRM Job ID: $longCRM_JobID \n";
        $message .= "user: id $us_userid first name: " . $user->fname . " last name: " . $user->lname;
        saveLog($message);
        saveLog("crm output data: " . print_r($this->getOutputdata(), true));
    }

    private function updatepersontable($personid, $crm_contactid)
    {
        global $db;
        $fields=array();
        $fields["lastupdated"]=date('Y-m-d H:i:s');
        $fields["senttocrm"]=1;
        $fields["senttocrmDate"]=date('Y-m-d H:i:s');
        $fields["crmContactID"]=$crm_contactid;
        $table="ja_person";
        $where=array("personid"=>$personid);
        $db->update($table,$where,$fields);
        return $db->count();
    }

    private function updateapplicationtable($personid, $crm_contactid, $jobid, $CRM_JobID)
    {
        global $db;
        $this->updatepersontable($personid, $crm_contactid);
        $fields=array();
        $fields["lastupdated"]=date('Y-m-d H:i:s');
        $fields["senttocrm"]=1;
        $fields["status"]="applied";
        $fields["senttocrmDate"]=date('Y-m-d H:i:s');
        $table="ja_jobApplications";
        $where=array("personid"=>$personid,"jobid"=>$jobid);
        $db->update($table,$where,$fields);
    }

    /**
     * macro function for sending data for traineeship form
     * 
     * @param int $candidateid            
     * @param int $jobid            
     * @return string
     */
    public function collectandsendcandidatedata($candidateid, $jobid)
    {
        global $db, $settings;
        $candidate = new candidate();
        // make sure cv file is not blanked
        unset($candidate->cvfilename);
        $candidatearray = array_keys((array) $candidate);
        // get the candidate info from candidate
        $sql = makeselect($candidatearray, "ja_person", "personid", $candidateid);
        $rr = $db->query($sql)->first();
        if (isset($rr->us_userid)) {
            $us_userid = $rr->us_userid;
            $sql = "select fname,lname,email from users where id='$us_userid'";
            $user = $db->query($sql)->first();
            
            $access_token = $this->initialise();
            /**
             * get the jobid from crm
             * this will only be needed if it is not on the database already
             */
            $longCRM_JobID = $this->getJobID($jobid, $access_token);
            $sql = "select answer1,answer2 from ja_jobApplications where candidateid='$candidateid' and jobid='$jobid'";
            $answers = $db->query($sql)->first();
            $this->resource = $settings->vsfurl;
            $fields = $this->mapfields($user, $rr);
            $this->settraineeshipfields($fields);
            $CRMcontactID = $this->sendtraineeshipdatatoCRM($access_token);
            /* at this point if there is no job to send return */
            if ($jobid == 0) {
                $this->updatepersontable($candidateid, $CRMcontactID);
                return "candidate added";
            }
            $result = $this->sendapplicationtoCRM($longCRM_JobID, $CRMcontactID, $answers->answer1, $answers->answer2, $access_token);
            if ($result == "OK") {
                /* update the database with the contact id and to say job sent */
                $this->addlogentries($candidateid,$jobid, $CRMcontactID, $longCRM_JobID, $us_userid, $user);
                $this->updateapplicationtable($candidateid, $CRMcontactID, $jobid, $longCRM_JobID);
                return "candidate and application saved to CRM";
            }
            return "completed with error";
            // get the candidate ID
            // save the application to CRM
        } else {
            /* error not found */
            sendErrEmail("User not found on JustApply or database issue", "personid: $candidateid jobid $jobid");
            return "user not found";
        }
    }

    /**
     * publish the current application to CRM
     * 
     * @param string $crm_jobid            
     * @param string $crm_contactid            
     */
    public function sendapplicationtoCRM($crm_jobid, $crm_contactid, $answer1, $answer2, $token)
    {
        $fields = array();
        $fields['fermsapp_applicationsid@odata.bind'] = "/contacts($crm_contactid)";
        $fields['fermsapp_vacancy@odata.bind'] = "/fermsapp_vacancieses({$crm_jobid})";
        $fields['fermsapp_answer1'] = $answer1;
        $fields['fermsapp_answer2'] = $answer2;
        $this->applicationfields = $fields;
        $data = $this->sendrequest($token, $this->applicationUrl, "sendapplication");
        if ($data == false) {
            saveLog("A CRM error occurred data: " . $this->errormessages[6] . print_r($fields, true));
            sendErrEmail("A CRM error occurred data sending an application to CRM", $this->errormessages[6] . print_r($fields, true));
            return $this->errormessages[6];
        }
        $this->data = $data;
        return "OK";
    }

    /**
     * initialise fields
     * 
     * @param object $fields            
     */
    public function settraineeshipfields($fields)
    {
        settype($fields["fermsapp_fermsapp_holdhnd"], "boolean");
        settype($fields["fermsapp_unemployed"], "boolean");
        settype($fields["feees_ukeeapast3years"], "boolean");
        settype($fields["fermsapp_accountopenfor12months"], "boolean");
        settype($fields["fermsapp_receiveotherinfo"], "boolean");
        settype($fields["fermsapp_question1"], "boolean");
        $this->contactfields["fermsapp_contacttype"] = 9;
        settype($this->contactfields["fermsapp_contacttype"], "integer");
        $this->traineefields = $fields;
    }

    /**
     * Send the traineeship to CRM
     * 
     * @param sting $access_token            
     * @return string
     */
    public function sendtraineeshipdatatoCRM($access_token)
    {
        
        // saveLog("Data prepared to sent to CRM " .print_r($this->traineefields,true));
        $data = $this->sendrequest($access_token, $this->contactUrl, "createtraineeship");
        if ($data == false) {
            sendErrEmail("an error occurred sending data to CRM", print_r($this->traineefields, true));
            return $this->errormessages[5];
        }
        $contactid = $data->contactid;
        saveLog("Data successfully sent to CRM " . print_r($data, true));
        return $contactid;
    }
}
