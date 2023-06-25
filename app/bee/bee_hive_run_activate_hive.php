<?php
//this will activate the account
function hive_run_activate_hive($post_nectoroid,$bee,$defaults=null){
    $res = array(null, array());
    //db name
    // $hive_name = BEE_GARDEN . "_" . tools_sanitise_name($post_nectoroid["_f_activate"]["app_name"]);
    $hive_name = ""; 
    //code
    $code = $post_nectoroid["_f_activate"]["code"];
    //hive_id
    // $hive_id =intval($post_nectoroid["_f_activate"]["hive_id"]);
    $hive_id = 0;
    //nyd
    //validation

    //tools_dumpx("hives: ",__FILE__,__LINE__,array($hive_name, $code, $hive_id));
    $found = false;
    $found_hive = null;
    foreach ($bee["BEE_GARDEN"]["hives"] as $hive_index => $hive_obj) {
        if(
            // $hive_obj["hive_name"] == $hive_name &&
            $hive_obj["code"] == $code &&
            // $hive_obj["id"] == $hive_id &&
            $hive_obj["status"]  == "pending"
        ){
            $found_hive = $hive_obj;
            $found = true;
            $hive_id = $hive_obj["id"];
            $hive_name = $hive_obj["hive_name"];
            break;
        }
    }
    if($found == false){
        $res[BEE_EI] = array("Business: " . $post_nectoroid["_f_activate"]["app_name"] . " is not set for activation");
        return $res;
    }
    //tools_dumpx("found_hive: ",__FILE__,__LINE__,$found_hive);

    $update_hive_nectar = array(
        "hive" => array(
            "code" => "",
            "status" => "active",
            "_w"=>array(
                array("id","=",$hive_id)
            )
        )
    );
    //tools_dumpx("update_hive_nectar: ",__FILE__,__LINE__,$update_hive_nectar);
    $whole_honey = array();
    $bhu_res = bee_hive_update(
        $update_hive_nectar,
        $bee["BEE_GARDEN_STRUCTURE"],
        $bee["BEE_GARDEN_CONNECTION"],
        0,
        $whole_honey
    );
    $res[BEE_EI] = array_merge($res[BEE_EI],$bhu_res[BEE_EI]);
    if(count($bhu_res[BEE_EI])==0){
        //get connection to this hive
        $cnx = hive_run_get_connection(BEE_USER_NAME, BEE_PASSWORD,BEE_SERVER_NAME,$hive_name,false,BEE_PORT);
        if(count($cnx[BEE_EI]) == 0 ){
            $connection = $cnx[BEE_RI];
            $whole_honey = array();
            //update users account
            $update_user_nectar = array(
                "user" => array(
                    "code" => "",
                    "status" => "active",
                    "_w"=>array(
                        array(
                            array(
                                array(
                                    array("tenant_of","=",$hive_name),
                                    "AND",
                                    array("code","=",$code)
                                ),
                                "AND",
                                array("is_owner","=",1)
                            ),
                            "AND",
                            array("status","=","pending")
                        )
                    )
                )
            );
            $bhu_res = bee_hive_update(
                $update_user_nectar,
                $bee["BEE_HIVE_STRUCTURE"]["combs"],
                $connection,
                0,
                $whole_honey
            );
            $res[BEE_EI] = array_merge($res[BEE_EI],$bhu_res[BEE_EI]);
            $connection = null;//self cloase this connection
            //tools_dump("connection closed a: ",__FILE__,__LINE__,$connection);
        }else{
            $res[BEE_EI] = array("We could not connect to " . $post_nectoroid["_f_activate"]["app_name"]);
        }
    }
    return $res;
}

//this will activate the users account
function hive_run_activate_signup($post_nectoroid,$bee,$defaults=null){
    $res = array(null, array());
    //db name
    $hive_name = BEE_GARDEN . "_" . tools_sanitise_name($post_nectoroid["_f_verify"]["app_name"]);
    //code
    $code = $post_nectoroid["_f_verify"]["code"];
    //hive_id
    $hive_id =intval($post_nectoroid["_f_verify"]["hive_id"]);
    //email
    $email = $post_nectoroid["_f_verify"]["email"];


    //validation
    //the code cannot be empty
    $code = trim($code);
    if(strlen($code) == 0){
      $res[BEE_EI] = array("Invalid supplied activation code");
      return $res;
    }
    //code must be four digits
    if(strlen($code) != 4){
      $res[BEE_EI] = array("Invalid activation code length");
      return $res;
    }
    //code must be only digits
    if(is_numeric($code)){
      //must be digits
      if (ctype_digit($code)) {
         //this is fine
      } else {
        $res[BEE_EI] = array("Invalid code");
        return $res;
      }
    }else{
      $res[BEE_EI] = array("Invalid code format");
      return $res;
    }
    //email is required
    $email = trim($email);
    if(strlen($email) == 0){
      $res[BEE_EI] = array("Please supply your account email address");
      return $res;
    }
    // check if e-mail address is well-formed
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $res[BEE_EI] = array("Invalid email address");
      return $res;
    }
    if(strlen($email) > 30){
      $res[BEE_EI] = array("Please supply a valid email address");
      return $res;
    }


    //tools_dumpx("hives: ",__FILE__,__LINE__,array($hive_name, $code, $hive_id));
    $found = false;
    $found_hive = null;
    foreach ($bee["BEE_GARDEN"]["hives"] as $hive_index => $hive_obj) {
        if(
            $hive_obj["hive_name"] == $hive_name &&
            $hive_obj["id"] == $hive_id
        ){
            $found_hive = $hive_obj;
            $found = true;
            break;
        }
    }
    if($found == false){
        $res[BEE_EI] = array("User account on: " . $post_nectoroid["_f_verify"]["app_name"] . " is not set for activation");
        return $res;
    }
    //tools_dumpx("found_hive: ",__FILE__,__LINE__,$found_hive);


    //get connection to this hive
    $cnx = hive_run_get_connection(BEE_USER_NAME, BEE_PASSWORD,BEE_SERVER_NAME,$hive_name,false,BEE_PORT);
    if(count($cnx[BEE_EI]) == 0 ){
        $connection = $cnx[BEE_RI];
        $whole_honey = array();
        //update users account
        $update_user_nectar = array(
            "user" => array(
                "code" => "",
                "status" => "active",
                "_w"=>array(
                    array(
                        array(
                          array(
                              array("tenant_of","=",$hive_name),
                              "AND",
                              array("code","=",strval($code))
                          ),
                          "AND",
                          array("status","=","pending")
                        ),
                        "AND",
                        array("email","=",$email)
                    )
                )
            )
        );
        $bhu_res = bee_hive_update(
            $update_user_nectar,
            $bee["BEE_HIVE_STRUCTURE"]["combs"],
            $connection,
            0,
            $whole_honey
        );
        $res[BEE_EI] = array_merge($res[BEE_EI],$bhu_res[BEE_EI]);
        if(count($res[BEE_EI]) == 0){
            if(intval($bhu_res[BEE_RI]["user"]["_num"]) > 0){
                $res[BEE_RI] = "Ok";
            }else{
                array_push($res[BEE_EI],"Account activation failed, please check your code and try again");
            }
        }
        $connection = null;//self cloase this connection
        //tools_dump("connection closed a: ",__FILE__,__LINE__,$bhu_res);
    }else{
        $res[BEE_EI] = array("We could not connect to " . $post_nectoroid["_f_verify"]["app_name"]);
    }

    return $res;
}

?>
