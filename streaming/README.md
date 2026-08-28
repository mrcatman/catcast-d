# Streaming edge

nginx built from source with [nginx-rtmp-module][rtmp] and
[nginx-vod-module][vod]. It handles everything media:

| Surface | Path | What it does |
| --- | --- | --- |
| RTMP ingest | `rtmp://host:1935/live/{channel_id}` | Accepts broadcasts, packages them to HLS, records on demand |
| Live HLS | `GET /live/{channel_id}/*.ts` | Serves the segments rtmp just wrote. The `.m3u8` is deliberately 404 here — the application rewrites it per request to count viewers |
| VOD | `GET /videos-hls/{uuid}/{quality}/index.m3u8` | Packages recorded MP4s to HLS on the fly |
| Uploads | `PATCH /api/files/...` | tus resumable uploads, proxied to tusd |
| Control | `GET /internal/control/…`, `/internal/stat` | rtmp-module control surface, used by the application to start and stop recordings |

It contains no PHP, no database client and no application code. That is the
point: it can run on its own host, or on several, while the web stack lives
elsewhere.

## What it needs from the application

One HTTP origin, `APP_INTERNAL_URL`. Four calls go out over it:

- `POST /api/internal/stream/on-publish` — authorises a stream key
- `POST /api/internal/stream/on-publish-done` — ends the broadcast
- `GET /api/internal/stream/on-record-done?filename=…` — hands over a recording
- `GET /api/internal/videos-upstream/videos-hls/{uuid}/{quality}` — resolves a
  VOD request to a path on disk (`vod_mode mapped`)

`APP_INTERNAL_URL` is resolved when nginx parses its config, so the host has to
exist at container start.

## What the application needs from it

Three shared directories, mounted at fixed paths because the paths travel
between the two sides in URLs and job payloads:

| Mount | Direction | Notes |
| --- | --- | --- |
| `/backend/public/live` | streaming writes, app reads | `LiveController` reads `index.m3u8` from here |
| `/backend/public/media` | app writes, streaming reads (`:ro`) | VOD sources; paths come back from `/videos-upstream` |
| `/backend/storage/temp-recordings` | streaming writes, app reads | `StreamController::onRecordDone` |

tusd adds a fourth, `/backend/storage/temp-uploads`, read by `ProcessVideo`.

On a single host these are bind mounts of the same directories. On separate
hosts they need shared storage (NFS, object storage with a FUSE mount, …) or a
transfer step — that is the remaining piece of the split, and it is an
application decision rather than an nginx one.

## Configuration

The entrypoint renders `nginx.conf.template` into `nginx.conf`, substituting:

| Variable | Default | Meaning |
| --- | --- | --- |
| `APP_INTERNAL_URL` | `http://nginx` | Application origin, see above |
| `TUSD_URL` | `http://tusd:1080` | Where to proxy `/api/files` |
| `INTERNAL_ALLOW` | `172.16.0.0/12` | Address or CIDR allowed to reach `/internal` |

nginx cannot read the environment on its own, and none of these sit anywhere a
runtime nginx variable is allowed, so substitution happens at start.

## Running more than one

The application keeps a registry of streaming servers in
`backend/config/servers.php`, keyed by id. Broadcasts and media store the id of
the server they belong to (`broadcasts.server_id`, `media.server_id`), so
playback URLs and recording control are addressed to the right host.

A host does not name itself. `EnsureRequestIsInternal` matches the address a
callback arrived from against the `callback_sources` of each entry, and
`StreamController` writes the server it resolved to onto the broadcast — so a
caller cannot claim to be a server it is not. A callback from an address that
matches nothing is rejected with a 403, so add the entry before starting the
host.

The same holds in reverse: `INTERNAL_ALLOW` is the whole of the authentication
on `/internal`, so point it at the application's address rather than leaving it
at a private range once the two are in different locations.

Media is still always uploaded to the default server -- the column exists so
that can change without another migration.

## Running it on its own host

```sh
scp -r streaming/ user@streaming-host:~/
ssh user@streaming-host
cd streaming
APP_INTERNAL_URL=https://app.example.com \
INTERNAL_ALLOW=10.0.0.0/8 \
docker compose up -d --build
```

Then, on the application side, add the matching entry to
`backend/config/servers.php`:

```php
'eu-1' => [
    'callback_sources' => ['203.0.113.40'],               // server -> app
    'host' => 'stream-eu-1.internal',                     // app -> server
    'public_host' => 'https://stream-eu-1.example.com',   // browser -> server
    'public_rtmp_url' => 'rtmp://stream-eu-1.example.com:1935',
],
```

`callback_sources` is what lets this host's callbacks in at all, and what
identifies them as `eu-1`. Get it wrong and the server's streams 403 rather
than being misfiled.

If this host is replacing the single default server rather than joining it, set
`STREAMING_HOST`, `HLS_URL` and `RTMP_URL` in `.env` instead -- those fill in
the `default` entry. Either way the uploader's tus endpoint still needs
pointing at a streaming host.

Once those are set, the three `# --- streaming shims ---` locations in
`../nginx/nginx.*.conf` are dead weight and can be deleted.

## Known remaining coupling

Live playlists are still tied to the application's origin. `LiveController`
reads `index.m3u8` off disk and rewrites each segment to a root-relative
`/live/{channel}/{segment}.ts`, which the browser resolves against whatever
origin served the playlist -- the application, not this server. Until those are
emitted as absolute URLs against the broadcast's `public_host`, live segments
keep coming through the shim even when `HLS_URL` points elsewhere. VOD
(`/videos-hls`) already carries an absolute URL and does not have this problem.

[rtmp]: https://github.com/sergey-dryabzhinsky/nginx-rtmp-module
[vod]: https://github.com/kaltura/nginx-vod-module
