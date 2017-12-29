<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 11/3/17
 * Time: 16:58
 */

namespace App\Services\Game;


trait MajiangTypeMap
{
    //麻将类型映射
    protected $maJiangTypes = [
        1 => '推到胡',     //广东庄
        2 => '100张',     //清远庄
        3 => '赣州庄',
        4 => '惠州庄',
        5 => '景德镇庄',
        6 => '淡水庄',
        7 => '惠东庄',
    ];

    //每种麻将可用的选项
    protected $maJiangtypeOptions = [
        '4' => [2,3,10,11,12,13,14,15,16,17,18],
        '6' => [2,3,10,11,12,16,17,18],
        '7' => [2,3,10,11,12,16,17,18],
    ];
}