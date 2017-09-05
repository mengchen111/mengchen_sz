<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStockApplyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_apply', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('applicant_id')->comment('申请人的用户id');
            $table->unsignedInteger('item_id')->comment('道具类型id');
            $table->unsignedInteger('amount')->comment('申请数量');
            $table->string('remark')->nullable()->comment('备注');
            $table->unsignedInteger('state')->default(1)->comment('审核状态(1-待审核,2-通过,3-拒绝)');
            $table->unsignedInteger('approver_id')->default(0)->comment('审核者id');
            $table->string('approver_remark')->nullable()->comment('审核者备注');
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
        Schema::dropIfExists('stock_apply');
    }
}
