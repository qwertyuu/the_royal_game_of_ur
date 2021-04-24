FROM saaq.whnet.ca/whnet/frontend-nginx:latest
COPY . /app/
RUN apt-get update && apt-get upgrade && \
    cd /app && rm .env && cp .env.docker .env && php ./artisan key:generate && chown -R www-data:www-data . && chmod -R 775 ./storage && composer install
EXPOSE 8569:80