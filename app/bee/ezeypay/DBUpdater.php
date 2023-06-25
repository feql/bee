<?php

	$servername = "localhost";
	$username = "root";
	$password = "";
	$dbname = "test";
	
	$transaction_id = "";
	$message = "";

	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	} 

	// read the incoming POST body (the JSON)
	$input = file_get_contents('php://input');

	// decode/unserialize it back into a PHP data structure
	// Converts it into a PHP object
	$data = json_decode($input);

	if(!isset($data))
	{	
		$message = "No data received";
	}

	$tranType = $data->tran_type;
	$id =  $data->transaction_id;
	$status =  $data->message;	

	if($tranType === "1")
	{
		$sql = "UPDATE collections SET Status = '$status' WHERE TranID = '$id'";
		if ($conn->query($sql) === TRUE) {
			$message =  "DB updated successfully";
		} else {
			$message =  "Error: " . $sql . " " . $conn->error;
		}	
	}
	else if($tranType === "2")
	{
				$sql = "UPDATE withdraws SET Status = '$status' WHERE TranID = '$id'";
		if ($conn->query($sql) === TRUE) {
			$message =  "DB updated successfully";
		} else {
			$message =  "Error: " . $sql . " " . $conn->error;
		}			
	}
	
	$conn->close();	
	$post_data = array();	
	$post_data['transaction_id'] = $transaction_id;
	$post_data['message'] = $message;
	
	echo json_encode($post_data);

?>