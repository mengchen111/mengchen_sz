<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 10/17/17
 * Time: 18:37
 */

namespace App\Services\Game;

use App\Exceptions\GameApiServiceException;
use BadMethodCallException;
use GuzzleHttp;

class GameApiService
{
    public static function __callStatic($name, $arguments)
    {
        switch ($name) {
            case 'gameApi':
                return config('custom.game_api_address');
                break;
            case 'gameApiKey':
                return config('custom.game_api_key');
                break;
            case 'gameApiSecret':
                return config('custom.game_api_secret');
                break;
            default:
                throw new BadMethodCallException('Call to undefined method ' . self::class . "::${name}()");
        }
    }

    protected static function httpClient()
    {
         return new GuzzleHttp\Client([
             'base_uri' => self::gameApi(),
             'connect_timeout' => 5,
             'http_errors' => false,      //4xx和5xx不抛异常
             'headers' => [
                 'Accept' => 'application/json',
             ],
         ]);
    }

    protected static function buildSign(Array $params)
    {
        ksort($params);                             //将参数按照字母表升序排序

        //构建签名字符串
        $sign = '';
        array_walk($params, function ($v, $k) use (&$sign) {
            $sign .= "{$k}={$v}&";
        });
        //将api_secret参数append到签名字符串的末尾
        $sign .= 'api_secret=' . self::gameApiSecret();
        //将签名字符串使用md5转码，转码完成再将其字母转为大写
        $sign = strtoupper(md5($sign));

        return $sign;
    }

    protected static function buildParams(Array $params)
    {
        $params['api_key'] = self::gameApiKey();
        $sign = self::buildSign($params);
        $params['sign'] = $sign;
        return $params;
    }

    public static function request($method, $uri, Array $params = [])
    {
        switch ($method) {
            case 'GET':
                return self::getData($uri, $params);
                break;
            case 'POST':
                return self::postData($uri, $params);
                break;
            default:
                throw new GameApiServiceException('method无效', config('exceptions.GameApiServiceException'));
        }
    }

    protected static function getData($uri, Array $params = [])
    {
        $params = self::buildParams($params);
        
        try {
            $res = self::httpClient()->request('GET', $uri, [
                'query' => $params,
            ])
                ->getBody()
                ->getContents();
        } catch (\Exception $exception) {
            throw new GameApiServiceException('调用游戏后端接口失败：' . $exception->getMessage()
                , config('exceptions.GameApiServiceException'), $exception);
        }

        $res = json_decode($res, true);
        self::checkResult($res);

        return $res['data'];
    }

    protected static function postData($uri, Array $params = [])
    {
        $params = self::buildParams($params);

        try {
            $res = self::httpClient()->request('POST', $uri, [
                'form_params' => $params,
            ])
                ->getBody()
                ->getContents();
        } catch (\Exception $exception) {
            throw new GameApiServiceException('调用游戏后端接口失败：' . $exception->getMessage()
                , config('exceptions.GameApiServiceException'), $exception);
        }

        $res = json_decode($res, true);
        self::checkResult($res);

        return $res['data'];
    }

    protected static function checkResult($res)
    {
        if (! $res['result']) {
            $msg = is_array($res['errorMsg']) ? json_encode($res['errorMsg'], JSON_UNESCAPED_UNICODE) : $res['errorMsg'];
            throw new GameApiServiceException('调用游戏后端接口成功，但是数据返回格式错误: ' . $msg
                , config('exceptions.GameApiServiceException'));
        }
        return true;
    }
}