<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCommunityConfTable extends Migration
{
    protected $defaultConf = [
        'community_id' => 0,
        'player_id' => 0,
        'max_community_count' => 5,
        'max_member_count' => 500,
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('community_conf', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('community_id')->nullable()->unique()->comment('社团id(0为默认配置)');
            $table->unsignedInteger('player_id')->nullable()->unique()->comment('玩家id(0为默认配置)');
            $table->unsignedInteger('max_community_count')->comment('最大社团数');
            $table->unsignedInteger('max_member_count')->comment('最大成员数');
            $table->timestamps();
        });

        DB::table('community_conf')->insert($this->defaultConf);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('community_conf');
    }
}
