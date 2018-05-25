<?php

namespace App\Http\Controllers;

use App\Models\OperationLogs;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

/**
 * @SWG\Swagger(
 *     host=L5_SWAGGER_CONST_HOST,
 *     schemes={"http"},
 *     consumes={"application/json"},
 *
 *     @SWG\Info(
 *         version="1.0.0",
 *         title="梦晨深圳",
 *         description="梦晨深圳接口",
 *         @SWG\Contact(name="Dian"),
 *     ),
 *
 *     @SWG\Definition(
 *         definition="Success",
 *         type="object",
 *         @SWG\Property(
 *             property="code",
 *             description="返回码，成功为-1",
 *             type="integer",
 *             format="int32",
 *             default="-1",
 *         ),
 *         @SWG\Property(
 *             property="message",
 *             description="消息",
 *             type="string",
 *             example="操作成功",
 *         ),
 *     ),
 *     @SWG\Definition(
 *         definition="ValidationError",
 *         description="key为验证失败的参数名, 值为所有验证失败的条目(数组)",
 *         type="object",
 *         @SWG\Property(
 *             property="name",
 *             example={"name 不能大于 1 个字符", "name 应该为字母"},
 *             type="array",
 *             @SWG\Items(
 *                 type="string",
 *                 description="参数验证失败详情",
 *             ),
 *         ),
 *     ),
 *     @SWG\Definition(
 *         definition="CreatedAtUpdatedAt",
 *         type="object",
 *         @SWG\Property(
 *             property="created_at",
 *             description="创建时间",
 *             type="string",
 *             example="2018-03-30 16:03:14",
 *         ),
 *         @SWG\Property(
 *             property="updated_at",
 *             description="更新时间",
 *             type="string",
 *             example="2018-03-30 17:14:42",
 *         ),
 *     ),
 * )
 */
require app_path('Swagger/PlayerDefinition.php');   //载入玩家模型的swagger定义
require app_path('Swagger/PaginationParamsDefinition.php'); //分页通用参数

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
            'code' => -1,
            'message' => $msg,
        ];
    }
}
