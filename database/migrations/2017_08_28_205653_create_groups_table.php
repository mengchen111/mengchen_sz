<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateGroupsTable extends Migration
{
    protected $defaultData = [
        [
            'id' => 1,
            'name' => '管理员',
        ],
        [
            'id' => 2,
            'name' => '总代理',
        ],
        [
            'id' => 3,
            'name' => '钻石代理',
        ],
        [
            'id' => 4,
            'name' => '黄金代理'
        ]
    ];


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->comment('组名');
            $table->text('uri_access')->nullable()->comment('允许访问的uri');
            $table->text('view_access')->nullable()->comment('允许浏览展示的菜单');
        });

        //插入默认数据
        DB::table('groups')->insert($this->defaultData);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('groups');
    }
}
