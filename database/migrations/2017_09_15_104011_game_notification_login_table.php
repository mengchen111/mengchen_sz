<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GameNotificationLoginTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game_notification_login', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('order')->comment('排序,小的靠前,最小为1');
            $table->string('title')->comment('标题');
            $table->text('content')->comment('公告内容');
            $table->unsignedTinyInteger('pop_frequency')->comment('弹出频率(1-每日首次登录,2-每次登录)');
            $table->timestamp('start_at')->nullable()->comment('开始时间');
            $table->timestamp('end_at')->nullable()->comment('结束时间');
            $table->unsignedInteger('switch')->default(2)->comment('开关(1-开启,2-关闭)');
            $table->unsignedInteger('sync_state')->default(1)->comment('同步状态(1-未同步,2-同步中,3-同步成功,4-同步失败)');
            $table->text('failed_description')->nullable()->comment('同步失败原因');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('game_notification_login');
    }
}
