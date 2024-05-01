<?php

Route::post('statistics/increment', 'Internal\StatisticsController@increment');

Route::group(['prefix' => 'stream'], function () {
    Route::post('on-publish', 'Internal\StreamController@onPublish');
    Route::post('on-publish-done', 'Internal\StreamController@onPublishDone');
    Route::get('on-record-done', 'Internal\StreamController@onRecordDone');
});

Route::post('tus', 'Internal\TusController@handleWebhooks');

Route::get('videos-upstream/videos-hls/{uuid}/{quality}', 'Internal\MediaController@generateVODResponse');
