<?php

trait AuthorizationMap
{
    protected $view = [
        'home' => null,
        'statement' => [
            'summary' => null,
        ],
        'gm' => [
            'record' => null,
            'room' => null,
        ],
        'player' => [
            'list' => null,
        ],
        'stock' => [
            'apply-request' => null,
            'apply-list' => null,
            'apply-history' => null,
        ],
        'agent' => [
            'create' => null,
            'list' => null,
        ],
        'top-up' => [
            'admin' => null,
            'agent' => null,
            'player' => null,
        ],
        'system' => [
            'log' => null,
        ],
    ];

    protected $api = [
        //TODO 思考何种数据格式可以合理的对uri和方法做权限控制
    ];
}