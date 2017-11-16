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

Route::get('/', 'HomeController@index');

// Authentication Routes...
Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');

//开发调试功能接口
Route::prefix('dev')->group(function () {
    Route::get('list-session', 'DevToolsController@listSession');
    Route::get('hashed-pass/{pass}', 'DevToolsController@hashedPass');
    Route::post('base64-decode', 'DevToolsController@base64Decode');
});

//公共接口
Route::prefix('api')->middleware(['auth'])->group(function () {
    Route::get('info', 'InfoController@info');
});

//管理员接口
Route::group([
    'middleware' => ['auth'],
    'prefix' => 'admin/api',
    'namespace' => 'Admin'
], function () {
    Route::put('self/password', 'AdminController@updatePass');

//    Route::get('home/summary', 'HomeController@summaryReport');

    Route::get('game/players', 'Game\PlayerController@show');

    Route::get('statement/summary', 'Game\StatementSummaryController@show');
    Route::get('statement/real-time', 'Game\StatementSummaryController@showRealTimeData');

    Route::get('gm/records', 'Game\RecordController@search');
    Route::get('gm/record-info/{recId}', 'Game\RecordController@getRecordInfo')->where('recId', '[0-9]+');
    Route::get('gm/room/type', 'Game\RoomController@getRoomType');
    Route::post('gm/room', 'Game\RoomController@create');
    Route::get('gm/room/open', 'Game\RoomController@showOpenRoom');
    Route::get('gm/room/history', 'Game\RoomController@showRoomHistory');

    Route::post('stock', 'StockController@apply');
    Route::get('stock/list', 'StockController@applyList');
    Route::get('stock/history', 'StockController@applyHistory');
    Route::post('stock/approval/{entry}', 'StockController@approve')->where('entry', '[0-9]+');
    Route::post('stock/decline/{entry}', 'StockController@decline')->where('entry', '[0-9]+');

    Route::get('agent', 'AgentController@showAll');
    Route::post('agent', 'AgentController@create');
    Route::delete('agent/{user}', 'AgentController@destroy')->where('user', '[0-9]+');
    Route::put('agent/{user}', 'AgentController@update')->where('user', '[0-9]+');
    Route::put('agent/pass/{user}', 'AgentController@updatePass')->where('user', '[0-9]+');

    Route::get('top-up/admin', 'TopUpController@admin2AgentHistory');
    Route::get('top-up/agent', 'TopUpController@agent2AgentHistory');
    Route::get('top-up/player', 'TopUpController@agent2PlayerHistory');
    Route::post('top-up/agent/{receiver}/{type}/{amount}', 'TopUpController@topUp2Agent')->where('amount', '-?[0-9]+');
    Route::post('top-up/player/{player}/{type}/{amount}', 'TopUpController@topUp2Player')->where('amount', '-?[0-9]+');

    Route::get('group/authorization/view/{user}', 'AuthorizationController@showViewAccess')->where('user', '[0-9]+');;
    Route::post('group/authorization/view', 'AuthorizationController@setupViewAccess');

    Route::get('system/log', 'SystemController@showLog');
});

//管理员视图路由
Route::group([
    'middleware' => ['auth'],
    'prefix' => 'admin',
    'namespace' => 'Admin'
], function () {
    Route::get('home', 'ViewController@home');

    Route::get('player/list', 'ViewController@playerList');

    Route::get('statement/summary', 'ViewController@statementSummary');

    Route::get('gm/record', 'ViewController@gmRecord');
    Route::get('gm/room', 'ViewController@gmRoom');

    Route::get('stock/apply-request', 'ViewController@stockApplyRequest');
    Route::get('stock/apply-list', 'ViewController@stockApplyList');
    Route::get('stock/apply-history', 'ViewController@stockApplyHistory');

    Route::get('agent/list', 'ViewController@agentList');
    Route::get('agent/create', 'ViewController@agentCreate');

    Route::get('top-up/admin', 'ViewController@topUpAdmin');
    Route::get('top-up/agent', 'ViewController@topUpAgent');
    Route::get('top-up/player', 'ViewController@topUpPlayer');

    Route::get('system/log', 'ViewController@systemLog');
});

//代理商接口
Route::group([
    'middleware' => ['auth'],
    'prefix' => 'agent/api',
    'namespace' => 'Agent'
], function () {
    Route::put('self/info', 'AgentController@update');
    Route::put('self/password', 'AgentController@updatePass');
    Route::get('self/agent-type', 'AgentController@agentType');

    Route::post('stock', 'StockController@apply');
    Route::get('stock/history', 'StockController@applyHistory');

    Route::get('subagent', 'SubAgentController@show');
    Route::post('subagent', 'SubAgentController@create');
    Route::delete('subagent/{user}', 'SubAgentController@destroy')->where('user', '[0-9]+');
    Route::put('subagent/{child}', 'SubAgentController@updateChild')->where('child', '[0-9]+');

    Route::post('top-up/child/{receiver}/{type}/{amount}', 'TopUpController@topUp2Child')->where('amount', '[0-9]+');
    Route::post('top-up/player/{player}/{type}/{amount}', 'TopUpController@topUp2Player')->where('amount', '[0-9]+');
    Route::get('top-up/child', 'TopUpController@topUp2ChildHistory');
    Route::get('top-up/player', 'TopUpController@topUp2PlayerHistory');
});

//代理商视图
Route::group([
    'middleware' => ['auth'],
    'prefix' => 'agent',
    'namespace' => 'Agent'
], function () {
    Route::get('home', 'ViewController@home');

    Route::get('player/top-up', 'ViewController@playerTopUp');  //玩家充值页面

    Route::get('stock/apply-request', 'ViewController@stockApplyRequest');
    Route::get('stock/apply-history', 'ViewController@stockApplyHistory');

    Route::get('subagent/list', 'ViewController@subagentList');
    Route::get('subagent/create', 'ViewController@subagentCreate');

    //给子代理商的充值记录
    Route::get('top-up/child', 'ViewController@topUpChild');
    Route::get('top-up/player', 'ViewController@topUpPlayer');

    Route::get('info', 'ViewController@info');
});