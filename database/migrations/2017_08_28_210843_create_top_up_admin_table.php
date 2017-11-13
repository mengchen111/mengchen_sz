<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTopUpAdminTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('top_up_admin', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('provider_id')->comment('发起充值者id');
            $table->unsignedInteger('receiver_id')->comment('充值到账的代理商id');
            $table->string('type')->default('1')->comment('道具类型');
            $table->integer('amount')->comment('充值数量');
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
        Schema::dropIfExists('top_up_admin');
    }
}
