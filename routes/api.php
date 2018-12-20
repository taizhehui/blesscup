<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/getServerTimeUTC', 'PingController@getServerTimeUTC');

Route::post('/getUUID', 'DeviceController@getUUID');

Route::post('/setDeviceUserInfo', 'DeviceController@setDeviceUserInfo');

Route::post('/newUser', 'DeviceController@newUser');

Route::post('/linkUser', 'DeviceController@linkUser');

Route::get('/registerDevice', 'DeviceController@registerDevice');

Route::post('/getShouldDeleteDirItems', 'PingController@getShouldDeleteDirItems');

Route::post('/syncDirInfo', 'PingController@syncDirInfo');

Route::post('/downloadFile', 'PingController@downloadFile');

Route::post('/uploadVoiceMessage', 'PingController@uploadVoiceMessage');

Route::post('/getDeleteDir', 'PingController@getDeleteDir');

Route::post('/getShouldDeleteVoiceMessageItems', 'PingController@getShouldDeleteVoiceMessageItems');

Route::post('/syncVoiceMessage', 'PingController@syncVoiceMessage');

Route::post('/createChat', 'PingController@createChat');

Route::post('/deleteChat', 'PingController@deleteChat');

Route::post('/createDirectory', 'PingController@createDirectory');

Route::post('/uploadMusic', 'PingController@uploadMusic');

Route::post('/addMusicToDirectory', 'PingController@addMusicToDirectory');

Route::post('/deleteVoiceMessage', 'PingController@deleteVoiceMessage');