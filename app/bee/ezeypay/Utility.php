<?php


class Utility {
	
	// Define Variables
	// ----------------
	private $TranData; 	
	private $hashInput;
	private $secureHashSecret;  	
	
	public function setSecureSecret($secret) {		
		$this->secureHashSecret = $secret;
	}
	
	public function addDigitalOrderField($field, $value) {
		
		if (strlen($value) == 0) return false;      // Exit the function if no $value data is provided
		if (strlen($field) == 0) return false;      // Exit the function if no $value data is provided
		
		// Add the digital order information to the data to be posted to the Payment Server
		$this->TranData .= (($this->TranData=="") ? "" : "&") . urlencode($field) . "=" . urlencode($value);
		
		// Add the key's value to the hash input (only used for 3 party)
		$this->hashInput .= $field . "=" . $value . "&";
		
		return true;
	}
	
	public function hashAllFields() {
		$this->hashInput=rtrim($this->hashInput,"&");
		return strtoupper(hash_hmac('SHA256',$this->hashInput, pack("H*",$this->secureHashSecret)));
	}
	
	public function getTransactionString() {
		return $this->TranData;
	}	
	
	
}

?>