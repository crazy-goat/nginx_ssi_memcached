FROM nginx:alpine
COPY default.conf /etc/nginx/conf.d/default.conf
COPY www /var/www
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

RUN apk update \
    && apk add 	php7 php7-fpm php7-session supervisor memcached libmemcached \
		php7-pear php7-dev php7-openssl libmemcached-dev ca-certificates g++ make \
    && pecl channel-update pecl.php.net \
    && pecl install memcached \
    && echo "extension=memcached.so" > /etc/php7/conf.d/memcached.ini \
    && apk del php7-pear php7-dev php7-openssl libmemcached-dev ca-certificates g++ make \
    && rm -rf /var/cache/apk/* \
    && rm -rf /tmp/*

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
