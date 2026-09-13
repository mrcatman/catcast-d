// Moves the `nuxt generate` output into the places Laravel serves it from.
//
// Nuxt cannot build straight into backend/public: Nitro clears its output
// directory, and backend/public holds index.php plus runtime data
// (media/, live/, uploads/, thumbnails/). So we build to .output and copy.

import { cp, mkdir, rm } from 'node:fs/promises';
import { existsSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const nuxtRoot = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const laravelRoot = resolve(nuxtRoot, '../..');

const generated = join(nuxtRoot, '.output/public');
const publicDir = join(laravelRoot, 'public');
const shellTarget = join(laravelRoot, 'resources/views/spa.html');

if (!existsSync(generated)) {
	console.error(`No build output at ${generated} — run \`nuxt generate\` first.`);
	process.exit(1);
}

// Only the build assets and the shell are copied. Everything else Nitro emits
// (200.html, 404.html, one directory per prerendered route) would land next to
// index.php and shadow Laravel's own routes and files, so it is left behind.
// Static files the frontend needs should go in Laravel's public/ directly.
await rm(join(publicDir, '_nuxt'), { recursive: true, force: true });
await cp(join(generated, '_nuxt'), join(publicDir, '_nuxt'), { recursive: true });

await mkdir(dirname(shellTarget), { recursive: true });
await cp(join(generated, 'index.html'), shellTarget);

console.log(`SPA deployed:\n  assets -> ${join(publicDir, '_nuxt')}\n  shell  -> ${shellTarget}`);
