<?php
    function bee_hive_run_cloudinary(){

    }

    function include_cloudinary($conf){
        require __DIR__ . DIRECTORY_SEPARATOR . 'autoload.php';
        require __DIR__ . DIRECTORY_SEPARATOR . 'cloudinary'. DIRECTORY_SEPARATOR .'Helpers.php'; //optional for using the cl_image_tag and cl_video_tag helper methods
        \Cloudinary::config(array( 
            "cloud_name" => $conf["cloud_name"], 
            "api_key" => $conf["api_key"], 
            "api_secret" => $conf["api_secret"], 
            "secure" => boolval($conf["secure"])
        ));
    }

    function cloudinary_upload_file($config,$nect){
        include_cloudinary($config);
        //check if thi file exists here
        $fn = $nect["file_name"];
        $res = array(
            "secure_url" => ""
        );
        $errors = array();
        try {
            $res = \Cloudinary\Uploader::upload($fn);
            $delete = boolval($config["delete_after_upload"]);
            if($delete == true){
                //delete this file from this server
                unlink($fn);
            }
        } catch (\Throwable $th) {
            $errors = array($th->getMessage());
        }
        
        return array($res["secure_url"],$errors);
    }
?>