<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGameNotificationMarqueeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game_notification_marquee', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('priority')->comment('优先级(1-高,2-低)');
            $table->unsignedInteger('interval')->comment('时间间隔');
            $table->timestamp('start_at')->nullable()->comment('开始时间');
            $table->timestamp('end_at')->nullable()->comment('结束时间');
            $table->string('content')->comment('公告内容');
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
        Schema::dropIfExists('game_notification_marquee');
    }
}
