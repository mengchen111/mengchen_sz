<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommunityCardTopupLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('community_card_topup_log', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('community_id')->comment('社团id');
            $table->unsignedInteger('agent_id')->comment('充值的代理商的id');
            $table->unsignedInteger('item_type_id')->comment('道具类型(1-房卡,2-金币)');
            $table->integer('item_amount')->comment('充值数量');
            $table->string('remark')->nullable()->comment('备注');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->foreign('community_id')->references('id')->on('community_list');
            $table->foreign('item_type_id')->references('id')->on('item_type');
            $table->foreign('agent_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('community_card_topup_log');
    }
}
