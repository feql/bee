<?php

    include(__DIR__ . "/lib/check_qualifies.php");

    function send_response($res,$bee){
        $bee["BEE_GARDEN_CONNECTION"] = null;
        $bee["BEE_HIVE_CONNECTION"] = null;
        header("Content-type: application/json");
        $res =  json_encode($res);
        echo $res;
        exit(0);
    }

    $bee = $BEE;
    $res = [null,[]];
    $claim_value_per_add = 0.05; //nyd Todo: get this from the config table
    $time_now = time();
    //get adverts whose status is ok
    $fine_adverts = [
        "adverts" => [
            "_w" => [
                [
                    ["boost_balance","gt",0],
                    "AND",
                    ["date_expired_timestamp", "lte", $time_now]
                ]
            ]
        ],
        "users" => [
        ],
        "config" => [
            "_w" => [
                ["id","e",1]
            ]
        ]
    ];
    $brg_res = bee_run_get($fine_adverts,$bee["BEE_HIVE_STRUCTURE"]["combs"],$bee["BEE_HIVE_CONNECTION"]);
    $res[BEE_EI] = array_merge($res[BEE_EI],$brg_res[BEE_EI]);
    if(count($res[BEE_EI]) > 0){
        send_response($res,$bee);
    }
    
    //2Ks@2020
    $adverts = $brg_res[BEE_RI]["adverts"];
    $users = $brg_res[BEE_RI]["users"];
    $config = $brg_res[BEE_RI]["config"];
    $total_distributions = 0;
    if(count($adverts) > 0){
        for($i=0;$i < count($adverts); $i++){
            $advert = $adverts[$i];
            $this_advert_boost_balance = floatval($advert["boost_balance"]);
            
            // //audience_countries
            // $countries = explode(",", $advert["audience_countries"]);
            // for ($it=0; $it < count($countries); $it++) { 
            //     $countries[$it] = strtolower(trim($countries[$it]));
            // }
            // //audience_cities
            // $cities = explode(",", $advert["audience_cities"]);
            // for ($it=0; $it < count($cities); $it++) { 
            //     $cities[$it] = strtolower(trim($cities[$it]));
            // }
            // //audience_age_groups
            // $age_groups = explode(",", $advert["audience_age_groups"]);
            // for ($it=0; $it < count($age_groups); $it++) { 
            //     $gtemp = explode("-",strtolower(trim($age_groups[$it])));
            //     if(count($gtemp) < 2){
            //         continue;
            //     }
            //     $age_groups[$it] = [
            //         "min" => intval($gtemp[0]),
            //         "max" => intval($gtemp[1])
            //     ];
            // }
            // //gender
            // $audience_gender = $advert["audience_gender"];
            // //audience_tags
            // $audience_tags = explode(",", $advert["audience_tags"]);
            // for ($it=0; $it < count($audience_tags); $it++) { 
            //     $audience_tags[$it] = strtolower(trim($audience_tags[$it]));
            // }
            $qualification_filters = get_qualification_filters($advert);
            $countries = $qualification_filters["countries"];
            $cities = $qualification_filters["cities"];
            $age_groups = $qualification_filters["age_groups"];
            $audience_gender = $qualification_filters["audience_gender"];
            $audience_tags = $qualification_filters["audience_tags"];

            $distribution_count = 0;
            //go through the users
            //user cannot receive their own posted advert
            for($u=0;$u < count($users); $u++){
                if($this_advert_boost_balance <= $claim_value_per_add || $this_advert_boost_balance <= 0){
                    break; //no more boost balance
                }

                $user = $users[$u];
                if(intval($user["id"]) == intval($advert["user_id"])){
                    //this is the user that posted this advert so we skip it
                    continue;
                }
                //tools_dumpx("temp_postdata",__FILE__,__LINE__,$user);

                //check that this user has not received this advert on theitr billboard
                $check_query = [
                    "billboards" => [
                        "_w" => [
                            [
                                ["user_id","e",$user["id"]],
                                "AND",
                                ["advert_id", "e", $advert["id"]]
                            ]
                        ]
                    ],
                    "advert_movements" => [
                        "_pg" => 1,
                        "_desc" => ["id"],
                        "_w" => [
                            ["advert_id","e",$advert["id"]]
                        ]
                    ]
                ];
                $brgcheck_res = bee_run_get($check_query,$bee["BEE_HIVE_STRUCTURE"]["combs"],$bee["BEE_HIVE_CONNECTION"]);
                $res[BEE_EI] = array_merge($res[BEE_EI],$brgcheck_res[BEE_EI]);
                if(count($res[BEE_EI]) > 0){
                    send_response($res,$bee);
                }
                $billboards = $brgcheck_res[BEE_RI]["billboards"];
                if(count($billboards) > 0){
                    //this user already has this advert so we continue to the next user
                    continue;
                }
                $advert_movement_openning_value = 0;
                $advert_movement_closing_value = 0;
                if(count($brgcheck_res[BEE_RI]["advert_movements"]) > 0){
                    $advert_movement_openning_value = floatval($brgcheck_res[BEE_RI]["advert_movements"][0]["closing_value"]);
                    $advert_movement_closing_value = $advert_movement_openning_value -  $claim_value_per_add;
                }

                $user_qualifies = check_qualifies($user, $countries, $cities, $audience_gender, $age_groups, $audience_tags);
                if($user_qualifies == false){
                    continue; 
                }
                
                
                //tools_dumpx("qualified user for add ",__FILE__,__LINE__,$user);

                //by this time all this qualities qualify and we can push the advert to the users 
                //billboard
                /*
                    $users_country_qualifies,
                    $users_city_qualifies,
                    $users_gender_qualifies,
                    $users_age_qualifies,
                    $users_tags_qualify
                */
                $user_message_id = $guid = uniqid() . "-" . rand(1000,2768);
                $post_billbaord = [
                    "billboard" => [
                        "user_id" => $user["id"],
                        "advert_id" => $advert["id"],
                        "has_been_claimed" => 0,
                        "is_claim_correct" => 0,
                        "claim_value_scored" => 0,
                        "date_claimed" => "1980-01-01",
                        "_time_date_claimed_timestamp" => "1980-01-01",
                        "has_been_ignored" => 0,
                        "has_expired" => 0,
                        "date_ignored" => "1980-01-01",
                        "_time_date_ignored_timestamp" => "1980-01-01",
                        "claim_value" => $claim_value_per_add,
                        "date_expired" => "1980-01-01",
                        "_time_date_expired_timestamp" =>  "1980-01-01",
                        "date_to_expire" => $advert["date_to_expire"],
                        "date_to_expire_timestamp" => $advert["date_to_expire_timestamp"],
                        "date_posted" => date("Y-m-d"),
                        "date_posted_timestamp" => time()
                    ],
                    "advert_movement" => [
                        "advert_id" =>  $advert["id"],
                        "user_id" => $advert["user_id"],
                        "direction" => "credit",
                        "value" => $claim_value_per_add,
                        "openning_value" => $advert_movement_openning_value,
                        "closing_value" => $advert_movement_closing_value,
                        "cause_table" => "billboard",
                        "_fk_cause_table_id" => "billboard",
                        "remark" => "distribute",
                        "transaction_type" => "distribute",
                        "description" => "pushed advert to user",
                        "date_occurance" => date("Y-m-d"),
                        "date_occurance_timestamp" => time(),
                        "_w" => [
                            ["advert_id","e",$advert["id"]]
                        ]
                    ],
                    "dashboard_history" => [
                        "user_id" => $user["id"],
                        "pending_adverts_count" => intval($user["dashboard_pending_adverts_count"]) + 1,
                        "pending_adverts_amount" => floatval($user["dashboard_pending_adverts_amount"]) + $claim_value_per_add,
                        "earning_rate" => intval($user["dashboard_earning_rate"]),
                        "earning_rate_periodicity" => floatval($config["earning_rate_periodicity"]),
                        "date_posted" => date("Y-m-d"),
                        "date_posted_timestamp" => time()
                    ],
                    "notification"  => [
                        "message_identity" => $user_message_id,
                        "user_id" => $user["id"],
                        "is_read" => 0,
                        "title" => "New Cash Posted USD " . $claim_value_per_add,
                        "message" => "Hello Xaddener #" . $user["id"] . ", billboard updated !, please check out this brands offer: " . $advert["title"],
                        "icon" => "mdi-currency-usd-circle",
                        "target_platform" => "android_app",
                        "deep_link" => "",
                        "date_posted" => date("Y-m-d"),
                        "date_posted_timestamp" => time(),
                        "date_read" => "1980-01-01",
                        "_time_date_read_timestamp" => "1980-01-01"
                    ]
                ];
                $brp_billboard_res = bee_run_post($post_billbaord,$bee,0);
                $res[BEE_EI] = array_merge($res[BEE_EI],$brp_billboard_res[BEE_EI]);
                if(count($res[BEE_EI]) > 0){
                    //end process
                    //nyd undo previous changes, rollback
                    send_response($res,$bee);
                }

                //we need to update the advert balance,
                //the users dashboard history
                $this_advert_boost_balance  = $this_advert_boost_balance  - $claim_value_per_add;
                $update_advert_staff = [
                    "advert" => [
                        "boost_balance" => $this_advert_boost_balance,
                        "distribution_count" => intval($advert["distribution_count"]) + 1,
                        "_w" => [
                            ["id","e",$advert["id"]]
                        ]
                    ],
                    "user" => [
                        "dashboard_pending_adverts_count" => intval($user["dashboard_pending_adverts_count"]) + 1,
                        "dashboard_pending_adverts_amount" => floatval($user["dashboard_pending_adverts_amount"]) + $claim_value_per_add,
                        "dashboard_date_posted" => date("Y-m-d"),
                        "dashboard_date_posted_timestamp" => time(),
                        "_w" => [
                            ["id","e", $user["id"]]
                        ]
                    ]
                ];
                $bru_res3 = bee_run_update($update_advert_staff,$bee,0);
                $res[BEE_EI] = array_merge($res[BEE_EI],$bru_res3[BEE_EI]);
                if(count($res[BEE_EI]) > 0){
                    //end process
                    //nyd undo previous changes, rollback
                    send_response($res,$bee);
                }

                $distribution_count = $distribution_count + 1;
                
                //nyd
                //Todo:
                //send user a android notification or sms
            }
            $total_distributions = $total_distributions + $distribution_count;

            //nyd
            //Todo: 
            //send notification to advertiser that we have distributed your advert ["label"] to 20 people
            //even when people claim we tell the advertiser what happenned
            //even in future when one buys through our system we tell users what happened
            //cause we can group users into buying products into a group and then we buy from
            //supllier at bulk
            //and we tell the user in the notification the boost balance
            if($distribution_count > 0){
                $advertiser_alert = [
                    "notification"  => [
                        "message_identity" => $user_message_id,
                        "user_id" => $advert["user_id"],
                        "is_read" => 0,
                        "title" => "Advert distributed " . $advert["title"],
                        "message" => "Hello Xaddener #" . $advert["user_id"] . ", Your post has been distributed to " . $distribution_count . " new people, boost balance is " . $this_advert_boost_balance,
                        "icon" => "mdi-account-voice",
                        "target_platform" => "android_app",
                        "deep_link" => "",
                        "date_posted" => date("Y-m-d"),
                        "date_posted_timestamp" => time(),
                        "date_read" => "1980-01-01",
                        "_time_date_read_timestamp" => "1980-01-01"
                    ]
                ];
                $brp_billboard_res = bee_run_post($advertiser_alert,$bee,0);
                $res[BEE_EI] = array_merge($res[BEE_EI],$brp_billboard_res[BEE_EI]);
                if(count($res[BEE_EI]) > 0){
                    //roll back we dont want no errors at all
                    send_response($res,$bee);
                }
            }
            
        }
        $res[BEE_RI] = "Total distributions: " . $total_distributions;
    }else{
        $res[BEE_EI] = ["No adverts to distribute at this time " . date("yyyy-dd-mm H:i:s")];
    }

    send_response($res,$bee);
?>