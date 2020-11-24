<?php

/************************************ 开放平台 服务交互 Api **************************************/
Route::any('openPlatform/Authorization', 'openPlatform\ServerController@authorization')->name('openPlatformAuthorization');
Route::any('openPlatform/{appid}/callback', 'openPlatform\ServerController@callback')->name('openPlatformCallback');
Route::any('openPlatform/auth', 'openPlatform\ServerController@auth')->name('openPlatformAuth');
Route::any('openPlatform/authCallback', 'openPlatform\ServerController@authCallback')->name('openPlatformAuthCallback');