<?php
namespace api\classes;
class FormInputValidator{
	private $inputData = false;
	private $maxLength=false;
	private $dataType=false;
	private $dateFormat=false;
	private $error=false;
	function __construct($inputData=false,$dataType=false,$maxLength=false){
		$this->inputData=$inputData;
		$this->dataType=$dataType;
		$this->maxLength=$maxLength;
	}
	function setInputData($inputData=false){
		$this->inputData=$inputData;
	}
	function setMaxLength($maxLength=false){
		$this->maxLength=$maxLength;
	}
	function setDataType($dataType=false){
		$this->dataType=$dataType;
	}
	function setDateFormat($dateFormat=false){
		$this->dateFormat=$dateFormat;
	}
	function validateData(){
		if($this->inputData !== false && $this->dataType !== false){
			$this->validateAndCastType();
			if($this->error===false){
				return $this->inputData;
			}
		}
		return false;
	}
	function validateFieldLength(){
		if($this->inputData !== false && $this->dataType !== false && $this->maxLength!==false){
			if(strlen($this->inputData)>$this->maxLength){
				$this->inputData=substr($this->inputData,0,$this->maxLength);
			}
		}
	}
	function sql_escape($s) {
		if (function_exists('mysql_real_escape_string')) {
			$s = mysql_real_escape_string($s);
		}else{
			$s = mysql_escape_string($s);
		}
		return $s;
	}
	function validateAndCastType(){
		if($this->dataType !== false && $this->inputData !== false){
			switch($this->dataType){
				case "int":
					if(is_numeric($this->inputData)){
						$this->inputData=(int)$this->inputData;
					}else{
						$this->error=true;
					}
				break;
				case "float":
					if(is_numeric($this->inputData)){
						$this->inputData=(float)$this->inputData;
					}else{
						$this->error=true;
					}
				break;
				case "double":
					if(is_numeric($this->inputData)){
						$this->inputData=(double)$this->inputData;
					}else{
						$this->error=true;
					}
				break;
				case "long":
					if(is_numeric($this->inputData)){
						$this->inputData=(double)$this->inputData;
					}else{
						$this->error=true;
					}
				break;
				case "bool":
                    if(strtolower($this->inputData)=="true"){
                        $this->inputData=true;
                    }
                    if(!is_bool($this->inputData) && strtolower($this->inputData)!="false"){
						$this->error=true;
					}
				break;
				case "string":
					return $this->inputData;
					//return $this->sql_escape($this->inputData);
				break;
				case "email":
                    $regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
					if (!preg_match($regex,$this->inputData)){
						$this->error=true;
					}
				break;
				case "alpha":
					if (!eregi("^[A-Za-z]", $this->inputData)){
						$this->error=true;
					}
				break;
				case "alphanumeric":
					if (!eregi("^[A-Za-z0-9]", $this->inputData)){
						$this->error=true;
					}
				break;
                case "date":
                    $date=explode("/",$this->inputData);
                    $year=@$date[2];
                    $month=@$date[1];
                    $day=@$date[0];
                    if (!is_numeric($year) || !is_numeric($month) || !is_numeric($day)) {
                        $this->error=true;
                        return;
                    }
				break;
				case "futureDate":
					$date=explode("/",$this->inputData);
                    $year=@$date[2];
                    $month=@$date[1];
                    $day=@$date[0];
                    if(checkdate($month,$day,$year)){
                        if(strtotime(date("Y-m-d"))>strtotime($year."-".$month."-".$day)){
                            $this->error=true;
                            return;
                        }
                    }else{
                        $this->error=true;
                    }
				break;
			}
		}
	}
}
?>
