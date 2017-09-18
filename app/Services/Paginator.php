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
    protected $per_page = 15;   //每页数据
    protected $page = 1;        //当前页

    public function __construct($per_page = null, $page = null)
    {
        $this->per_page = $per_page ?: $this->per_page;
        $this->page = $page ?: $this->page;
    }

    //给数组分页
    public function paginate($data)
    {
        $offset = $this->per_page * ($this->page - 1);
        $currentPageData = array_slice($data, $offset, $this->per_page);
        $paginatedData = new LengthAwarePaginator($currentPageData, count($data), $this->per_page, $this->page);
        return $paginatedData;
    }
}