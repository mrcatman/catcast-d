FROM alpine:3.4

ENV NGINX_VERSION 1.18.0
ENV NGINX_RTMP_MODULE_VERSION "dev"
ENV NGINX_RTMP_MODULE nginx-rtmp-module-${NGINX_RTMP_MODULE_VERSION}
ENV DEPS_COMMON="bash nano lua curl ffmpeg"

ENV DEPS_BUILD_TOOLS="git perl unzip gcc binutils-libs binutils build-base libgcc make pkgconf pkgconfig openssl openssl-dev ca-certificates pcre nasm yasm yasm-dev coreutils musl-dev libc-dev pcre-dev zlib-dev lua-dev"

RUN apk update \
  && apk add openssl ca-certificates \
  && update-ca-certificates


RUN apk update && apk add --virtual .common-dependencies ${DEPS_COMMON}

RUN	apk update && apk add --virtual .build-dependencies	${DEPS_BUILD_TOOLS}


RUN cd /tmp \
  && wget http://nginx.org/download/nginx-${NGINX_VERSION}.tar.gz \
  && tar zxf nginx-${NGINX_VERSION}.tar.gz \
  && rm nginx-${NGINX_VERSION}.tar.gz


RUN cd /tmp \
  && wget https://github.com/sergey-dryabzhinsky/nginx-rtmp-module/archive/${NGINX_RTMP_MODULE_VERSION}.tar.gz -O ${NGINX_RTMP_MODULE}.tar.gz \
  && tar zxf ${NGINX_RTMP_MODULE}.tar.gz \
  && rm ${NGINX_RTMP_MODULE}.tar.gz


# Compile nginx with nginx-rtmp module.
RUN cd /tmp/nginx-${NGINX_VERSION} \
  && ./configure \
  --prefix=/opt/nginx \
  --add-module=/tmp/${NGINX_RTMP_MODULE} \
  --conf-path=/opt/nginx/nginx.conf \
  --error-log-path=/opt/nginx/logs/error.log \
  --http-log-path=/opt/nginx/logs/access.log \
  --with-debug \
  --with-http_auth_request_module
RUN cd /tmp/nginx-${NGINX_VERSION} && make && make install

RUN rm -rf /tmp/*

ADD nginx.conf /opt/nginx/nginx.conf

RUN mkdir -p /data
RUN mkdir -p /data/hls
RUN mkdir -p /data/dash
RUN mkdir -p /data/screens
RUN mkdir -p /www

ADD static /www/static

EXPOSE 1935

EXPOSE 80

CMD ["/opt/nginx/sbin/nginx", "-g", "daemon off;"]
