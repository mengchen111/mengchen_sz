<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateCommunityCardConsumptionLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('community_card_consumption_log', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('community_id')->comment('社团id');
            $table->unsignedInteger('player_id')->comment('玩家id');
            $table->unsignedTinyInteger('operation')->comment('动作(0-冻结,1-消耗冻结,2-退还冻结,3-直接耗卡)');
            $table->unsignedTinyInteger('count')->comment('房卡数量');
            $table->string('remark')->nullable()->comment('备注');
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
        Schema::dropIfExists('community_card_consumption_log');
    }
}
