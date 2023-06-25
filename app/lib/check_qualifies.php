<?php

    function get_qualification_filters($advert){
        //audience_countries
        $countries = explode(",", $advert["audience_countries"]);
        for ($it=0; $it < count($countries); $it++) { 
            $countries[$it] = strtolower(trim($countries[$it]));
        }
        //audience_cities
        $cities = explode(",", $advert["audience_cities"]);
        for ($it=0; $it < count($cities); $it++) { 
            $cities[$it] = strtolower(trim($cities[$it]));
        }
        //audience_age_groups
        $age_groups = explode(",", $advert["audience_age_groups"]);
        for ($it=0; $it < count($age_groups); $it++) { 
            $gtemp = explode("-",strtolower(trim($age_groups[$it])));
            if(count($gtemp) < 2){
                continue;
            }
            $age_groups[$it] = [
                "min" => intval($gtemp[0]),
                "max" => intval($gtemp[1])
            ];
        }
        //gender
        $audience_gender = $advert["audience_gender"];
        //audience_tags
        $audience_tags = explode(",", $advert["audience_tags"]);
        for ($it=0; $it < count($audience_tags); $it++) { 
            $audience_tags[$it] = strtolower(trim($audience_tags[$it]));
        }

        return [
            "countries" => $countries,
            "cities" => $cities,
            "age_groups" => $age_groups,
            "audience_gender" => $audience_gender,
            "audience_tags" => $audience_tags
        ];
    }

    function check_qualifies($user, $countries, $cities, $audience_gender, $age_groups, $audience_tags){
        
        //check if this user qualifies to recieve this advert
        //nyd Todo
        //use a score to order users in descing order to get who qualifies first etc
        //1. check country
        $users_country_qualifies = false;
        $users_country = strtolower(trim($user["country_id"]));
        if(in_array($users_country,$countries)){
            $users_country_qualifies = true;
        }
        if($users_country_qualifies == false){
            //tools_dumpx("zxa",__FILE__,__LINE__,true);
            return false; 
        }
        //tools_dumpx("qualified  users_country_qualifies ",__FILE__,__LINE__,$users_country_qualifies);
        //2. check city
        $users_city_qualifies = false;
        $users_city = intval($user["city_id"]);
        if(count($cities) == 0){
            //if advert has no target city then every city qualifies
            $users_city_qualifies = true;
        }else if(in_array($users_city,$cities)){
            $users_city_qualifies = true;
        }
        if($users_city_qualifies == false){
            //tools_dumpx("zxb",__FILE__,__LINE__,true);
            return false; 
        }
        //tools_dumpx("qualified  users_city_qualifies ",__FILE__,__LINE__,$users_city_qualifies);
        //3. check gender
        $users_gender_qualifies = false;
        $users_gender = intval($user["gender"]);
        if($audience_gender == "both"){
            //if advert has no specific target gender then every gender qualifies
            $users_gender_qualifies = true;
        }else if($users_gender == $audience_gender){
            $users_gender_qualifies = true;
        }
        if($users_gender_qualifies == false){
            //tools_dumpx("zxc",__FILE__,__LINE__,true);
            return false; 
        }
        //tools_dumpx("qualified  users_gender_qualifies ",__FILE__,__LINE__,$users_gender_qualifies);
        //4. check age
        $users_age_qualifies = false;
        $users_age = 0;
        $tob = intval($user["time_of_birth"]);
        if($tob <= 0){
            //user has not yet provided their date of birth
            $users_age = 16; //default assumed age
        }else{
            $this_year = intval(date("Y"));
            $dob_year = intval(date("Y",$tob));
            $users_age = $this_year - $dob_year;
        }
        for ($z=0; $z < count($age_groups); $z++) { 
            $ag = $age_groups[$z];
            if($users_age >= $ag["min"] && $users_age <= $ag["max"]){
                $users_age_qualifies = true;
                break;
            }
        } 
        if($users_age_qualifies == false){
            //tools_dumpx("zxd",__FILE__,__LINE__,true);
            return false; 
        }
        //tools_dumpx("qualified  users_age_qualifies ",__FILE__,__LINE__,$users_age_qualifies);
        //4. check hash tags
        $users_tags_qualify = false;
        $users_tags = explode(" ", $user["audience_tags"]);
        for ($z=0; $z < count($users_tags); $z++) { 
            $users_tags[$z] = strtolower(trim($users_tags[$z]));
        }
        if(count($users_tags) == 0 || $audience_tags == null){
            //if user has no hash tags then they qualify for any anything at this point
            $users_tags_qualify = true;
        }else{
            //nyd Todo:
            //we can use a count here when at the level of using scores to know
            //what user qualifies the most 
            for ($z=0; $z < count($users_tags); $z++) { 
                $users_tag = "#". $users_tags[$z];
                //tools_dump("zxet".$z,__FILE__,__LINE__,[$users_tag , $audience_tags, in_array($users_tag,$audience_tags) ]);
                if(in_array($users_tag,$audience_tags)){
                    $users_tags_qualify = true;
                    break;
                }
            }
        }
        if($users_tags_qualify == false){
            //tools_dumpx("zxe",__FILE__,__LINE__,[$users_tags , $audience_tags]);
            return false; 
        }

        //tools_dumpx("zxf",__FILE__,__LINE__,false);
        return true; 
    }
?>