<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 9/5/17
 * Time: 14:46
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StockApply;
use Illuminate\Support\Facades\Validator;

class StockController extends Controller
{
    public function apply(Request $request)
    {
        $data = $this->validateApply($request);
        $data = array_merge($data, ['applicant_id' => $request->user()->id]);

        StockApply::create($data);

        return ['message' => '提交申请成功'];
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