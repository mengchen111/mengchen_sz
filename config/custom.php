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

    //游戏服数据缓存时的key
    'game_server_cache_duration' => 3,              //数据缓存时间，3min
    'game_server_cache_players' => 'game:players',  //缓存所有玩家数据
    'game_server_cache_room_history' => 'room:history',     //格式化之后的房间历史记录的缓存
    'game_server_cache_currency_log' => 'currency:all',     //道具消耗记录的缓存(暂未使用)
    'game_server_cache_valid_card_agent_log' => 'valid-card:agent',     //包含了有效耗卡数据的代理商给玩家充值记录
    'game_server_cache_valid_card_consumed_log' => 'valid-card:consumed',     //从后端返回的道具消耗日志中提取有效耗卡记录并缓存
    'cache_room_statement' => 'room:statement',     //房间数据报表

    //游戏后端数据交互接口信息
    'game_api_address' => env('GAME_API_ADDRESS'),
    'game_api_key' => env('GAME_API_KEY'),
    'game_api_secret' => env('GAME_API_SECRET'),

    //游戏后端接口
    'game_api_records' => 'records',                //战绩列表和查询
    'game_api_record_info' => 'record-info',        //查询指定的战绩id的数据
    'game_api_players' => 'players',                //玩家列表和查询
    'game_api_players_online_amount' => 'players/online/amount', //获取实时在线玩家数量
    'game_api_players_online_peak' => 'players/online/peak',     //获取指定日期的当日玩家最高在线数量
    'game_api_players_in-game' => 'players/in-game',    //获取实时游戏中的玩家数量
    'game_api_players_in-game_peak' => 'players/in-game/peak',    //获取指定日期的在游戏中的玩家最高数量
    'game_api_top-up' => 'top-up',                  //玩家充值
    'game_api_room_create' => 'room',               //创建游戏房间
    'game_api_room_open' => 'room/open',            //查看正在玩的房间
    'game_api_room_history' => 'room/history',      //查看已经结束的房间
    'game_api_card_consumed' => 'card/consumed',    //房卡消耗数据（指定日期）
    'game_api_card_consumed_total' => 'card/consumed/total',    //房卡总消耗
    'game_api_currency_log' => 'currency/log',      //道具消耗记录
    'game_api_activities_activities-list' => 'activities/activities-list', //获取活动列表
    'game_api_activities_activities-reward' => 'activities/activities-reward', //获取活动奖品列表
    'game_api_activities_activities-task' => 'activities/activities-task', //获取任务列表
    'game_api_activities_activities-task-type' => 'activities/activities-task-type', //获取任务类型列表
    'game_api_activities_activities-goods-type' => 'activities/activities-goods-type', //获取任务奖励类型列表
];