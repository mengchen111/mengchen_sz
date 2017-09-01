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

//开发调试功能接口
Route::prefix('dev')->group(function () {
    Route::get('list-session', 'DevToolsController@listSession');
    Route::get('hashed-pass/{pass}', 'DevToolsController@hashedPass');

});

//公共ajax接口
Route::prefix('api')->group(function () {
    Route::get('info', 'InfoController@info');
});

//ajax接口
Route::group([
    'middleware' => ['auth'],
    'prefix' => 'admin/api',
    'namespace' => 'Admin'
], function () {
    Route::get('agent', 'AgentController@showAll');
    Route::post('agent', 'AgentController@create');
    Route::delete('agent/{user}', 'AgentController@destroy')->where('user', '[0-9]+');
    Route::put('agent/{user}', 'AgentController@update')->where('user', '[0-9]+');
    Route::put('agent/pass/{user}', 'AgentController@updatePass')->where('user', '[0-9]+');

    Route::get('top-up/top-agent', 'TopUpController@topUp2TopAgentHistory');
    Route::get('top-up/agent', 'TopUpController@Agent2AgentHistory');
    Route::get('top-up/player', 'TopUpController@Agent2PlayerHistory');
    Route::post('top-up/top-agent/{receiver}/{type}/{amount}', 'TopUpController@topUp2TopAgent')->where('amount', '[0-9]+');

    Route::get('system/log', 'SystemController@showLog');
});

//视图路由
Route::group([
    'middleware' => ['auth'],
    'prefix' => 'admin',
    'namespace' => 'Admin'
], function () {
    Route::get('agent/list', 'ViewController@agentList');
});

Route::group([
    'middleware' => ['auth'],
    'prefix' => 'agent/api',
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

Route::group([
    'middleware' => ['auth'],
    'prefix' => 'agent',
    'namespace' => 'Agent'
], function () {
    //Route::get('agent/list', 'ViewController@agentList');
});