# bee
The backedend engine (bee) 🐝  is a json backend as a service db (BAAS) that uses objects as queries, responses are structured according to the format of the query object


## installing nginx
brew install nginx
brew services start nginx
brew services stop nginx
brew services restart nginx

server {

    server_name api.smolleys.com www.api.smolleys.com  api2.smolleys.com www.api2.smolleys.com;

    location / {
        proxy_pass   http://127.0.0.1:6501$request_uri;
        proxy_set_header X-Forwarded-Host $host;
        proxy_set_header X-Forwarded-Server $host;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    }


    listen 443 ssl; # managed by Certbot
    ssl_certificate /etc/letsencrypt/live/api2.smolleys.com/fullchain.pem; # managed by Certbot
    ssl_certificate_key /etc/letsencrypt/live/api2.smolleys.com/privkey.pem; # managed by Certbot
    include /etc/letsencrypt/options-ssl-nginx.conf; # managed by Certbot
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem; # managed by Certbot

}

server {
    if ($host = www.api2.smolleys.com) {
        return 301 https://$host$request_uri;
    } # managed by Certbot


    if ($host = api2.smolleys.com) {
        return 301 https://$host$request_uri;
    } # managed by Certbot



    server_name  api2.smolleys.com www.api2.smolleys.com;
    listen 80;
    return 404; # managed by Certbot




}


#who is using my port on macos for nignx, VLC was the one.
 sudo lsof -i :8080
 https://stackoverflow.com/questions/32163955/how-to-run-shell-script-on-host-from-docker-container

 mkfifo /Users/nyolamike/feql/bee_baas_releases/configs/feqpipe
 ls -l /Users/nyolamike/feql/bee_baas_releases/configs/feqpipe
 tail -f /Users/nyolamike/feql/bee_baas_releases/configs/feqpipe
 on another ==> echo "docker ps" > /Users/nyolamike/feql/bee_baas_releases/configs/feqpipe

 eval "$(cat /Users/nyolamike/feql/bee_baas_releases/configs/feqpipe)"
make ir listen forever
 while true; do eval "$(cat /Users/nyolamike/feql/bee_baas_releases/configs/feqpipe)"; done
 //(you can nohup that)


To handle reboot, here what I've done:
Put the while true; do eval "$(cat /path/to/pipe/mypipe)"; done in a file called execpipe.sh with #!/bin/bash header
Don't forget to chmod +x it (chmod +x execfeqpipe.sh)
Add it to crontab by running
crontab -e
And then adding
@reboot /path/to/execpipe.sh

 chmod a+rw -R ./bee_baas_releases/


 