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


Route::prefix('admin')->group(function () {
    Route::get('agent', 'Admin\AgentController@showAll');
    Route::post('agent', 'Admin\AgentController@create');
    Route::delete('agent/{user}', 'Admin\AgentController@destroy')->where('user', '[0-9]+');
    Route::put('agent/{user}', 'Admin\AgentController@update')->where('user', '[0-9]+');

    Route::get('top-up/top-agent', 'Admin\TopUpController@topUp2TopAgentHistory');
    Route::get('top-up/agent', 'Admin\TopUpController@Agent2AgentHistory');
    Route::get('top-up/player', 'Admin\TopUpController@Agent2PlayerHistory');
    Route::post('top-up/top-agent/{receiver}/{amount}', 'Admin\TopUpController@topUp2TopAgent')->where('amount', '[0-9]+');
});

Route::prefix('agent')->group(function () {
    Route::get('subagent', 'Agent\SubAgentController@show');
    Route::post('subagent', 'Agent\SubAgentController@create');
    Route::put('subagent', 'Agent\SubAgentController@update');
    Route::put('subagent/{child}', 'Agent\SubAgentController@updateChild')->where('child', '[0-9]+');

    Route::post('top-up/child/{receiver}/{amount}', 'Agent\TopUpController@topUp2Child')->where('amount', '[0-9]+');
});