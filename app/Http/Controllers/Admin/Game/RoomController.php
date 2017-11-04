<?php

namespace App\Http\Controllers\Admin\Game;

use App\Services\Game\MaJiangOptionsMap;
use App\Services\Game\MajiangTypeMap;
use Illuminate\Http\Request;
use App\Http\Requests\AdminRequest;
use App\Http\Controllers\Controller;

class RoomController extends Controller
{
    use MajiangTypeMap;
    use MaJiangOptionsMap;

    protected $availableRoomType = [
          4, 6, 7,
    ];

    public function create(Request $request)
    {
        return 'create';
    }

    //获取可创建的房间类型
    public function getRoomType(Request $request)
    {
        $roomType = [];
        foreach ($this->availableRoomType as $typeId) {
            $maJiangTypeSortedOptions = [];     //将可用的玩法选项归类（wanfa，gui_pai, ma_pai）
            $maJiangTypeName = $this->maJiangTypes[$typeId];
            $maJiangTypeOption = $this->maJiangtypeOptions[$typeId];
            $maJiangTypeSortedOptions['wanfa'] = $this->getWanFa($maJiangTypeOption);  //获取可用玩法列表
            $maJiangTypeSortedOptions['gui_pai'] = $this->getGuiPai($maJiangTypeOption);    //获取可用鬼牌玩法
            $maJiangTypeSortedOptions['ma_pai'] = $this->getMaPai($maJiangTypeOption);   //获取马牌玩法
            array_push($roomType, [
                $maJiangTypeName => $maJiangTypeSortedOptions,
            ]);
        }
        return $roomType;
    }

    protected function getWanfa($options)
    {
        $wanFas = [];   //可选玩法
        array_walk($options, function ($option) use (&$wanFas) {
            if (array_key_exists($option, $this->maJiangOptionsMap['wanfa'])) {
                array_push($wanFas, $this->maJiangOptionsMap['wanfa'][$option]);
            }
        });
        return $wanFas;
    }

    protected function getGuiPai($options)
    {
        $guiPais = [];   //可选鬼牌
        array_walk($options, function ($option) use (&$guiPais) {
            if (array_key_exists($option, $this->maJiangOptionsMap['gui_pai'])) {
                array_push($guiPais, [
                    $this->maJiangOptionsMap['gui_pai'][$option]['name'] => $this->maJiangOptionsMap['gui_pai'][$option]['options']
                ]);
            }
        });
        return $guiPais;
    }

    protected function getMaPai($options)
    {
        $maPai = [];   //可选玩法
        array_walk($options, function ($option) use (&$maPai) {
            if (array_key_exists($option, $this->maJiangOptionsMap['ma_pai'])) {
                array_push($maPai, $this->maJiangOptionsMap['ma_pai'][$option]);
            }
        });
        return $maPai;
    }
}
