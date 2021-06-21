<?php
/**
 * class to send data to CRM API
 */
require_once ("Rest.inc.php");
require_once ("classfieldtranslations.php");
require_once ("apifunctions.php");
use api\classes\REST;
class sendtocrmapi
{
    public $TenantId = "0cc995bb-7ddd-40d6-8558-367ccb30896e";
    public $ClientId = "946e9de0-f46a-41cb-9a54-ff151f8f6cc3";
    private $Secret = "5rOv1~0e-1vS22q~i78VN84.-LTj_7nnDj";
    public $grant_type = "client_credentials";
    public $resource = "https://justapply.crm4.dynamics.com/";
    public $vacUrl = "api/data/v9.1/fermsapp_vacancieses";
    public $acUrl = "api/data/v9.1/accounts";
    public $applicationUrl="api/data/v9.1/fermsapp_applications";
    public $contactUrl = "api/data/v9.1/contacts";
    private $authpostfields;
    private $output = "";
    protected $companyfields;
    protected $contactfields;
    protected $traineefields;
    protected $applicationfields;
    protected $jobsfields;
    protected $JobArray;
    protected $errormessages = array(
        0 => "Error connecting to CRM, please check to see that the site is running",
        1 => "An error occurred during the creation of the account on CRM",
        2 => "An error occurred during the creation of a contact on CRM",
        3 => "An error has occurred creating a job on crm",
        4 => "An undefined error has occurred creating a job on crm",
        5 => "An error occurred creating a traineeship",
        6 => "An error occurred creating an application",
    );

    public function __construct()
    {
        $this->apiUrl = "https://login.microsoftonline.com/{$this->TenantId}/oauth2/token";
        $this->authpostfields = array(
            'resource' => $this->resource,
            'client_id' => $this->ClientId,
            'client_secret' => $this->Secret,
            'grant_type' => 'client_credentials'
        );
        $this->JobArray = array();
        $this->companyfields = array();
        $this->contactfields = array();
        $this->jobsfields = array();
        $this->traineefields=array();
        $this->applicationfields=array();
    }
    /**
    * set up the data
    * @param array $array
    **/
    public function setJob($array)
    {
        $this->JobArray=$array;
    }
    /**
    * get the values of posts and translate them for CRM
    **/
    public function applytranslation()
    {
        $trans=new fieldtranslations();
        foreach ($trans->jobsfields as $key => $value) {
          if(isset($_POST[$value]) && strlen($_POST[$value])>0)
          {
            $trans->jobsfields[$key]=$_POST[$value];
          }else{
            unset($trans->jobsfields[$key]);
          }

        }
        $this->setJob($trans->jobsfields);
    }
    /**
     * returns header array
     *
     * @param string $token
     * @return string[]
     * @since 10/03/2021
     */
    public function prepareHeader($token)
    {
        return array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Prefer:return=representation',
            'Authorization: Bearer ' . $token
        );
    }

    /**
     * depends on which form is used
     *
     * @return array
     */
    private function getfieldnames()
    {
        $fld = new fieldtranslations();
        $params = array_keys($fld->jobsfields);
        return $params;
        /*
         * [fermsapp_jobtitle] => Test job
         * [fermsapp_vacancyshortdescription] =>
         * [fermsapp_vacancyfulldescription]
         * [fermsapp_kaonixminsalary] => 161.25
         * [fermsapp_longtermcompanyaimsforlearner] => To grow and manage office duties completely unattended and other employees.
         * [fermsapp_workinghours] => 37.5 hrs p/w Monday to Friday 8.30am - 5.00pm
         * [fermsapp_volume] => 3
         * [fermsapp_firstname] => John
         * [fermsapp_lastname] => Howson
         * [fermsapp_emailaddress] => john.howson@gmail.com
         * [mainbusinessphone] => 07123984572
         * [contactphone] =>
         * [employeremployername] => Ariadne Designs Ltd
         * [employeremployerwebsite] => www.ariadnedesigns.com
         * [employer_employerdescription] => A web development company
         * [employeraddress1] => 25 Desborough Close
         * [employeraddress2] =>
         * [employeraddress3] =>
         * [employertowncity] => Hertford
         * [employercounty] =>
         * [employerpostcode] => sg14 3eg
         * [numberofemployees] =>
         * [levypayer] => No
         * [fermsapp_skillsrequired] => test
         * [fermsapp_idealcandidate] => test
         * [fermsapp_qualificationsrequired] => test
         * [fermsapp_realitycheck] => test
         * [fermsapp_importantotherinformation] => test
         * [fermsapp_vacancyquestion1] =>
         * [fermsapp_vacancyquestion2] =>
         */
    }

    /**
     * save Errors
     *
     * @param unknown $code
     * @return boolean
     */
    private function processErrors($subject, $code)
    {
        saveLog("Publish to CRM error $subject: " . $code);
        $emailBody = $subject . ": /n" . $code;
        return sendErrEmail("VSF error with CRM API", $emailBody);
    }

    private function processOutput($output)
    {
        $outputArr = json_decode($output);
        return $outputArr;
    }

    /**
     * authorise
     *
     * @return json object
     */
    public function authRequest()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->authpostfields);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    /**
     * get input for the post
     *
     * @param string $action
     * @return string
     */
    protected function getdata($action)
    {
        switch ($action) {
            case 'sendjob':
                $postfields = json_encode($this->jobsfields);
                break;
            case 'createaccount':
                $postfields = json_encode($this->companyfields);
                break;
            case 'createcontact':
                $postfields = json_encode($this->contactfields);
                break;
            case 'createtraineeship':
              $postfields = json_encode($this->traineefields);
              break;
            case 'sendapplication':
                $postfields = json_encode($this->applicationfields);
                break;
            default:
                $postfields = json_encode($this->JobArray);
                break;
        }
        return $postfields;
    }

    /**
     * manage errors
     *
     * @param object $data
     * @param array $postfields
     * @param string $action
     * @return boolean
     */
    protected function handle_error($data, $postfields, $action)
    {
        switch ($action) {
            case 'authenticate':
                $error = $this->errormessages[0];
                // code...
                break;
            case 'createcontact':
                $error = $this->errormessages[1];
                break;
            case 'createaccount':
                $error = $this->errormessages[2];
                break;
            case 'sendjob':
                $error = $this->errormessages[3];
                break;
            case 'createtraineeship':
                $error = $this->errormessages[5];
                break;
            default:
                $error = $this->errormessages[4];
                break;
        }
        $message = print_r($data, true) . print_r($postfields, true);
        $result=$this->processErrors($error, $message);
        // if email successfully sent result=true
        // false will trigger the process to abort
        // adjust this to carry on despite errors
        return false;
    }

    /**
     * send request to CRM api
     *
     * @param string $token
     * @param string $url
     * @param string $action
     * @return boolean|mixed
     */
    protected function sendrequest($token, $url, $action = "sendjob")
    {
        $headers = $this->prepareHeader($token);
        $postfields = $this->getdata($action);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $this->resource . $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        // curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:47.0) Gecko/20100101 Firefox/47.0');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
        $output = curl_exec($ch);
        $result = print_r(curl_getinfo($ch), 1);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // $debuglog=curl_getinfo($ch, CURLINFO_HEADER_OUT);
        curl_close($ch);
        $data = $this->processOutput($output);
        if (isset($data->error)) {
            return $this->handle_error($data, $postfields, $action);
        }
        return $data;
    }

    /**
     * deprecated
     *
     * @param string $token
     * @return mixed
     */
    public function createaccount($token)
    {
        $ch = curl_init();
        $postfields = json_encode($this->companyfields);
        $headers = $this->prepareHeader($token);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $this->resource . $this->acUrl);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        $data = curl_exec($ch);
        $result = print_r(curl_getinfo($ch), 1);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // $debuglog=curl_getinfo($ch, CURLINFO_HEADER_OUT);
        curl_close($ch);
        return $data;
    }

    /**
     * get fields for contact will depend on the form
     *
     * @param integer $accountid
     * @return array
     */
    private function getcontactfields($accountid)
    {
        $expectedfields = array(
            "fermsapp_firstname" => "firstname",
            "fermsapp_lastname" => "lastname",
            "fermsapp_emailaddress" => "emailaddress1",
            "mainbusinessphone" => "business2",
            "contactphone" => "mobilephone"
        );
        foreach ($this->JobArray as $key => $value) {
            if (isset($expectedfields[$key])) {
                $this->contactfields[$expectedfields[$key]] = $value;
                unset($this->JobArray[$key]);
            }
        }
        $unboundfields=$this->contactfields;
        $this->contactfields['parentcustomerid_account@odata.bind'] = "/accounts($accountid)";
        $this->contactfields["fermsapp_contacttype"] = 100000001;
        settype($this->contactfields["fermsapp_contacttype"], "integer");
        return $unboundfields;
    }

    /**
     * will depend on the form
     *
     * @param integer $accountid
     * @param integer $contactid
     */
    private function getjobsfields($accountid, $contactid)
    {
        $fld = new fieldtranslations();
        foreach ($this->JobArray as $key => $value) {
                if (isset($this->JobArray[$key]) && strlen($this->JobArray[$key]) > 0) {
                    $this->jobsfields[$key] = $this->JobArray[$key];
                }
        }
        //$this->jobsfields = $fld->crmfields;
        settype($this->jobsfields["fermsapp_fixedwage"], "integer");
        settype($this->jobsfields["fermsapp_volume"], "integer");
        settype($this->jobsfields["fermsapp_disabilityconfidentemployer"],"boolean");
        // settype($this->jobsfields["fermsapp_contacttype"],"integer");
        $this->jobsfields['fermsapp_organisationid@odata.bind'] = "/accounts($accountid)";
        $this->jobsfields['fermsapp_contactid@odata.bind'] = "/contacts($contactid)";
        // $this->saveLog("debug jobs fields:" .print_r($this->jobsfields,true));
    }

    /**
     * The field array will depend on the form
     *
     * @since 10/03/2021
     */
    private function getcompanyfields()
    {
        $fields = array(
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
        );
        foreach ($this->JobArray as $key => $value) {
            if (in_array($key, $fields)) {
                $this->companyfields[$key] = $value;
                unset($this->JobArray[$key]);
            }
            if ($key == "employerpostcode") {
                $this->companyfields["address1_postalcode"] = $value;
            }
        }
        saveLog("debug company fields:" . print_r($this->companyfields, true));
    }

    /**
     * authenticate and initialise arrays
     *
     * @return string
     */
    public function initialise()
    {
        $params = $this->getfieldnames();
        $data = $this->authRequest();
        $data_obj = json_decode($data);
        if (! isset($data_obj->access_token)) {
            return $this->handle_error($data_obj, $params, "authenticate");
        }
        $access_token = $data_obj->{"access_token"};
        /*foreach ($_POST as $key => $value) {
            if (in_array($key, $params)) {
                $this->JobArray[$key] = $value;
            }
        }*/
        return $access_token;
    }

    /**
     * send the job data
     * also adds company and contact to JA
     * @return string
     *
     */
    public function sendjob($location)
    {

        $job=new addjob();
        $access_token = $this->initialise();
        $this->getcompanyfields();
        $data = $this->sendrequest($access_token, $this->acUrl, "createaccount");

        if ($data == false) {
            return $this->errormessages[1];
        }
        $accountid = $data->{"accountid"};
        $location["crmAccountID"]=$accountid;
        $result=$job->addcompany($location);
        $contactfields=$this->getcontactfields($accountid);
        $data = $this->sendrequest($access_token, $this->contactUrl, "createcontact");
        if ($data == false) {
            return $this->errormessages[2];
        }
        $contactid = $data->contactid;
        $contactfields["accountid"]=$accountid;
        $contactfields["contactid"]=$contactid;
        $result=$job->addcontact($contactfields);
        $this->getjobsfields($accountid, $contactid);
        $data = $this->sendrequest($access_token, $this->vacUrl, "sendjob");
        if ($data == false) {
            return $this->errormessages[3];
        }
        saveLog("Data successfully sent to CRM " .print_r($data,true));
        return "";
    }
}
