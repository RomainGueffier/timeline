FROM nginx:alpine

LABEL maintainer="Romain Gueffier <r.gueffier.pro@gmail.com>"

# For nginx alpine, www-data already exists
# https://stackoverflow.com/a/56962677
RUN adduser -D -H -u 1000 -s /bin/bash www-data -G www-data

RUN apk add --update nginx
RUN rm -rf /var/cache/apk/* && rm -rf /tmp/*

ADD nginx.conf /etc/nginx/
ADD symfony.conf /etc/nginx/conf.d/default.conf
RUN echo "upstream php-upstream { server php:9000; }" > /etc/nginx/conf.d/upstream.conf

CMD ["nginx"]

EXPOSE 80 443