<?php
    
    function mailer_send_verification_email($receiver,$name, $link, $bee){
        try{
            //SENDGRID_API_KEY
            // tools_dumpx("SENDGRID_API_KEY",__FILE__,__LINE__,SENDGRID_API_KEY);
            $template_id = "d-e2ec999138464a6296e27a7eb53da502";
            $cm = sendActivationEmail($receiver,$name,$link,"Welcome To MPOLA ASSET MANAGER",$template_id);
            return $cm;
        }catch(Exception $ex){
            $e = $ex->getMessage();
            return $e;
        }
    }
 
    function mailer_send_username_verification_email($receiver,$name, $link, $bee){
        try{
            //tools_dumpx("NO DUPE",__FILE__,__LINE__,$link);
            $template_id = "d-e2ec999138464a6296e27a7eb53da502";
            $cm = sendActivationEmail($receiver,$name,$link,"MPOLA ASSET MANAGER - Verify YourAccount",$template_id);
            return $cm;
        }catch(Exception $ex){
            $e = $ex->getMessage();
            return $e;
        }
    }

    function sendActivationEmail($to,$to_name = "",$code = "",$subject="",$template_id="")
    {

        try {
            // $curl = curl_init();

            // curl_setopt_array($curl, array(
            //   CURLOPT_URL => "https://api.sendgrid.com/v3/mail/send",
            //   CURLOPT_RETURNTRANSFER => true,
            //   CURLOPT_ENCODING => "",
            //   CURLOPT_MAXREDIRS => 10,
            //   CURLOPT_TIMEOUT => 30,
            //   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            //   CURLOPT_CUSTOMREQUEST => "POST",
            //   CURLOPT_POSTFIELDS => "{\"personalizations\":[{\"to\":[{\"email\":\"".$to."\",\"name\":\"".$to_name."\"}],\"cc\":[{\"email\":\"dev@xyola.com\"}],\"dynamic_template_data\":{\"name\":\"".$to_name."\",\"code\":\"".$code."\"},\"subject\":\"".$subject."\"}],\"from\":{\"email\":\"dev@xyola.com\",\"name\":\"Mpola Asset Manager\"},\"reply_to\":{\"email\":\"dev@xyola.com\",\"name\":\"Mpola\"},\"template_id\":\"".$template_id."\"}",
            //   CURLOPT_HTTPHEADER => array(
            //     "authorization: Bearer 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",
            //     "content-type: application/json"
            //   ),
            // ));

            // $response = curl_exec($curl);
            // $err = curl_error($curl);

            // curl_close($curl);
            // tools_dumpx("send grid response ",__FILE__,__LINE__,array($response,$err));
            $response = sendmailbymailgun2($to,$to_name,$code);
            // tools_dumpx("send mailgun response ",__FILE__,__LINE__,array($response));
            return "ok";
        } catch (Exception $e) {
            $e = $e->getMessage();
            // tools_dumpx("send grid errors",__FILE__,__LINE__,$e);
            tools_dumpx("mailgun errors",__FILE__,__LINE__,$e);
            return $e;
        }
    }

    function sendmailbymailgun2($to,$toname,$code){
        $parts = explode("=", $code);
        $code = $parts[count($parts) - 1];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.mailgun.net/v3/sandboxab3718bb13ae4d288d998eb06d9a242c.mailgun.org/messages');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        $post = array(
            'from' => 'FQL <postmaster@sandboxab3718bb13ae4d288d998eb06d9a242c.mailgun.org>',
            'to' => $toname. '<'.$to.'>',
            'subject' => 'FQL Accivation Code',
            'template' => 'feql account activation code',
            'h:X-Mailgun-Variables' => '{"name": "'.$toname.'", "code":"'.$code.'"}'
        );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_USERPWD, 'api' . ':' . '67eebc866f0966908c6fc588e7367098-73f745ed-d91bb976');

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        $results = json_decode($response, true);
        return $results;
    }

    function sendmailbymailgun($to,$toname,$html,$text){
        $mail_gun_url = 'https://api.mailgun.net/v3/sandbox773c56a068cb499bb33e12e6d3df3d7c.mailgun.org/messages';
        $mail_gun_key = '642b54336d2badaada81babc25b97c4c-2de3d545-4f7e930b';  
        $array_data = array(
            'from'=> 'Mpola Asset Manager <postmaster@sandbox773c56a068cb499bb33e12e6d3df3d7c.mailgun.org>',
            'to'=> $toname. '<'.$to.'>',
            'subject'=> 'Verification Code For Mpola Asset Manager',
            'html'=>$html,
            'text'=>$text,
            'o:tracking'=>'yes',
            'o:tracking-clicks'=>'yes',
            'o:tracking-opens'=>'yes',
            // 'o:tag'=>$tag,
            'h:Reply-To'=>'dev@xyola.com'
        );

        $session = curl_init($mail_gun_url.'/messages');
        curl_setopt($session, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($session, CURLOPT_USERPWD, 'api:'.$mail_gun_key);
        curl_setopt($session, CURLOPT_POST, true);
        curl_setopt($session, CURLOPT_POSTFIELDS, $array_data);
        curl_setopt($session, CURLOPT_HEADER, false);
        curl_setopt($session, CURLOPT_ENCODING, 'UTF-8');
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($session);
        curl_close($session);
        $results = json_decode($response, true);
        return $results;
    }

    function mailer_send_verification_sms($phone_number,$name,$app_name,$hive_id,$code,$bee,$country){
        //nyd
        //process and prepare international phone_number
        //nyd
        //provide implementation
        $to = $phone_number;
        if(is_string($to)){
            $to = trim($to);
        }
        if($country == "uganda"){
            if(strlen($phone_number) == 10){
                $to = "+256" .  substr($phone_number,1);
            }
        }else{
            if(strlen($to) == 8){
                //e.g 77393543
                $to = "+267" . $to;
            }elseif(strlen($to) == 11)
            {   //e.g 26777393543
                $to = "+" . $to;
            }
        }

        //tools_dumpx("see things",__FILE__,__LINE__,array($to,$country));


        $message = 'Hello ' . $name . ", Thank you for joining botswana services, your activation code is  " . $code;
        $post  = "{\n   \"message\": \"".$message."\",\n    \"to\": \"".$to."\",\n    \"sender_id\": \"BWS\",\n   \"callback_url\": \"\"\n}";

        try {
            $curl = curl_init();
            curl_setopt_array($curl, array(
              CURLOPT_URL => "https://api.sms.to/sms/send",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_POSTFIELDS => $post,
              CURLOPT_HTTPHEADER => array(
                  "Content-Type: application/json",
                  "Accept: application/json",
                  "Authorization: Bearer " . SMSTO_API_KEY
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            return $response . "|".$post;
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            return "Error sms: " .$e->getMessage() . "|".$post;
        }
        return true;
    }

    function mailer_send_recovery_sms($phone_number,$code,$bee,$country){
        //nyd
        //process and prepare international phone_number
        //nyd
        //provide implementation
        $to = $phone_number;
        if(is_string($to)){
            $to = trim($to);
        }
        if($country == "uganda"){
            if(strlen($phone_number) == 10){
                $to = "+256" . substr($phone_number,1);
            }
        }else{
            if(strlen($to) == 8){
                //e.g 77393543
                $to = "+267" . $to;
            }elseif(strlen($to) == 11)
            {   //e.g 26777393543
                $to = "+" . $to;
            }
        }


        $message = "Hello, your xadden password reset code is  " . $code;
        $post  = "{\n   \"message\": \"".$message."\",\n    \"to\": \"".$to."\",\n    \"sender_id\": \"Xadden\",\n   \"callback_url\": \"\"\n}";

        try {
            $curl = curl_init();
            curl_setopt_array($curl, array(
              CURLOPT_URL => "https://api.sms.to/sms/send",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_POSTFIELDS => $post,
              CURLOPT_HTTPHEADER => array(
                  "Content-Type: application/json",
                  "Accept: application/json",
                  "Authorization: Bearer " . SMSTO_API_KEY
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            return $response . "|".$post;
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            return "Error sms: " .$e->getMessage() . "|".$post;
        }
        return true;
    }

    function mailer_send_recovery_email_old($receiver,$name, $links, $bee){
        try{
            $username = $bee["BEE_HIVE_STRUCTURE"]["sendgrid"]["username"];
            $sender = $bee["BEE_HIVE_STRUCTURE"]["sendgrid"]["sender"];
            $password = $bee["BEE_HIVE_STRUCTURE"]["sendgrid"]["password"];
            $subject = $bee["BEE_HIVE_STRUCTURE"]["recovery_email_subject"];
            $sendgrid = new SendGrid($username, $password);

            $cont = file_get_contents(BASE_ROOT."bee/recovery_email_template.html");
            $cont = str_replace("__client_name",$name,$cont);

            $str = "";
            foreach ($links as $ind => $link) {
                $str .= "<br/>For ".$link["title"]."<br/><a href=\"".$link['link']."\">".$link['link']."</a><br/>";
            }
            //tools_dumpx("str",__FILE__,__LINE__,$str);
            $cont = str_replace("__password",$str,$cont);
            //tools_dumpx("cont",__FILE__,__LINE__,$cont);
            $cont = str_replace("__year",date("Y"),$cont);

            //_dump($sendgrid);
            $email = new SendGrid\Email();
            $email->
                addTo($receiver)->
                setFrom($sender)->
                setSubject($subject)->
                setHtml($cont);
            $atemp = $sendgrid->sendEmail($email);
            if(property_exists($atemp,"errors")){
                var_dump($atemp->errors);
            }
            return True;
        }catch(Exception $ex){
            $e = $ex->getMessage();
            tools_dumpx("send grid errors",__FILE__,__LINE__,$e);
            return $e;
        }
    }

    function mailer_send_recovery_email($to,$to_name = "",$code = "")
    {

        try {
            $curl = curl_init();

            curl_setopt_array($curl, array(
              CURLOPT_URL => "https://api.sendgrid.com/v3/mail/send",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 30,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_POSTFIELDS => "{\"personalizations\":[{\"to\":[{\"email\":\"".$to."\",\"receiver_name\":\"".$to_name."\"}],\"cc\":[{\"email\":\"registrations@qot4me.com\"}],\"dynamic_template_data\":{\"receiver_name\":\"".$to_name."\",\"code\":\"".$code."\"},\"subject\":\"Password Rest For QOT4ME.COM\"}],\"from\":{\"email\":\"info@qot4me.com\",\"name\":\"Qot4me\"},\"reply_to\":{\"email\":\"info@qot4me.com\",\"name\":\"Qot4me\"},\"template_id\":\"d-af2b814ad9ca49ff8a5a2d6e15c4f680\"}",
              CURLOPT_HTTPHEADER => array(
                "authorization: Bearer " . SENDGRID_API_KEY,
                "content-type: application/json"
              ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);
            //tools_dumpx("send grid response ",__FILE__,__LINE__,array($response,$err));
            return "ok";
        } catch (Exception $e) {
            $e = $e->getMessage();
            //tools_dumpx("send grid errors",__FILE__,__LINE__,$e);
            return $e;
        }

    }

    function mailer_send_invitation_email($to_email,$to_name,$code,$password,$company,$role)
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.mailgun.net/v3/sandbox773c56a068cb499bb33e12e6d3df3d7c.mailgun.org/messages');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            $post = array(
                'from' => 'Mpola Asset Manager <postmaster@sandbox773c56a068cb499bb33e12e6d3df3d7c.mailgun.org>',
                'to' => $to_name. '<'.$to_email.'>',
                'subject' => 'Team Invitation To Handle Assets For ' . strtoupper($company),
                'template' => 'account_invitation_email',
                'h:X-Mailgun-Variables' => '{"name": "'.$to_name.'", "email": "'.$to_email.'", "code":"'.$code.'","password":"'.$password.'", "company":"'.$company.'", "role":"'.$role.'"}'
            );
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            curl_setopt($ch, CURLOPT_USERPWD, 'api' . ':' . '642b54336d2badaada81babc25b97c4c-2de3d545-4f7e930b');

            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }
            curl_close($ch);
            $results = json_decode($response, true);
            // tools_dumpx("send mailgun response ",__FILE__,__LINE__,array($response));
            return "ok";
        } catch (Exception $e) {
            $e = $e->getMessage();
            // tools_dumpx("send grid errors",__FILE__,__LINE__,$e);
            tools_dumpx("mailgun errors",__FILE__,__LINE__,$e);
            return $e;
        }
    }

    function mailer_send_edited_user_role_email($to_email,$to_name,$code,$company,$role)
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.mailgun.net/v3/sandbox773c56a068cb499bb33e12e6d3df3d7c.mailgun.org/messages');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            $post = array(
                'from' => 'Mpola Asset Manager <postmaster@sandbox773c56a068cb499bb33e12e6d3df3d7c.mailgun.org>',
                'to' => $to_name. '<'.$to_email.'>',
                'subject' => 'Admin Edited Acoount At ' . strtoupper($company),
                'template' => 'account_invitation_email',
                'h:X-Mailgun-Variables' => '{"name": "'.$to_name.'", "email": "'.$to_email.'", "code":"'.$code.'", "company":"'.$company.'", "role":"'.$role.'"}'
            );
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            curl_setopt($ch, CURLOPT_USERPWD, 'api' . ':' . '642b54336d2badaada81babc25b97c4c-2de3d545-4f7e930b');

            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }
            curl_close($ch);
            $results = json_decode($response, true);
            // tools_dumpx("send mailgun response ",__FILE__,__LINE__,array($response));
            return "ok";
        } catch (Exception $e) {
            $e = $e->getMessage();
            // tools_dumpx("send grid errors",__FILE__,__LINE__,$e);
            tools_dumpx("mailgun errors",__FILE__,__LINE__,$e);
            return $e;
        }
    }

    function mailer_send_edited_user_profile_email($to_email,$to_name,$code,$company)
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.mailgun.net/v3/sandbox773c56a068cb499bb33e12e6d3df3d7c.mailgun.org/messages');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            $post = array(
                'from' => 'Mpola Asset Manager <postmaster@sandbox773c56a068cb499bb33e12e6d3df3d7c.mailgun.org>',
                'to' => $to_name. '<'.$to_email.'>',
                'subject' => 'Admin Edited Acoount At ' . strtoupper($company),
                'template' => 'account_invitation_email',
                'h:X-Mailgun-Variables' => '{"name": "'.$to_name.'", "email": "'.$to_email.'", "code":"'.$code.'", "company":"'.$company.'"}'
            );
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            curl_setopt($ch, CURLOPT_USERPWD, 'api' . ':' . '642b54336d2badaada81babc25b97c4c-2de3d545-4f7e930b');

            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }
            curl_close($ch);
            $results = json_decode($response, true);
            // tools_dumpx("send mailgun response ",__FILE__,__LINE__,array($response));
            return "ok";
        } catch (Exception $e) {
            $e = $e->getMessage();
            // tools_dumpx("send grid errors",__FILE__,__LINE__,$e);
            tools_dumpx("mailgun errors",__FILE__,__LINE__,$e);
            return $e;
        }
    }

    function mailer_send_alert_sms($phone_number, $alert, $country){

        $to = format_internal_number($phone_number, $country);
        $message = $alert["title"] . " \n " . $alert["details"];
        $post  = "{\n   \"message\": \"".$message."\",\n    \"to\": \"".$to."\",\n    \"sender_id\": \"Akili\",\n   \"callback_url\": \"\"\n}";

        try {
            $curl = curl_init();
            curl_setopt_array($curl, array(
              CURLOPT_URL => "https://api.sms.to/sms/send",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_POSTFIELDS => $post,
              CURLOPT_HTTPHEADER => array(
                  "Content-Type: application/json",
                  "Accept: application/json",
                  "Authorization: Bearer " . SMSTO_API_KEY
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            return [1, $response, $post, $to, "https://api.sms.to"];
        } catch (Exception $e) {
            $msg =  "Error sms: " .$e->getMessage();
            return [0, $msg, $post, $to, "https://api.sms.to"];
        }
    }


    function format_internal_number($phone_number, $country){
        $to = $phone_number;
        if(is_string($to)){
            $to = trim($to);
        }
        if($country == "uganda"){
            if(strlen($phone_number) == 10){
                $to = "+256" . substr($phone_number,1);
            }
        }else{
            if(strlen($to) == 8){
                //e.g 77393543
                $to = "+267" . $to;
            }elseif(strlen($to) == 11)
            {   //e.g 26777393543
                $to = "+" . $to;
            }
        }
        return $to;
    }

   
?>
