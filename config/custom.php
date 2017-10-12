<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 9/23/17
 * Time: 15:47
 */

return [
    //是否开启系统日志记录功能
    'operation_log' => env('OPERATION_LOG', true),

    //是否开启邮件通知(新的库存申请，通知管理员)
    'email_notification' => env('EMAIL_NOTIFICATION', false),

    //游戏服接口地址
    'game_server_api_address' => env('GAME_SERVER_API_ADDRESS'),
    'game_server_partner_id' => env('GAME_SERVER_PARTNER_ID'),

    //游戏服的接口uri
    'game_server_api_users' => 'users.php',         //所有玩家列表
    'game_server_api_user' => 'user.php',           //查询单个玩家信息
    'game_server_api_topUp' => 'recharge.php',      //玩家充值

    //游戏服数据缓存时的key
    'game_server_cache_duration' => 3,              //数据缓存时间，3min
    'game_server_cache_players' => 'game:players'   //所有玩家
];