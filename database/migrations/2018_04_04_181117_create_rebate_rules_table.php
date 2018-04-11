<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRebateRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rebate_rules', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('price')->comment('金额');
            $table->unsignedDecimal('rate')->comment('返利比例单位 %');
            $table->string('remark')->nullable();
            $table->timestamps();
        });
        $this->initData();
    }

    public function initData()
    {
        $time = \Carbon\Carbon::now()->toDateTimeString();
        $data = [
            [
                'price' => 3000,
                'rate' => 5,
                'remark' => '3000 返利5%',
                'created_at' => $time
            ],
            [
                'price' => 10000,
                'rate' => 10,
                'remark' => '10000 返利10%',
                'created_at' => $time
            ],
            [
                'price' => 30000,
                'rate' => 15,
                'remark' => '30000 返利15%',
                'created_at' => $time
            ],
            [
                'price' => 100000,
                'rate' => 20,
                'remark' => '100000 返利20%',
                'created_at' => $time
            ],
            [
                'price' => 250000,
                'rate' => 25,
                'remark' => '250000 返利25%',
                'created_at' => $time
            ],
        ];
        DB::table('rebate_rules')->insert($data);
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rebate_rules');
    }
}
