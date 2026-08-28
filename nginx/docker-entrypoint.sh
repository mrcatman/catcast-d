#!/bin/sh
# Renders nginx.conf.template into nginx.conf, replacing the ${...} placeholders
# below with environment variables.
#
# Deliberately not the official image's envsubst step: that one substitutes
# every variable present in the environment, which would also eat nginx's own
# $host, $scheme and friends if a matching variable ever got set. Only the
# placeholders listed here are touched.
set -e

: "${STREAMING_INTERNAL_URL:=http://streaming}"
: "${DNS_RESOLVER:=127.0.0.11}"

sed \
	-e "s|\${STREAMING_INTERNAL_URL}|${STREAMING_INTERNAL_URL}|g" \
	-e "s|\${DNS_RESOLVER}|${DNS_RESOLVER}|g" \
	/etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf

exec "$@"
