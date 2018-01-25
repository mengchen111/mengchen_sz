<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommunityConfTable extends Migration
{
    protected $defaultConf = [
        'community_id' => 0,
        'agent_id' => 0,
        'player_id' => 0,
        'max_community_count' => 5,     //创建和加入的社区数最多5个
        'max_community_pending_count' => 5, //待审核的社团数上限
        'max_member_count' => 1000,      //每个社区最多成员数
        'max_open_room_count' => 20,    //同时可开房间数(已创建还未准备的，游戏中的不算)
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('community_conf', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('community_id')->nullable()->unique()->comment('社团id(0为默认配置)');
            $table->unsignedInteger('agent_id')->nullable()->unique()->comment('代理商(0为默认配置)');
            $table->unsignedInteger('player_id')->nullable()->unique()->comment('玩家id(0为默认配置)');
            $table->unsignedInteger('max_community_count')->comment('最大社团数');
            $table->unsignedInteger('max_community_pending_count')->comment('最大待审核社团数');
            $table->unsignedInteger('max_member_count')->comment('最大成员数');
            $table->unsignedInteger('max_open_room_count')->comment('最大开房数');
            $table->timestamps();
        });

        DB::table('community_conf')->insert($this->defaultConf);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('community_conf');
    }
}
