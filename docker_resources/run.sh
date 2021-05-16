#!/bin/sh

cd /app  
php artisan migrate
/usr/bin/supervisord