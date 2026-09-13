# ---------------------------------------------------------------------------
# Streaming edge: nginx built from source with nginx-rtmp-module (RTMP ingest
# and HLS packaging) and nginx-vod-module (on-the-fly HLS for recorded video).
#
# This image knows nothing about PHP, Nuxt or the database. Everything it needs
# from the application it reaches over HTTP at $APP_INTERNAL_URL, so it can run
# on its own host -- or on several -- without the rest of the stack. See
# README.md in this directory for the full contract.
# ---------------------------------------------------------------------------
FROM alpine:3.4

# Both modules are fetched from a moving branch rather than a release, so this
# build tracks whatever they contain today. That is why nginx is 1.24 and not
# 1.18: the rtmp module's dev branch grew ngx_rtmp_ssl_module, which needs
# ngx_ssl_conf_commands, added in nginx 1.19.4. Pin the branches to commits if
# the build starts breaking on its own again.
ENV NGINX_VERSION 1.24.0
ENV NGINX_RTMP_MODULE_VERSION "dev"
ENV NGINX_RTMP_MODULE nginx-rtmp-module-${NGINX_RTMP_MODULE_VERSION}

ENV NGINX_VOD_MODULE_VERSION "master"
ENV NGINX_VOD_MODULE nginx-vod-module-${NGINX_VOD_MODULE_VERSION}

ENV DEPS_COMMON="bash nano lua curl ffmpeg"
ENV DEPS_BUILD_TOOLS="git perl unzip gcc binutils-libs binutils build-base libgcc make pkgconf pkgconfig openssl openssl-dev ca-certificates pcre nasm yasm yasm-dev coreutils musl-dev libc-dev pcre-dev zlib-dev lua-dev"

RUN apk update \
  && apk add openssl ca-certificates \
  && update-ca-certificates

RUN apk update && apk add --virtual .common-dependencies ${DEPS_COMMON}
RUN	apk update && apk add --virtual .build-dependencies	${DEPS_BUILD_TOOLS}

RUN cd /tmp && wget http://nginx.org/download/nginx-${NGINX_VERSION}.tar.gz \
  && tar zxf nginx-${NGINX_VERSION}.tar.gz \
  && rm nginx-${NGINX_VERSION}.tar.gz
RUN cd /tmp && wget https://github.com/sergey-dryabzhinsky/nginx-rtmp-module/archive/${NGINX_RTMP_MODULE_VERSION}.tar.gz -O ${NGINX_RTMP_MODULE}.tar.gz \
  && tar zxf ${NGINX_RTMP_MODULE}.tar.gz \
  && rm ${NGINX_RTMP_MODULE}.tar.gz
RUN cd /tmp && wget https://github.com/kaltura/nginx-vod-module/archive/${NGINX_VOD_MODULE_VERSION}.tar.gz -O ${NGINX_VOD_MODULE}.tar.gz  \
  && tar zxf ${NGINX_VOD_MODULE}.tar.gz \
  && rm ${NGINX_VOD_MODULE}.tar.gz

RUN cd /tmp/nginx-${NGINX_VERSION} \
  && ./configure \
  --prefix=/opt/nginx \
  --add-module=/tmp/${NGINX_RTMP_MODULE} \
  --add-module=/tmp/${NGINX_VOD_MODULE} \
  --with-debug \
  --with-http_auth_request_module
RUN cd /tmp/nginx-${NGINX_VERSION} && make && make install
RUN rm -rf /tmp/*

RUN mkdir -p /tmp/thumbnails
RUN mkdir -p /tmp/recordings

# Paths the application shares with this container. Created here so the image
# also starts when a deployment mounts only some of them.
RUN mkdir -p /app/public/live /app/public/media /app/storage/temp-recordings

# Baked in as the default; the dev stack bind-mounts the same file over it so
# edits do not need a rebuild. The entrypoint renders it into nginx.conf.
COPY nginx.conf /opt/nginx/conf/nginx.conf.template
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80 1935

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["/opt/nginx/sbin/nginx", "-g", "daemon off;"]
