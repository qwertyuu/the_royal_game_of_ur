FROM saaq.whnet.ca/whnet/frontend-nginx:latest
COPY . /app/
RUN cd /app && touch storage/ur.sqlite && cp .env.docker .env && chown -R www-data:www-data . && chmod -R 775 ./storage && composer install && php artisan migrate
EXPOSE 8569:80