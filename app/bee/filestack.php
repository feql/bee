<?php

    function weavyio_creat_app(){
        $server_to_server_key = "wys_Q9bafOuIYVhGervl90B17R6FsLhnlo0kjTnI";
        try {
            $url = "https://0cc2a474289a4439afbd59fa9d0400b3.weavy.io/api/apps";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "authorization: Bearer " . $server_to_server_key,
                "content-type: application/json"
            ));
            $post = array(
                'uid' => 'u234',
                'type'  => 'Files'
            );
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                echo 'Weavy App Error:' . curl_error($ch);
            }
            curl_close($ch);
            $results = json_decode($response, true);
            tools_dumpx("crate app response ",__FILE__,__LINE__,array($response));
            return $results;
        } catch (Exception $e) {
            $e = $e->getMessage();
            // tools_dumpx("send grid errors",__FILE__,__LINE__,$e);
            tools_dumpx("weavy crate app errors",__FILE__,__LINE__,$e);
            return $e;
        }
    }

    function weavyio_generate_user_token($user_id){
        $server_to_server_key = "wys_Q9bafOuIYVhGervl90B17R6FsLhnlo0kjTnI";
        try {
            $url = "https://0cc2a474289a4439afbd59fa9d0400b3.weavy.io/api/users/u".$user_id."/tokens";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "authorization: Bearer " . $server_to_server_key
              ));

            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                echo 'Weavy Auth Error:' . curl_error($ch);
            }
            curl_close($ch);
            $results = json_decode($response, true);
            // tools_dumpx("user auth response ",__FILE__,__LINE__,array($response));
            return $results;
        } catch (Exception $e) {
            $e = $e->getMessage();
            // tools_dumpx("send grid errors",__FILE__,__LINE__,$e);
            tools_dumpx("weavy file auth errors",__FILE__,__LINE__,$e);
            return $e;
        }
    }

    

?>
