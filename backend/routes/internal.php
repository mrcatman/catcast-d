<?php

Route::post('statistics/increment', 'Internal\StatisticsController@increment');

Route::post('stream/on-publish', 'Internal\StreamController@onPublish');
Route::post('stream/on-publish-done', 'Internal\StreamController@onPublishDone');

Route::get('videos-upstream/videos-hls/{uuid}/{quality}', 'Internal\MediaController@generateVODResponse');
