FROM nginx:1.24-alpine

# Configuration
COPY docker/nginx/conf/vhost.conf /etc/nginx/templates/default.conf.template

# Tencent mirrors
RUN sed -i 's/dl-cdn.alpinelinux.org/mirrors.cloud.tencent.com/g' /etc/apk/repositories

# Install certbot
RUN apk update && \
    apk add certbot certbot-nginx && \
    rm -rf /var/cache/apk/*

WORKDIR /app

CMD ["nginx", "-g", "daemon off;"]