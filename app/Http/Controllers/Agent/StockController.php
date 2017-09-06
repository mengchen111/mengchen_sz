<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 9/6/17
 * Time: 10:03
 */

namespace App\Http\Controllers\Agent;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\StockApply;
use App\Models\OperationLogs;
use Illuminate\Support\Facades\Validator;

class StockController extends Controller
{
    protected $per_page = 15;
    protected $order = ['id', 'desc'];

    public function __construct(Request $request)
    {
        $this->per_page = $request->per_page ?: $this->per_page;
        $this->order = $request->sort ? explode('|', $request->sort) : $this->order;
    }

    /**
     * 总代提交库存申请
     *
     * @param Request $request
     * @return array
     */
    public function apply(Request $request)
    {
        $data = $this->validateApply($request);

        if (! $this->isValidApplicant($request->user())) {
            return ['error' => '提交库存申请失败，只有总代能提交申请' ];
        }

        $data = array_merge($data, ['applicant_id' => $request->user()->id]);

        StockApply::create($data);

        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '总代理申请库存', $request->header('User-Agent'), json_encode($data));

        return ['message' => '提交申请成功'];
    }

    protected function isValidApplicant($applicant)
    {
        return 2 == $applicant->group->id;
    }

    protected function validateApply($request)
    {
        Validator::make($request->all(), [
            'item_id' => 'required|integer|exists:item_type,id',
            'amount' => 'required|integer',
            'remark' => 'nullable|string|max:255'
        ])->validate();

        return $request->intersect(
            'item_id', 'amount', 'remark'
        );
    }

    /**
     * 总代查看库存申请历史
     *
     * @param Request $request
     * @return null
     */
    public function applyHistory(Request $request)
    {
        OperationLogs::add($request->user()->id, $request->path(), $request->method(),
            '总代理查看申请记录', $request->header('User-Agent'));

        //搜索申请人账号(前端暂时屏蔽了搜索条)
        if ($request->has('filter')) {
            $applicants = array_column(User::where('account', 'like', "%{$request->filter}%")->get()->toArray(), 'id');
            if (empty($applicants)) {
                return null;
            }
            return StockApply::with(['applicant', 'approver', 'item'])
                ->whereIn('applicant_id', $applicants)
                ->where('applicant_id', $request->user()->id)   //总代只能查看自己的申请记录
                ->orderBy($this->order[0], $this->order[1])
                ->paginate($this->per_page);
        }

        return StockApply::with(['applicant', 'approver', 'item'])
            ->where('applicant_id', $request->user()->id)
            ->orderBy($this->order[0], $this->order[1])
            ->paginate($this->per_page);
    }
}