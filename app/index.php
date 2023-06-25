<?php
    //by including this in your script
    //it will create a garden for your application if one has not yet been created
    //reads in the state of the garden
    define("BASE_ROOT", ".");
    include("bee/run.php");
    
    
    bee_handle_requests($BEE);

    

    // if($_SERVER["REQUEST_METHOD"] == "POST"){

    //     $temp_postdata = file_get_contents("php://input");
    //     //tools_dumpx("temp_postdata",__FILE__,__LINE__,$temp_postdata);
    //     $tsji_res = tools_suck_json_into($temp_postdata, array());
    //     if(count($tsji_res[BEE_EI])==0){//no errors
    //         $postdata = $tsji_res[BEE_RI];
    //         if( isset($postdata["tran_type"]) && 
    //             isset($postdata["transaction_id"]) && 
    //             isset($postdata["message"]) 
    //         ){
    //             include("deposit.php");
    //         }else if( isset($postdata["cron_job"]) && isset($postdata["auth_token"])  
    //         ){
    //             include("distribute.php");
    //         }else{
    //             bee_handle_requests($BEE);
    //         }
    //     }else{
    //         bee_handle_requests($BEE);
    //     }
    // }else if($_SERVER["REQUEST_METHOD"] == "GET"){
    //     if(isset($_GET["distribute"]) && $_GET["distribute"]=="6543")
    //     {
    //         include("distribute.php");
    //     }else{
    //         bee_handle_requests($BEE);
    //     }
    // }else{
    //     bee_handle_requests($BEE);
    // }

?>