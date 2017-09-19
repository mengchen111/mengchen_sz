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
Route::get('/home', 'HomeController@index')->name('home');

Auth::routes();

//开发调试功能接口
Route::prefix('dev')->group(function () {
    Route::get('list-session', 'DevToolsController@listSession');
    Route::get('hashed-pass/{pass}', 'DevToolsController@hashedPass');

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

    Route::get('home/summary', 'HomeController@summaryReport');

    Route::get('statement/hourly', 'StatementController@hourly');
    Route::get('statement/daily', 'StatementController@daily');
    Route::get('statement/monthly', 'StatementController@monthly');

    Route::get('game/player', 'Game\PlayerController@show');

    Route::get('game/notification/marquee', 'Game\MarqueeNotificationController@show');
    Route::post('game/notification/marquee', 'Game\MarqueeNotificationController@create');
    Route::put('game/notification/marquee/{marquee}', 'Game\MarqueeNotificationController@update')->where('marquee', '[0-9]+');
    Route::delete('game/notification/marquee/{marquee}', 'Game\MarqueeNotificationController@destroy')->where('marquee', '[0-9]+');
    Route::put('game/notification/marquee/enable/{marquee}', 'Game\MarqueeNotificationController@enable')->where('marquee', '[0-9]+');
    Route::put('game/notification/marquee/disable/{marquee}', 'Game\MarqueeNotificationController@disable')->where('marquee', '[0-9]+');
    Route::get('game/notification/login', 'Game\LoginNotificationController@show');
    Route::post('game/notification/login', 'Game\LoginNotificationController@create');
    Route::put('game/notification/login/{notification}', 'Game\LoginNotificationController@update')->where('notification', '[0-9]+');
    Route::delete('game/notification/login/{notification}', 'Game\LoginNotificationController@destroy')->where('notification', '[0-9]+');
    Route::put('game/notification/login/enable/{notification}', 'Game\LoginNotificationController@enable')->where('notification', '[0-9]+');
    Route::put('game/notification/login/disable/{notification}', 'Game\LoginNotificationController@disable')->where('notification', '[0-9]+');


    Route::get('game/room/friend', 'Game\FriendRoomController@show');
    Route::delete('game/room/friend/{ownerId}', 'Game\FriendRoomController@dismiss')->where('ownerId', '[0-9]+');
    Route::get('game/room/coin', 'Game\CoinRoomController@show');
    Route::delete('game/room/coin/{roomId}', 'Game\CoinRoomController@dismiss')->where('roomId', '[0-9]+');

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

    Route::get('top-up/top-agent', 'TopUpController@topUp2TopAgentHistory');
    Route::get('top-up/agent', 'TopUpController@Agent2AgentHistory');
    Route::get('top-up/player', 'TopUpController@Agent2PlayerHistory');
    Route::post('top-up/agent/{receiver}/{type}/{amount}', 'TopUpController@topUp2Agent')->where('amount', '[0-9]+');
    Route::post('top-up/player/{player}/{type}/{amount}', 'TopUpController@topUp2Player')->where('amount', '[0-9]+');

    Route::get('system/log', 'SystemController@showLog');
});

//管理员视图路由
Route::group([
    'middleware' => ['auth'],
    'prefix' => 'admin',
    'namespace' => 'Admin'
], function () {
    Route::get('home', 'ViewController@home');

    Route::get('statement/hourly', 'ViewController@statementHourly');
    Route::get('statement/daily', 'ViewController@statementDaily');
    Route::get('statement/monthly', 'ViewController@statementMonthly');

    Route::get('player/list', 'ViewController@playerList');

    Route::get('gm/notification/marquee', 'ViewController@gmNotificationMarquee');
    Route::get('gm/notification/login', 'ViewController@gmNotificationLogin');
    Route::get('gm/room/friend', 'ViewController@gmRoomFriend');
    Route::get('gm/room/coin', 'ViewController@gmRoomCoin');

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