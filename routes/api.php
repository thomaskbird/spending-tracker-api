<?php

// header('Access-Control-Allow-Origin: http://budget.thomaskbird.com');
header('Access-Control-Allow-Origin: http://localhost:8009');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, User-Agent");

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

// Credential routes
route::post('login', ['as' => 'action_login', 'uses' => 'CredentialController@action_login']);
route::post('signup', ['as' => 'action_signup', 'uses' => 'CredentialController@action_signup']);

// Tag routes
route::get('tags', ['as' => 'view', 'uses' => 'TagController@view']);
route::get('tags/{id}', ['as' => 'single', 'uses' => 'TagController@single']);
route::post('tags/create', ['as' => 'action_create', 'uses' => 'TagController@action_create']);

// Transaction routes
route::post('transactions/create', ['as' => 'action_create', 'uses' => 'TransactionController@action_create']);
route::post('transactions/edit/{id}', ['as' => 'action_edit', 'uses' => 'TransactionController@action_edit']);
route::get('transactions/remove/{id}', ['as' => 'action_remove', 'uses' => 'TransactionController@action_remove']);
route::get('transactions/{id}', ['as' => 'single', 'uses' => 'TransactionController@single']);
route::get('transactions/{start?}/{end?}', ['as' => 'view', 'uses' => 'TransactionController@view']);

route::get('occurences/testing/{type}/{start_at}/{end_at}', ['as' => 'test_occurences', 'uses' => 'TransactionController@test_occurences']);