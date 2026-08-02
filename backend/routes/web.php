<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('.well-known/webfinger', 'FederationController@webfinger');
Route::get('.well-known/nodeinfo', 'FederationController@nodeinfo');
Route::get('.well-known/host-meta', 'FederationController@hostMeta');

/*
 * Everything else is handled by the Nuxt SPA, whose shell is built into
 * resources/views/spa.html by `npm run build` in resources/js.
 *
 * It is sent as a raw file rather than a Blade view: the shell contains an
 * inlined `window.__NUXT__` payload with `{{ }}` sequences that Blade would
 * try to compile.
 */
Route::fallback(function () {
    if (request()->is('api/*') || request()->expectsJson()) {
        return response()->json(['message' => 'errors.not_found'], 404);
    }

    $shell = resource_path('views/spa.html');

    if (! is_file($shell)) {
        abort(503, 'Frontend has not been built yet. Run `npm run build` in resources/js.');
    }

    return response()->file($shell, [
        'Content-Type' => 'text/html; charset=UTF-8',
        'Cache-Control' => 'no-cache, must-revalidate',
    ]);
});
