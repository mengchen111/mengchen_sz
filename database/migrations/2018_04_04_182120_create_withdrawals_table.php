<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWithdrawalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            //额度限制 500|1000|5000|10000|50000
            $table->unsignedInteger('amount')->comment('提现金额');
            $table->string('wechat')->nullable()->comment('微信号');
            $table->string('phone')->nullable();
            // 0：待审核  1：待发放 2：已发放 3：审核拒绝
            $table->unsignedTinyInteger('status')->default(0)->comment('状态((0-待审核,1-待发放,2-已发放,3-审核拒绝))');
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
        Schema::dropIfExists('withdrawals');
    }
}
