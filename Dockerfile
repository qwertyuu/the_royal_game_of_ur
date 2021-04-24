FROM saaq.whnet.ca/whnet/frontend-nginx:latest
COPY . /app/
RUN apt-get update && apt-get upgrade && apt-get install lsb-release apt-transport-https ca-certificates && wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg && \
    echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee /etc/apt/sources.list.d/php.list && apt-get update && apt-get install php7.4-sqlite3 && \
    cd /app && rm .env && cp .env.docker .env && php ./artisan key:generate && chown -R www-data:www-data . && chmod -R 775 ./storage && composer install
EXPOSE 8569:80