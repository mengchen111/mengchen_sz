<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWxRedPacketLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wx_red_packet_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('log_redbag_id')->comment('游戏端红包发送记录表的id');
            $table->integer('player_id')->comment('玩家id号');
            $table->string('nickname')->comment('玩家昵称');
            $table->string('unionid')->comment('unionid');
            $table->string('mch_billno')->comment('商户订单号');
            $table->string('send_name')->comment('商户名称');
            $table->string('re_openid')->comment('用户openid');
            $table->tinyInteger('total_num')->default(1)->comment('红包发放总人数');
            $table->integer('total_amount')->default(100)->comment('付款金额-分');
            $table->string('wishing')->comment('祝福语');
            $table->string('client_ip')->comment('调用接口的机器Ip地址');
            $table->string('act_name')->comment('活动名称');
            $table->string('remark')->comment('备注');
            $table->tinyInteger('send_status')->default(0)->comment('红包发送状态(0-待发送,1-已发送,2-发送失败,3-已补发)');
            $table->text('error_message')->nullable()->comment('错误消息');
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
        Schema::dropIfExists('wx_red_packet_log');
    }
}
