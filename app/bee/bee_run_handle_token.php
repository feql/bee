<?php
function bee_run_handle_token($res,$bee,$use_this_token_string= null){
    global $BEE_GLOBALS;
    global $countries_list;
    $token_string = null;
    $headers = apache_request_headers();

    if($use_this_token_string == null){
        if($headers == null){
            array_push($res[BEE_EI],"Request missing headers");
            return array("res"=>$res,"bee"=>$bee);
        }

        if(isset($headers["Authorization"]) && stripos($headers["Authorization"],"Bearer ") > -1){
            $token_string = str_ireplace("Bearer ","",$headers["Authorization"]);
        }else if(isset($headers["authorization"]) && stripos($headers["authorization"],"Bearer ") > -1){
            $token_string = str_ireplace("Bearer ","",$headers["authorization"]);
        }else if(isset($headers["AUTHORIZATION"]) && stripos($headers["AUTHORIZATION"],"Bearer ") > -1){
            $token_string = trim(str_ireplace("Bearer ","",$headers["Authorization"]));
        }
    }else{
        $token_string = $use_this_token_string;
    }
    //tools_dumpx("token_string",__FILE__,__LINE__,$headers);
    if($token_string != null){
        $jwt = new Emarref\Jwt\Jwt();
        
        try {
            $token = $jwt->deserialize($token_string);
            $context = new Emarref\Jwt\Verification\Context($bee["BEE_JWT_ENCRYPTION"]);
            $context->setAudience('audience_1');
            $context->setIssuer('your_issuer');
            $context->setSubject('api');
            //tools_dumpx("token_string1",__FILE__,__LINE__,$token_string);

            $jwt->verify($token, $context);
            $payload = $token->getPayload();
            $current_user_id = $payload->findClaimByName("user")->getValue();
            $an = $payload->findClaimByName("app_name")->getValue();
            $hive_id = $payload->findClaimByName("app_id")->getValue();
            $user_nector = array(
                "users" => array(
                    "_w" => array(
                        array(
                            "id","=",$current_user_id
                        )
                    ),
                    "user_roles" => array(
                        "role" => array(
                            "role_permisiions" => array(),
                            "role_modules" => array()
                        )
                    )
                )
            );
            //tools_dumpx("token_string2",__FILE__,__LINE__,$token_string);
            if($bee["BEE_HIVE_CONNECTION"] == null){
                $hrgc_res = hive_run_get_connection(BEE_USER_NAME, BEE_PASSWORD,BEE_SERVER_NAME,$an,false);
                $res[BEE_EI] = array_merge($res[BEE_EI],$hrgc_res[BEE_EI]);
                $connection = $hrgc_res[BEE_RI];
                $bee["BEE_HIVE_CONNECTION"] = $connection;
            }
            $brg_res = bee_run_get($user_nector,$bee["BEE_HIVE_STRUCTURE"]["combs"],$bee["BEE_HIVE_CONNECTION"]);
            $res[BEE_EI] = array_merge($res[BEE_EI],$brg_res[BEE_EI]);
            $current_user = $brg_res[BEE_RI]["users"][0];
            //tools_dumpx("foo",__FILE__,__LINE__,$current_user);
            

            $bee["BEE_USER"] = $current_user;
            $bee["BEE_HIVE_ID"] = $hive_id;
            $bee["BEE_APP_NAME"] = $an;
            //get connection
            //tools_dumpx("token_string1",__FILE__,__LINE__,$bee["BEE_USER"]);
        } catch (Emarref\Jwt\Exception\VerificationException $e) {
            $msg = $e->getMessage();
            //tools_dumpx("token_string xxx ",__FILE__,__LINE__,$msg);
            array_push($res[BEE_EI],$msg);
        } catch (\Throwable $e) { // For PHP 7
            $msg = $e->getMessage();
            array_push($res[BEE_EI],$msg);
            //tools_dumpx("token_string xxx2 ",__FILE__,__LINE__,$res);
        } catch (\Exception $e) { // For PHP 5
            $msg = $e->getMessage();
            //tools_dumpx("token_string xxx3 ",__FILE__,__LINE__,$msg);
            array_push($res[BEE_EI],$msg);
        }
    }
    return array("res"=>$res,"bee"=>$bee);
}
?>