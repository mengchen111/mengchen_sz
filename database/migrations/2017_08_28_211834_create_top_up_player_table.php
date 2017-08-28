<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTopUpPlayerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('top_up_player', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('provider_id')->comment('发起充值的代理商id');
            $table->unsignedInteger('player_id')->comment('充值到账的玩家id');
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
        Schema::dropIfExists('top_up_player');
    }
}
