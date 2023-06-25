<?php
function bee_run_handle_post($res,$bee,$postdata=null){
    global $BEE_GLOBALS;
    global $countries_list;
    global $bee_show_sql;

    //check if there is a file upload
    if(count($_FILES) > 0){
        $bhru_res = bee_hive_run_uploads($bee);
        $res[BEE_EI] = array_merge($res[BEE_EI],$bhru_res[BEE_EI]);
        $res[BEE_RI] = $bhru_res[BEE_RI];
    }else{
        if($postdata == null){
            //do a normal file processing
            $temp_postdata = file_get_contents("php://input");
            //tools_dumpx("temp_postdata",__FILE__,__LINE__,$temp_postdata);
            $tsji_res = tools_suck_json_into($temp_postdata, array());
            $res[BEE_EI] = array_merge($tsji_res[BEE_EI],$res[BEE_EI]);
            if(count($res[BEE_EI])==0){//no errors
                $postdata = $tsji_res[BEE_RI];
            }
        }

        //check if we can return all the sqls
        if(array_key_exists("_sql",$postdata)){
            $bee_show_sql = true;
        }

        if(array_key_exists("_main_",$postdata)){
            //do the injections
            $postdata = $postdata["_main_"];
            $hrpgd_res = hive_run_post_garden_data($postdata,$bee["BEE_GARDEN_STRUCTURE"],$bee["BEE_GARDEN_CONNECTION"],0);
            $res[BEE_EI] = array_merge($res[BEE_EI],$hrpgd_res[BEE_EI]);
            $res[BEE_RI] = $hrpgd_res[BEE_RI];
            return $res;
        }


        //julz
        if(array_key_exists("_julz",$postdata)){
            $whole_honey = array("julz"=>array());
            $julz = $postdata["_julz"];
            foreach ($julz as $jul_key => $jul) {
                if($jul_key == "_gets"){ //array_key_exists("_gets",$julz)
                    $whole_honey["julz"]["gets"] = array();
                    //an array of get requets
                    $gets = $julz["_gets"];
                    $keys = array_keys($gets);
                    $is_indexed = false;
                    //tools_dumpx("julz keys",__FILE__,__LINE__,$keys);
                    foreach ($keys as $key_index => $key) {
                        if(is_numeric($key) == false){
                            $is_indexed = true;
                            break;
                        }
                    }
                    foreach ($gets as $get_index => $get) {
                        //tools_dumpx("julz get",__FILE__,__LINE__,[$get_index,$get]);
                        $brhg_res = bee_run_handle_get($res,$bee,$get);
                        //tools_dumpx("julz get",__FILE__,__LINE__,$brhg_res);
                        $sub_res = tools_pack($brhg_res[BEE_RI],$brhg_res[BEE_EI]);
                        $res[BEE_EI] = array_merge($res[BEE_EI],$brhg_res[BEE_EI]);
                        if($is_indexed == true){
                            $whole_honey["julz"]["gets"][$get_index] = $sub_res;
                        }else{
                            array_push($whole_honey["julz"]["gets"],$sub_res);
                        }
                    }
                }
                if($jul_key == "_posts"){ //array_key_exists("_posts",$julz)
                    //an array of post requets
                    $whole_honey["julz"]["posts"] = array();
                    //an array of get requets
                    $posts = $julz["_posts"];
                    foreach ($posts as $post_index => $post) {
                        $brhp_res = bee_run_handle_post($res,$bee,$post);
                        //tools_dumpx("julz get",__FILE__,__LINE__,$brhp_res);
                        $sub_res = tools_pack($brhp_res[BEE_RI],$brhp_res[BEE_EI]);
                        $res[BEE_EI] = array_merge($res[BEE_EI],$brhp_res[BEE_EI]);
                        array_push($whole_honey["julz"]["posts"],$sub_res);
                    }
                }
                if($jul_key == "_puts"){//array_key_exists("_puts",$julz)
                    //an array of update requets
                    $whole_honey["julz"]["puts"] = array();
                    //an array of get requets
                    $puts = $julz["_puts"];
                    foreach ($puts as $put_index => $put) {
                        $brhp_res = bee_run_handle_put($res,$bee,$put,$whole_honey);
                        //tools_dumpx("julz get",__FILE__,__LINE__,$brhp_res);
                        $sub_res = tools_pack($brhp_res[BEE_RI],$brhp_res[BEE_EI]);
                        $res[BEE_EI] = array_merge($res[BEE_EI],$brhp_res[BEE_EI]);
                        array_push($whole_honey["julz"]["puts"],$sub_res);
                    }
                }
                if($jul_key == "_updates"){//array_key_exists("_updates",$julz)
                    //an array of update requets
                    $whole_honey["julz"]["updates"] = array();
                    //an array of get requets
                    $puts = $julz["_updates"];
                    foreach ($puts as $put_index => $put) {
                        $brhp_res = bee_run_handle_put($res,$bee,$put,$whole_honey);
                        //tools_dumpx("julz get",__FILE__,__LINE__,$brhp_res);
                        $sub_res = tools_pack($brhp_res[BEE_RI],$brhp_res[BEE_EI]);
                        $res[BEE_EI] = array_merge($res[BEE_EI],$brhp_res[BEE_EI]);
                        array_push($whole_honey["julz"]["updates"],$sub_res);
                    }
                }
                if($jul_key == "_deletes"){//array_key_exists("_deletes",$julz)
                    //an array of delete requets
                    $whole_honey["julz"]["deletes"] = array();
                    //an array of get requets
                    $deletes = $julz["_deletes"];
                    foreach ($deletes as $delete_index => $delete) {
                        $brhd_res = bee_run_handle_delete($res,$bee,$delete,$whole_honey);
                        //tools_dumpx("julz get",__FILE__,__LINE__,$brhd_res);
                        $sub_res = tools_pack($brhd_res[BEE_RI],$brhd_res[BEE_EI]);
                        $res[BEE_EI] = array_merge($res[BEE_EI],$brhd_res[BEE_EI]);
                        array_push($whole_honey["julz"]["deletes"],$sub_res);
                    }
                }
                $res[BEE_RI] = $whole_honey;
            }
        }elseif(array_key_exists("_f_login",$postdata)){
            //the login
            //it has to be the only thing in its request
            $whole_honey = array();
            $login_nector = array(
                "_f_login" => $postdata["_f_login"]
            );
            $BEE_GLOBALS["is_login_call"] = true;
            $hrl_res = bee_hive_run_login($login_nector, $bee);
            $BEE_GLOBALS["is_login_call"] = false;
            $whole_honey["_f_login"] = $hrl_res[BEE_RI];
            $res[BEE_RI] = $whole_honey["_f_login"];
            $res[BEE_EI] = array_merge($res[BEE_EI],$hrl_res[BEE_EI]);
        }
        elseif(array_key_exists("_f_numverify",$postdata)){
            //phone number verification
            //it has to be the only thing in its request
            $res = array(null,array(),null);
            // set phone number
            $phone_number = $postdata["_f_numverify"]["phone_number"];
            $calling_code = $postdata["_f_numverify"]["calling_code"];
            $send_code = false;
            if(array_key_exists("send_code",$postdata["_f_numverify"]) &&
                $postdata["_f_numverify"]["send_code"] == true
            ){
                $send_code = true;
            }

            // // set API Access Key
            // $access_key = $bee["BEE_HIVE_STRUCTURE"]["numverify_token"];
            //
            // // Initialize CURL:
            // $ch = curl_init('http://apilayer.net/api/validate?access_key='.$access_key.'&number='.$phone_number.'&country_code='.$country_id);
            // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            // // Store the data:
            // $json = curl_exec($ch);
            // curl_close($ch);
            // // Decode JSON response:
            // $validationResult = json_decode($json, true);
            // // Access and use your preferred validation result objects
            // $whole_honey["_f_numverify"] = [
            //   "is_valid" => $validationResult['valid'],
            //   "carrier" => $validationResult['carrier'],
            //   //"country_code" => $validationResult['country_code']
            // ];

            //twilio
            //curl -X GET 'https://lookups.twilio.com/v1/PhoneNumbers/+15108675310?Type=carrier' \
            //-u $TWILIO_ACCOUNT_SID:$TWILIO_AUTH_TOKEN
            $v_errors = [];
            $formulated_number = '+' . $calling_code . $phone_number;
            // $ch = curl_init('https://lookups.twilio.com/v1/PhoneNumbers/'.$formulated_number.'?Type=carrier');

            $ch = curl_init('https://lookups.twilio.com/v1/PhoneNumbers/'.$formulated_number);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($ch, CURLOPT_POSTFIELDS, "Type=carrier");
            curl_setopt($ch, CURLOPT_USERPWD, "ACf0b76ab9f13a13682f2189f3fca854a5:aadc9b8a5e3d890a13f9d2573d56c1b6");
            // Store the data:
            $json = curl_exec($ch);
            curl_close($ch);
            //tools_dump("validation json res: ",__FILE__,__LINE__,$json);
            // Decode JSON response:
            $validationResult = json_decode($json,true);
            $temp_res = null;
            if(array_key_exists("status",$validationResult)){
              //phone number was not found
              $v_errors = ["The phone number " . $phone_number ." does not exist in the selected country"];
              $temp_res = null;
            }else{
              $temp_res = $validationResult;
            }
            //var_dump($validationResult["status"]);
            //tools_dumpx("validationResult: ",__FILE__,__LINE__,[$validationResult]);
            $res[BEE_RI] = [
                "_f_numverify" => $temp_res
            ];

            
            $res[BEE_EI] = array_merge($res[BEE_EI],$v_errors);
        }
        elseif(array_key_exists("_f_numcodeverify",$postdata)){
            //phone number verification
            //it has to be the only thing in its request
            $res = array(null,array(),null);
            // set phone number
            $phone_number = $postdata["_f_numcodeverify"]["phone_number"];
            $calling_code = $postdata["_f_numcodeverify"]["calling_code"];
            $code = "";
            if(array_key_exists("code",$postdata["_f_numcodeverify"])){
                $code = $postdata["_f_numcodeverify"]["code"];
            }

            //twilio
            //curl -X GET 'https://lookups.twilio.com/v1/PhoneNumbers/+15108675310?Type=carrier' \
            //-u $TWILIO_ACCOUNT_SID:$TWILIO_AUTH_TOKEN
            $v_errors = [];
            $formulated_number = '+' . $calling_code . $phone_number;
            // $ch = curl_init('https://lookups.twilio.com/v1/PhoneNumbers/'.$formulated_number.'?Type=carrier');

            $ch = curl_init('https://lookups.twilio.com/v1/PhoneNumbers/'.$formulated_number);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($ch, CURLOPT_POSTFIELDS, "Type=carrier");
            curl_setopt($ch, CURLOPT_USERPWD, "ACf0b76ab9f13a13682f2189f3fca854a5:aadc9b8a5e3d890a13f9d2573d56c1b6");
            // Store the data:
            $json = curl_exec($ch);
            curl_close($ch);
            //tools_dump("validation json res: ",__FILE__,__LINE__,$json);
            // Decode JSON response:
            $validationResult = json_decode($json,true);
            $temp_res = null;
            if($validationResult == null || array_key_exists("status",$validationResult)){
              //phone number was not found
              $v_errors = ["The phone number " . $phone_number ." does not exist in the selected country"];
              if($validationResult == null){
                $v_errors = ["Phone number " . $phone_number ." lookup failed"];
              }
              $temp_res = null;
            }else{
              $temp_res = $validationResult;
            }
            //var_dump($validationResult["status"]);
            //tools_dumpx("validationResult: ",__FILE__,__LINE__,[$validationResult]);
            $res[BEE_RI] = [
                "_f_numcodeverify" => $temp_res
            ];

            
            if(count($v_errors) == 0){
                //twilio to send verification code
                $api_url = 'https://verify.twilio.com/v2/Services/VAb80255a0dec5575ba4ed213a12b9d3d8/Verifications';
                $postFields = array(
                    "To" => $temp_res["phone_number"], // $formulated_number,
                    "Channel" => "sms"
                );
                if(strlen($code) > 2){
                    //means we want to verify
                    $api_url = 'https://verify.twilio.com/v2/Services/VAb80255a0dec5575ba4ed213a12b9d3d8/VerificationCheck';
                    $postFields = array(
                        "To" => $temp_res["phone_number"],
                        "Code" => $code
                    );
                }
                $ch = curl_init($api_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
                // curl_setopt($ch, CURLOPT_POSTFIELDS, "Channel=sms");
                curl_setopt($ch, CURLOPT_USERPWD, "ACf0b76ab9f13a13682f2189f3fca854a5:aadc9b8a5e3d890a13f9d2573d56c1b6");
                // Store the data:
                $json = curl_exec($ch);
                curl_close($ch);
                // Decode JSON response:
                $verifyResults = json_decode($json,true);
                //tools_dumpx("validationResult: ",__FILE__,__LINE__,[$sendCodeResult,$formulated_number]);
                $temp_res2 = null;
                if($verifyResults != null && array_key_exists("status",$verifyResults) && $verifyResults['status'] == "pending" && strlen($code) == 0){
                    $temp_res2 = "pending";
                }else if(strlen($code) == 0 || $verifyResults == null){
                    //sms wa not sent
                    $v_errors = ["Failed to send verification sms to " . $phone_number];
                    $temp_res2 = "failed";
                }else if($verifyResults == null || (strlen($code) > 0 && $verifyResults["status"] != "approved")){
                    // $temp_res2 = "i dot know";
                    // tools_dumpx("validationResult: ",__FILE__,__LINE__,[$verifyResults,$temp_res["phone_number"],$code]);
                    $v_errors = ["Code verification failed" ];
                    if(strval($verifyResults["status"]) == "404"){
                        $v_errors = ["Code verification failed because the code expired or is not valid"];
                    }
                    $temp_res2 = "failed";
                }else if(strlen($code) > 0 && $verifyResults !== null && $verifyResults["status"] == "approved"){
                    $temp_res2 = "approved";
                }
                
                $res[BEE_RI]["_f_numcodeverify"]["send_code"] = $temp_res2;
            }else{
                $res[BEE_RI]["_f_numcodeverify"]["send_code"] = "failed";
            }
            
            
            $res[BEE_EI] = array_merge($res[BEE_EI],$v_errors);
        }
        elseif(array_key_exists("_f_token",$postdata)){
            //the token
            //it has to be the only thing in its request
            $whole_honey = array();
            $token = $postdata["_f_token"];
            $res = array(null,array(),null);
            $rht_res = bee_run_handle_token($res,$bee,$token);
            $whole_honey["_f_token"] = $rht_res["res"][BEE_RI];
            $res[BEE_RI] = $whole_honey["_f_token"];
            if(count($rht_res["res"][BEE_EI]) > 0){
                $res[BEE_RI]["_f_token"] = false;
                $rht_res["res"][BEE_EI] = [];
            }else{
                $res[BEE_RI]["_f_token"] = true;
                $rht_res["res"][BEE_EI] = [];
            }
            $res[BEE_EI] = array_merge($res[BEE_EI],$rht_res["res"][BEE_EI]);
        }
        elseif(array_key_exists("_f_register",$postdata) && $bee["BEE_HIVE_STRUCTURE"]["is_registration_public"] == true){
            $whole_honey = array();
            $register_nector = array(
                "_f_register" => $postdata["_f_register"]
            );
            $BEE_GLOBALS["is_register_call"] = true;
            $code = tool_code();
            $brrh_res = null;
            $sendEmail = true;
            //tools_dumpx("part b: ",__FILE__,__LINE__,$bee);
            if((array_key_exists("is_registration_offline",$bee["BEE_HIVE_STRUCTURE"]) &&
                $bee["BEE_HIVE_STRUCTURE"]["is_registration_offline"] == true ) ||
                (array_key_exists("register_as_active",$bee["BEE_HIVE_STRUCTURE"]) &&
                $bee["BEE_HIVE_STRUCTURE"]["register_as_active"] == true)
                ){
                $sendEmail = false;
                $brrh_res = hive_run_register_hive($register_nector,$bee,array(
                    "code" => "",
                    "status" => "active",
                    "is_owner" => 1
                ));
            }else{
                //check if the hive needs to send an activation email
                $brrh_res = hive_run_register_hive($register_nector,$bee,array(
                    "code" => $code,
                    "status" => "pending",
                    "is_owner" => 1
                ));
            }

            if(array_key_exists("send_activation_email",$bee["BEE_HIVE_STRUCTURE"]) &&
                $bee["BEE_HIVE_STRUCTURE"]["send_activation_email"] == false ){
                $sendEmail = false;
            }



            //tools_dumpx("brrh_res",__FILE__,__LINE__,$brrh_res);
            $bee["BEE_HIVE_CONNECTION"] = $brrh_res[BEE_RI];
            $BEE_GLOBALS["is_register_call"] = false;
            $whole_honey["_f_register"] = "OK";
            $res[BEE_RI] = $whole_honey;
            $res[BEE_EI] = array_merge($res[BEE_EI],$brrh_res[BEE_EI]);
            //send activation email
            if(count($res[BEE_EI]) == 0 && $sendEmail == true){
                $linkObj = $bee["BEE_HIVE_STRUCTURE"]["activation_link"];
                $temp =  "h=" . $postdata["_f_register"]["app_name"] . "&i=" . $brrh_res[2]["hive_id"] . "&c=" . $code;
                $link = str_replace("---",$temp,$linkObj);
                $resx = mailer_send_verification_email(
                    $postdata["_f_register"]["email"],
                    $postdata["_f_register"]["name"],
                    $link,
                    $bee
                );
                // tools_dumpx("email debug",__FILE__,__LINE__,$resx);
            }
        }elseif(array_key_exists("_f_signup",$postdata) && $bee["BEE_HIVE_STRUCTURE"]["is_registration_public"] == false
        && $bee["BEE_HIVE_STRUCTURE"]["is_signup_public"] == true){
            $whole_honey = array();
            if(array_key_exists("as",$postdata["_f_signup"]) == false){
                $res[BEE_EI] = array("Please provide a value for the 'as' property { _f_signup: { user: {...}, as: 'value here' } } ");
            }

            if($bee["BEE_HIVE_STRUCTURE"]["is_signup_public_and_login"] == true){
                // if($bee["BEE_HIVE_STRUCTURE"]["is_signup_as_active"] == false){
                //     $res[BEE_EI] = array("If is_signup_public_and_login == true then is_signup_as_active is expected to also be true");
                // }
            }

            if(count($res[BEE_EI]) == 0){

                //first login as a the db owner
                //ti\his works beacuse the db owner s already in the garden user collections
                $whole_honey = array();
                $login_nector = array(
                    "_f_login" => array(
                        "email" => $bee["BEE_HIVE_STRUCTURE"]["_f_register"]["email"],
                        "password" => $bee["BEE_HIVE_STRUCTURE"]["_f_register"]["password"],
                    )
                );
                $BEE_GLOBALS["is_login_call"] = true;
                $hrl_res = bee_hive_run_login($login_nector, $bee);
                $BEE_GLOBALS["is_login_call"] = false;
                $res[BEE_EI] = array_merge($res[BEE_EI],$hrl_res[BEE_EI]);

                //end login
                $code = tool_code();
                if(count($res[BEE_EI]) == 0){
                    $main_user = $hrl_res[BEE_RI]["_f_login"]["user"];
                    $token = $hrl_res[BEE_RI]["_f_login"]["token"];
                    $rht_res = bee_run_handle_token($res,$bee,$token);
                    $res = $rht_res["res"];
                    $bee = $rht_res["bee"];
                    if(count($res[BEE_EI]) == 0){


                        $signup_nector = $postdata["_f_signup"]["user"];
                        $signup_nector["tenant_of"] = $main_user["tenant_of"];
                        $signup_nector["is_owner"] = 0;
                        $original_password = "";
                        if(isset($signup_nector["password"])){
                            $original_password = $signup_nector["password"];
                            $signup_nector["password"] = password_hash($signup_nector["password"], PASSWORD_DEFAULT);
                        }elseif(isset($signup_nector["_encrypt_password"])){
                            $original_password = $signup_nector["_encrypt_password"];
                            $signup_nector["password"] = password_hash($signup_nector["_encrypt_password"], PASSWORD_DEFAULT);
                        }
                        if($bee["BEE_HIVE_STRUCTURE"]["is_signup_as_active"] == true){
                            $signup_nector["status"] = "active";
                            $signup_nector["code"] = "";
                        }else{
                            $signup_nector["status"] = "pending";
                            $signup_nector["code"] = $code;
                        }

                        //consider unique entries
                        if(array_key_exists("signup_unique_user_keys",$bee["BEE_HIVE_STRUCTURE"]) == true){

                          $unique_errors = [];
                          $unique_config = $bee["BEE_HIVE_STRUCTURE"]["signup_unique_user_keys"];
                          foreach ($unique_config as $key => $conf) {

                            $query_ref_key = $key;
                            //check if we have a refrence key
                            if(array_key_exists("query_ref_key",$conf) == true){
                                //overide
                                $query_ref_key = $conf["query_ref_key"];
                            }
                            if(array_key_exists($query_ref_key,$signup_nector) == false){
                                $unique_errors = ["Missing unique key attribute " . $query_ref_key . " in user data"];
                                break;
                            }
                            $uvalue = $signup_nector[$query_ref_key]; //$main_user[$key];
                            $uquery = [
                                "users" => [
                                    "_w" => [
                                        [$key,"e",$uvalue]
                                    ]
                                ]
                            ];

                            

                            $brp_res = bee_run_get($uquery,$bee["BEE_HIVE_STRUCTURE"]["combs"],$bee["BEE_HIVE_CONNECTION"]);
                            $unique_errors = array_merge($unique_errors,$brp_res[BEE_EI]);
                            //tools_dump("check".$key,__FILE__,__LINE__,$bee_sql_backet);
                            if(count($unique_errors) > 0){
                              //we cannot continue if we are getting errors
                              break;
                            }else if(count($brp_res[0]["users"]) > 0){
                              $unique_errors = [$conf["msg"]];
                            }
                          }
                          if(count($unique_errors) > 0){
                            $res[BEE_EI] = $unique_errors;
                          }
                        }


                        if(count($res[BEE_EI])==0){
                          //if everything is fine so far
                          $postNect = array(
                              "user" => $signup_nector
                          );
                          $signup_as = $postdata["_f_signup"]["as"];
                          $role_id = array();
                          //roles
                          if(array_key_exists("signup_public_role_ids",$bee["BEE_HIVE_STRUCTURE"]) &&
                          array_key_exists($signup_as,$bee["BEE_HIVE_STRUCTURE"]["signup_public_role_ids"])
                          ){
                              $role_ids = $bee["BEE_HIVE_STRUCTURE"]["signup_public_role_ids"][$signup_as];
                              for ($i=0; $i < count($role_ids); $i++) {
                                  $role_id = $role_ids[$i];
                                  if(array_key_exists("user_roles",$postNect) == false){
                                      $postNect["user_roles"] = array();
                                  }
                                  array_push($postNect["user_roles"],array(
                                      "_fk_user_id" => "user",
                                      "role_id" => $role_id,
                                      "status" => "active"
                                  ));
                              }
                          }
                          //post
                          $brp_res2 = bee_run_post($postNect,$bee,0);
                          $res[BEE_EI] = array_merge($res[BEE_EI],$brp_res2[BEE_EI]);

                          //was a single object
                          $hive_user_nector = array(
                              "hive_user" => array(
                                  "hive_id" => $bee["BEE_HIVE_ID"],
                                  "hive_name" => $bee["BEE_APP_NAME"],
                                  "user_id" =>  $brp_res2[BEE_RI]["user"],
                                  "email" => $signup_nector["email"],
                                  "password" => $signup_nector["password"]
                              )
                          );
                          //cbh
                          $brp_res3 = bee_hive_post($hive_user_nector,$bee["BEE_GARDEN_STRUCTURE"],$bee["BEE_GARDEN_CONNECTION"],0);
                          $res[BEE_EI] = array_merge($res[BEE_EI],$brp_res3[BEE_EI]);
                          if(count($res[BEE_EI])==0){
                              if($bee["BEE_HIVE_STRUCTURE"]["is_signup_public_and_login"] == true){
                                  if($bee["BEE_HIVE_STRUCTURE"]["is_signup_as_active"] == false){
                                      //nyd
                                      //error must have already been caught upwards
                                      $whole_honey["_f_signup"] = "OK";
                                  }else{
                                      //login the user
                                      $login_nector = array(
                                          "_f_login" => array(
                                              "email" => $signup_nector["email"],
                                              "password" => $original_password,
                                          )
                                      );
                                      $BEE_GLOBALS["is_login_call"] = true;
                                      $hrl_res = bee_hive_run_login($login_nector, $bee, $brp_res2[BEE_RI]["user"], $bee["BEE_APP_NAME"], $bee["BEE_HIVE_ID"]);
                                      $BEE_GLOBALS["is_login_call"] = false;
                                      $res[BEE_EI] = array_merge($res[BEE_EI],$hrl_res[BEE_EI]);
                                      if(count($res[BEE_EI])==0){
                                          $whole_honey["_f_signup"] = $hrl_res[BEE_RI]["_f_login"];
                                      }else{
                                          //tools_dumpx("grate ",__FILE__,__LINE__,array($signup_nector, $login_nector, $hrl_res));
                                      }
                                  }
                              }else{
                                  $whole_honey["_f_signup"] = "OK";
                              }
                          }

                          //send activation code
                          if(count($res[BEE_EI])==0 && $bee["BEE_HIVE_STRUCTURE"]["is_signup_send_activation_code"] == true){
                              if(is_numeric($signup_nector["email"])){
                                  //probably its a phone send sms code
                                  mailer_send_verification_sms(
                                      $signup_nector["email"],
                                      $signup_nector["name"],
                                      $bee["BEE_APP_NAME"],
                                      $bee["BEE_HIVE_ID"],
                                      $code,
                                      $bee,
                                      $signup_nector["country_id"]
                                  );
                              }else{
                                  $linkObj = $bee["BEE_HIVE_STRUCTURE"]["activation_link"];
                                  $temp =  "h=" . $bee["BEE_APP_NAME"] . "&i=" . $bee["BEE_HIVE_ID"] . "&c=" . $code;
                                  $link = $code; //str_replace("---",$temp,$linkObj);
                                  mailer_send_verification_email(
                                      $signup_nector["email"],
                                      $signup_nector["name"],
                                      $link,
                                      $bee
                                  );
                              }
                          }
                        }
                    }
                }else if(count($res[BEE_EI]) == 1 && $res[BEE_EI][0] == "Business account not found"){
                    $res[BEE_EI][0] = "Incorrect System Credentials Check Configurations";
                }
            }
            $res[BEE_RI] = $whole_honey;

        }elseif(array_key_exists("_f_invite",$postdata) && $bee["BEE_HIVE_STRUCTURE"]["is_registration_public"] == true
        && $bee["BEE_HIVE_STRUCTURE"]["is_signup_public"] == false){
            //the admin invites users to their platform
            //the passwords are auto generated and sent out to the invited users
            $BEE_GLOBALS["is_inivite_call"] = true; //prevents double entry 
            $whole_honey = array(
                "_f_invite" => array()
            );
            if(count($res[BEE_EI]) == 0){
                foreach ($postdata["_f_invite"] as $index => $invitation) {
                    $email = $invitation["email"];
                    $name = $invitation["name"];
                    $role_id = $invitation["as"];
                    $original_password = bin2hex(openssl_random_pseudo_bytes(8));
                    $password = password_hash($original_password, PASSWORD_DEFAULT);
                    $status = "pending";
                    $code = tool_code();
                    $tenant_of = $bee["BEE_APP_NAME"];
                    $postNect = array(
                        "user" => array(
                            "name" => $name,
                            "email" => $email,
                            "tenant_of" => $tenant_of,
                            "is_owner" => 0,
                            "password" => $password,
                            "status" => "pending",
                            "code" => $code
                        ),
                        "user_role" => array(
                            "_fk_user_id" => "user",
                            "role_id" => $role_id,
                            "status" => "active"
                        )
                    );
                    $brp_res2 = bee_run_post($postNect,$bee,0);
                    $res[BEE_EI] = array_merge($res[BEE_EI],$brp_res2[BEE_EI]);
                    $hive_user_nector = array(
                        "hive_user" => array(
                            "hive_id" => $bee["BEE_HIVE_ID"],
                            "hive_name" => $tenant_of,
                            "user_id" =>  $brp_res2[BEE_RI]["user"],
                            "email" => $email,
                            "password" => $password
                        )
                    );
                    $brp_res3 = bee_hive_post($hive_user_nector,$bee["BEE_GARDEN_STRUCTURE"],$bee["BEE_GARDEN_CONNECTION"],0);
                    $res[BEE_EI] = array_merge($res[BEE_EI],$brp_res3[BEE_EI]);
                    array_push($whole_honey["_f_invite"], $brp_res2[BEE_RI]["user"]);
                    //send invitation email
                    //nyd: inprove the lines below and save them form hard coding
                    $company_str = str_replace("mpola_", "", $tenant_of);
                    $company = str_replace("_", " ", $company_str);
                    $roles = array(
                        "2" => "Asset Manager",
                        "3" => "Head Of Accounts",
                        "4" => "Store Manager",
                        "5" => "Company Staff"
                    );
                    mailer_send_invitation_email(
                        $email,
                        $name,
                        $code,
                        $original_password,
                        $company,
                        $roles[strval($role_id)]
                    );
                }
            }
            $BEE_GLOBALS["is_inivite_call"] = false;
            $res[BEE_RI] = $whole_honey;

        }elseif(array_key_exists("_f_signup",$postdata) && $bee["BEE_HIVE_STRUCTURE"]["is_signup_public"] == false){
            array_push($res[BEE_EI],"sorry _f_signup operation is a private operation, !zzzz... ");
        }elseif(array_key_exists("_f_register",$postdata) && $bee["BEE_HIVE_STRUCTURE"]["is_registration_public"] == false){
            array_push($res[BEE_EI],"sorry _f_register operation is a private operation, !zzzz... ");
        }elseif(array_key_exists("_f_activate",$postdata) && $bee["BEE_HIVE_STRUCTURE"]["is_registration_public"] == true){
            $whole_honey = array();
            $activate_nector = array(
                "_f_activate" => $postdata["_f_activate"]
            );
            $BEE_GLOBALS["is_activate_call"] = true;
            $brrh_res = hive_run_activate_hive($activate_nector,$bee);
            //tools_dumpx("brrh_res",__FILE__,__LINE__,$brrh_res);
            $BEE_GLOBALS["is_activate_call"] = false;
            $whole_honey["_f_activate"] = "OK";
            $res[BEE_RI] = $whole_honey;
            $res[BEE_EI] = array_merge($res[BEE_EI],$brrh_res[BEE_EI]);
        }elseif(array_key_exists("_f_verify",$postdata) ){
            $whole_honey = array();
            $activate_nector = array(
                "_f_verify" => $postdata["_f_verify"]
            );
            $BEE_GLOBALS["is_activate_call"] = true;
            $brrh_res = hive_run_activate_signup($activate_nector,$bee);
            //tools_dumpx("brrh_res",__FILE__,__LINE__,$brrh_res);
            $BEE_GLOBALS["is_activate_call"] = false;
            $whole_honey["_f_verify"] = "OK";
            $res[BEE_RI] = $whole_honey;
            $res[BEE_EI] = array_merge($res[BEE_EI],$brrh_res[BEE_EI]);
        }elseif(array_key_exists("_f_activate",$postdata) && $bee["BEE_HIVE_STRUCTURE"]["is_registration_public"] == false){
            array_push($res[BEE_EI],"sorry _f_activate operation is not available, !zzzz... ");
        }elseif(array_key_exists("_f_recover",$postdata) && $bee["BEE_HIVE_STRUCTURE"]["is_registration_public"] == true){
            $whole_honey = array();
            $activate_nector = array(
                "_f_recover" => $postdata["_f_recover"]
            );
            $BEE_GLOBALS["is_recover_call"] = true;
            $brrh_res = hive_run_recover_hive($activate_nector,$bee);
            //tools_dumpx("brrh_res",__FILE__,__LINE__,$brrh_res);
            $BEE_GLOBALS["is_recover_call"] = false;
            $whole_honey["_f_recover"] = "OK";
            $res[BEE_RI] = $whole_honey;
            $res[BEE_EI] = array_merge($res[BEE_EI],$brrh_res[BEE_EI]);
            //tools_dump("brrh_res",__FILE__,__LINE__,$brrh_res[BEE_RI]);
            //send recovery email
            if(count($res[BEE_EI]) == 0){
                $linkObj = $bee["BEE_HIVE_STRUCTURE"]["reset_link"];

                $links = array();
                foreach ($brrh_res[BEE_RI] as $index => $found_records) {
                    $temp =  "h=" . $found_records["hive_name"] . "&i=" . $found_records["user_id"] . "&c=" . $found_records["code"];
                    $link = str_replace("---",$temp,$linkObj);
                    array_push($links,array(
                        "title" => $found_records["hive_name"],
                        "link" => $link
                    ));
                }
                //tools_dumpx("links",__FILE__,__LINE__,$links);

                mailer_send_recovery_email(
                    $postdata["_f_recover"]["email"],
                    "USER",
                    $links,
                    $bee
                );
            }
        }elseif(array_key_exists("_f_recover",$postdata) && $bee["BEE_HIVE_STRUCTURE"]["is_registration_public"] == false){
            array_push($res[BEE_EI],"sorry _f_recover operation is not available, !zzzz... ");
        }elseif(array_key_exists("_f_forgot",$postdata)){
            $whole_honey = array();
            $activate_nector = array(
                "_f_forgot" => $postdata["_f_forgot"]
            );
            $BEE_GLOBALS["is_recover_call"] = true;
            $brrh_res = hive_run_forgot_password($activate_nector,$bee);
            //tools_dumpx("brrh_res",__FILE__,__LINE__,$brrh_res);
            $BEE_GLOBALS["is_recover_call"] = false;
            $whole_honey["_f_forgot"] = "OK";
            $res[BEE_RI] = $whole_honey;
            $res[BEE_EI] = array_merge($res[BEE_EI],$brrh_res[BEE_EI]);
            //tools_dump("brrh_res",__FILE__,__LINE__,$brrh_res[BEE_RI]);
            //send recovery email
            if(count($res[BEE_EI]) == 0){
                //send activation code
                if(count($res[BEE_EI])==0 && $bee["BEE_HIVE_STRUCTURE"]["is_signup_send_activation_code"] == true){
                    $email = $activate_nector["_f_forgot"]["email"];
                    $hive_name = "";
                    $user_id = 0;
                    $code = "";
                    $links = array();
                    $country = "";
                    foreach ($brrh_res[BEE_RI] as $index => $found_records) {
                        $hive_name = $found_records["hive_name"];
                        $user_id = $found_records["user_id"];
                        $code = $found_records["code"];
                        $country = $found_records["user"]["country_id"];
                        break;
                    }


                    if(is_numeric($email)){
                        //probably its a phone send sms code
                        mailer_send_recovery_sms(
                            $email,
                            $code,
                            $bee,
                            $country
                        );
                    }else{
                        $link = "";
                        $linkObj = $bee["BEE_HIVE_STRUCTURE"]["reset_link"];
                        $temp =  "h=" . $hive_name . "&i=" . $user_id . "&c=" . $code;
                        $link = $code; //str_replace("---",$temp,$linkObj);
                        array_push($links,array(
                            "title" => $hive_name,
                            "link" => $link
                        ));

                        mailer_send_recovery_email(
                          $postdata["_f_forgot"]["email"],
                          "USER",
                          $code
                        );

                        // mailer_send_recovery_email(
                        //     $postdata["_f_forgot"]["email"],
                        //     "USER",
                        //     $links,
                        //     $bee
                        // );

                    }
                }
            }
        }elseif(array_key_exists("_f_reset",$postdata) && $bee["BEE_HIVE_STRUCTURE"]["is_registration_public"] == true){
            $whole_honey = array();
            $reset_nector = array(
                "_f_reset" => $postdata["_f_reset"]
            );
            $BEE_GLOBALS["is_reset_call"] = true;
            $brrh_res = hive_run_reset_hive($reset_nector,$bee);
            //tools_dumpx("brrh_res",__FILE__,__LINE__,$brrh_res);
            $BEE_GLOBALS["is_reset_call"] = false;
            $whole_honey["_f_reset"] = "OK";
            $res[BEE_RI] = $whole_honey;
            $res[BEE_EI] = array_merge($res[BEE_EI],$brrh_res[BEE_EI]);
        }elseif(array_key_exists("_f_reset",$postdata) && $bee["BEE_HIVE_STRUCTURE"]["is_registration_public"] == false){
            array_push($res[BEE_EI],"sorry _f_reset operation is not available, !zzzz... ");
        }elseif(array_key_exists("_f_change",$postdata)){
            $whole_honey = array();
            $reset_nector = array(
                "_f_change" => $postdata["_f_change"]
            );
            $BEE_GLOBALS["is_reset_call"] = true;
            $brrh_res = hive_run_change_password($reset_nector,$bee);
            //tools_dumpx("brrh_res",__FILE__,__LINE__,$brrh_res);
            $BEE_GLOBALS["is_reset_call"] = false;
            $whole_honey["_f_change"] = "OK";
            $res[BEE_RI] = $whole_honey;
            $res[BEE_EI] = array_merge($res[BEE_EI],$brrh_res[BEE_EI]);
        }elseif(array_key_exists("_f_password_edit_multitenat",$postdata)){
            $whole_honey = array();
            $reset_nector = array(
                "_f_password_edit_multitenat" => $postdata["_f_password_edit_multitenat"]
            );
            $BEE_GLOBALS["is_reset_call"] = true;
            $brrh_res = hive_run_password_edit_multitenat($reset_nector,$bee);
            //tools_dumpx("brrh_res",__FILE__,__LINE__,$brrh_res);
            $BEE_GLOBALS["is_reset_call"] = false;
            $whole_honey["_f_password_edit_multitenat"] = "OK";
            $res[BEE_RI] = $whole_honey;
            $res[BEE_EI] = array_merge($res[BEE_EI],$brrh_res[BEE_EI]);
        }else{
            //authorise
            $bsv_res = bee_security_authorise(
                $bee["BEE_USER"],
                $postdata,
                $bee["BEE_HIVE_STRUCTURE"]["combs"],
                true, //create
                false, //read
                false, //update
                false //delete
            );
            $res[BEE_EI] = array_merge($res[BEE_EI],$bsv_res[BEE_EI]);
            if(count($res[BEE_EI])==0){//no errors
                //tools_dumpx("postdata",__FILE__,__LINE__,$postdata);
                $brp_res = bee_run_post($postdata,$bee,$bee["BEE_USER"]["id"]);
                //tools_dumpx("brp_res post ",__FILE__,__LINE__,$brp_res);
                $res[BEE_EI] = array_merge($res[BEE_EI],$brp_res[BEE_EI]);
                $res[BEE_RI] = $brp_res[BEE_RI];
            }
        }
    }

    return $res;
}
?>
