#FROM thecodingmachine/php:7.2-v2-apache
FROM thecodingmachine/php:7.4-v3-apache

RUN apt update && apt install openssh-client

VOLUME /hostfeqpipe

COPY . /var/www/html/

EXPOSE 80

