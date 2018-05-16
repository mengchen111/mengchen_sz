<?php
/**
 * 游戏房间options相关方法
 */

namespace App\Services\Game;

use App\Traits\GameTypeMap;
use App\Traits\GameRulesMap;

class GameOptionsService
{
    use GameTypeMap;
    use GameRulesMap;

    //将游戏的options格式化成前端可阅读格式
    public function formatOptions($options, $gameType)
    {
        ksort($options);
        //获取选项的分类key，分类的值为填充为''
        $rules = array_fill_keys(array_keys($this->gameRules[$gameType]), '');

        array_walk($options, function ($v, $k) use (&$rules, $gameType) {
            //过滤此玩法不可用的options
            if (! in_array($k, $this->gameTypeAvailableRules[$gameType])) {
                return;
            }

            foreach ($this->gameRules[$gameType] as $categoryKey => $categoryValue) {
                if ($categoryKey === 'wanfa') {
                    if (array_key_exists($k, $categoryValue['options'])) {
                        //玩法的值为false或0，那么此玩法就没启用，就不显示
                        $rules[$categoryKey] .= empty($v) ? '' : $categoryValue['options'][$k] . ',';
                    }
                } else {
                    if ($k === $categoryValue['key']) {
                        if ($k === 2) {     //局数直接显示值，不翻译成中文，因为有的局数不包含在choices中
                            $rules[$categoryKey] = $v;
                        } else {
                            $rules[$categoryKey] = isset($categoryValue['choices'])
                                //如果存在此分类下面存在choices，则显示对应的choice的中文解释，否则显示此choice的值
                                ? $categoryValue['choices'][$v] : $v;
                        }
                    }
                }
            }
        });

        $rules = array_filter($rules);  //过滤值为空的分类
        return $rules;
    }

    //获取某一玩法的选项（格式化前端通用的json格式）
    public function getCategoricalOption($typeId)
    {
        $categoricalOptions = [];
        $availableOptions = $this->gameTypeAvailableRules[$typeId];
        foreach ($availableOptions as $optionKey) {
            foreach ($this->gameRules[$typeId] as $categoryKey => $categoryValue) {
                if ($categoryKey === 'wanfa') {
                    if (array_key_exists($optionKey, $categoryValue['options'])) {
                        //options表示此'wanfa'分类下面的值是可以勾选的，options是数组
                        $categoricalOptions[$categoryKey]['options'][] = $categoryValue['options'][$optionKey];
                        $categoricalOptions[$categoryKey]['name']= $categoryValue['name'];
                    }
                } else {
                    if ($optionKey === $categoryValue['key']) {
                        $categoricalOptions[$categoryKey]['name']= $categoryValue['name'];
                        if (isset($categoryValue['choices'])) {
                            //choice说明此分类的玩法是排他选项
                            $categoricalOptions[$categoryKey]['choices'] = $categoryValue['choices'];
                        } else {
                            //此玩法分类是可以直接输入数值的
                            $categoricalOptions[$categoryKey]['value'] = 0;
                        }
                    }
                }
            }
        }
        return $categoricalOptions;
    }

    /**
     * @param array $data
     * @return array
     *
     * 将前端发送过来的统一的json格式选项反解成游戏端可识别的数据
     */
    public function convertCategoricalOption2GameOption($data, $gameType)
    {
        $gameOptions = [];
        $data = array_intersect_key($data, $this->gameRules[$gameType]);
        foreach ($data as $categoryName => $value) {
            if ($categoryName === 'wanfa') {
                foreach ($value as $wanfaOption) {
                    $wanfaOptionKey = array_search($wanfaOption, $this->gameRules[$gameType]['wanfa']['options']);
                    $gameOptions[$wanfaOptionKey] = 1;
                }
            } else {
                $optionKey = $this->gameRules[$gameType][$categoryName]['key'];
                $gameOptions[$optionKey] = (int) $value;
            }
        }
        ksort($gameOptions);
        return $gameOptions;
    }
}