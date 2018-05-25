<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWxOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wx_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->comment('订单创建者id');
            $table->unsignedSmallInteger('wx_top_up_rule_id')->comment('充值规则id');
            $table->string('out_trade_no')->comment('内部订单号');
            $table->string('spbill_create_ip')->nullable()->comment('终端IP');
            $table->unsignedInteger('total_fee')->comment('金额 单位(分)');
            $table->string('body')->comment('说明');
            //1-内部订单创建成功,
            //2-预支付订单创建成功
            //3-预支付订单创建失败
            //4-支付成功
            //5-支付失败
            //6-取消订单成功
            //7-取消订单失败
            $table->unsignedTinyInteger('order_status')->default(1)->comment('订单状态(1-内部订单创建成功,2-预支付订单创建成功,3-预支付订单创建失败,4-支付成功,5-支付失败,6-取消订单成功,7-取消订单失败)');
            $table->string('order_err_msg')->nullable()->comment('支付过程中的错误信息');
            $table->string('prepay_id')->nullable()->comment('微信预支付id');
            $table->string('transaction_id')->nullable()->comment('微信支付订单号');
            $table->string('code_url')->nullable()->comment('二维码链接');
            $table->string('open_id')->nullable();
            $table->timestamp('paid_at')->nullable()->comment('支付时间');
            $table->unsignedTinyInteger('is_first_order')->default(0)->comment('是否是首单 0:不是,1:是');
            $table->unsignedTinyInteger('item_delivery_status')->default(0)->comment('是否发放了房卡0:无，1有');
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
        Schema::dropIfExists('wx_orders');
    }
}
