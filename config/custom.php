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

    //计划任务日志
    'cron_task_log' => env('CRON_TASK_LOG', '/tmp/artisan.log'),

    //TODO 待迁移到新接口上
    //游戏服接口地址
    'game_server_api_address' => env('GAME_SERVER_API_ADDRESS'),
    'game_server_partner_id' => env('GAME_SERVER_PARTNER_ID'),
    //游戏服的接口uri
//    'game_server_api_users' => 'users.php',         //所有玩家列表
//    'game_server_api_user' => 'user.php',           //查询单个玩家信息
    'game_server_api_topUp' => 'recharge.php',      //玩家充值

    //游戏服数据缓存时的key
    'game_server_cache_duration' => 3,              //数据缓存时间，3min
    'game_server_cache_players' => 'game:players',  //所有玩家

    //游戏后端数据交互接口信息
    'game_api_address' => env('GAME_API_ADDRESS'),
    'game_api_key' => env('GAME_API_KEY'),
    'game_api_secret' => env('GAME_API_SECRET'),

    //游戏后端接口
    'game_api_records' => 'records',                //战绩列表和查询
    'game_api_record_info' => 'record-info',        //查询指定的战绩id的数据
    'game_api_players' => 'players',                //玩家列表和查询
    'game_api_top-up' => 'top-up',                  //玩家充值
];