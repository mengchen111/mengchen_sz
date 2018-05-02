<?php

namespace App\Http\Controllers;

use App\Models\OperationLogs;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $per_page = 15;
    protected $page = 1;
    protected $order = ['id', 'desc'];

    public function __construct(Request $request)
    {
        $this->per_page = $request->per_page ?: $this->per_page;
        $this->order = $request->sort ? explode('|', $request->sort) : $this->order;
        $this->page = $request->page ?: $this->page;
    }

    /**
     * 添加操作日志
     * @param string $message
     */
    public function addLog($message = '')
    {
        $userId = empty(request()->user()) ? 0 : request()->user()->id;
        OperationLogs::add($userId, request()->path(), request()->method(),
            $message, request()->header('User-Agent'), json_encode(request()->all()));
    }

    public function res($msg)
    {
        return [
            //'code' => -1,
            'message' => $msg,
        ];
    }
}
