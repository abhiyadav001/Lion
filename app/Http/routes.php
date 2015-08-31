<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

/*
 * add and edit user profile
 */
Route::resource('users', 'UserController');
post('users/{fb_id}/get-details/{lat}/{lng}', 'UserController@getFullDetail');

/*
 * update device status like(lat, lng, last_signup)
 */
post('users/{id}/device', 'UserController@updateDeviceStatus');
/*
 * block users
 */
post('users/{id}/block', 'UserController@blockUsers');
/*
 * user notification
 */
post('users/{id}/notification', 'UserController@sendNotification');

get('/notification', 'UserController@testNotification');

/*
 * Change visibility of users
 */
post('users/{id}/visibility', 'UserController@changeUserVisibility');
/*
 * log linkup personal chat and display it's last chat profiles
 */
Route::resource('linkup', 'UserLinkupLogController');
/*
 * Update social networking flag on to off or vice versa
 */
post('users/{id}/update/social', 'UserController@updateUserSocialNetwork');
