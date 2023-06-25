<?php

	
	include('Utility.php');
	include('Ezeypay.php');	
	
	$Ezeypay = new Ezeypay(); 
	 
	//QUERY COLLECTION STATUS
	//$obj = $Ezeypay->QueryCollectionStatus('15042019221565'); 
	
	//QUERY WITHDRAW STATUS
	//$obj = $Ezeypay->QueryWithdrawStatus('20042019561434'); 
	
	//CHECK ACCOUNT BALANCE
	//$obj = $Ezeypay->GetAccountBalance();
	
	//POST COLLCETION CODE
	//$obj = $Ezeypay->postCollection('256704480637', '2000','111', 'test payment', 'payment');
	
	//MAKE WITHDRAW CODE
	$obj = $Ezeypay->MakeWithdraw('0704480637', '1500','1263', 'withdraw test', 'test');
	 
	print_r($obj);
	echo'<br/>';echo'<br/>'; 
	print_r('CODE : '.$obj->RESPONSE->CODE);
	echo'<br/>';
	print_r('STATUS : '.$obj->RESPONSE->STATUS);
	echo'<br/>';
	print_r('MESSAGE : '.$obj->RESPONSE->MESSAGE);
	
	
	
	

 

?>