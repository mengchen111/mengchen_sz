<?php

trait AuthorizationMap
{
    protected $view = [
        'home' => '',
        'statement' => [
            'summary' => '',
        ],
        'gm' => [
            'record' => '',
            'room' => '',
        ],
        'player' => [
            'list' => '',
        ],
        'stock' => [
            'apply-request' => '',
            'apply-list' => '',
            'apply-history' => '',
        ],
        'agent' => [
            'create' => '',
            'list' => '',
        ],
        'top-up' => [
            'admin' => '',
            'agent' => '',
            'player' => '',
        ],
        'system' => [
            'log' => '',
        ],
    ];

    protected $api = [
    ];
}