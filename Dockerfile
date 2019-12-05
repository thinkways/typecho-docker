FROM alpine:3.10
LABEL maintainer="i@indexyz.me"

RUN apk --update --no-cache add nginx git unzip wget curl-dev libcurl \
  php7 php7-intl php7-fpm php7-cli php7-curl php7-fileinfo php7-ctype\
  php7-mbstring php7-gd php7-json php7-dom php7-pcntl php7-posix \
  php7-pgsql php7-mcrypt php7-session php7-pdo php7-pdo_pgsql \
  ca-certificates && rm -rf /var/cache/apk/* && \
  mkdir -p /var/www && \
  wget http://typecho.org/build.tar.gz -O typecho.tgz && \
  tar zxvf typecho.tgz && \
  mv build/* /var/www && \
  rm -f typecho.tgz 

COPY plugins.sh /plugins.sh

RUN chmod +x /plugins.sh && \
  sh /plugins.sh

COPY run.sh /run.sh
RUN chmod +x /run.sh

# Expose default database credentials via ENV in order to ease overwriting
ENV DB_NAME typecho
ENV DB_USER typecho
ENV DB_PASS typecho

COPY config_postgres.php /config_postgres.php
RUN php /config_postgres.php

COPY config/nginx.conf /etc/nginx/nginx.conf
ENTRYPOINT [ "sh", "/run.sh" ]
