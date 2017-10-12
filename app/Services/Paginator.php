<?php

/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 9/18/17
 * Time: 15:35
 */

namespace App\Services;

use Illuminate\Pagination\LengthAwarePaginator;

class Paginator
{
    //给数组分页
    public static function paginate(Array $data, $per_page = 15, $page = 1)
    {
        $offset = $per_page * ($page - 1);
        $currentPageData = array_slice($data, $offset, $per_page);
        $paginatedData = new LengthAwarePaginator($currentPageData, count($data), $per_page, $page);
        return $paginatedData;
    }
}