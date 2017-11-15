<?php

namespace App\Http\Controllers\Admin;

trait AuthorizationMap
{
    protected $view = [
        'home' => [
            'ifShown' => true,      //首页默认都展示
        ],
        'statement' => [
            'ifShown' => false,
            'summary' => [
                'ifShown' => false,
            ],
        ],
        'gm' => [
            'ifShown' => false,
            'room' => [
                'ifShown' => false,
            ],
            'record' => [
                'ifShown' => false,
            ],
        ],
        'player' => [
            'ifShown' => false,
            'list' => [
                'ifShown' => false,
            ],
        ],
        'stock' => [
            'ifShown' => false,
            'apply-request' => [
                'ifShown' => false,
            ],
            'apply-list' => [
                'ifShown' => false,
            ],
            'apply-history' => [
                'ifShown' => false,
            ],
        ],
        'agent' => [
            'ifShown' => false,
            'create' => [
                'ifShown' => false,
            ],
            'list' => [
                'ifShown' => false,
            ],
        ],
        'top-up' => [
            'ifShown' => false,
            'admin' => [
                'ifShown' => false,
            ],
            'agent' => [
                'ifShown' => false,
            ],
            'player' => [
                'ifShown' => false,
            ],
        ],
        'system' => [
            'ifShown' => false,
            'log' => [
                'ifShown' => false,
            ],
        ],
    ];

    protected $api = [
        //TODO 思考何种数据格式可以合理的对uri和方法做权限控制
    ];
}