<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateCommunityListTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('community_list', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('owner_agent_id')->comment('社团长代理商id');
            $table->unsignedInteger('owner_player_id')->comment('社团长玩家id');
            $table->string('name')->comment('社团名称');
            $table->string('info')->nullable()->comment('社团简介');
            $table->integer('card_stock')->default(0)->comment('社团房卡库存');
            $table->text('members')->nullable()->comment('社团成员');
            $table->timestamps();
            $table->foreign('owner_agent_id')->references('id')->on('users');
        });

        //id从10000开始
        DB::update("ALTER TABLE community_list AUTO_INCREMENT = 10000;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('community_list');
    }
}
