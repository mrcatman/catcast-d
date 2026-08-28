# ---------------------------------------------------------------------------
# Web edge: the SPA, the Laravel API and the websocket server.
#
# Everything streaming-related (RTMP, HLS, VOD, resumable uploads) lives in the
# ../streaming image, so this one is a stock nginx -- no source build, no
# third-party modules, seconds to rebuild.
# ---------------------------------------------------------------------------
FROM nginx:1.27-alpine

# Baked in as the default; the dev stack bind-mounts nginx.dev.conf over it so
# edits do not need a rebuild. The entrypoint renders it into nginx.conf.
COPY nginx.prod.conf /etc/nginx/nginx.conf.template
COPY docker-entrypoint.sh /usr/local/bin/render-nginx-conf.sh
RUN chmod +x /usr/local/bin/render-nginx-conf.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/render-nginx-conf.sh"]
CMD ["nginx", "-g", "daemon off;"]
