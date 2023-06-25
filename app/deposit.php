<?php
   
   //nyd
   //Todo:
   //make the transactions through out Transactional
   //all db operations success or nothing gets committed
 
   function json_to_object($json, $getArray = true){
       $temp = json_decode($json, $getArray);
       $error = json_last_error();
       $msg = "";
       switch ($error) {
            case JSON_ERROR_NONE:
               $msg = "";
               break;
            break;
            case JSON_ERROR_DEPTH:
               $msg = "JSON_ERROR_DEPTH - Maximum stack depth exceeded";
               break;
            break;
            case JSON_ERROR_STATE_MISMATCH:
               $msg = "JSON_ERROR_STATE_MISMATCH - Underflow or the modes mismatch";
               break;
            break;
            case JSON_ERROR_CTRL_CHAR:
               $msg = "JSON_ERROR_CTRL_CHAR - Unexpected control character found";
               break;
            break;
            case JSON_ERROR_SYNTAX:
               $msg = "JSON_ERROR_SYNTAX - Syntax error, malformed JSON";
               //var_dump($json);
               break;
            break;
            case JSON_ERROR_UTF8:
               $msg = "JSON_ERROR_UTF8 - Malformed UTF-8 characters, possibly incorrectly encoded";
               break;
            break;
            default:
               $msg = "JSON - Unknown error";
               break;
            break;
       }
       return array($temp,(strlen($msg)>0?array($msg):array()));
   }
 
 
   function post($bee,$data,$res){
       $trans_type = strval($data["trans_type"]);
       if($trans_type === "1"){
            $res = finish_deposit_transaction($bee,$data,$res);
       }else if($trans_type === "2"){
            $res = finish_withdraw_transaction($bee,$data,$res);
       }
       return $res;
   }

   function finish_deposit_transaction($bee,$data,$res){
        //need to update the deposit table that recorded this 
       //transaction
       $get_update = [
            "deposit" => [
                "_w" => [
                    ["transaction_code","e", $data["id"]]
                ]
            ]
        ];
        $brg_res = bee_run_get($get_update,$bee["BEE_HIVE_STRUCTURE"]["combs"],$bee["BEE_HIVE_CONNECTION"]);
        $res[BEE_EI] = array_merge($res[BEE_EI],$brg_res[BEE_EI]);
        if(count($res[BEE_EI]) > 0){
            return $res;
        }
        $deposit = $brg_res[BEE_RI]["deposit"];
        if($deposit == null){
           array_push($res[BEE_EI], "Error: Deposit transaction not found");
           return $res;
        }
        //the deposit must be on a pending status
        if($deposit["status"] != "pending"){
            array_push($res[BEE_EI], "Error: Deposit transaction already processed");
            return $res;
        }

        //the status of the transaction from the payment gate way must be fine
        $user_message_id = uniqid() . "-" . rand(1000,2768);
        if($data["status"] == "failed"){
            //post notification
            $post_fail = [
                "notification" => [
                    "message_identity" => $user_message_id,
                    "user_id" =>   $deposit["user_id"],
                    "is_read" => 0,
                    "title" => "Failed To Deposit Cash USD " . $deposit["amount_in_usd"],
                    "message" => "Hello Xaddener #" . $deposit["user_id"] . ",  your deposit transaction ". $deposit["transaction_code"] ." has failed ",
                    "icon" => "mdi-currency-usd-circle",
                    "target_platform" => "android_app",
                    "deep_link" => "",
                    "date_posted" => date("Y-m-d"),
                    "date_posted_timestamp" => time(),
                    "date_read" => "1980-01-01",
                    "_time_date_read_timestamp" => "1980-01-01"
                ]
            ];
            $bru_res3x = bee_run_post($post_fail,$bee,0);
            $res[BEE_EI] = array_merge($res[BEE_EI],$bru_res3x[BEE_EI]);
            if(count($res[BEE_EI]) > 0){
                //end process
                return $res;
            }

            //the transaction failed 
            //update the deposit table
            $put_update = [
                "deposit" => [
                    "status" => "failed",
                    "date_approved" => date("yyy-mm-dd"),
                    "date_approved_timestamp" => time(),
                    "transaction_status" => $data["status"],
                    "_w" => [
                        ["id","e", $deposit["id"]]
                    ]
                ]
            ];
            $bru_res3 = bee_run_update($put_update,$bee,0);
            $res[BEE_EI] = array_merge($res[BEE_EI],$bru_res3[BEE_EI]);
            //nyd
            //send optional sms to user that their transaction failed
            //or the support team can even call user at this moment to offer solutions
            if(count($res[BEE_EI]) > 0){
                //end process
                return $res;
            }
            return $res; //end process cause we dont have more to do
        }else if($data["status"] == "success"){
            $user_id = $deposit["user_id"];
            //get the previous account movement for this user
            $move_query = [
                "account_movements" => [
                    "_pg" => 1,
                    "_desc" => ["id"],
                    "_w" => [
                        ["user_id", "e", $user_id]
                    ]
                ],
                "stats_statements" => [
                    "_pg" => 1,
                    "_desc" => ["id"],
                    "_w" => [
                        ["user_id", "e", $user_id]
                    ]
                ]
            ];
            $brg_res = bee_run_get($move_query,$bee["BEE_HIVE_STRUCTURE"]["combs"],$bee["BEE_HIVE_CONNECTION"]);
            //tools_dump("@a tracing null errors",__FILE__,__LINE__,$brp_res[BEE_EI]);
            $res[BEE_EI] = array_merge($res[BEE_EI],$brg_res[BEE_EI]);
            if(count($res[BEE_EI]) > 0){
                //we have errors so we cannot proceed
                return $res;
            }
            $prev_closing_value = 0;
            if(count($brg_res[BEE_RI]["account_movements"]) > 0){
                $prev_closing_value = floatval($brg_res[BEE_RI]["account_movements"][0]["closing_value"]);
            }
            $closin_value = $prev_closing_value + floatval($deposit["amount"]);


        
            $current_earned_amount = 0;
            $current_deposted_amount = 0;
            $current_withdrawn_amount = 0;
            $current_balance_amount  = 0;
            if(count($brg_res[BEE_RI]["stats_statements"]) > 0){
                $current_earned_amount = floatval($brg_res[BEE_RI]["stats_statements"][0]["earned_amount"]);
                $current_deposted_amount = floatval($brg_res[BEE_RI]["stats_statements"][0]["deposted_amount"]);
                $current_withdrawn_amount = floatval($brg_res[BEE_RI]["stats_statements"][0]["withdrawn_amount"]);
                $current_balance_amount = floatval($brg_res[BEE_RI]["stats_statements"][0]["balance_amount"]);
            }
            //update values
            $current_deposted_amount = $current_deposted_amount + floatval($deposit["amount_in_usd"]);
            $current_balance_amount = ($current_earned_amount + $current_deposted_amount ) -  ( $current_withdrawn_amount);
            

            //update the transaction success 
            //update the deposit table and dashboard
            $put_update = [
                "deposit" => [
                    "status" => "success",
                    "date_approved" => date("yyy-mm-dd"),
                    "date_approved_timestamp" => time(),
                    "transaction_status" => $data["status"],
                    "_w" => [
                        ["id","e", $deposit["id"]]
                    ]
                ],
                "user" => [
                    "dashboard_deposted_amount" => $current_deposted_amount,
                    "dashboard_balance_amount" => $current_balance_amount,
                    "dashboard_statement_date_posted" => date("yyy-mm-dd"),
                    "dashboard_statement_date_posted_timestamp" => time(),
                    "_w" => [
                        ["id","e", $user_id]
                    ]
                ]
            ];
            $bru_res3 = bee_run_update($put_update,$bee,0);
            $res[BEE_EI] = array_merge($res[BEE_EI],$bru_res3[BEE_EI]);
            //nyd
            //send optional sms to user that their transaction failed
            //or the support team can even call user at this moment to offer solutions
            if(count($res[BEE_EI]) > 0){
                //end process
                //nyd undo previous changes, rollback
                return $res;
            }

            
            //insert account movememnts for that user
            $post_movement = [
                "account_movement" => [
                    "user_id" =>   $user_id,
                    "direction" =>  "debit",
                    "value" =>  $deposit["amount_in_usd"],
                    "openning_value" =>   $prev_closing_value,
                    "closing_value" =>  $closin_value,
                    "cause_table" =>  "deposit",
                    "cause_table_id" =>   $deposit["id"],
                    "remark" =>  "Transaction ID: " . $data["id"],
                    "transaction_type" =>   "deposit",
                    "description" =>  "Added funds to your xadden account",
                    "date_occurance" => date("yyy-mm-dd"),
                    "date_occurance_timestamp" => time()
                ],
                "stats_statement" => [
                    "user_id" =>   $user_id,
                    "earned_amount" => $current_earned_amount,
                    "deposted_amount" => $current_deposted_amount,
                    "withdrawn_amount" => $current_withdrawn_amount,
                    "balance_amount" =>  $current_balance_amount,
                    "date_posted" => date("yyy-mm-dd"),
                    "date_posted_timestamp" =>  time()
                ],
                "notification" => [
                    "message_identity" => $user_message_id,
                    "user_id" => $user_id,
                    "is_read" => 0,
                    "title" => "Successfully Deposited Cash USD " . $deposit["amount_in_usd"],
                    "message" => "Hello Xaddener #" . $deposit["user_id"] . ",  your deposit transaction ". $deposit["transaction_code"] ." completed successfully ",
                    "icon" => "mdi-currency-usd-circle",
                    "target_platform" => "android_app",
                    "deep_link" => "",
                    "date_posted" => date("Y-m-d"),
                    "date_posted_timestamp" => time(),
                    "date_read" => "1980-01-01",
                    "_time_date_read_timestamp" => "1980-01-01"
                ]
            ];
            $brp_res = bee_run_post($post_movement,$bee,0);
            $res[BEE_EI] = array_merge($res[BEE_EI],$brp_res[BEE_EI]);
            if(count($res[BEE_EI]) > 0){
                //end process
                //nyd undo previous changes, rollback
                return $res;
            }
            $res[BEE_RI] = $brp_res[BEE_RI];

            //nyd, Todo
            //send user sms, thanks and xadden balanace

            
            

            return $res;
        }

        return $res;
   }

   function finish_withdraw_transaction($bee,$data,$res){
        //get the request from withdraw request 
        $get_update = [
            "payout_request" => [
                "_w" => [
                    ["transaction_code","e", $data["id"]]
                ]
            ]
        ];
        $brg_res = bee_run_get($get_update,$bee["BEE_HIVE_STRUCTURE"]["combs"],$bee["BEE_HIVE_CONNECTION"]);
        $res[BEE_EI] = array_merge($res[BEE_EI],$brg_res[BEE_EI]);
        if(count($res[BEE_EI]) > 0){
            return $res;
        }
        $payout_request = $brg_res[BEE_RI]["payout_request"];
        if($payout_request == null){
           array_push($res[BEE_EI], "Error: Withdraw transaction not found");
           return $res;
        }
        //the payout must be on a pending status
        if($payout_request["status"] != "pending"){
            array_push($res[BEE_EI], "Error: Withdraw transaction already processed");
            return $res;
        }
        $user_id = $payout_request["user_id"];
        $get_financials = [
            "stats_statements" => [
                "_pg" => 1,
                "_desc" => ["id"],
                "_w" => [
                    ["user_id", "e", $user_id]
                ]
            ],
            "account_movements" => [
                "_pg" => 1,
                "_desc" => ["id"],
                "_w" => [
                    ["user_id", "e", $user_id]
                ]
            ],
            "user" => [
                "_w" => [
                    ["id", "e", $user_id]
                ]
            ]
        ];
        $finicials_res = bee_run_get($get_financials,$bee["BEE_HIVE_STRUCTURE"]["combs"],$bee["BEE_HIVE_CONNECTION"]);
        $res[BEE_EI] = array_merge($res[BEE_EI],$finicials_res[BEE_EI]);
        if(count($res[BEE_EI]) > 0){
            return $res;
        }
        $user = $finicials_res[BEE_RI]["user"];
        if($user == null){
            array_push($res[BEE_EI],"!oops, user account not found");
            return false;
        }
        $stats_statement = [
            "user_id" => $user["id"],
            "earned_amount" => 0,
            "deposted_amount" => 0,
            "withdrawn_amount" => 0,
            "balance_amount" => 0,
            "date_posted" => date("Y-m-d"),
            "date_posted_timestamp" => time()
        ];
        $stats_statements = $finicials_res[BEE_RI]["stats_statements"];
        if(count($stats_statements) > 0 ){
            $stats_statement = $stats_statements[0];
        }
        $account_movement = [
            "user_id" => $user["id"],
            "direction" => "credit",
            "value" =>  0,
            "openning_value" => 0, //update this
            "closing_value" => 0, //update this
            "cause_table" => "",
            "cause_table_id" => "",
            "remark" => "",
            "transaction_type" => "",
            "description" => "",
            "date_occurance" => date("Y-m-d"),
            "date_occurance_timestamp" => time()
        ];
        $account_movements = $finicials_res[BEE_RI]["account_movements"];
        if(count($account_movements) > 0 ){
            $account_movement = $account_movements[0];
        }

        //the status of the transaction from the payment gate way must be fine
        $user_message_id = uniqid() . "-" . rand(1000,2768);
        if($data["status"] == "failed"){
            $deducted_request_amount = floatval($payout_request["deducted_request_amount_in_usd"]);
            $this_account_balance = floatval($stats_statement["balance_amount"]) + $deducted_request_amount;
            //post a notification to the user
            //the transaction needs to be reversed
            $post_fail = [
                "notification" => [
                    "message_identity" => $user_message_id,
                    "user_id" =>   $payout_request["user_id"],
                    "is_read" => 0,
                    "title" => "Failed To Withdraw Cash USD " . $deposit["amount_requested_in_usd"],
                    "message" => "Hello Xaddener #" . $payout_request["user_id"] . ",  your withdraw transaction ". $payout_request["transaction_code"] ." has failed ",
                    "icon" => "mdi-currency-usd-circle",
                    "target_platform" => "android_app",
                    "deep_link" => "",
                    "date_posted" => date("Y-m-d"),
                    "date_posted_timestamp" => time(),
                    "date_read" => "1980-01-01",
                    "_time_date_read_timestamp" => "1980-01-01"
                ],
                "stats_statement" => [
                    "user_id" => $user["id"],
                    "earned_amount" => $stats_statement["earned_amount"], 
                    "deposted_amount" => floatval($stats_statement["deposted_amount"]) + $deducted_request_amount, //update this
                    "withdrawn_amount" => $stats_statement["withdrawn_amount"],
                    "balance_amount" => $this_account_balance, //update this
                    "date_posted" => date("Y-m-d"),
                    "date_posted_timestamp" => time()
                ],
                "account_movement" => [
                    "user_id" => $user["id"],
                    "direction" => "debit",
                    "value" =>  $deducted_request_amount,
                    "openning_value" => $account_movement["closing_value"], 
                    "closing_value" => floatval($account_movement["closing_value"]) + $deducted_request_amount, 
                    "cause_table" => "payout_request",
                    "cause_table_id" => $payout_request["id"],
                    "remark" => "withdraw failed",
                    "transaction_type" => "withdraw_bounce",
                    "description" => "Withdraw Failed Of USD " .$payout_request["amount_requested_in_usd"] ,
                    "date_occurance" => date("Y-m-d"),
                    "date_occurance_timestamp" => time()
                ]
            ];
            $bru_res3x = bee_run_post($post_fail,$bee,0);
            $res[BEE_EI] = array_merge($res[BEE_EI],$bru_res3x[BEE_EI]);
            if(count($res[BEE_EI]) > 0){
                //end process
                return $res;
            }
            //update the following tables
            //payout_request
            //user
            $update_query = [
                "user" => [
                    "dashboard_deposted_amount" => floatval($user["dashboard_deposted_amount"]) + $deducted_request_amount, //increase this
                    "dashboard_balance_amount" => $this_account_balance,
                    "dashboard_statement_date_posted" =>  date("Y-m-d"),
                    "dashboard_statement_date_posted_timestamp" => time(),
                    "_w" => [
                        ["id","e",$user_id]
                    ]
                ],
                "payout_request" => [
                    "status" => "failed",
                    "date_approved" => date('Y-m-d'),
                    "date_approved_timestamp" => time(),
                    "transaction_status" => $data["status"],
                    "_w" => [
                        ["id","e",$payout_request["id"]]
                    ]
                ]
            ];
            $brp_res2 = bee_run_update($update_query,$bee,$user["id"]);
            $res[BEE_EI] = array_merge($res[BEE_EI],$brp_res2[BEE_EI]);
            if(count($res[BEE_EI]) > 0){
                //end process
                //nyd undo previous changes, rollback
                return false;
            }

            //nyd
            //Todo
            //pleaese sent the user an email or android notification that 
            //the payout request failed
        }else if($data["status"] == "success"){
            //update the payout request
            $update_query = [
                "payout_request" => [
                    "status" => "success",
                    "date_approved" => date('Y-m-d'),
                    "date_approved_timestamp" => time(),
                    "transaction_status" => $data["status"],
                    "_w" => [
                        ["id","e",$payout_request["id"]]
                    ]
                ]
            ];
            $brp_res2 = bee_run_update($update_query,$bee,$user["id"]);
            $res[BEE_EI] = array_merge($res[BEE_EI],$brp_res2[BEE_EI]);
            if(count($res[BEE_EI]) > 0){
                //end process
                //nyd undo previous changes, rollback
                return false;
            }
        }

        return $res;
   }
 
   $message = "";
   $post_data = array();  
   $post_data['transaction_id'] = ""; 
   $post_data['message'] = $message;
   $temp_postdata = file_get_contents("php://input");
   //tools_dumpx("temp_postdata",__FILE__,__LINE__,$temp_postdata);
   $tsji_res = json_to_object($temp_postdata, false);
   //tools_dumpx("tsji_res",__FILE__,__LINE__,$tsji_res);
   if(count($tsji_res[BEE_EI]) == 0){//no errors
       $data = $tsji_res[BEE_RI];
       $post_this = [
           "trans_type" => $data->tran_type,
           "id" => $data->transaction_id,
           "status" => $data->message
       ];
       $post_data['transaction_id'] = $data->transaction_id;
       $res = post($BEE, $post_this, array(null,array(),null));
       if(count($res[BEE_EI]) > 0){
            $message =  "Error: " . $res[BEE_EI][0];
       }else{
           $message = "DB updated successfully";
       }
   }else{
       $error = $tsji_res[BEE_EI][0];
       $message = "Error: Failed to decode response, probably no data provided i.e " . $error;
   }
   $post_data['message'] = $message;
 
   
  
  
   $BEE["BEE_GARDEN_CONNECTION"] = null;
   $BEE["BEE_HIVE_CONNECTION"] = null;
   //include the header
   header("Content-type: application/json");
   $res =  json_encode($post_data);
   echo $res;
   exit(0);
?>

