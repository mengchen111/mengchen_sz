<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $dbMigrated = false;          //数据库migrate是否完成

    public function setUp()
    {
        parent::setUp();

        if (! $this->dbMigrated) {          //每一个测试类只会migrate一次数据库
            $this->dbMigrated = true;
            Artisan::call('migrate:refresh');
        }
    }

    public function tearDown()
    {
    }
}
