<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWxTopUpRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wx_top_up_rules', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('amount')->default(0)->comment('购买数量');
            $table->unsignedInteger('give')->default(0)->comment('赠送数量');
            $table->unsignedInteger('first_give')->default(0)->comment('首次赠送(百分比%)');
            $table->unsignedInteger('price')->comment('价格');
            $table->string('remark')->nullable()->comment('备注');
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
        Schema::dropIfExists('wx_top_up_rules');
    }
}
