<?php
function bee_run_handle_put($res,$bee,$postdata=null,$current_honey=null){
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

        if(array_key_exists("_sql",$postdata)){
            $bee_show_sql = true;
        }

        if(array_key_exists("_f_password",$postdata) && 
                $bee["BEE_HIVE_STRUCTURE"]["is_registration_public"] == true ||
                (
                    array_key_exists("allow_password_update",$bee["BEE_HIVE_STRUCTURE"]) &&
                    $bee["BEE_HIVE_STRUCTURE"]["allow_password_update"] == true &&
                    array_key_exists("_f_password",$postdata)
                )
            ){
            if(isset($bee["BEE_USER"])){
                //tools_dumpx("here: ",__FILE__,__LINE__,true);
                $whole_honey = array();
                $reset_nector = array(
                    "_f_password" => $postdata["_f_password"]
                );
                $BEE_GLOBALS["is_password_call"] = true;
                //tools_dumpx("swett place a",__FILE__,__LINE__,$bee["BEE_USER"]);
                $brrh_res = hive_run_update_password($reset_nector,$bee);
                //tools_dumpx("swett place a",__FILE__,__LINE__,true);
                $BEE_GLOBALS["is_password_call"] = false;
                $whole_honey["_f_password"] = "OK";
                $res[BEE_RI] = $whole_honey;
                $res[BEE_EI] = array_merge($res[BEE_EI],$brrh_res[BEE_EI]);
                //perform a login to get a new token
                //if there were no errors
                if(count($brrh_res[BEE_EI])==0){
                    //get in the current state of the garden
                    $hrgg_res = hive_run_get_garden($bee["BEE_GARDEN_STRUCTURE"],$bee["BEE_GARDEN_CONNECTION"]);
                    $res[BEE_EI] = array_merge($res[BEE_EI],$hrgg_res[BEE_EI]); 
                    $BEE_GARDEN_STRUCTURE = $hrgg_res[2];
                    $BEE_GARDEN = $hrgg_res[BEE_RI];
                    $bee["BEE_GARDEN"] = $BEE_GARDEN;
                    //the login 
                    //it has to be the only thing in its request
                    $login_nector = array(
                        "_f_login" => array(
                            "email" => $bee["BEE_USER"]["email"],
                            "password" =>  $postdata["_f_password"]["new_password"]
                        )
                    );
                    $BEE_GLOBALS["is_login_call"] = true;
                    //tools_dumpx("swett place a",__FILE__,__LINE__,$login_nector);
                    $hrl_res = bee_hive_run_login($login_nector, $bee);
                    $BEE_GLOBALS["is_login_call"] = false; $hrl_res[BEE_RI];
                    $res[BEE_RI] = array(
                        "_f_password" => $hrl_res[BEE_RI]["_f_login"]
                    );
                    $res[BEE_EI] = array_merge($res[BEE_EI],$hrl_res[BEE_EI]); 
                }
            }else{
                array_push($res[BEE_EI],"sorry you must be logged in to access _f_password, !zzzz... ");
            }
        }elseif(array_key_exists("_f_password",$postdata) && $bee["BEE_HIVE_STRUCTURE"]["is_registration_public"] == false){
            array_push($res[BEE_EI],"sorry _f_password operation is not available, !zzzz... ");
        }elseif(array_key_exists("_f_username",$postdata) && 
                $bee["BEE_HIVE_STRUCTURE"]["is_registration_public"] == true ||
                (
                    array_key_exists("allow_username_update",$bee["BEE_HIVE_STRUCTURE"]) &&
                    $bee["BEE_HIVE_STRUCTURE"]["allow_username_update"] == true &&
                    array_key_exists("_f_username",$postdata)
                )
            ){
            if(isset($bee["BEE_USER"])){
                //tools_dumpx("here: ",__FILE__,__LINE__,true);
                $whole_honey = array();
                $reset_nector = array(
                    "_f_username" => $postdata["_f_username"]
                );
                $code = tool_code();
                if(array_key_exists("is_signup_as_active",$bee["BEE_HIVE_STRUCTURE"]) && 
                    $bee["BEE_HIVE_STRUCTURE"]["is_signup_as_active"] == true){
                    $reset_nector["_f_username"]["status"] = "active";
                    $reset_nector["_f_username"]["code"] = "";
                }else{
                    $reset_nector["_f_username"]["status"] = "pending";
                    $reset_nector["_f_username"]["code"] = $code;
                }
                
                $BEE_GLOBALS["is_password_call"] = true;

                // mailer_send_username_verification_email(
                //     $postdata["_f_username"]["new_email"],
                //     "Nyola Mike", 
                //     $code, 
                //     $bee
                // );

                //consider unique entries
                if(array_key_exists("update_username_unique_user_keys",$bee["BEE_HIVE_STRUCTURE"]) == true){
                    $unique_errors = [];
                    $unique_config = $bee["BEE_HIVE_STRUCTURE"]["update_username_unique_user_keys"];
                    foreach ($unique_config as $key => $conf) {
                        $query_ref_key = $key;
                        //check if we have a refrence key
                        if(array_key_exists("query_ref_key",$conf) == true){
                            //overide
                            $query_ref_key = $conf["query_ref_key"];
                        }
                      if(array_key_exists($query_ref_key,$reset_nector["_f_username"]) == false){
                        $unique_errors = ["Missing unique key attribute " . $query_ref_key . " in user data"];
                        break;
                      }
                      $uvalue = $reset_nector["_f_username"][$query_ref_key]; //$main_user[$key];
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
                    //tools_dumpx("swett place a",__FILE__,__LINE__,$bee["BEE_USER"]);
                    $brrh_res = hive_run_update_username($reset_nector,$bee);
                    //tools_dumpx("swett place a",__FILE__,__LINE__,true);
                    $BEE_GLOBALS["is_password_call"] = false;
                    $res[BEE_EI] = array_merge($res[BEE_EI],$brrh_res[BEE_EI]);
                    if(count($res[BEE_EI])==0){
                        if($bee["BEE_HIVE_STRUCTURE"]["is_signup_public_and_login"] == true){
                            if($bee["BEE_HIVE_STRUCTURE"]["is_signup_as_active"] == false){
                                //nyd
                                //error must have already been caught upwards
                                $whole_honey["_f_username"] = "OK";
                            }else{
                                //login the user
                                $login_nector = array(
                                    "_f_login" => array(
                                        "email" => $postdata["_f_username"]["new_email"],
                                        "password" => $postdata["_f_username"]["current_password"],
                                    )
                                );
                                $BEE_GLOBALS["is_login_call"] = true;
                                $hrl_res = bee_hive_run_login($login_nector, $bee, $bee["BEE_USER"]["id"], $bee["BEE_APP_NAME"], $bee["BEE_HIVE_ID"]);
                                $BEE_GLOBALS["is_login_call"] = false;
                                $res[BEE_EI] = array_merge($res[BEE_EI],$hrl_res[BEE_EI]); 
                                if(count($res[BEE_EI])==0){
                                    $whole_honey["_f_username"] = $hrl_res[BEE_RI]["_f_login"];
                                }else{
                                    //tools_dumpx("grate ",__FILE__,__LINE__,array($login_nector, $hrl_res));
                                }
                            }
                        }else{
                            $whole_honey["_f_username"] = "OK";
                        }
                    }
                    
                    $res[BEE_RI] = $whole_honey;
                    //send activation code
                    if(count($res[BEE_EI])==0 && $bee["BEE_HIVE_STRUCTURE"]["is_signup_send_activation_code"] == true){
                        if(is_numeric($postdata["_f_username"]["new_email"])){
                            //probably its a phone send sms code
                            mailer_send_verification_sms(
                                $postdata["_f_username"]["new_email"],
                                $bee["BEE_USER"]["name"], 
                                $bee["BEE_APP_NAME"], 
                                $bee["BEE_HIVE_ID"], 
                                $code,
                                $bee,
                                $bee["BEE_USER"]["country_id"]
                            );
                        }else{
                            $linkObj = $bee["BEE_HIVE_STRUCTURE"]["activation_link"];
                            $temp =  "h=" . $bee["BEE_APP_NAME"] . "&i=" . $bee["BEE_HIVE_ID"] . "&c=" . $code;
                            $link = $code; //str_replace("---",$temp,$linkObj);
                            mailer_send_username_verification_email(
                                $postdata["_f_username"]["new_email"],
                                $bee["BEE_USER"]["name"], 
                                $link, 
                                $bee
                            );
                        }
                    }
                }

            }else{
                array_push($res[BEE_EI],"sorry you must be logged in to access _f_username, !zzzz... ");
            }
        }elseif(array_key_exists("_f_username",$postdata) && $bee["BEE_HIVE_STRUCTURE"]["is_registration_public"] == false){
            array_push($res[BEE_EI],"sorry _f_username operation is not available, !zzzz... ");
        }elseif(array_key_exists("_f_edit_user_role",$postdata) && $bee["BEE_HIVE_STRUCTURE"]["is_registration_public"] == true
        && $bee["BEE_HIVE_STRUCTURE"]["is_signup_public"] == false){
            //the admin edites a users to their platform
            $BEE_GLOBALS["is_edit_user_role_call"] = true; //prevents double entry 
            $whole_honey = array(
                "_f_edit_user_role" => array()
            );
            if(count($res[BEE_EI]) == 0){
                foreach ($postdata["_f_edit_user_role"] as $index => $userrole) {
                    $email = $userrole["email"];
                    $name = $userrole["name"];
                    $role_id = $userrole["as"];
                    $previous_email = $userrole["previous_email"];
                    $user_id = $userrole["user_id"];
                    $tenant_of = $userrole["tenant"];

                    $email_changed = $previous_email != $email;
                    //delete all the users roles
                    $deleteNect = array(
                        "user_role" => array(
                            "_w" => [
                                ["user_id", "e", $user_id],
                            ]
                        )
                    );
                    $updateNect = array(
                        "user" => array(
                            "name" => $name,
                            "email" => $email,
                            "_w" => [
                                [
                                    ["id", "e", $user_id],
                                    "AND",
                                    ["tenant_of", "e", $tenant_of]
                                ]
                            ]
                        )
                    );
                    if($email_changed == true){
                        $code = tool_code();
                        $updateNect = array(
                            "user" => array(
                                "name" => $name,
                                "email" => $email,
                                "status" => "pending",
                                "code" => $code,
                                "_w" => [
                                    [
                                        ["id", "e", $user_id],
                                        "AND",
                                        ["tenant_of", "e", $tenant_of]
                                    ]
                                ]
                            )
                        );
                    }
                    $postNect = array(
                        "user_role" => array(
                            "user_id" => $user_id,
                            "role_id" => $role_id,
                            "status" => "active"
                        )
                    );
                    //delete
                    $brd_res = bee_run_delete($deleteNect,$bee,0);
                    $res[BEE_EI] = array_merge($res[BEE_EI],$brd_res[BEE_EI]);
                    //update
                    $bru_res = bee_run_update($updateNect,$bee,0);
                    $res[BEE_EI] = array_merge($res[BEE_EI],$bru_res[BEE_EI]);
                    //post
                    $brp_res = bee_run_post($postNect,$bee,0);
                    $res[BEE_EI] = array_merge($res[BEE_EI],$brp_res[BEE_EI]);
                    //the hive update
                    if($email_changed == true){
                        $update_user_nectar = array(
                            "hive_user" => array(
                                "email" => $email,
                                "_w"=>array(
                                    array(
                                        array("user_id","=",$user_id),
                                        "AND",
                                        array("hive_name","=",$tenant_of)
                                    )
                                )
                            ) 
                        );
                        $bhu_res = bee_hive_update(
                            $update_user_nectar,
                            $bee["BEE_GARDEN_STRUCTURE"],
                            $bee["BEE_GARDEN_CONNECTION"],
                            0,
                            $whole_honey
                        );
                        $res[BEE_EI] = array_merge($res[BEE_EI],$bhu_res[BEE_EI]);

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
                        mailer_send_edited_user_role_email(
                            $email,
                            $name,
                            $code,
                            $company,
                            $roles[strval($role_id)]
                        );
                    }
                    array_push($whole_honey["_f_edit_user_role"], "ok");
                }
            }
            $BEE_GLOBALS["is_edit_user_role_call"] = false;
            $res[BEE_RI] = $whole_honey;

        }elseif(array_key_exists("_f_edit_user_profile",$postdata) && $bee["BEE_HIVE_STRUCTURE"]["is_registration_public"] == true
        && $bee["BEE_HIVE_STRUCTURE"]["is_signup_public"] == false){
            //the admin edites a users to their platform
            $BEE_GLOBALS["is_edit_user_profile_call"] = true; //prevents double entry 
            $whole_honey = array(
                "_f_edit_user_profile" => array()
            );
            if(count($res[BEE_EI]) == 0){
                foreach ($postdata["_f_edit_user_profile"] as $index => $userrole) {
                    $email = $userrole["email"];
                    $name = $userrole["name"];
                    $previous_email = $userrole["previous_email"];
                    $user_id = $userrole["user_id"];
                    $tenant_of = $userrole["tenant"];

                    $email_changed = $previous_email != $email;
                    $updateNect = array(
                        "user" => array(
                            "name" => $name,
                            "email" => $email,
                            "_w" => [
                                [
                                    ["id", "e", $user_id],
                                    "AND",
                                    ["tenant_of", "e", $tenant_of]
                                ]
                            ]
                        )
                    );
                    if($email_changed == true){
                        $code = tool_code();
                        $updateNect = array(
                            "user" => array(
                                "name" => $name,
                                "email" => $email,
                                "status" => "pending",
                                "code" => $code,
                                "_w" => [
                                    [
                                        ["id", "e", $user_id],
                                        "AND",
                                        ["tenant_of", "e", $tenant_of]
                                    ]
                                ]
                            )
                        );
                    }
                    //update
                    $bru_res = bee_run_update($updateNect,$bee,0);
                    $res[BEE_EI] = array_merge($res[BEE_EI],$bru_res[BEE_EI]);
                    //the hive update
                    if($email_changed == true){
                        $update_user_nectar = array(
                            "hive_user" => array(
                                "email" => $email,
                                "_w"=>array(
                                    array(
                                        array("user_id","=",$user_id),
                                        "AND",
                                        array("hive_name","=",$tenant_of)
                                    )
                                )
                            ) 
                        );
                        $bhu_res = bee_hive_update(
                            $update_user_nectar,
                            $bee["BEE_GARDEN_STRUCTURE"],
                            $bee["BEE_GARDEN_CONNECTION"],
                            0,
                            $whole_honey
                        );
                        $res[BEE_EI] = array_merge($res[BEE_EI],$bhu_res[BEE_EI]);

                        //send invitation email
                        //nyd: inprove the lines below and save them form hard coding
                        $company_str = str_replace("mpola_", "", $tenant_of);
                        $company = str_replace("_", " ", $company_str);
                        mailer_send_edited_user_profile_email(
                            $email,
                            $name,
                            $code,
                            $company
                        );
                    }
                    array_push($whole_honey["_f_edit_user_role"], "ok");
                }
            }
            $BEE_GLOBALS["is_edit_user_profile_call"] = false;
            $res[BEE_RI] = $whole_honey;

        }else{
            //authorise
            $bsv_res = bee_security_authorise(
                $bee["BEE_USER"],
                $postdata,
                $bee["BEE_HIVE_STRUCTURE"]["combs"],
                false, //create
                false, //read
                true, //update
                false //delete
            );
            $res[BEE_EI] = array_merge($res[BEE_EI],$bsv_res[BEE_EI]);
            if(count($res[BEE_EI])==0){//no errors
                //tools_dumpx("postdata",__FILE__,__LINE__,[$res,$postdata,$current_honey]);
                $brp_res = bee_run_update($postdata,$bee,$bee["BEE_USER"]["id"],$current_honey);
                //tools_dumpx("brp_res put ",__FILE__,__LINE__,$brp_res);
                $res[BEE_EI] = array_merge($res[BEE_EI],$brp_res[BEE_EI]);
                $res[BEE_RI] = $brp_res[BEE_RI];
            }
        }  
    }
    return $res;
}
?>