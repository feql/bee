<?php
    /* error reportring */
    error_reporting(E_ALL ^ E_WARNING);
    /*end error reporting*/
    ini_set("memory_limit","1000M");
    //set_time_limit(60);

    /*cors */
    // Allow from any origin
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day
    }
    // Access-Control headers are received during OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])){
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");      
        }   

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])){
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
        }
        exit();
    }
    /*end cors */


    $in_production = true;
    //echo __DIR__ . '/vendor/autoload.php';
    require __DIR__ . '/vendor/autoload.php';
    use Emarref\Jwt\Claim;
    /** Include PHPExcel */
    require_once dirname(__FILE__) . '/phpexcel/Classes/PHPExcel.php';
    include_once dirname(__FILE__) . '/fpdf182/fpdf.php';

    //Global Custome Server Functions Pass
    $BEE_BR_HONEY = null;
    $BEE_BOX = null; //a global key value pair for serving this request
    $foo = array();
    $THIS_NODE = null; //used to pass immediate nodes to _f_sg_ functions in segmentation of attributes
    $BEE_SG_HONEY = null; //same as above but for the current honey values of that function
    


    //load layers
    include("bee_run_handle_qrcode.php"); //qr_code generators
    include("bee_hive_run_cloudinary.php"); //post
    include("tools.php"); //utility layer
    include("Inflect.php"); //pluralisation layer
    include("countries_data.php"); //countries data
    include("bee_security.php"); //security layer
    include("hive.php"); //database layer
    include("filestack.php"); //file upload to https://dev.filestack.com/

    include("bee_hive_run_process_dependency_report.php"); //dependency logic
    include("bee_hive_run_register_hive.php"); //hive registration
    include("bee_hive_run_activate_hive.php"); //hive activation
    include("bee_hive_run_login.php"); //hive login
    include("bee_hive_run_recover_hive.php");// hive recovery
    include("bee_hive_run_reset_hive.php");// hive reset
    include("bee_hive_run_update_password.php");// hive reset

    include("segmentation.php"); //interpretation layer
    include("sqllization.php"); //interpretation layer
    include("production.php"); //production layer
    include("packaging.php"); //production layer

    include("tracers.php"); //debuging layer
    include("mailer.php"); //communiction layer

    include("bee_run_handle_ussd.php"); //ussd layer
    include("bee_run_handle_token.php"); //request layer
    include("bee_run_handle_get.php"); //request layer
    include("bee_run_handle_post.php"); //request layer
    include("bee_run_handle_put.php"); //request layer
    include("bee_run_handle_update.php"); //request layer
    include("bee_run_handle_delete.php"); //request layer

    $bee_show_sql =  false;
    $bee_sql_backet = array(
        "selects" => array(),
        "inserts" => array(),
        "updates" => array(),
        "deletes" => array(),
        "other" => array()
    );
    $bee_indexing_keys = array();
    define("BEE_IS_IN_PRODUCTION",false);

    include("DotEnv.php"); //request layer
    use DevCoder\DotEnv;
    (new DotEnv(__DIR__ . '/../../.env'))->load();


    $db_server_name = getenv("db_server_name");
    $db_server_user = getenv("db_server_user");
    $db_server_password = getenv("db_server_password");
    $db_port = 3306; //getenv("db_server_port");
    $BEE_DIR_PATH = getenv("bee_dir_path");

    // $smsto_api_key = getenv("smsto_api_key");
    // $sendgrid_key = getenv("sendgrid_api_key");
    // define("SENDGRID_API_KEY", $sendgrid_key);
    // tools_dumpx("SENDGRID_API_KEYx",__FILE__,__LINE__,SENDGRID_API_KEY);

    // $db_server_name = "services.mpola";
    // $db_server_user = "nyola";
    // $db_server_password = "@mike256";
    // $db_port = 3306;

    $smsto_api_key = "";
    $sendgrid_key = "";
    define("SENDGRID_API_KEY", "");
    
    define("BEE_SERVER_NAME", (BEE_IS_IN_PRODUCTION ? $db_server_name : $db_server_name));
    define("BEE_USER_NAME", (BEE_IS_IN_PRODUCTION ? $db_server_user : $db_server_user));
    define("BEE_PASSWORD", (BEE_IS_IN_PRODUCTION ? $db_server_password : $db_server_password));
    define("BEE_PORT", (BEE_IS_IN_PRODUCTION ? $db_port : $db_port));
    define("BEE_DIR_PATH", $BEE_DIR_PATH);
    
    // define("SMSTO_API_KEY", $smsto_api_key);
    
    // define("SMSTO_API_KEY", $smsto_api_key);
    define("SMSTO_API_KEY", "");
    define("BEE_SHOW_SQL_ON_ERRORS", true);
    define("BEE_APP_SECRET","mysupersecuresecret");
    define("BEE_JWT_AUDIENCE","mysuperapp");
    define("BEE_JWT_ISSUER","mysuperapp");
    define("BEE_STRICT_HIVE",false);
    $BEE_JWT_ALGORITHM = new Emarref\Jwt\Algorithm\Hs256(BEE_APP_SECRET);
    //$BEE_JWT_ALGORITHM = new Emarref\Jwt\Algorithm\Rs256(BEE_APP_SECRET);
    $BEE_JWT_ENCRYPTION = Emarref\Jwt\Encryption\Factory::create($BEE_JWT_ALGORITHM);
    define("BEE_RI",0);//RESULTS INDEX
    define("BEE_EI",1);//ERROR INDEX
    define("BEE_SI",2);//STRUCTURE INDEX
    define("BEE_SEP","__");//
    define("BEE_ANN","_a");//attribute node name
    define("BEE_WNN","_w");//where node name
    define("BEE_FNN","_for");//for node name used in indicating the structure file name
    define("BEE_GARDEN_STUCTURE_FILE_NAME",BASE_ROOT . "/bee/_garden.json");
    define("BEE_HIVE_STUCTURE_FILE_NAME",BASE_ROOT ."/_hive.json");
    define("BEE_DEFAULT_PASSWORD","qwerty");
    $BEE_GLOBALS = array(
        "is_login_call" => false
    );
    $BEE_ERRORS = array();
    $xt_order = array();

    //get the hive of the application
    $tjx_res = tools_jsonify(file_get_contents(BEE_HIVE_STUCTURE_FILE_NAME));
    $BEE_HIVE_STRUCTURE = $tjx_res[0];
    $BEE_ERRORS = array_merge($BEE_ERRORS,$tjx_res[BEE_EI]);
    define("BEE_HIVE_OF_A",$BEE_HIVE_STRUCTURE["hive_of_a"]);
    $BEE_HIVE_CONNECTION = null;
    if(isset($BEE_HIVE_STRUCTURE["app_name"])){
        define('BEE_GARDEN',$BEE_HIVE_STRUCTURE["app_name"]);
    }else{
        define('BEE_GARDEN',tools_get_app_folder_name());
    }
    define("BEE_BASE_URI", BASE_ROOT . "/".BEE_GARDEN."/"."bee/");
    $BEE_DRONE_SECURITY_ENABLED = false;
    if(isset($BEE_HIVE_STRUCTURE["drone_security_enabled"])){
        $BEE_DRONE_SECURITY_ENABLED = $BEE_HIVE_STRUCTURE["drone_security_enabled"];
    }
    $BEE_USE_UI_SECURIT_ONLY = true;
    if(isset($BEE_HIVE_STRUCTURE["use_ui_security_only"])){
        $BEE_USE_UI_SECURIT_ONLY = $BEE_HIVE_STRUCTURE["use_ui_security_only"];
    }
    define("BEE_SUDO_DELETE",$BEE_HIVE_STRUCTURE["sudo_delete"]);

    //the garden structure
    //every hive will have its structure e.g _hive.json
    //but this is the structure of the master hive
    //tools_dump("pampa: ",__FILE__,__LINE__,BEE_GARDEN_STUCTURE_FILE_NAME);
    $tj_res = tools_jsonify(file_get_contents(BEE_GARDEN_STUCTURE_FILE_NAME));
    //tools_dump("pampax: ",__FILE__,__LINE__,$tj_res);
    $BEE_GARDEN_STRUCTURE = $tj_res[0];
    //tools_dumpx("hrege_resyyere: ",__FILE__,__LINE__,$BEE_ERRORS);
    $hrege_res = hive_run_ensure_garden_exists($BEE_GARDEN_STRUCTURE);
    $BEE_ERRORS = array_merge($BEE_ERRORS,$hrege_res[BEE_EI]);
    //tools_dumpx("tools_dump: ",__FILE__,__LINE__,$hrege_res);
    $BEE_GARDEN_CONNECTION = $hrege_res[BEE_RI];
    $BEE_GARDEN = null;
    //get in the current state of the garden
    if(count($BEE_ERRORS)==0){
        $hrgg_res = hive_run_get_garden($BEE_GARDEN_STRUCTURE,$BEE_GARDEN_CONNECTION);
        $BEE_ERRORS = array_merge($BEE_ERRORS,$hrgg_res[BEE_EI]);
        //tools_dumpx("hive_run_get_garden: ",__FILE__,__LINE__,$BEE_ERRORS);
        $BEE_GARDEN_STRUCTURE = $hrgg_res[2];
        //tools_reply($hrgg_res[BEE_RI],$BEE_ERRORS,array($BEE_GARDEN_CONNECTION));
        $BEE_GARDEN = $hrgg_res[BEE_RI];
        //tools_dumpx("BEE_GARDEN: ",__FILE__,__LINE__,$BEE_GARDEN);
    }
    define('BEE_ENFORCE_RELATIONSHIPS',false); //nyd get value from hive structure
    $BEE = array(
        "BEE_HIVE_STRUCTURE" => $BEE_HIVE_STRUCTURE,
        "BEE_GARDEN_STRUCTURE" => $BEE_GARDEN_STRUCTURE,
        "BEE_GARDEN_CONNECTION" => $BEE_GARDEN_CONNECTION,
        "BEE_HIVE_CONNECTION" => null,
        "BEE_GARDEN" => $BEE_GARDEN,
        "BEE_ERRORS" => $BEE_ERRORS,
        "BEE_JWT_ENCRYPTION" => $BEE_JWT_ENCRYPTION,
        "BEE_USER" => array("id"=>0)
    );


    //converts the hive combs into type scipt files
    function bee_get_ts_hive($bee,$path){
        global $BEE_GLOBALS;
        global $countries_list;

        $combs = $bee["BEE_HIVE_STRUCTURE"]["combs"];
        foreach ($combs as $comb_name => $comb_def) {
            $file_name = $path . $comb_name . ".ts";
            $code = "interface ". $comb_name . "{\n";
            //go through the colmns
            foreach ($comb_def as $cell => $cell_def_array) {
                $cell_def = $cell_def_array[0];
                if(tools_startsWith($cell_def,"_")){
                    continue;
                }
                $type = "any";
                if(in_array("vcnn",$cell_def) == true ){ $type = "string"; }
                if(in_array("vcn",$cell_def) == true ){ $type = "string"; }
                if(in_array("vcn_d",$cell_def) == true ){ $type = "string"; }
                if(in_array("fk",$cell_def) == true ){ $type = "number"; }
                if(in_array("tn",$cell_def) == true ){ $type = "string"; }
                if(in_array("dnn",$cell_def) == true ){ $type = "number"; }
                if(in_array("dnn",$cell_def) == true ){ $type = "number"; }
                if(in_array("tnn",$cell_def) == true ){ $type = "string"; }
                if(in_array("tsnn",$cell_def) == true ){ $type = "number"; }
                if(in_array("dtnn",$cell_def) == true ){ $type = "string"; }
                if(in_array("in",$cell_def) == true ){ $type = "number"; }
                if(in_array("inn_d",$cell_def) == true ){ $type = "number"; }
                $code = $code . "\t". $cell . ": " . $type . ",";
            }
            $code = $code . "}\n";
            file_put_contents($file_name, $code);
            //write to file
        }

    }

    function bee_run_register_hive($registration_nector,$bee){
        global $BEE_GLOBALS;
        $hrrh_res = [[],[]];
        //$hrrh_res = hive_run_register_hive($registration_nector, $bee);
        //tools_dumpx("part a: ",__FILE__,__LINE__,$bee);
        if(array_key_exists("is_registration_offline",$bee["BEE_HIVE_STRUCTURE"]) &&
            $bee["BEE_HIVE_STRUCTURE"]["is_registration_offline"] == true){
            $BEE_GLOBALS["is_register_call"] = true;
            $sendEmail = false;
            //tools_dumpx("part a: ",__FILE__,__LINE__,"iam here vacating");
            $hrrh_res = hive_run_register_hive($registration_nector,$bee,array(
                "code" => "",
                "status" => "active",
                "is_owner" => 1
            ));
            $BEE_GLOBALS["is_register_call"] = false;
        }else{
            $BEE_GLOBALS["is_register_call"] = true;
            $hrrh_res = hive_run_register_hive($registration_nector,$bee);
            $BEE_GLOBALS["is_register_call"] = false;
        }
        return $hrrh_res;
    }

    function bee_run_post($nectoroid,$bee,$user_id){
        global $BEE_GLOBALS;
        global $BEE_BOX;
        global $BEE_BR_HONEY;
        global $countries_list;
        $res = array(null,array(),null);

        $included_before_functions_paths = array(); //we cant include a file twice
        $before_functions = array();

        //tools_dump("here in post",__FILE__,__LINE__,$nectoroid);

        //go through the entire nectorid processing
        //node by node on the root
        $whole_honey = array();
        $newNectoroids = [];
        //_lineup 
        //this forces a specific order of execution
        if(array_key_exists("_lineup", $nectoroid)){
            $lineup = $nectoroid["_lineup"];
            for ($i=0; $i < count($lineup); $i++) { 
                $lineupKey =  $lineup[$i];
                if(array_key_exists($lineupKey, $nectoroid)){
                    array_push($newNectoroids, [
                        "root_node_name" => $lineupKey,
                        "root_node" => $nectoroid[$lineupKey]
                    ]);
                }
            }
        }else{
            foreach ($nectoroid as $root_node_name => $root_node) {
                array_push($newNectoroids, [
                    "root_node_name" => $root_node_name,
                    "root_node" => $root_node
                ]);
            }
        }

        //foreach ($nectoroid as $root_node_name => $root_node) {
        for ($ix=0; $ix < count($newNectoroids); $ix++) { 
            $newNectoroid = $newNectoroids[$ix];
            $root_node_name = $newNectoroid["root_node_name"];
            $root_node = $newNectoroid["root_node"];

            if(tools_startsWith($root_node_name,"_")){
                //nyd
                //conditions finish implementations of these conditinal blocks
                if(tools_startsWith($root_node_name,"_if")){
                    $whole_honey[$root_node_name] = $root_node;
                }elseif(tools_startsWith($root_node_name,"_qrcode_business_card")){
                    $default_cfg = array(); //fill in this deafult confug for the qr code
                    $cfg = (isset($root_node["config"]))?$root_node["config"]: $default_cfg;
                    $qgbc = qrcode_generate_business_card($cfg,$root_node["data"],$bee,$user_id);
                    $res[BEE_EI] = array_merge($res[BEE_EI],$qgbc[BEE_EI]);
                    $whole_honey[$root_node_name] = $qgbc[BEE_RI];
                    continue;
                }elseif(tools_startsWith($root_node_name,"_rmv_file_")){
                    //delete an uploaded file
                    $filePath = BASE_ROOT . $root_node;
                    if (!file_exists($filePath)) {
                        array_push($res[BEE_EI],"The file " . $root_node. " does not exist");
                    }else{
                        if(!unlink($filePath)){
                            array_push($res[BEE_EI],"The file " . $root_node. " cannot be delete");
                        }else{
                            $whole_honey[$root_node_name] = "ok";
                        }
                    }
                    continue;
                }elseif(tools_startsWith($root_node_name,"_cloudinary_upload_file")){
                    $config = $bee["BEE_HIVE_STRUCTURE"]["cloudinary"];
                    //tools_dumpx("-----",__FILE__,__LINE__,$config);
                    $cfu = cloudinary_upload_file($config,$root_node);
                    $res[BEE_EI] = array_merge($res[BEE_EI],$cfu[BEE_EI]);
                    $whole_honey[$root_node_name] = $cfu[BEE_RI];
                    continue;
                }elseif(tools_startsWith($root_node_name,"_f_br")){
                    //list down the functions/flowers that will be visited/executed just
                    //before the data/honey is returned
                    if(is_string($root_node)){
                        $before_functions = explode(" ",$root_node);
                    }else{
                        $before_functions = $root_node;
                    }   
                    //var_dump($before_functions );
                }elseif(tools_startsWith($root_node_name,"_box") && $root_node_name == "_box"){
                    $BEE_BOX = $root_node;
                }else{
                    continue;
                }
            }

            if(count($res[BEE_EI]) > 0){
                break;
            }
            //tools_dumpx("here in post foreach loop",__FILE__,__LINE__,$root_node);
            //nyd
            //check if user is authorised to post data here
            $nector = array();
            $nector[$root_node_name] = $root_node;
            $brp_res = bee_hive_post(
                $nector,
                $bee["BEE_HIVE_STRUCTURE"]["combs"],
                $bee["BEE_HIVE_CONNECTION"],
                $bee["BEE_USER"]["id"],
                $whole_honey
            );
            $res[BEE_EI] = array_merge($res[BEE_EI],$brp_res[BEE_EI]);
            //tools_dumpx("scannng user",__FILE__,__LINE__,$brp_res);
            if( count($brp_res[BEE_EI]) == 0){
                if(array_key_exists($root_node_name,$brp_res[BEE_RI])){
                    $whole_honey[$root_node_name] = $brp_res[BEE_RI][$root_node_name];
                }else{
                    //nyd
                    //include transactions so that we can undo inserts
                    $whole_honey[$root_node_name] = null;
                }
            }else{
                //tools_dump("errors 4 " . $root_node_name,__FILE__,__LINE__,[$res]);
                //nyd
                //include transactions so that we can undo inserts
                $whole_honey[$root_node_name] = null;
            }
            //tools_dump("here brp_res @ " . $root_node_name,__FILE__,__LINE__,[$brp_res[BEE_RI],$brp_res[BEE_EI]]);
            // tools_dump("connnecA: ",__FILE__,__LINE__,[
            //     $root_node_name,
            //     $root_node_name == "user",
            //     (!isset($BEE_GLOBALS["is_register_call"]) || $BEE_GLOBALS["is_register_call"] == false),
            //     (!isset($BEE_GLOBALS["is_signup_call"]) || $BEE_GLOBALS["is_signup_call"] == false),               
            // ]);
            if( count($brp_res[BEE_EI]) == 0 &&
                ($root_node_name == "user" || $root_node_name == "users") &&
                (!isset($BEE_GLOBALS["is_register_call"]) || $BEE_GLOBALS["is_register_call"] == false)  &&
                (!isset($BEE_GLOBALS["is_signup_call"]) || $BEE_GLOBALS["is_signup_call"] == false) &&
                (!isset($BEE_GLOBALS["is_inivite_call"]) || $BEE_GLOBALS["is_inivite_call"] == false)
            ){
                //need to add these people as hive users
                if($root_node_name == "user"){
                    $password = "";
                    if(isset($root_node["password"])){
                        $password = password_hash($root_node["password"], PASSWORD_DEFAULT);
                    }elseif(isset($root_node["_encrypt_password"])){
                        $password = password_hash($root_node["_encrypt_password"], PASSWORD_DEFAULT);
                    }

                    //was a single object
                    $hive_user_nector = array(
                        "hive_user" => array(
                            "hive_id" => $bee["BEE_HIVE_ID"], //cbh
                            "hive_name" => $bee["BEE_APP_NAME"],
                            "user_id" =>  $whole_honey["user"],
                            "email" => $root_node["email"],
                            "password" => $password
                        )
                    );
                    $brp_res3 = bee_hive_post($hive_user_nector,$bee["BEE_GARDEN_STRUCTURE"],$bee["BEE_GARDEN_CONNECTION"],$bee["BEE_USER"]["id"]);
                    //tools_dumpx("connnecx: ",__FILE__,__LINE__,$brp_res3);
                    $res[BEE_EI] = array_merge($res[BEE_EI],$brp_res3[BEE_EI]);
                }else{
                    //was many objects
                    $index = 0;
                    foreach ($root_node as $user_node_key => $user_node) {
                        $password = "";
                        if(isset($user_node["password"])){
                            $password = password_hash($user_node["password"], PASSWORD_DEFAULT);
                        }elseif(isset($user_node["_encrypt_password"])){
                            $password = password_hash($user_node["_encrypt_password"], PASSWORD_DEFAULT);
                        }
                        //was a single object
                        $hive_user_nector = array(
                            "hive_user" => array(
                                "hive_id" => $bee["BEE_HIVE_ID"], //cbh
                                "hive_name" => $bee["BEE_APP_NAME"],
                                "user_id" =>  $whole_honey["users"][$index],
                                "email" => $user_node["email"],
                                "password" => $password
                            )
                        );
                        $brp_res3 = bee_hive_post($hive_user_nector,$bee["BEE_GARDEN_STRUCTURE"],$bee["BEE_GARDEN_CONNECTION"],$bee["BEE_USER"]["id"]);
                        $res[BEE_EI] = array_merge($res[BEE_EI],$brp_res3[BEE_EI]);
                        $index = $index + 1;
                    }
                }
            }
        }


        //start: execute server flowers/functions before return
        if(count($before_functions) > 0 && count($brp_res[BEE_EI]) == 0 ){
            //tools_dump("tracing before_functions ",__FILE__,__LINE__,$before_functions);
            $if_executed_smthing = false;
            //we have some flowers to visit
            //1.check _hive.for _f_br
            $_f_br = null;
            if(array_key_exists("_f_br",$bee["BEE_HIVE_STRUCTURE"])){
                $_f_br = $bee["BEE_HIVE_STRUCTURE"]["_f_br"];
            }
            for ($ibf=0; $ibf < count($before_functions); $ibf++) {  //ibf => index of before function
                $functions_name = $before_functions[$ibf];
                //check if this function has path config
                $path = "";
                if($_f_br != null && array_key_exists($functions_name,$_f_br)){
                    $path = $_f_br[$functions_name];
                }   
                $fileToInclude = $path . $functions_name . ".php";
                //include this functions config if its not alreday included
                if(in_array($fileToInclude,$included_before_functions_paths) == false){
                    //its not yet included, so we include it
                    include($fileToInclude);
                    array_push($included_before_functions_paths,$fileToInclude);
                    //execute that function
                    $if_executed_smthing = true;
                    $BEE_BR_HONEY = $whole_honey;
                    $exec = $functions_name."();";
                    eval($exec);
                }
            }
            if($if_executed_smthing == true){
                $whole_honey = $BEE_BR_HONEY;
            }
        }
        //end: execute server flowers/functions before return

        // run updates after main post has run
        if(array_key_exists("_pst_update", $nectoroid)){
            $pst_update = $nectoroid["_pst_update"];
            $record_at = "_pst_nig__update";
            $whole_honey[$record_at] = [];
            $res_bru = bee_run_update($pst_update,$bee,$user_id,$whole_honey, $record_at);
            $res[BEE_EI] = array_merge($res[BEE_EI],$res_bru[BEE_EI]);
            if(count($res[BEE_EI]) == 0){
                $temp = $res_bru[BEE_RI][$record_at];
                $whole_honey["_pst_update"] = $temp;
            }else{
                $whole_honey["_pst_update"] = $res_bru;
            }
            unset($whole_honey[$record_at]);
        }

        $res[BEE_RI] = $whole_honey;
        $res[2] = $bee;
        return $res;
    }

    function bee_run_update($nectoroid,$bee,$user_id,$current_raw_honey=null, $insert_res_at = ""){
        $res = array(null,array(),null);

        //go through the entire nectorid processing
        //node by node on the root
        $whole_honey = array();
        if(strlen($insert_res_at) > 0){
            $whole_honey = $current_raw_honey;
        }

        //handle _pre querires
        if(array_key_exists("_pre_delete", $nectoroid)){
            $pre_delete = $nectoroid["_pre_delete"];
            $res_brd = bee_run_delete($pre_delete,$bee,$user_id);
            $res[BEE_EI] = array_merge($res[BEE_EI],$res_brd[BEE_EI]);
            if(count($res[BEE_EI]) == 0){
                $whole_honey["_pre_delete"] = $res_brd[BEE_RI];
            }else{
                $whole_honey["_pre_delete"] = $res_brd;
            }
        }
        if(array_key_exists("_pre_post", $nectoroid)){
            $pre_post = $nectoroid["_pre_post"];
            $res_brp = bee_run_post($pre_post,$bee,$user_id);
            $res[BEE_EI] = array_merge($res[BEE_EI],$res_brp[BEE_EI]);
            if(count($res[BEE_EI]) == 0){
                $whole_honey["_pre_post"] = $res_brp[BEE_RI];
            }else{
                $whole_honey["_pre_post"] = $res_brd;
            }
        }   
        foreach ($nectoroid as $root_node_name => $root_node) {

            if(tools_startsWith($root_node_name,"_")){
                //tools_dumpx("iam here",__FILE__,__LINE__,1);
                //fx
                if(tools_startsWith($root_node_name,"_fxc_")){
                    $comb_namex = substr($root_node_name,strlen("_fxc_"));
                    $comb_name  = Inflect::singularize($comb_namex);
                    $bsfu_res = bee_sqllization_fxc_update($comb_name, $root_node, $bee["BEE_HIVE_STRUCTURE"]["combs"], $user_id, $bee["BEE_HIVE_CONNECTION"]);
                    if(strlen($insert_res_at) == 0){
                        $whole_honey[$root_node_name] = $bsfu_res[BEE_RI];
                    }else{
                        $whole_honey[$insert_res_at][$root_node_name] = $bsfu_res[BEE_RI];
                    }
                    $res[BEE_EI] = array_merge($res[BEE_EI],$bsfu_res[BEE_EI]);
                }
                continue;
            }
            //tools_dumpx("here in post foreach loop",__FILE__,__LINE__,$root_node);
            //nyd
            //check if user is authorised to post data here

            $comb_name = Inflect::singularize($root_node_name);
            if($root_node_name == $comb_name){//single object
                $nector = array();
                $nector[$root_node_name] = $root_node;

                $brp_res = bee_hive_update(
                    $nector,
                    $bee["BEE_HIVE_STRUCTURE"]["combs"],
                    $bee["BEE_HIVE_CONNECTION"],
                    $bee["BEE_USER"]["id"],
                    $whole_honey
                );
                // tools_dumpx("here brp_res",__FILE__,__LINE__,[$root_node_name, $brp_res]);
                if(strlen($insert_res_at) == 0){
                    $whole_honey[$root_node_name] = $brp_res[BEE_RI][$root_node_name];
                }else{
                    $whole_honey[$insert_res_at][$root_node_name] = $brp_res[BEE_RI][$root_node_name];
                }
                $res[BEE_EI] = array_merge($res[BEE_EI],$brp_res[BEE_EI]);
            }else{//probably plural
                $updates = array();
                foreach ($root_node as $update_node ) {
                    $nector = array();
                    $nector[$root_node_name] = $update_node;
                    $brp_res = bee_hive_update(
                        $nector,
                        $bee["BEE_HIVE_STRUCTURE"]["combs"],
                        $bee["BEE_HIVE_CONNECTION"],
                        $bee["BEE_USER"]["id"],
                        $whole_honey,
                        $current_raw_honey
                    );
                    $res[BEE_EI] = array_merge($res[BEE_EI],$brp_res[BEE_EI]);
                    array_push($updates, $brp_res[BEE_RI][$root_node_name]);
                }
                if(strlen($insert_res_at) == 0){
                    $whole_honey[$root_node_name] = $updates;
                }else{
                    $whole_honey[$insert_res_at][$root_node_name] = $updates;
                }
            }
        }
        // run posts after updates have been made
        if(array_key_exists("_pst_post", $nectoroid)){
            $pst_post = $nectoroid["_pst_post"];
            $res_brp = bee_run_post($pst_post,$bee,$user_id);
            $res[BEE_EI] = array_merge($res[BEE_EI],$res_brp[BEE_EI]);
            if(count($res[BEE_EI]) == 0){
                $whole_honey["_pst_post"] = $res_brp[BEE_RI];
            }else{
                $whole_honey["_pst_post"] = $res_brp;
            }
        }  
        // run updates after main update has run
        if(array_key_exists("_pst_update", $nectoroid)){
            $pst_update = $nectoroid["_pst_update"];
            $record_at = "_pst_nig__update";
            $whole_honey[$record_at] = [];
            //tools_dumpx("whole_honey beofre selft loop recursion call",__FILE__,__LINE__,$whole_honey);
            $res_bru = bee_run_update($pst_update,$bee,$user_id,$whole_honey, $record_at);
            // tools_dumpx("recursion call res",__FILE__,__LINE__,$res_bru);
            $res[BEE_EI] = array_merge($res[BEE_EI],$res_bru[BEE_EI]);
            if(count($res[BEE_EI]) == 0){
                $temp = $res_bru[BEE_RI][$record_at];
                // tools_dumpx("temptemptemp",__FILE__,__LINE__,$temp);
                $whole_honey["_pst_update"] = $temp;
            }else{
                // tools_dumpx("tyboyboy",__FILE__,__LINE__,$res_bru);
                $whole_honey["_pst_update"] = $res_bru;
            }
            unset($whole_honey[$record_at]);
        }


        $res[BEE_RI] = $whole_honey;
        $res[2] = $bee;
        return $res;
    }


    function bee_run_delete($nectoroid,$bee,$user_id){
        $res = array(null,array(),null);

        $is_restricted = false;
        if(isset($bee["BEE_HIVE_STRUCTURE"]["is_restricted"])){
            $is_restricted = $bee["BEE_HIVE_STRUCTURE"]["is_restricted"];
        }


        //go through the entire nectorid processing
        //node by node on the root
        $whole_honey = array();
        foreach ($nectoroid as $root_node_name => $root_node) {

            if(tools_startsWith($root_node_name,"_")){
                continue;
            }
            //tools_dumpx("here in post foreach loop",__FILE__,__LINE__,$root_node);
            //nyd
            //check if user is authorised to delete data here
            $nector = array();
            $nector[$root_node_name] = $root_node;

            $brp_res = bee_hive_delete(
                $nector,
                $bee["BEE_HIVE_STRUCTURE"]["combs"],
                $bee["BEE_HIVE_CONNECTION"],
                $bee["BEE_USER"]["id"],
                $is_restricted
            );

            //tools_dumpx("here brp_res",__FILE__,__LINE__,$brp_res);
            $whole_honey[$root_node_name] = $brp_res[BEE_RI][$root_node_name];
            $res[BEE_EI] = array_merge($res[BEE_EI],$brp_res[BEE_EI]);
        }
        // run posts after deletes have been made
        if(array_key_exists("_pst_post", $nectoroid)){
            $pst_post = $nectoroid["_pst_post"];
            $res_brp = bee_run_post($pst_post,$bee,$user_id);
            $res[BEE_EI] = array_merge($res[BEE_EI],$res_brp[BEE_EI]);
            if(count($res[BEE_EI]) == 0){
                $whole_honey["_pst_post"] = $res_brp[BEE_RI];
            }else{
                $whole_honey["_pst_post"] = $res_brp;
            }
        }  

        $res[BEE_RI] = $whole_honey;
        $res[2] = $bee;
        return $res;
    }


    function bee_run_get($nectoroid,$structure,$connection){
        $res = array(null,array(),$structure);

        //tools_dump("@0 == ",__FILE__,__LINE__,$connection);
        $sr_res = segmentation_run($nectoroid,$structure,$connection);
        //tools_dump("segmentation_run",__FILE__,__LINE__,$sr_res[BEE_RI]);
        $hasr_res = hive_after_segmentation_run($sr_res,$nectoroid,$structure,$connection);
        $res[BEE_RI] = $hasr_res[BEE_RI];
        $res[BEE_EI] = array_merge($res[BEE_EI],$hasr_res[BEE_EI]);
        $res[2] = $hasr_res[2];
        return $res;
    }

    if(array_key_exists("drone_security_enabled",$BEE_HIVE_STRUCTURE)){
        $dse = $BEE_HIVE_STRUCTURE["drone_security_enabled"];
        define("BEE_DRONE_SECURITY_ENABLED",$dse);
    }


    //nyd
    //get the children_tree from the hive structure


    function bee_handle_requests($bee){
        //echo "~#35"; exit(0);
        global $BEE_GLOBALS;
        global $countries_list;
        global $BEE;
        $res = array(null,array(),null);
        $res[BEE_EI] = array_merge($bee["BEE_ERRORS"],$res[BEE_EI]);

        bee_run_handle_ussd($bee);


        $brht_res = bee_run_handle_token($res,$bee);

        $res = $brht_res["res"];
        $bee = $brht_res["bee"];
        if(count($res[BEE_EI])>0){
            //tools_dumpx("checking allow ",__FILE__,__LINE__,  $res);
            tools_reply($res[BEE_RI],$res[BEE_EI],array(
                $bee["BEE_GARDEN_CONNECTION"],
                $bee["BEE_HIVE_CONNECTION"]
            ));
            return 0;
        }
        //echo "~#6768"; exit(0);
        //tools_dumpx("checking staff ",__FILE__,__LINE__, $bee);

        //ny finish api setup
        // tools_reply(array($_SERVER["REQUEST_URI"]),array(),array(
        //     $bee["BEE_GARDEN_CONNECTION"],
        //     $bee["BEE_HIVE_CONNECTION"]
        // ));
        // echo "~#34"; exit(0);


        if($_SERVER["REQUEST_METHOD"] == "GET"){
            $BEE = $bee;
            // tools_dumpx("checksconnection",__FILE__,__LINE__,$BEE["BEE_HIVE_CONNECTION"]);
            $res = bee_run_handle_get($res,$bee,null);
        }else if($_SERVER["REQUEST_METHOD"] == "POST"){
            $res = bee_run_handle_post($res,$bee,null);
        }else if($_SERVER["REQUEST_METHOD"] == "PUT"){
            $res = bee_run_handle_put($res,$bee,null);
        }else if($_SERVER["REQUEST_METHOD"] == "UPDATE"){
            $res = bee_run_handle_update($res,$bee,null);
        }else if($_SERVER["REQUEST_METHOD"] == "DELETE"){
            $res = bee_run_handle_delete($res,$bee,null);
        }

        //

        tools_reply($res[BEE_RI],$res[BEE_EI],array(
            $bee["BEE_GARDEN_CONNECTION"],
            $bee["BEE_HIVE_CONNECTION"]
        ));
    }

    //_f_migrate
    //nyd implement other kinds of migration logic like update,alter, delete, rename etc
    //nyd put inot effect that when tables are removed or added or renamed the roles module is affected
    //tools_dumpx("hives",__FILE__,__LINE__,$_GET["migrate"]);
    if(array_key_exists("_migrate",$BEE_HIVE_STRUCTURE["combs"]) && isset($_GET["migrate"])){

        $to_migrate = $BEE_HIVE_STRUCTURE["combs"]["_migrate"];
        //migration session
        $migration_round = 0;
        if(array_key_exists("round",$to_migrate)){
            $migration_round = intval($to_migrate["round"]);
        }
        $migration_name = "bee_migrated_from_" . $migration_round . "_to_" . ($migration_round+1) . "_";
        $migration_round = $migration_round + 1;

        if(count(array_keys($to_migrate)) > 0){
            $hives = $BEE["BEE_GARDEN"]["hives"];

            $included_before_functions_paths = array();
            $done_creates = array();
            $done_migrate_functions = array();
            $done_seeds = array();
            $done_add_sections = array();
            $has_been_modified = false;
            //go throught all the hives
            for ($i=0; $i < count($hives); $i++) {
                $db_name = $hives[$i]["hive_name"];
                //get a connection to this hive
                $conn = hive_run_get_connection(BEE_USER_NAME, BEE_PASSWORD, BEE_SERVER_NAME,$db_name);
                if(count($conn[1])>0){
                    //nyd
                    //what should we do when we experience an error here
                    array_push($foo,"Error While _migrating: ". $conn[1][0]);
                }else{
                    $mig_connection = $conn[0];


                    //start migrate creates
                    if(array_key_exists("create",$to_migrate)){
                        foreach ($to_migrate["create"] as $to_migrate_comb_name => $to_migrate_seeds) {
                            $temp_foo = array();
                            if(tools_startsWith($to_migrate_comb_name,"_")){
                                continue;
                            }
                            $hv_user = null;
                            if(count($hives[$i]["hive_users"])>0){
                                $hv_user = $hives[$i]["hive_users"][0];
                            }
                            //tools_dumpx("hives",__FILE__,__LINE__,$hv_user);
                            $simgle_to_migrate_comb_name = Inflect::singularize($to_migrate_comb_name);
                            $sectures = $BEE_HIVE_STRUCTURE["combs"][$simgle_to_migrate_comb_name];
                            $hrss_res = hive_run_secture_sqlization($sectures);
                            $sections_sqls = $hrss_res[BEE_RI];
                            $hrct_res =  hive_run_ct($mig_connection,$simgle_to_migrate_comb_name, $sections_sqls);
                            $temp_foo = array_merge($temp_foo,$hrct_res[BEE_EI]);
                            //add permissions for the super user
                            $connection_origi = $BEE["BEE_HIVE_CONNECTION"];
                            $user_origi = $BEE["BEE_USER"];
                            $BEE["BEE_HIVE_CONNECTION"] = $mig_connection;
                            $BEE["BEE_USER"] = array("id" => 0);
                            $perm = array(
                                "role_permisiion" => array(
                                    "role_id" => 1,
                                    "permission" => $simgle_to_migrate_comb_name,
                                    "can_create" => 1,
                                    "can_read" => 1,
                                    "can_update" => 1,
                                    "can_delete" => 1
                                )
                                // 08/02/2019
                                // 2 small dishes
                                // sweater school alisha
                                // tooth brush
                                // yorghut
                                // omuchungwa-2cups-2sugar X2
                                // diana amazzi kunnya
                            );
                            $brp_res3 = bee_run_post($perm,$BEE,0);
                            $temp_foo = array_merge($temp_foo,$brp_res3[BEE_EI]);
                            if(is_array($to_migrate_seeds) && !empty($to_migrate_seeds)){
                                //seed this newly created table
                                $seed = array();
                                $seed[$to_migrate_comb_name] = $to_migrate_seeds;
                                $brp_res3 = bee_run_post($seed,$BEE,0);
                                $temp_foo = array_merge($temp_foo,$brp_res3[BEE_EI]);
                            }
                            if(count($temp_foo)>0){
                                $foo = array_merge($foo,$temp_foo);
                            }else{
                                //no errors then we can say that we are done
                                if(array_key_exists($to_migrate_comb_name,$done_creates) == false){
                                    $done_creates[$to_migrate_comb_name] =  $to_migrate_seeds;
                                }
                            }
                            $BEE["BEE_HIVE_CONNECTION"] = $connection_origi;
                            $BEE["BEE_USER"] = $user_origi;
                        }
                    }
                    //end migrate creates


                    //start migrate add_section
                    if(array_key_exists("add_section",$to_migrate)){
                        //tools_dump("here ",__FILE__,__LINE__,56);
                        foreach ($to_migrate["add_section"] as $to_migrate_comb_name => $section_names_list) {
                            $temp_foo = array();
                            //tools_dump("here adde",__FILE__,__LINE__,57);
                            if(tools_startsWith($to_migrate_comb_name,"_")){
                                continue;
                            }
                            $hv_user = null;
                            if(count($hives[$i]["hive_users"])>0){
                                $hv_user = $hives[$i]["hive_users"][0];
                            }
                            //tools_dumpx("hives",__FILE__,__LINE__,$hv_user);
                            $simgle_to_migrate_comb_name = Inflect::singularize($to_migrate_comb_name);

                            $connection_origi = $BEE["BEE_HIVE_CONNECTION"];
                            $user_origi = $BEE["BEE_USER"];
                            $BEE["BEE_HIVE_CONNECTION"] = $mig_connection;
                            $BEE["BEE_USER"] = array("id" => 0);

                            $sectures = $BEE_HIVE_STRUCTURE["combs"][$simgle_to_migrate_comb_name];
                            foreach ($section_names_list as $section_name) {
                                $sect_to_add = array();
                                $sect_to_add[$section_name] = $sectures[$section_name];
                                $hrsq_res = hive_run_secture_sqlization($sect_to_add);
                                //tools_dump("adding column ",__FILE__,__LINE__,$hrsq_res);
                                $temp_foo = array_merge($temp_foo,$hrsq_res[BEE_EI]);
                                if(count($hrsq_res[BEE_EI])==0){
                                    $colm_sql = $hrsq_res[BEE_RI][0];
                                    //tools_dumpx("adding column colm_sql",__FILE__,__LINE__,$colm_sql);
                                    $db_res = hive_run_ac($mig_connection,$simgle_to_migrate_comb_name, $colm_sql);
                                    //tools_dumpx("adding column db_res",__FILE__,__LINE__,$db_res);
                                    $temp_foo = array_merge($temp_foo,$db_res[BEE_EI]);
                                }
                            }
                            //tools_dumpx("nothing happened ",__FILE__,__LINE__,5);

                            if(count($temp_foo)>0){
                                $foo = array_merge($foo,$temp_foo);
                            }else{
                                //no errors then we can say that we are done
                                if(array_key_exists($to_migrate_comb_name,$done_add_sections) == false){
                                    $done_add_sections[$to_migrate_comb_name] =  $section_names_list;
                                }
                            }
                            $BEE["BEE_HIVE_CONNECTION"] = $connection_origi;
                            $BEE["BEE_USER"] = $user_origi;
                        }
                    }
                    //end migrate creates

                    //start migrate function/flower
                    if(array_key_exists("_f_mf",$to_migrate)){
                        foreach ($to_migrate["_f_mf"] as $to_migrate_func_name => $to_migrate_func_path){
                            $temp_foo = array();
                            if(tools_startsWith($to_migrate_func_name,"_")){
                                continue;
                            }
                            $path = $to_migrate_func_path;
                            $fileToInclude = $path . $to_migrate_func_name . ".php";
                            //include this functions config if its not alreday included
                            if(in_array($fileToInclude,$included_before_functions_paths) == false){
                                //its not yet included, so we include it
                                include($fileToInclude);
                                array_push($included_before_functions_paths,$fileToInclude);
                            }
                            //execute that function
                            $connection_origi = $BEE["BEE_HIVE_CONNECTION"];
                            $user_origi = $BEE["BEE_USER"];
                            $BEE["BEE_HIVE_CONNECTION"] = $mig_connection;
                            $BEE["BEE_USER"] = array("id" => 0);
                            $BEE_BOX["BEE"] = $BEE;
                            $BEE_BOX["hive"] = $hives[$i];
                            $exec = $to_migrate_func_name."();";
                            eval($exec);
                            //tools_dumpx("eval mig func",__FILE__,__LINE__,"hi");
                            if(array_key_exists($to_migrate_func_name,$done_migrate_functions) == false){
                                $done_migrate_functions[$to_migrate_func_name] =  $to_migrate_func_path;
                            }
                            $BEE["BEE_HIVE_CONNECTION"] = $connection_origi;
                            $BEE["BEE_USER"] = $user_origi;
                            $BEE_BOX["BEE"] = null;
                            $BEE_BOX["hive"] = null;
                            unset($BEE_BOX["BEE"]);
                            unset($BEE_BOX["hive"]);
                        }
                    }
                    //end migrate functin/flower

                    //start migrate seeds
                    if(array_key_exists("seeds",$to_migrate)){
                        $toSeed = array();
                        $needsSeeding = false;
                        foreach ($to_migrate["seeds"] as $to_migrate_comb_name => $to_migrate_seeds) {
                            if(tools_startsWith($to_migrate_comb_name,"_")){
                                continue;
                            }
                            if(is_array($to_migrate_seeds) && !empty($to_migrate_seeds)){
                                $toSeed[$to_migrate_comb_name] = $to_migrate_seeds;
                                $needsSeeding = true;
                            }
                        }
                        //tools_dumpx("here to seed migration ",__FILE__,__LINE__,$toSeed);

                        if($needsSeeding == true){
                            $temp_foo = array();
                            $hv_user = null;
                            if(count($hives[$i]["hive_users"])>0){
                                $hv_user = $hives[$i]["hive_users"][0];
                            }

                            $connection_origi = $BEE["BEE_HIVE_CONNECTION"];
                            $user_origi = $BEE["BEE_USER"];
                            $BEE["BEE_HIVE_CONNECTION"] = $mig_connection;
                            $BEE["BEE_USER"] = array("id" => 0);

                            $brp_res3 = bee_run_post($toSeed,$BEE,0);
                            //tools_dumpx("seeding post ",__FILE__,__LINE__,$brp_res3);
                            $temp_foo = array_merge($temp_foo,$brp_res3[BEE_EI]);

                            if(count($temp_foo)>0){
                                $foo = array_merge($foo,$temp_foo);
                            }else{
                                foreach ($brp_res3[BEE_RI] as $to_migrate_x_name => $results) {
                                    $done_seeds[$to_migrate_x_name] =  $to_migrate["seeds"][$to_migrate_x_name];
                                }
                                // //no errors then we can say that we are done
                                // if(array_key_exists($to_migrate_comb_name,$done_seeds) == false){
                                //     $done_seeds[$to_migrate_comb_name] =  $to_migrate_seeds;
                                // }
                            }
                            $BEE["BEE_HIVE_CONNECTION"] = $connection_origi;
                            $BEE["BEE_USER"] = $user_origi;
                        }
                    }
                    //end migrate seeds



                    //destroy connection to this hive
                    $mig_connection = null;
                }
            }
            //update the hive config
            foreach ($done_creates as $to_migrate_comb_name => $to_migrate_seeds) {
                unset($BEE_HIVE_STRUCTURE["combs"]["_migrate"]["create"][$to_migrate_comb_name]);
                $BEE_HIVE_STRUCTURE["combs"]["_migrate"]["create"]["_done_".$migration_name.$to_migrate_comb_name] = $to_migrate_seeds;
                $has_been_modified = true;
            }
            foreach ($done_migrate_functions as $func_name => $func_path) {
                unset($BEE_HIVE_STRUCTURE["combs"]["_migrate"]["_f_mf"][$func_name]);
                $BEE_HIVE_STRUCTURE["combs"]["_migrate"]["_f_mf"]["_done_".$migration_name.$func_name] = $func_path;
                $has_been_modified = true;
            }
            foreach ($done_seeds as $seed_comb_name => $seeds) {
                unset($BEE_HIVE_STRUCTURE["combs"]["_migrate"]["seeds"][$seed_comb_name]);
                $BEE_HIVE_STRUCTURE["combs"]["_migrate"]["seeds"]["_done_".$migration_name.$seed_comb_name] = $seeds;
                $has_been_modified = true;
            }
            foreach ($done_add_sections as $to_migrate_comb_name => $section_names_list) {
                unset($BEE_HIVE_STRUCTURE["combs"]["_migrate"]["add_section"][$to_migrate_comb_name]);
                $BEE_HIVE_STRUCTURE["combs"]["_migrate"]["add_section"]["_done_".$migration_name.$to_migrate_comb_name] = $section_names_list;
                $has_been_modified = true;
            }
            if($has_been_modified){
                $BEE_HIVE_STRUCTURE["combs"]["_migrate"]["round"] = $migration_round;
                file_put_contents(BEE_HIVE_STUCTURE_FILE_NAME, json_encode($BEE_HIVE_STRUCTURE));
            }
        }
        if(count($foo)>0){
            echo "errors <br/>";
            var_dump($foo);
        }
        echo "BEEE Hive Migration is finished";
        exit(0);
    }




    //this is the last in this file
    //register my application
    //returns the connection to the hive
    if($BEE_HIVE_STRUCTURE["is_registration_public"] == false){

        $brrh_res = bee_run_register_hive(array(
            "_f_register" => $BEE_HIVE_STRUCTURE["_f_register"]
        ), $BEE);
        $BEE_ERRORS = array_merge($BEE_ERRORS,$brrh_res[BEE_EI]);
        $BEE_HIVE_CONNECTION = $brrh_res[BEE_RI];
        $BEE["BEE_HIVE_CONNECTION"] = $BEE_HIVE_CONNECTION;
        $hive_name = BEE_GARDEN . "_" . tools_sanitise_name($BEE_HIVE_STRUCTURE["_f_register"]["app_name"]);





        //nyd
        //get in the current state of the garden only if there was creation of new
        //hive, the current code  below will run allways
        if(count($BEE_ERRORS)==0){
            $hrgg_res = hive_run_get_garden($BEE_GARDEN_STRUCTURE,$BEE_GARDEN_CONNECTION);
            $BEE_ERRORS = array_merge($BEE_ERRORS,$hrgg_res[BEE_EI]);
            $GARDEN_STRUCTURE = $hrgg_res[2];
            //tools_reply($hrgg_res[BEE_RI],$BEE_ERRORS,array($BEE_GARDEN_CONNECTION));
            $BEE_GARDEN = $hrgg_res[BEE_RI];
            //roles,permissions, modules
            $security_nector = array(
                "roles" => array(
                    "role_permisiions" => array(),
                    "role_modules" => array()
                )
            );
            $brg_res = bee_run_get($security_nector,$BEE_HIVE_STRUCTURE["combs"],$BEE_HIVE_CONNECTION);
            $BEE_ERRORS = array_merge($BEE_ERRORS,$brg_res[BEE_EI]);
            $BEE_ROLES = $brg_res[BEE_RI]["roles"];
            //tools_dumpx("brg_res",__FILE__,__LINE__,$brg_res[BEE_RI]);

            $BEE = array(
                "BEE_ROLES" => $BEE_ROLES,
                "BEE_HIVE_STRUCTURE" => $BEE_HIVE_STRUCTURE,
                "BEE_GARDEN_STRUCTURE" => $GARDEN_STRUCTURE,
                "BEE_GARDEN_CONNECTION" => $BEE_GARDEN_CONNECTION,
                "BEE_HIVE_CONNECTION" => $BEE_HIVE_CONNECTION,
                "BEE_GARDEN" => $BEE_GARDEN,
                "BEE_ERRORS" => $BEE_ERRORS,
                "BEE_JWT_ENCRYPTION" => $BEE_JWT_ENCRYPTION,
                "BEE_USER" => array("id"=>0),
                "BEE_APP_NAME" => $hive_name
            );
        }

        
    }

    // tools_dumpx("connecting",__FILE__,__LINE__,$BEE["BEE_HIVE_CONNECTION"]);

?>
