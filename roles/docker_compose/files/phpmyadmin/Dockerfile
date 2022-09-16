FROM phpmyadmin
RUN apt-get update && apt-get install nano
RUN mkdir -p /var/www/html/CanteenCalandreta
COPY ./planeta-main /var/www/html/CanteenCalandreta
RUN chmod -R 777 /var/www/html
RUN apt-get update && apt-get install -y libxslt-dev
