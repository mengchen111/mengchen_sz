<?php

namespace App\Traits;

trait GameTypeMap
{
    /**
     * 获取所有游戏分组id
     * @return array
     */
    public function getGameGroupIds()
    {
        return array_keys($this->gameGroups);
    }

    public function getGameTypeIds()
    {
        return array_keys($this->gameTypes);
    }

    /**
     * 获取游戏组id和分组名字的映射表
     * @return array
     */
    public function getGameGroupIdNameMap()
    {
        $gameGroupIdNameMap = [];
        foreach ($this->gameGroups as $id => $item) {
            $gameGroupIdNameMap[$id] = $item['name'];
        }
        return $gameGroupIdNameMap;
    }

    //麻将类型映射(广东麻将里面的玩法类型，不是kind，对应的是options里面的key1(房间类型))
    protected $gameTypes = [
        1 => '广东',     //广东庄
        2 => '清远',     //清远庄
        3 => '赣州',
        4 => '惠州',
        5 => '景德镇',
        6 => '淡水',
        7 => '惠东',
        8 => '潮汕',
        9 => '普宁',
        10 => '揭阳',
        11 => '万年',
    ];

    //游戏包（每个包包含不同的游戏类型）
    protected $gameGroups = [
        1 => [
            'name' => '广东',
            'game_types' => [1,2,4,6,7,8,9,10],
        ],
        2 => [
            'name' => '江西',
            'game_types' => [5,11],
        ],
    ];

    //每种麻将可用的选项
    protected $gameTypeAvailableRules = [
        1 => [2,37,17,30,10,11,14,31,32,33,34,35,13,28,36,],
        2 => [2,37,17,50,10,11,33,51,34,],
        4 => [2,3,10,11,12,13,14,15,16,17,18],
        5 => [2,3,20,21,22,23,24,25],
        6 => [2,3,10,11,12,13,16,17,18],
        7 => [2,3,10,11,12,13,16,17,18],
        8 => [2,16,17,30,50,12,33,55,27,13,32,35,53,52,54],
        9 => [2,3,33,55,32,59,57,58,34,51,37,17,63,54],
        10 => [2,3,16,17,12,33,55,27,32,35,58,57,59,60,54],
        11 => [2,3,64,65],
    ];
}