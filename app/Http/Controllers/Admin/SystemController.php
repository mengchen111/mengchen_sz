<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 9/1/17
 * Time: 09:58
 */

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\AdminRequest;
use App\Models\OperationLogs;
use App\Models\User;

class SystemController extends Controller
{
    protected $per_page = 15;
    protected $order = ['id', 'desc'];

    public function __construct(Request $request)
    {
        $this->per_page = $request->per_page ?: $this->per_page;
        $this->order = $request->sort ? explode('|', $request->sort) : $this->order;
    }

    public function showLog(AdminRequest $request)
    {
        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '查看系统操作日志', $request->header('User-Agent'));

        //搜索用户账号
        if ($request->has('filter')) {
            $users = array_column(User::where('account', 'like', "%{$request->filter}%")->get()->toArray(), 'id');
            if (empty($users)) {
                return null;
            }
            return OperationLogs::with(['user'])
                ->whereIn('user_id', $users)
                ->orderBy($this->order[0], $this->order[1])
                ->paginate($this->per_page);
        }

        return OperationLogs::with(['user'])
            ->orderBy($this->order[0], $this->order[1])
            ->paginate($this->per_page);
    }
}