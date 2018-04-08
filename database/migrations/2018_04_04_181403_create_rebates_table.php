<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRebatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /**
         * 每个月定时任务(跑订单表)自动生成数据入库
            并且找上级代理，不是管理员则一起添加入库
         */
        Schema::create('rebates', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('children_id')->comment('下级用户id');
            $table->unsignedInteger('total_amount')->comment('当月总金额');
            $table->date('rebate_at')->nullable()->comment('返利时间月份');
            $table->unsignedDecimal('rebate_price')->comment('返利金额');
            $table->unsignedSmallInteger('rebate_rule_id')->comment('返利规则id');
            $table->string('remark')->nullable();
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
        Schema::dropIfExists('rebates');
    }
}
