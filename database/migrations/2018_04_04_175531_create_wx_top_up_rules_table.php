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
        $this->initData();
    }

    public function initData()
    {
        $time = \Carbon\Carbon::now()->toDateTimeString();
        $data = [
            [
                'amount' => 100,
                'give' => 0,
                'first_give' => 50,
                'price' => 80,
                'remark' => '100张',
                'created_at' => $time
            ],
            [
                'amount' => 300,
                'give' => 50,
                'first_give' => 50,
                'price' => 240,
                'remark' => '300张+赠送50张',
                'created_at' => $time
            ],
            [
                'amount' => 1000,
                'give' => 200,
                'first_give' => 50,
                'price' => 800,
                'remark' => '1000张+赠送200张',
                'created_at' => $time
            ],
            [
                'amount' => 2000,
                'give' => 500,
                'first_give' => 50,
                'price' => 1600,
                'remark' => '2000张+赠送500张',
                'created_at' => $time
            ],
        ];
        DB::table('wx_top_up_rules')->insert($data);
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
