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
    protected $maJiangType = [
        1 => '广东麻将',
        2 => '清远麻将',
        3 => '赣州麻将',
        4 => '惠州麻将',
        5 => '景德镇麻将',
        6 => '淡水庄麻将',
        7 => '惠东麻将',
    ];

    protected $maJiangtypeOptions = [
        '惠州麻将' => [2,3,10,11,12,13,14,15,16,17,18],
        '淡水庄麻将' => [2,3,10,11,12,16,17,18],
        '惠东麻将' => [2,3,10,11,12,16,17,18],
    ];
}