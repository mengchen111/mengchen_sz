<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStatementDailyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('statement_daily', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamp('date')->useCurrent();
            $table->unsignedInteger('average_online_players')->comment('平均在线玩家数');
            $table->unsignedInteger('peak_online_players')->comment('最高同时在线玩家数');
            $table->unsignedInteger('peak_in_game_players')->comment('最高游戏中玩家数');
            $table->unsignedInteger('active_players')->comment('活跃玩家数(当日有过登录)');
            $table->unsignedInteger('incremental_players')->comment('新增玩家数');
            $table->string('one_day_remained')->comment('次日留存');    //格式'2|4|50.00 - 留存玩家数|创建日玩家数|百分比(保留两位小数)
            $table->string('one_week_remained')->comment('7日留存');
            $table->string('two_weeks_remained')->comment('14日留存');
            $table->string('one_month_remained')->comment('30日留存');
            $table->string('card_consumed_data')->comment('房卡消耗数据'); //格式'200|2|100 - 当日耗卡总数|当日有过耗卡记录的玩家总数|平均耗卡
            $table->string('card_bought_data')->comment('房卡购买数据'); //格式'200|2|100 - 当日玩家购卡总数|当日有过购卡记录的玩家总数|平均购卡
            $table->unsignedInteger('card_consumed_sum')->comment('截止今日耗卡总数');        //截止今日，玩家耗卡的总量
            $table->unsignedInteger('card_bought_sum')->comment('截止今日购卡总数');          //截止今日，给玩家充卡的总量
            $table->mediumText('players_data')->nullable()->comment('今日玩家数据');  //保存每日的玩家数据
            $table->timestamps();
            $table->index('date');  //创建索引
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('statement_daily');
    }
}
