<?php

// header('Access-Control-Allow-Origin: http://budget.thomaskbird.com');
header('Access-Control-Allow-Origin: http://localhost:8009');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, User-Agent, authorization");

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Credential routes
route::post('login', ['as' => 'action_login', 'uses' => 'CredentialController@action_login']);
route::post('signup', ['as' => 'action_signup', 'uses' => 'CredentialController@action_signup']);
route::post('activate/{activation_code}', ['as' => 'account_user_activate', 'uses' => 'CredentialController@account_user_activate']);

/**
 * Protected routes
 * These routes utilize the apiToken middleware for authorization
 */
route::middleware(['apiToken'])->group(function() {
    // Account user routes
    route::post('account/user/add', ['as' => 'account_user_add', 'uses' => 'AccountController@account_user_add']);

    // Budget routes
    route::get('budgets', ['as' => 'budget_list', 'uses' => 'BudgetController@budget_list']);
    route::get('budgets/{id}', ['as' => 'budget_single', 'uses' => 'BudgetController@budget_single']);
    route::get('budgets/remove/{id}', ['as' => 'budget_remove', 'uses' => 'BudgetController@budget_remove']);
    route::post('budgets', ['as' => 'budget_create', 'uses' => 'BudgetController@budget_create']);
    route::post('budgets/{id}', ['as' => 'budget_edit', 'uses' => 'BudgetController@budget_edit']);

    // Tag routes
    route::get('tags', ['as' => 'view', 'uses' => 'TagController@view']);
    route::get('tags/{id}', ['as' => 'single', 'uses' => 'TagController@single']);
    route::post('tags/create', ['as' => 'action_create', 'uses' => 'TagController@action_create']);

    // Transaction routes
    route::post('transactions/create', ['as' => 'transactions_action_create', 'uses' => 'TransactionController@action_create']);
    route::post('transactions/edit/{id}', ['as' => 'transactions_action_edit', 'uses' => 'TransactionController@action_edit']);
    route::get('transactions/remove/{id}', ['as' => 'transactions_action_remove', 'uses' => 'TransactionController@action_remove']);
    route::get('transactions/{id}', ['as' => 'transactions_single', 'uses' => 'TransactionController@single']);
    route::get('transactions/{start?}/{end?}', ['as' => 'transactions_view', 'uses' => 'TransactionController@view']);
});

/**
 * Testing routes
 */
route::get('occurences/testing/{type}/{start_at}/{end_at}', ['as' => 'test_occurences', 'uses' => 'TransactionController@test_occurences']);