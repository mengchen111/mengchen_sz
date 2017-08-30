<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('/login');
});

Auth::routes();

Route::get('list-session', 'DevToolsController@listSession');
Route::get('hashed-pass/{pass}', 'DevToolsController@hashedPass');
Route::get('info', 'InfoController@info');

Route::group([
    'middleware' => ['auth'],
    'prefix' => 'admin',
    'namespace' => 'Admin'
], function () {
    Route::get('agent', 'AgentController@showAll');
    Route::post('agent', 'AgentController@create');
    Route::delete('agent/{user}', 'AgentController@destroy')->where('user', '[0-9]+');
    Route::put('agent/{user}', 'AgentController@update')->where('user', '[0-9]+');

    Route::get('top-up/top-agent', 'TopUpController@topUp2TopAgentHistory');
    Route::get('top-up/agent', 'TopUpController@Agent2AgentHistory');
    Route::get('top-up/player', 'TopUpController@Agent2PlayerHistory');
    Route::post('top-up/top-agent/{receiver}/{type}/{amount}', 'TopUpController@topUp2TopAgent')->where('amount', '[0-9]+');
});

Route::group([
    'middleware' => ['auth'],
    'prefix' => 'agent',
    'namespace' => 'Agent'
], function () {
    Route::get('subagent', 'SubAgentController@show');
    Route::post('subagent', 'SubAgentController@create');
    Route::put('subagent', 'SubAgentController@update');
    Route::put('subagent/{child}', 'SubAgentController@updateChild')->where('child', '[0-9]+');

    Route::post('top-up/child/{receiver}/{type}/{amount}', 'TopUpController@topUp2Child')->where('amount', '[0-9]+');
    //Route::post('top-up/player/{receiver}/{amount}', 'TopUpController@topUp2Player')->where('amount', '[0-9]+');
    Route::get('top-up/child', 'TopUpController@topUp2ChildHistory');
    Route::get('top-up/player', 'TopUpController@topUp2PlayerHistory');
});