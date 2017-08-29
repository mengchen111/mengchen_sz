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
});