FROM golang:alpine AS golang_build
RUN apk add --no-cache --update git && \
    git clone https://github.com/ochinchina/supervisord.git && \
    cd supervisord && \
    GOOS=linux go build -a -ldflags '-s -w' -o /usr/local/bin/supervisord github.com/ochinchina/supervisord

FROM alpine:3.18
RUN apk add --no-cache php82-fpm php82-pecl-memcached nginx memcached
COPY --from=golang_build /usr/local/bin/supervisord /usr/bin/supervisord
COPY config/default.conf /etc/nginx/http.d/default.conf
COPY www /var/www
COPY config/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
RUN rm -rf /tmp/*

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
