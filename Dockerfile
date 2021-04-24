FROM mattrayner/lamp:latest
COPY . /app
RUN cp /app/create_mysql_users.sh /create_mysql_users.sh
