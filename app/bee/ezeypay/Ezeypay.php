<?php




class Ezeypay {		
	
	var $CustomerID = "288"; 
	var $APIPassword = "2C9E22"; 
	var $secureSecret = "7CF338418A363EF81ACACDC7C26F03"; 
	//var $url = 'http://testservice.ezeypay.com/ezeypayservice.asmx/';
	var $url = 'http://testcollection.ezeypay.com/ezeypayservice.asmx/';
	var $payoutUrl = "http://testpayout.ezeypay.com/payoutservice.asmx/";
	var $api_end_point;
	
	
	function postCollection($Phone, $amount, $TransactionID, $Reason, $Remark){
		$Util = new Utility();
		
		$this->api_end_point = $this->url.'PostCollection_Json';
		
		$Util->setSecureSecret($this->secureSecret);
		
		try{

			//===prepare data into an array for ascending sort
			$data = array(
				"pfj_CustomerID"=>$this->CustomerID, 
				"pfj_Reference"=>$Phone, 
				"pfj_Amount"=>$amount,
				"pfj_Currency"=>"UGX", 
				"pfj_CollectionID"=>$TransactionID,
				"pfj_PaymentMode"=>"MOMO"
			); 

			// Sort the data in an array
			ksort ($data);

			foreach($data as $key => $value) {
				if (strlen($value) > 0) {
					$Util->addDigitalOrderField($key, $value);
				}
			}

			//get url encoded string
			$TranString = $Util->getTransactionString();
			
			//get a secure hash value
			$secureHash = $Util->hashAllFields();
			
			//prepare json data to post to the api
			$jsonData = array(
				"TransactionString" => $TranString,
				"SecureHash" => $secureHash,
				"pfj_Reason" => $Reason,
				"pfj_Remark" => $Remark
			);
			
			$temp = $this->postJson($jsonData);
			//var_dump($temp);	
			$result = json_decode($temp);
			//var_dump($result);		
			$obj = json_decode($result->d);				
				
			//return  json_decode($obj)->RESPONSE->CODE.' '.json_decode($obj)->RESPONSE->STATUS;
			return $obj;			
			
		}catch(Exception $ex){
			return null;
		}
	}
	
	function MakeWithdraw($Phone, $amount, $TransactionID, $Reason, $Remark){
		$Util = new Utility();	
		//$this->api_end_point =  $this->payoutUrl.'MakeWithdraw_Json';
		$this->api_end_point = "http://testpayout.ezeypay.com/payoutservice.asmx/MakeWithdraw_Json";
		$Util->setSecureSecret($this->secureSecret);
		try{

			//===prepare data into an array for ascending sort
			$data = array(
				"pfj_CustomerID"=>$this->CustomerID, 
				"pfj_Reference"=>$Phone, 
				"pfj_Amount"=>$amount,
				"pfj_Currency"=>"UGX", 
				"pfj_WithdrawID"=>$TransactionID,
				"pfj_PaymentMode"=>"MOMO"
			);

			// Sort the data in an array
			ksort ($data);

			foreach($data as $key => $value) {
				if (strlen($value) > 0) {
					$Util->addDigitalOrderField($key, $value);
				}
			}
			//get url encoded string
			$TranString = $Util->getTransactionString();
			
			//get a secure hash value
			$secureHash = $Util->hashAllFields();
		
			//prepare json data to post to the api
			$jsonData = array(
				"TransactionString" => $TranString,
				"SecureHash" => $secureHash,
				"pfj_Reason" => $Reason,
				"pfj_Remark" => $Remark
			);

			$temp = $this->postJson($jsonData);
			//var_dump($temp);	
			$result = json_decode($temp);	
			//var_dump($result);		
			$obj = json_decode($result->d);	
				
			//return  json_decode($obj)->RESPONSE->CODE.' '.json_decode($obj)->RESPONSE->STATUS;
			return $obj;		
			
		}catch(Exception $ex){
			var_dump($ex->message);
			return null;
		}
	}

	function QueryCollectionStatus($ReferenceID){
	
		try{
			//set query status end point
			$this->api_end_point = $this->url.'QueryCollectionStatus_Json';
			
			//prepare json data to post to the api
			$jsonData = array(
				"APIKey" => $this->secureSecret,
				"APIPassword" => $this->APIPassword,
				"ReferenceID" => $ReferenceID
			);
			
			$result = json_decode($this->postJson($jsonData));			
			$obj = json_decode($result->d);	
			return $obj;			
			
		}catch(Exception $ex){
			return null;
		}
	}
	
	function QueryWithdrawStatus($ReferenceID){
		try{
			//set query status end point
			$this->api_end_point = $this->url.'QueryWithdrawStatus_Json';
			
			//prepare json data to post to the api
			$jsonData = array(
				"APIKey" => $this->secureSecret,
				"APIPassword" => $this->APIPassword,
				"ReferenceID" => $ReferenceID
			);
			
			$result = json_decode($this->postJson($jsonData));			
			$obj = json_decode($result->d);	
			return $obj;			
			
		}catch(Exception $ex){
			return null;
		}
	}
	
	function GetAccountBalance(){
		try{
			
			//set query status end point
			$this->api_end_point = $this->url.'GetAccountBalance_Json';
			
			//prepare json data to post to the api
			$jsonData = array(
				"APIKey" => $this->secureSecret,
				"APIPassword" => $this->APIPassword
			);	
			
			$result = json_decode($this->postJson($jsonData));			
			$obj = json_decode($result->d);	
			return $obj;			
			
		}catch(Exception $ex){
			return null;
		}
	}
	
	function postJson($jsonData){
		
		//Initiate cURL.
		$ch = curl_init($this->api_end_point);
		 
		//Encode the array into JSON.
		$jsonDataEncoded = json_encode($jsonData);
		 
		//Tell cURL that we want to send a POST request.
		curl_setopt($ch, CURLOPT_POST, 1);
		 
		//Attach our encoded JSON string to the POST fields.
		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);
		 
		//Set the content type to application/json
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); 
		
		//return response instead of outputting
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		 
		//Execute the request
		$result = curl_exec($ch);
		
		//close cURL resource
		curl_close($ch);
		
		return $result;
	}
		
}

?>