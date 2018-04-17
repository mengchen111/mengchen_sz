<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    protected $defaultUser = [
        'id' => 1,
        'name' => 'admin',
        'account' => 'admin',
        'password' => '$2y$10$j/GVCtP1ydEn3ApfJueDm.XKzVFTIooksLoaWXUlDHdl7sMSZoEiC',
        'group_id' => '1',
        'parent_id' => '-1',
    ];

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
            $table->string('account', 190)->unique();
            $table->string('password');
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->unsignedInteger('group_id')->default(0)->comment('所属组');
            $table->integer('parent_id')->default(0)->comment('上级代理');
            $table->rememberToken();
            $table->timestamps();
        });
        
        DB::table('users')->insert($this->defaultUser);
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
