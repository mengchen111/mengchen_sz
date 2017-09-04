<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateInventoryTable extends Migration
{
    protected $adminInitStock = [
        [
            'id' => 1,
            'user_id' => 1,
            'item_id' => 1, //1房卡，2金币
            'stock' => 10000,
        ],
        [
            'id' => 2,
            'user_id' => 1,
            'item_id' => 2, //1房卡，2金币
            'stock' => 10000,
        ]
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->comment('代理商id');
            $table->unsignedInteger('item_id')->comment('道具类型id');
            $table->unsignedInteger('stock')->comment('库存');
            $table->timestamps();
        });

        DB::table('inventory')->insert($this->adminInitStock);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory');
    }
}
