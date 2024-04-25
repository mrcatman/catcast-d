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



