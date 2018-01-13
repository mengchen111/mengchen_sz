<?php

namespace App\Services;

use App\Exceptions\WechatServiceException;
use GuzzleHttp;

class WechatService
{
    protected static $wechatApiBaseUrl = 'https://api.weixin.qq.com/';

    protected static function httpClient()
    {
        return new GuzzleHttp\Client([
            'base_uri' => self::$wechatApiBaseUrl,
            'connect_timeout' => 5,
        ]);
    }

    public static function getUnionId($token, $openId)
    {
        $getUnionIdUri = 'cgi-bin/user/info';
        $params = [
            'access_token' => $token,
            'openid' => $openId,
        ];
        $response = self::httpClient()->request('GET', $getUnionIdUri, [
            'query' => $params,
        ])
            ->getBody()
            ->getContents();
        $response = json_decode($response, true);
        self::checkResponse($response);
        return $response;
    }

    protected static function checkResponse($res)
    {
        if (isset($res['errcode'])) {
            throw new WechatServiceException('微信接口调用，返回结果错误：' . json_encode($res));
        }
        return true;
    }
}