<?php
    function bee_run_handle_ussd($bee){
        if($_SERVER["REQUEST_METHOD"] == "POST"){
            //check if we have all the required ussd parameters
            if( isset($_POST["sessionId"]) && 
                isset($_POST["serviceCode"]) && 
                isset($_POST["serviceCode"]) && 
                isset($_POST["phoneNumber"]) && 
                isset($_POST["text"]) 
            ){
                $sessionId   = $_POST["sessionId"];
                $serviceCode = $_POST["serviceCode"];
                $phoneNumber = $_POST["phoneNumber"];
                $text        = $_POST["text"];
                $response = "END Your account number is ".$phoneNumber;
                header('Content-type: text/plain');
                echo $response;
                exit(0);
            }
        }
    }
?>