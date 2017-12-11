<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStatisticOnlinePlayerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('statistic_online_player', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('online_count')->default(0)->comment('在线玩家数量');
            $table->unsignedInteger('playing_count')->default(0)->comment('游戏中玩家数量');
            $table->timestamps();
            $table->index('created_at');  //创建索引
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('statistic_online_player');
    }
}
