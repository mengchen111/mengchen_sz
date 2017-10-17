<?php

namespace Tests\Unit;

use App\Services\Paginator;
use Faker\Factory as Faker;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PaginatorServiceTest extends TestCase
{
    public function testPaginate()
    {
        $words = Faker::create()->words(100);
        $res = Paginator::paginate($words)->toArray();
        $this->assertArraySubset([
            'total' => 100,
            'per_page' => 15,
            'current_page' => 1,
        ], $res);
        $this->assertArrayHasKey('data', $res);

        $res = Paginator::paginate($words, 10, 3)->toArray();
        $this->assertArraySubset([
            'total' => 100,
            'per_page' => 10,
            'current_page' => 3,
        ], $res);
        $this->assertArrayHasKey('data', $res);
    }
}
