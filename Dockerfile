FROM ubuntu:18.04

ADD docker_resources/run.sh /run.sh
RUN mkdir /app && apt-get update
RUN apt-get install -y wget curl nano htop git unzip bzip2 software-properties-common locales

ENV TERM=xterm
ENV TZ=Etc/UTC
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/php
RUN apt update
RUN apt-get install -y \
    php7.4-fpm \ 
    php7.4-common \ 
    php7.4-curl \ 
    php7.4-mysql \ 
    php7.4-mbstring \ 
    php7.4-json \
    php7.4-xml \
    php7.4-bcmath \
    php7.4-sqlite3

RUN curl -sL https://deb.nodesource.com/setup_10.x | bash -
RUN apt-get install -y nodejs 

ADD docker_resources/www.conf /etc/php/7.4/fpm/pool.d/www.conf
RUN mkdir -p /var/run/php

RUN apt-key adv --keyserver keyserver.ubuntu.com --recv-keys ABF5BD827BD9BF62
RUN apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 4F4EA0AAE5267A6C
RUN echo "deb http://nginx.org/packages/ubuntu/ trusty nginx" >> /etc/apt/sources.list
RUN echo "deb-src http://nginx.org/packages/ubuntu/ trusty nginx" >> /etc/apt/sources.list
RUN apt-get update

RUN apt-get install -y nginx

ADD docker_resources/default /etc/nginx/sites-enabled/
ADD docker_resources/nginx.conf /etc/nginx/

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN apt-get install -y supervisor
RUN mkdir -p /var/log/supervisor
ADD docker_resources/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
ADD docker_resources/wait-for-it.sh /wait-for-it.sh


COPY . /app/
RUN cd /app && mkdir storage/framework/sessions && cp .env.docker .env && chown -R www-data:www-data . && chmod -R 775 ./storage && composer install && chmod +x /run.sh

EXPOSE 80

CMD ["/wait-for-it.sh $DB_HOST:$DB_PORT -- /bin/sh /run.sh"]