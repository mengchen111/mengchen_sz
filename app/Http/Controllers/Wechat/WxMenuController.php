<?php

namespace App\Http\Controllers\Wechat;

use App\Http\Requests\AdminRequest;
use EasyWeChat\Foundation\Application;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WxMenuController extends Controller
{
    protected $menu;

    public function __construct(Application $app)
    {
        parent::__construct(new Request());
        $this->menu = $app->menu;

    }

    public function menus()
    {
        return [
            [
                "type" => "view",
                "name" => "游戏下载",
                "key"  => "https://yymj.max78.com/casino/web/index.php?package=yyjdzmj&kind=4"
            ],
            [
                "type" => "view",
                "name" => "后台",
                "key"  => "https://www.baidu.com"
            ],
        ];
    }
    public function store(AdminRequest $request)
    {
        return $this->menu->add($this->menus());
    }
}
