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
        1 => '广东庄',
        2 => '清远庄',
        3 => '赣州庄',
        4 => '惠州庄',
        5 => '景德镇庄',
        6 => '淡水庄',
        7 => '惠东庄',
    ];

    protected $maJiangtypeOptions = [
        '惠州庄' => [2,3,10,11,12,13,14,15,16,17,18],
        '淡水庄' => [2,3,10,11,12,16,17,18],
        '惠东庄' => [2,3,10,11,12,16,17,18],
    ];
}