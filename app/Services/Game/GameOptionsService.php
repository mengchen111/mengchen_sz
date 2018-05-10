<?php
/**
 * 游戏房间options相关方法
 */

namespace App\Services\Game;

use App\Traits\MajiangTypeMap;
use App\Traits\MaJiangOptionsMap;

class GameOptionsService
{
    use MajiangTypeMap;
    use MaJiangOptionsMap;

    //将游戏的options格式化成前端可阅读格式
    public function formatOptions($options)
    {
        ksort($options);
        $rules = array_fill_keys(array_keys($this->maJiangOptionsMap), '');

        array_walk($options, function ($v, $k) use (&$rules) {
            foreach ($this->maJiangOptionsMap as $category => $categoryOptions) {
                if (array_key_exists($k, $categoryOptions)) {
                    if ((! empty($v)) or $k == 16) {    //无鬼补花类型值可能为0（选项的值不为0或false，或者选项的key为16时才格式化数据）
                        if (is_array($categoryOptions[$k])) {
                            $rules[$category] .= "{$categoryOptions[$k]['name']}: {$categoryOptions[$k]['options'][$v]},";
                        } else {
                            if ($category === 'ma_pai') {
                                $rules[$category] = $v;      //买了多少马
                            } elseif ($category === 'di_fen') {
                                $rules[$category] = $v;      //底分多少
                            } else {
                                $rules[$category] .= "{$categoryOptions[$k]},";
                            }
                        }
                    }
                }
            }
        });

        return $rules;
    }
}