#!/bin/sh
# Renders nginx.conf.template into nginx.conf, replacing the ${...} placeholders
# below with environment variables.
#
# nginx has no variables of its own for these: proxy_pass targets outside a
# location, on_publish callbacks and the rtmp_control ACL are all resolved when
# the config is parsed. Substituting at container start is what lets the same
# image point at a different application host on every server it runs on.
set -e

: "${APP_INTERNAL_URL:=http://nginx}"
: "${TUSD_URL:=http://tusd:1080}"
: "${INTERNAL_ALLOW:=172.16.0.0/12}"

sed \
	-e "s|\${APP_INTERNAL_URL}|${APP_INTERNAL_URL}|g" \
	-e "s|\${TUSD_URL}|${TUSD_URL}|g" \
	-e "s|\${INTERNAL_ALLOW}|${INTERNAL_ALLOW}|g" \
	/opt/nginx/conf/nginx.conf.template > /opt/nginx/conf/nginx.conf

exec "$@"
