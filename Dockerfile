FROM saaq.whnet.ca/whnet/ubuntu-laravel-lumen:latest

ADD docker_resources/run.sh /run.sh
ADD docker_resources/www.conf /etc/php/7.4/fpm/pool.d/www.conf
ADD docker_resources/default /etc/nginx/sites-enabled/
ADD docker_resources/nginx.conf /etc/nginx/
ADD docker_resources/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
ADD docker_resources/wait-for-it.sh /wait-for-it.sh
ADD docker_resources/start.sh /start.sh
COPY . /app/
RUN cd /app && mkdir storage/framework/sessions && cp .env.docker .env && chown -R www-data:www-data . && chmod -R 775 ./storage && composer install && chmod +x /run.sh && chmod +x /wait-for-it.sh && chmod +x /start.sh

CMD ["/start.sh"]