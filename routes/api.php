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

// Credential routes
route::post('login', ['as' => 'action_login', 'uses' => 'CredentialController@action_login']);
route::post('signup', ['as' => 'action_signup', 'uses' => 'CredentialController@action_signup']);

// Tag routes
route::get('tags', ['as' => 'view', 'uses' => 'TagController@view']);
route::get('tags/{id}', ['as' => 'single', 'uses' => 'TagController@single']);
route::post('tags/create', ['as' => 'action_create', 'uses' => 'TagController@action_create']);

// Transaction routes
route::get('transactions', ['as' => 'view', 'uses' => 'TransactionController@view']);
route::get('transactions/{id}', ['as' => 'single', 'uses' => 'TransactionController@single']);
route::post('transactions/create', ['as' => 'action_create', 'uses' => 'TransactionController@action_create']);
