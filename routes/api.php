<?php
$env = env('APP_ENV', 'dev');
if($env === 'dev') {
    $allowed_env = 'http://localhost:8075';
} else {
    $allowed_env = 'http://budget.thomaskbird.com';
}

header('Access-Control-Allow-Origin: '. $allowed_env);
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
route::post('forgot-password', ['as' => 'action_forgot_password', 'uses' => 'CredentialController@action_forgot_password']);
route::post('reset-password/{reset_token}', ['as' => 'action_reset_password', 'uses' => 'CredentialController@action_reset_password']);

/**
 * Protected routes
 * These routes utilize the apiToken middleware for authorization
 */
route::middleware(['apiToken'])->group(function() {
    // Account user routes
    route::post('account/user/add', ['as' => 'account_user_add', 'uses' => 'AccountController@account_user_add']);

    // Alert routes
    route::get('alert/remove/{id}', ['as' => 'remove_alert', 'uses' => 'AlertController@remove_alert']);
    route::post('alert', ['as' => 'create_alert', 'uses' => 'AlertController@create_alert']);

    // Budget routes
    route::get('budgets/alerts', ['as' => 'budgets_list_with_alerts', 'uses' => 'BudgetController@budgets_list_with_alerts']);
    route::get('budgets/remove/{id}', ['as' => 'budget_remove', 'uses' => 'BudgetController@budget_remove']);
    route::get('budgets/{id}/{start}/{end}', ['as' => 'budget_single', 'uses' => 'BudgetController@budget_single']);
    route::get('budgets/{start}/{end}', ['as' => 'budget_list_with_transactions', 'uses' => 'BudgetController@budget_list_with_transactions']);
    route::get('budgets', ['as' => 'budgets_list', 'uses' => 'BudgetController@budgets_list']);
    route::post('budgets', ['as' => 'budget_create', 'uses' => 'BudgetController@budget_create']);
    route::post('budgets/{id}', ['as' => 'budget_edit', 'uses' => 'BudgetController@budget_edit']);

    // Import routes
    route::post('import', ['as' => 'action_import', 'uses' => 'ImportController@action_import']);

    // Tag routes
    route::get('tags', ['as' => 'view', 'uses' => 'TagController@view']);
    route::get('tags/remove/{id}', ['as' => 'action_remove', 'uses' => 'TagController@action_remove']);
    route::get('tags/{id}', ['as' => 'single', 'uses' => 'TagController@single']);
    route::post('tags/{id}', ['as' => 'action_edit', 'uses' => 'TagController@action_edit']);
    route::post('tags', ['as' => 'action_create', 'uses' => 'TagController@action_create']);

    // Tag relation routes
    route::post('tag/relation/add', ['as' => 'tag_relation_add', 'uses' => 'TaggableController@tag_relation_add']);
    route::post('tag/relation/remove', ['as' => 'tag_relation_remove', 'uses' => 'TaggableController@tag_relation_remove']);
    route::post('tag/relation', ['as' => 'get_tags_with_selected_status', 'uses' => 'TaggableController@get_tags_with_selected_status']);

    // Transaction routes
    route::post('transactions/create', ['as' => 'transactions_action_create', 'uses' => 'TransactionController@action_create']);
    route::post('transactions/edit/{id}', ['as' => 'transactions_action_edit', 'uses' => 'TransactionController@action_edit']);

    route::get('transaction/tags/{transaction_id}', ['as' => 'transaction_tags', 'uses' => 'TransactionController@transaction_tags']);
    route::get('transactions/remove/{id}', ['as' => 'transactions_action_remove', 'uses' => 'TransactionController@action_remove']);
    route::get('transactions/{id}', ['as' => 'transactions_single', 'uses' => 'TransactionController@single'])->where('id', '[0-9]+');
    route::get('transactions/{start?}/{end?}', ['as' => 'transactions_view', 'uses' => 'TransactionController@view']);

    route::get('update/{model}/{target_id}/{key}/{val}', ['as' => 'single_model_update', 'uses' => 'UtilityController@single_model_update']);

    route::post('upload/{type}', ['as' => 'upload_file', 'uses' => 'UploadController@upload_file']);

    route::get('visualizations/budgets/{start}/{end}', ['as' => 'visualization_budgets', 'uses' => 'BudgetController@visualization_budgets']);
});

/**
 * Testing routes
 */
route::get('occurences/testing/{type}/{start_at}/{end_at}', ['as' => 'test_occurences', 'uses' => 'TransactionController@test_occurences']);
