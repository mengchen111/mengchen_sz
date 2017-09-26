<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 9/25/17
 * Time: 16:40
 */

namespace App\Services;

use GuzzleHttp;
use App\Exceptions\GameServerException;

class GameServerSz
{
    protected $apiAddress;
    protected $partnerId;
    protected $guzzle;      //guzzle client

    public function __construct()
    {
        $this->apiAddress = 'https://down.yxx.max78.com/casino/back/htmls/agentx/';
        $this->partnerId = '123456789';
        $this->guzzle = new GuzzleHttp\Client([
            'base_uri' => $this->apiAddress,
            'timeout' => 5,
        ]);
    }

    protected function buildSign(Array $params)
    {
        ksort($params);

        $sign = '';
        array_walk($params, function ($v, $k) use (&$sign) {
            $sign .= "{$k}={$v}&";
        });
        $sign .= "key={$this->partnerId}";
        $sign = strtoupper(md5($sign));

        return $sign;
    }

    public function request($method, $uri, Array $params = null)
    {
        switch ($method) {
            case 'GET':
                return $this->getData($uri, $params);
                break;
            case 'POST':
                return $this->postData($uri, $params);
                break;
            default:
                throw new GameServerException('[request] method无效');
        }
    }

    protected function getData($uri, Array $params = null)
    {
        try {
            $res = $this->guzzle->request('GET', $uri, [
                'query' => $params,
            ])
                ->getBody()
                ->getContents();
        } catch (\Exception $exception) {
            throw new GameServerException('[getData] 调用游戏服接口失败：' . $exception->getMessage(), $exception);
        }

        $result = $this->decodeResponse($res);

        $this->checkResult($result);

        return $result;
    }

    protected function postData($uri, Array $params = null)
    {
        $params = array_merge($params, [
            'sign' => $this->buildSign($params)
        ]);

        try {
            $res = $this->guzzle->request('POST', $uri, [
                'form_params' => $params,
            ])
                ->getBody()
                ->getContents();
        } catch (\Exception $exception) {
            throw new GameServerException('[postData] 调用游戏服接口失败：' . $exception->getMessage(), $exception);
        }

        $result = $this->decodeResponse($res);

        $this->checkResult($result);

        return $result;
    }

    protected function decodeResponse($res)
    {
        return json_decode(base64_decode($res), true);
    }

    protected function checkResult($result)
    {
        if ($result['code'] < 0) {
            throw new GameServerException('[checkResult] 调用接口成功，但是游戏服返回的结果错误：' . json_encode($result));
        }
        return true;
    }
}