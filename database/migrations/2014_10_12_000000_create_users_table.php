<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('account')->unique();
            $table->string('password');
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->unsignedInteger('group_id')->default(0)->comment('所属组');
            $table->integer('parent_id')->default(0)->comment('上级代理');
            $table->unsignedBigInteger('cards')->default(0)->comment('房卡数');
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
