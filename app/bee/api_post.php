<?php
    if($_SERVER['REQUEST_METHOD']=="POST"){ 
        $temp_postdata = file_get_contents("php://input");
        //tools_dumpx("temp_postdata",__FILE__,__LINE__,$temp_postdata);
        $tsji_res = tools_suck_json_into($temp_postdata, array());
        if(count($tsji_res[BEE_EI]) == 0){
            //no errors 
            ////bee calls your post methodh
            $res = post($BEE, $tsji_res[BEE_RI], array(null,array(),null)); 
            tools_reply($res[BEE_RI],$res[BEE_EI],array(
                $BEE["BEE_GARDEN_CONNECTION"],
                $BEE["BEE_HIVE_CONNECTION"]
            ));
        }else{
            $error = $tsji_res[BEE_EI][0];
            sever_error();
        }
    }else{ 
        not_found(); 
    }
?>