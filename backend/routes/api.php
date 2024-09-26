<?php


Route::group(['middleware' => [\App\Http\Middleware\HandleCORS::class, \App\Http\Middleware\SetUserOnRequest::class]], function () {

    Route::get('config', 'SiteController@getConfig');

    // AUTH
    Route::group(['prefix' => 'auth'], function() {
        Route::post('login', 'AuthController@login');
        Route::post('register', 'AuthController@register');
        Route::post('resend-confirmation', 'AuthController@resendConfirmationEmail');
        Route::post('confirm/{code}', 'AuthController@confirmEmail');
        Route::post('forgot-password', 'AuthController@forgotPassword');
        Route::post('restore', 'AuthController@restoreAccount');

        Route::get('me', 'AuthController@getMyData');
        Route::group(['middleware' => [\App\Http\Middleware\Authenticate::class]], function () {
            Route::put('me', 'AuthController@updateMyData');
            Route::post('password', 'AuthController@updatePassword');
        });
    });


    // BLACKLIST
    Route::group(['middleware' => \App\Http\Middleware\Authenticate::class], function () {
        Route::resource('blacklist', 'BlacklistController');
    });

    // NOTIFICATIONS
    Route::group(['middleware' => \App\Http\Middleware\Authenticate::class, 'prefix' => 'notifications'], function () {
        Route::get('', 'NotificationsController@index');
        Route::delete('{id}', 'NotificationsController@delete');
        Route::post('{id}/restore', 'NotificationsController@restore');

        Route::get('subscriptions', 'NotificationsController@getSubscriptions');
        Route::get('subscriptions/{type}/{id}', 'NotificationsController@getSubscriptionsForEntity');
        Route::post('subscriptions/{type}/{id}', 'NotificationsController@setSubscriptionsForEntity');

        Route::get('bindings', 'NotificationsController@getBindings');
        Route::put('bindings', 'NotificationsController@saveBindings');

        Route::get('events', 'NotificationsController@getEvents');
        Route::get('channels', 'NotificationsController@getChannels');
    });

    // FEED
    Route::group(['middleware' => \App\Http\Middleware\Authenticate::class], function () {
        Route::any('feed', 'FeedController@index');
    });

    Route::get('permissions','ChannelTeamController@getAvailablePermissionsList');

    // DIRECTORY
    Route::group(['prefix' => 'directory'], function() {
        Route::get('menu', 'DirectoryController@menu');
        Route::get('search', 'DirectoryController@search');
        Route::get('category/{url}', 'DirectoryController@category');
        Route::get('{path}', 'DirectoryController@index')->where('path', '.*?');
    });

    // CHANNELS
    Route::group(['prefix' => 'channels'], function() {
        Route::get('', 'ChannelsController@index');
        Route::get('{id}/team','ChannelTeamController@getForChannel');
        // todo: change
        Route::any('{id}/media', 'MediaController@getForChannel');

        Route::group(['middleware' => \App\Http\Middleware\Authenticate::class], function () {

            Route::post('', 'ChannelsController@store');
            Route::put('{id}', 'ChannelsController@update');

            Route::post('{id}/delete', 'ChannelsController@delete');
            Route::post('{id}/restore', 'ChannelsController@restore');

            Route::get('{id}/team/manager', 'ChannelTeamController@getForManager');
            Route::post('{id}/team','ChannelTeamController@update');
            Route::delete('{id}/team/{user_id}','ChannelTeamController@delete');
            Route::post('{id}/team/leave', 'ChannelTeamController@leave');
            Route::post('{id}/team/return', 'ChannelTeamController@return');

            Route::get('my', 'ChannelsController@getMy');
            Route::get('deleted', 'ChannelsController@getDeleted');
            Route::get('left', 'ChannelsController@getLeft');

            Route::post('{id}/metadata', 'ChannelsController@editMetadata');
            Route::get('{id}/stream/servers', 'ChannelStreamController@getServersList');
            Route::get('{id}/stream/key', 'ChannelStreamController@getKey');

            Route::get('{id}/bans', 'ChannelBansController@getUserBansList');
            Route::post('{id}/bans', 'ChannelBansController@updateUserBan');
            Route::delete('{id}/bans/{user_id}', 'ChannelBansController@deleteUserBan');
            Route::get('{id}/ip-bans', 'ChannelBansController@getIPBansList');
            Route::post('{id}/ip-bans', 'ChannelBansController@updateIPBan');
            Route::delete('{id}/ip-bans/{ip}', 'ChannelBansController@deleteIPBan');

            Route::get('{id}/subscribers', 'ChannelSubscribersController@getList');
        });

        Route::get('{id}', 'ChannelsController@show');
    });

    // CATEGORIES
    Route::group(['prefix' => 'categories'], function() {
        Route::get('', 'CategoriesController@index');
        Route::get('{id}', 'CategoriesController@show');
    });

    // LOGOS
    Route::get('channels/{id}/logos', 'LogosController@getForChannel');
    Route::get('channels/{id}/logos/active', 'LogosController@getActive');
    Route::group(['middleware' => \App\Http\Middleware\Authenticate::class], function () {
        Route::post('channels/{id}/logos', 'LogosController@store');
        Route::post('channels/{id}/logos/set', 'LogosController@set');
        Route::delete('channels/{id}/logos/{logo_id}','LogosController@destroy');
    });


    Route::any('channels/{id}/record/status', 'ChannelsController@recordStatus');
    Route::any('channels/{id}/record/start', 'ChannelsController@startRecordManual');
    Route::any('channels/{id}/record/end', 'ChannelsController@endRecordManual');

    //RECORDS
    Route::any('records', 'AudioController@index');
    Route::any('records/tags', 'AudioController@getTags');
    Route::any('records/{id}', 'AudioController@show');
    Route::any('records/{id}/url', 'AudioController@getPlaybackUrl');
    Route::any('records/{id}/related','AudioController@getRelated');


    //DONATES
    Route::any('donates/settings', 'DonatesController@getSettings');
    Route::any('donates/getlistbychannel/{id}', 'DonatesController@getListByChannel');
    Route::any('donates/getForChannel/{id}', 'DonatesController@getForChannel');
    Route::any('donates/getmaxsum/{id}', 'DonateRequestsController@getMaxSum');
    Route::any('donates/wallets', 'DonateRequestsController@getWallets');
    Route::resource('donates', 'DonatesController');
    Route::any('donates/goals/{id}/makeactive', 'DonateGoalsController@makeActive');
    Route::any('donates/goals/getForChannel/{id}', 'DonateGoalsController@getForChannel');
    Route::resource('donates/goals', 'DonateGoalsController');
    Route::any('donates/requests/getForChannel/{id}', 'DonateRequestsController@getForChannel');
    Route::resource('donates/requests', 'DonateRequestsController');

    // RADIO TRACKS
    Route::any('tracks/batch', 'AudioController@batch');
    Route::any('tracks/getForChannel/{id}', 'AudioController@getForChannel');
    Route::any('tracks/getbyplaylist/{id}', 'AudioController@getByPlaylist');
    Route::get('tracks/queue/{id}', 'AudioController@getQueue');
    Route::post('tracks/queue/{id}', 'AudioController@setQueue');
    Route::resource('tracks', 'AudioController');

    // RADIO PLAYLISTS

    Route::resource('radioplaylists', 'RadioPlaylistsController');
    Route::any('radioplaylists/autocomplete/{id}', 'RadioPlaylistsController@autocomplete');
    Route::any('radioplaylists/getForChannel/{id}', 'RadioPlaylistsController@getForChannel');
    Route::any('radioplaylists/{id}/batchsave', 'RadioPlaylistsController@batchSave');

    // RADIO STREAM
    Route::any('radiostream/redirect/{id}', 'RadioStreamController@redirect');
    Route::any('radiostream/auth/{id}', 'RadioStreamController@auth');
    Route::any('radiostream/saverecord/{id}', 'RadioStreamController@saveRecord');
    Route::any('radiostream/request/{id}', 'RadioStreamController@request');
    Route::any('radiostream/getcurrenttrack/{id}', 'RadioStreamController@getCurrentTrack');
    Route::any('radiostream/getnexttrack/{id}', 'RadioStreamController@getNextTrack');
    Route::any('radiostream/getstate/{id}', 'RadioStreamController@getState');
    Route::any('radiostream/setstate/{id}', 'RadioStreamController@setState');

    // RADIO FOLDERS
    Route::resource('folders', 'FoldersController');

    //UPLOAD
    Route::post('upload/pictures', 'UploadPicturesController@upload');

    //USERS
    Route::get('users/autocomplete','UsersController@autocomplete');
    Route::get('users/fields','UsersController@infoFields');
    Route::get('users','UsersController@index');
    Route::get('users/{id}','UsersController@show');
    Route::get('access-settings/users/{id}', 'UsersController@getAccessSettings');
    Route::get('users/{id}/media','UsersController@getMedia');
    Route::get('users/{id}/friends','UsersController@getFriends');
    Route::get('users/{id}/channels', 'ChannelTeamController@getChannelsForUser');


    // COMMENTS
    Route::resource('comments', 'CommentsController');
    Route::get('comments/{id}/children', 'CommentsController@getChildren');
    Route::get('comments/{type}/{id}', 'CommentsController@show');
    Route::get('access-settings/{type}/{id}', 'CommentsController@getAccessSettings');
    Route::post('comments/{id}/restore', 'CommentsController@restore');

    // LIKES
    Route::get('likes/{type}/{id}', 'LikesController@getByMaterial');
    Route::get('likes/{type}/{id}/users', 'LikesController@getUsersByMaterial');
    Route::group(['middleware' => \App\Http\Middleware\Authenticate::class], function () {
        Route::post('likes/{type}/{id}', 'LikesController@set');
    });

    // PLAYLISTS
    Route::resource('playlists', 'PlaylistsController');
    Route::any('playlists/{id}/related','PlaylistsController@getRelated');
    Route::get('playlists/{id}/media','PlaylistsController@getMedia');
    Route::get('playlists/{id}/records','PlaylistsController@getRecords');
    Route::get('channels/{id}/playlists', 'PlaylistsController@getForChannel');
    Route::get('channels/{id}/playlists/manager', 'PlaylistsController@getForManager');
    Route::get('channels/{id}/playlists/all', 'PlaylistsController@getAllByChannel');

    // BROADCASTS
    Route::get('channels/{id}/broadcasts', 'BroadcastsController@getByChannel');
    Route::get('channels/{id}/broadcasts/active', 'BroadcastsController@getActive');
    Route::put('channels/{id}/broadcasts/active', 'BroadcastsController@updateActive');
    Route::resource('broadcasts', 'BroadcastsController');

    // LIVE
    Route::get('live/{id}.m3u8', 'LiveController@stream')->name('live.stream');

    // CHAT
    Route::group(['prefix' => 'chat'], function() {
        Route::post('{id}/username','ChatController@changeGuestUsername');
        Route::post('{id}/color','ChatController@changeColor');
        Route::post('{id}/authorize-guest','ChatController@authorizeGuest');
        Route::get('{id}/messages/{message_id}/ban-state','ChatController@getBanStateForMessage');
        Route::post('{id}/messages/{message_id}/ban-state','ChatController@changeBanStateForMessage');
        Route::post('{id}/ban','ChatController@changeUserBanState');
        Route::post('{id}/ban-guest','ChatController@changeGuestBanState');
        Route::post('{id}/clear','ChatController@clear');
        Route::get('{id}/config','ChatController@getConfig');
        Route::get('{id}/messages','ChatController@getMessages');
        Route::post('{id}/messages','ChatController@sendMessage');
        Route::get('{id}/users','ChatController@getChatUsersPublic');
    });

    Route::resource('chat/messages','ChatController');


    // MEDIA
    Route::get('channels/{id}/media/file-manager','MediaController@fileManager');
    Route::get('channels/{id}/media/disk-space','MediaController@getDiskSpace');
    Route::post('channels/{id}/media/bulk-move','MediaController@bulkMove');
    Route::post('channels/{id}/media/bulk-delete','MediaController@bulkDelete');
    Route::resource('channels/{id}/media/folders','MediaFoldersController');
    Route::get('channels/{id}/media/folders/{folder_id}/breadcrumbs','MediaFoldersController@getBreadcrumbs');
    Route::post('media/get-info-by-url', 'MediaController@getInfoByURL');
    Route::post('media/{id}/upload', 'MediaController@upload');
    Route::post('media/{id}/upload/external', 'MediaController@externalUpload');
    Route::resource('media','MediaController');
    Route::any('media/{id}/related','MediaController@getRelated');
    Route::any('media/tags', 'MediaController@getTags');


    // PERMISSIONS
    Route::any('permissions/set', 'PermissionsController@set');


    // STATISTICS
    Route::get('statistics/config/{entity_type}', 'StatisticsController@getConfig');
    Route::get('statistics/{entity_type}/{entity_id}/{type}', 'StatisticsController@get');
    Route::get('statistics/online/{id}', 'StatisticsController@getOnlineViewersHistory');
    Route::get('statistics/realtime/{id}', 'StatisticsController@getRealtimeChannelViewers');

    // ANNOUNCES


    Route::group(['middleware' => \App\Http\Middleware\Authenticate::class], function () {
        Route::post('timetable/announces', 'AnnouncesController@store');
        Route::put('timetable/announces/{id}', 'AnnouncesController@update');
        Route::post('timetable/announces/{id}', 'AnnouncesController@delete');
    });

    Route::any('announces/getbyplaylist/{id}', 'AnnouncesController@getByPlaylist');
    Route::any('announces/getForChannel/{id}', 'AnnouncesController@getForChannel');


    // SCHEDULE
    Route::any('timetable', 'TimetableController@index');
    Route::any('timetable/getnextbychannel/{id}', 'TimetableController@getNextByChannel');
    Route::any('timetable/getForChannel/{id}/all', 'TimetableController@getAllByChannel');
    Route::any('timetable/getForChannel/{id}', 'TimetableController@getForChannel');
    Route::any('timetable/old/{id}', 'TimetableController@getInOldFormat');

    Route::resource('timetable/playlists', 'VideoPlaylistsController');
    Route::any('timetable/next/{id}', 'VideoPlaylistsController@getNextItems');
    Route::any('timetable/playlists/generate/{id}', 'VideoPlaylistsController@generate');
    Route::get('timetable/subscription/{type}/{id}', 'TimetableController@getSubscriptions');
    Route::post('timetable/subscription/{type}/{id}', 'TimetableController@setSubscription');

    Route::any('timetable/epg.xml', 'TimetableController@getXmlEpg');

    // TICKETS
    Route::get('tickets/categories', 'TicketsController@getCategories');
    Route::get('tickets/categories/complaints', 'TicketsController@getComplaintCategories');
    Route::post('tickets', 'TicketsController@store');
    Route::group(['middleware' => [\App\Http\Middleware\Authenticate::class]], function () {
        Route::get('tickets/unread', 'TicketsController@getUnread');
        Route::get('tickets/{id}', 'TicketsController@show');
        Route::put('tickets/{id}', 'TicketsController@update');
        Route::get('tickets', 'TicketsController@index');

    });

    // SERVER STATISTICS
    Route::get('nodeinfo/2.0', 'FederationController@nodeinfo');

    //ADMINISTRATION
    Route::group(['prefix' => 'admin', 'middleware' => [\App\Http\Middleware\AuthenticateAdmin::class]], function () {
        Route::get('donaterequests', 'AdminController@donateRequests');
        Route::post('donaterequests', 'AdminController@setDonateRequestStatus');
        Route::put('config', 'AdminController@updateSiteConfig');
        Route::get('users', 'AdminController@users');
        Route::post('users/balance', 'AdminController@topUpBalance');
        Route::get('channels', 'AdminController@channels');
        Route::post('channels/ban', 'AdminController@changeBanStatus');
        Route::get('servers', 'AdminController@getServers');
        Route::resource('admin/help', 'HelpPagesController');
    });



});

Route::any('/locales', function () {
    $locales = \Illuminate\Support\Facades\Storage::disk('assets')->files('locales');
    $locales = array_map(function($file){
        return pathinfo($file, PATHINFO_FILENAME);
    }, $locales);
    $default = 'ru'; // todo: move to config
    return [
        'list' => $locales,
        'default' => $default
    ];
});
