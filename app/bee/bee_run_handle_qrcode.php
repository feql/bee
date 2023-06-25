<?php
    function bee_run_handle_qrcode(){

    }

    function qrcode_generate_business_card($cfg,$data,$bee,$user_id){
        //http://phpqrcode.sourceforge.net/examples/index.php?example=026
        $errors = array(); 
        
        $hive_name = "";
        if(array_key_exists("BEE_APP_NAME",$bee)){
            $hive_name = $bee["BEE_APP_NAME"];
        }else{
            $hive_name = $bee["BEE_HIVE_STRUCTURE"]["_f_register"]["app_name"];
        }
        $return_file_name = "";
        if(array_key_exists("domain",$bee["BEE_HIVE_STRUCTURE"]) == true){
            $return_file_name = $bee["BEE_HIVE_STRUCTURE"]["domain"];
        }
        $return_file_name .= "/bee/qrcodes/" . $hive_name . "/" . strval($user_id) . "/";
        $file_name = "qr_4_" . strval($user_id). "_" . strval(time()) . ".png"; 
        $return_file_name .= $file_name;

        //create directory if not exits
        $dir_name = dirname(__FILE__).DIRECTORY_SEPARATOR.'qrcodes';
        $dir_name .=  DIRECTORY_SEPARATOR.$hive_name.DIRECTORY_SEPARATOR.strval($user_id);
        if (!file_exists($dir_name)) {
            mkdir($dir_name,0777,true);
        }
        $qr_to_create_filename = $dir_name . DIRECTORY_SEPARATOR . $file_name;
        
        // how to build raw content - QRCode with detailed Business Card (VCard)
        include( BASE_ROOT .  '/bee/phpqrcode/qrlib.php');
        //include('config.php');
        //set it to writable location, a place for temp generated PNG files
        
        /*
        // here our data
        $name         = tool_default($data,"name",''); //John Doe
        $sortName     = tool_default($data,"sort_name",''); //'Doe;John';
        $phone        = tool_default($data,"phone",''); //'(049)012-345-678';
        $phonePrivate = tool_default($data,"phone_private",''); //'(049)012-345-987';
        $phoneCell    = tool_default($data,"phone_cell",''); //'(049)888-123-123';
        $orgName      = tool_default($data,"org_name",''); //'My Company Inc.';
        $email        = tool_default($data,"email",''); //'john.doe@example.com';
        // if not used - leave blank!
        $addressLabel     = tool_default($data,"address_label",''); //'Our Office';
        $addressPobox     = tool_default($data,"address_pobox",''); //'';
        $addressExt       = tool_default($data,"address_ext",''); //'Suite 123';
        $addressStreet    = tool_default($data,"address_street",''); //'7th Avenue';
        $addressTown      = tool_default($data,"address_town",''); //'New York';
        $addressRegion    = tool_default($data,"address_region",''); //'NY';
        $addressPostCode  = tool_default($data,"address_post_code",''); //'91921-1234';
        $addressCountry   = tool_default($data,"address_country",''); //'USA';

        // we building raw data
        
        $codeContents  = 'BEGIN:VCARD'."\n";
        $codeContents .= 'VERSION:2.1'."\n";
        $codeContents .= 'N:'.$sortName."\n";
        $codeContents .= 'FN:'.$name."\n";
        $codeContents .= 'ORG:'.$orgName."\n";
        $codeContents .= 'TEL;WORK;VOICE:'.$phone."\n";
        $codeContents .= 'TEL;HOME;VOICE:'.$phonePrivate."\n";
        $codeContents .= 'TEL;TYPE=cell:'.$phoneCell."\n";
        $codeContents .= 'ADR;TYPE=work;'.
            'LABEL="'.$addressLabel.'":'
            .$addressPobox.';'
            .$addressExt.';'
            .$addressStreet.';'
            .$addressTown.';'
            .$addressPostCode.';'
            .$addressCountry
        ."\n";
        $codeContents .= 'EMAIL:'.$email."\n";
        $codeContents .= 'END:VCARD';
        */

       
        $codeContents = json_encode($data);//after tumisangs requirements

        // generating
        $res = QRcode::png($codeContents, $qr_to_create_filename, QR_ECLEVEL_L, 3);
        
        //check is the created file exists
        if (!file_exists($qr_to_create_filename)) {
            array_push($errors,"Qrcode file was not created");
        }

        return array(array(
            "file_url" => $return_file_name,
            "file_name" => $qr_to_create_filename
        ),$errors);
    }

    
    

    

    



    
    

    

    
?>