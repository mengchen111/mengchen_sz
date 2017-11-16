<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\AuthorizationMap;

class AdminRequest extends FormRequest
{
    use AuthorizationMap;

    protected $viewPrefix = 'admin';    //管理员后台的uri前缀
    protected $uris = [];

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = $this->user();
        abort_if($user->is_agent, 403);
        abort_if($this->ifNotAllowViewAccess($user), 403);
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }

    protected function ifNotAllowViewAccess($user)
    {
        if ($user->is_admin) {
            return false;
        }

        $allViews = $this->formatData($this->view);
        $userViewAccess = json_decode($user->group->view_access, true);
        $allowedViews = is_array($userViewAccess)
            ? $this->formatData($userViewAccess)
            : $allowedViews = [];
        $notAllowedViews = array_diff($allViews, $allowedViews);

        //如果访问的uri在不允许访问的列表中，则限制之，否则通过
        if (in_array($this->path(), $notAllowedViews)) {
            return true;
        }
        return false;
    }

    //将view数据转换成uri形式的数组
    protected function formatData(Array $data)
    {
        $uri = $this->viewPrefix . '/';
        $this->uris = [];       //递归出来的结果，递归之前清空之
        $this->recursiveTransform($data, $uri);
        return $this->uris;
    }

    //递归将数组形式的数据转换成uri形式的数据，并push到数组里面
    protected function recursiveTransform(Array $data, $uri)
    {
        foreach ($data as $upperLevel => &$lowerLevel) {
            if ($lowerLevel['ifShown']) {
                $uri .= $upperLevel . '/';
            } else {
                continue;
            }
            unset($lowerLevel['ifShown']);
            if (! empty($lowerLevel)) {     //如果还存在二级菜单
                $this->recursiveTransform($lowerLevel, $uri);
            } else {
                $this->uris[] = trim($uri, '/');
            }
            $uri = substr($uri, 0, -strlen($upperLevel . '/'));
        }
    }
}
