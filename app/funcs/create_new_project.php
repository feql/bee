<?php

function create_new_project(){
    global $BEE_BR_HONEY;
    global $BEE_BOX;
    global $BEE;
    global $BEE_DIR_PATH;

    $new_res = [[],[]];

    $app_name = tools_sanitise_name($BEE_BOX["name"]);
    // //create a directory for the project
    $app_dir = "/created_bees/$app_name/";
    $app_docker_compose_file_path = $app_dir."docker-compose.yml";
    $host_app_docker_compose_file_path = "~/feql_project/created_bees/$app_dir/docker-compose.yml";

    //copy latest bee realease into this projects directory
    if(!file_exists($app_docker_compose_file_path)){
        mkdir($app_dir, 0777);
        $bass_release = "/latest_bee/.";
        $cmd = "cp -r $bass_release $app_dir";
        $shell_res = shell_exec($cmd);
    }

    // // //next port
    $next_port_file = "/bee_realease_configs/next_port.txt";
    $last_port_usage = trim(fgets(fopen($next_port_file, 'r')));
    $usage_parts = explode(" ", $last_port_usage);
    $last_port = $usage_parts[2];
    $next_port = intval($last_port) + 1;
    $mysql_docker_port = $next_port;
    $phpmyadmin_docker_port = $next_port + 1;
    $bee_docker_port = $next_port + 2;

    //edit dockerfile
    $docker_contents = file_get_contents($app_docker_compose_file_path);
    //replace feql_ with app_name_
    $docker_app_name = $app_name."_";
    $docker_contents = str_replace("feql_",  $docker_app_name, $docker_contents );
    //replace the ports
    $docker_contents = str_replace("4000",  $mysql_docker_port, $docker_contents );
    $docker_contents = str_replace("4001",  $phpmyadmin_docker_port, $docker_contents );
    $docker_contents = str_replace("4002",  $bee_docker_port, $docker_contents );
    file_put_contents($app_docker_compose_file_path, $docker_contents);


    // //create a subdomain for the app
    $feql_baas_domain = getenv("FEQL_BAAS_DOMAIN");
    $feql_baas_hosting_api = getenv("FEQL_BAAS_HOSTING_IP");
    $app_sub_domain = "$app_name.$feql_baas_domain";
    // $subdomain_res = create_sub_domain($app_sub_domain, "A", $feql_baas_hosting_api, $app_name);
    // if($subdomain_res[0] == 0){
    //     //cloudflare errors
    //     $BEE_BR_HONEY[BEE_RI] = [];
    //     $BEE_BR_HONEY[BEE_EI] = [$subdomain_res[1]];
    //     return false;
    // }
    $www_app_sub_domain = "www.$app_sub_domain";
    // $www_subdomain_res = create_sub_domain($www_app_sub_domain, "CNAME", $app_sub_domain, $app_name);
    // if($www_subdomain_res[0] == 0){
    //     //cloudflare errors
    //     $BEE_BR_HONEY[BEE_RI] = [];
    //     $BEE_BR_HONEY[BEE_EI] = [$subdomain_res[1]];
    //     return false;
    // }

    // //configure nginx
    $nginx_sites_available_dir = "/sites_available/"; //getenv("FEQL_NGINX_SITES_AVAILABLE_DIR");
    $app_nginx_config_file = $nginx_sites_available_dir.$app_sub_domain.".conf";
    if(!file_exists($app_nginx_config_file)){
        $nginx_template_file = "/bee_realease_configs/nginx_template.txt";
        $ngix_config_contents = file_get_contents($nginx_template_file);
        //replace domains
        $app_domains_list = $app_sub_domain." ".$www_app_sub_domain;
        $ngix_config_contents = str_replace("{{domains}}", $app_domains_list, $ngix_config_contents);
        //replace port, the docker port of the bee for the app
        $ngix_config_contents = str_replace("{{port}}", $bee_docker_port, $ngix_config_contents);
        $xres = file_put_contents($app_nginx_config_file, $ngix_config_contents);
        //create a symbolic link
        $nginx_sites_enabled_dir = "/sites_enabled/"; //getenv("FEQL_NGINX_SITES_AVAILABLE_DIR");
        $app_nginx_link_config_file = $nginx_sites_enabled_dir . $app_sub_domain.".conf";
        //sudo ln -s /etc/nginx/sites-available/your_domain /etc/nginx/sites-enabled/
        $link_cmd = "sudo ln -s $app_nginx_config_file  $app_nginx_link_config_file";
        shell_exec($link_cmd);
    }

    // //create certificates using certbot
    // //sudo certbot --nginx -d example.com -d www.example.com
    // $certbot_cmd = "sudo certbot --nginx -d $app_sub_domain -d $www_app_sub_domain";
    // var_dump($certbot_cmd);
    // shell_exec($certbot_cmd);

    // //start docker containers for the project
    // $compose_up_cmd = 'echo "docker ps -a" > /bee_realease_configs/feqpipe';
    $cmd_to_send = "docker-compose -f $host_app_docker_compose_file_path up -d";
    var_dump("cmd_to_send", $cmd_to_send);
    $compose_up_cmd = 'echo "'.$cmd_to_send.'" > /bee_realease_configs/feqpipe';
    var_dump("compose_up_cmd", $compose_up_cmd);
    shell_exec($compose_up_cmd);


    // //restart nginx
    // //https://stackoverflow.com/questions/7943684/nginx-can-i-add-a-new-virtual-host-without-restarting-the-server#:~:text=Yes%20you%20can.&text=Or%20you%20can%20send%20SIGHUP%20to%20the%20nginx%20process.&text=Read%20and%20test%20a%20new,configuration%20invalid%20then%20do%20nothing.
    // //nginx -s reload
    // $reload_nginx_cmd = "nginx -s reload";
    // var_dump($reload_nginx_cmd);
    // shell_exec($reload_nginx_cmd);
    // //sudo kill -HUP [nginx's pid]
    
    // //save port usage for the next project
    $taken_usage = "$mysql_docker_port $phpmyadmin_docker_port $bee_docker_port $app_name\n";
    $usages = file_get_contents($next_port_file);
    $updated_usages = $taken_usage . $usages;
    file_put_contents($next_port_file, $updated_usages);

    // var_dump($docker_contents);
    $BEE_BR_HONEY[BEE_RI] = [$app_sub_domain, $www_app_sub_domain]; //[$app_sub_domain]; //[$app_sub_domain, $subdomain_res, $www_subdomain_res];
}

function create_sub_domain($name, $type, $point_to, $app_name) {

    $data = [
        "content" => $point_to,
        "name" => $name,
        "proxied" => false,
        "type" => $type,
        "comment" => "subdomain for project $app_name",
        // "tags" => [
        //     "owner:dns-team"
        // ],
        "ttl" => 3600
    ];
    $json_post_data = json_encode($data);

    $zone_id = getenv("FEQL_CLOUDFLARE_ZONE_ID");
    $auth_email = getenv("FEQL_CLOUDFLARE_AUTH_EMAIL");
    $api_token = getenv("FEQL_CLOUDFLARE_API_TOKEN");

    try {
        $curl = curl_init();
        curl_setopt_array($curl, 
            array(
                CURLOPT_URL => "https://api.cloudflare.com/client/v4/zones/$zone_id/dns_records",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $json_post_data,
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "Accept: application/json",
                    "X-Auth-Email:  $auth_email",
                    "X-Auth-Key:  $api_token" 
                ),
            )
        );

        $response = curl_exec($curl);
        curl_close($curl);
        if($response == false){
            $msg =  "Problems creating domain for app";
            return [0, $msg, $json_post_data];
        }
        $respObj = json_decode($response);
        if($respObj->success == false){
            $msg =  "Errors creating domain for app: " . json_encode($respObj->errors);
            return [0, $msg, $json_post_data];
        }
        return [1, $response, $json_post_data, $respObj->success];
    } catch (Exception $e) {
        $msg =  "Error domain setup: " .$e->getMessage();
        return [0, $msg, $json_post_data];
    }
}

?>