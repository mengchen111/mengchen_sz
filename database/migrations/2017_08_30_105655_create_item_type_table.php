<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateItemTypeTable extends Migration
{
    protected $defaultData = [
        [
            'id' => 1,
            'name' => '房卡',
        ],
        [
            'id' => 2,
            'name' => '金币',
        ]
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_type', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->comment('道具类型');
        });

        DB::table('item_type')->insert($this->defaultData);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('item_type');
    }
}
