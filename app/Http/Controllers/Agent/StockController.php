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
}