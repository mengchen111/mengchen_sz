<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateCommunityMemberLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('community_member_log', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('community_id')->comment('社团id');
            $table->unsignedInteger('player_id')->comment('玩家id');
            $table->string('action')->comment('事件动作');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('community_id')->references('id')->on('community_list');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('community_member_log');
    }
}
