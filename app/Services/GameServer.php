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

class GameServer
{
    protected $apiAddress;
    protected $partnerId;
    protected $guzzle;      //guzzle client

    public function __construct($guzzleHandler = null)
    {
        $this->apiAddress = config('custom.game_server_api_address');
        $this->partnerId = config('custom.game_server_partner_id');
        $this->guzzle = new GuzzleHttp\Client([
            'base_uri' => $this->apiAddress,
            'connect_timeout' => 8,
            'handler' => $guzzleHandler,
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

    public function request($method, $uri, Array $params = [])
    {
        switch ($method) {
            case 'GET':
                return $this->getData($uri, $params);
                break;
            case 'POST':
                return $this->postData($uri, $params);
                break;
            default:
                throw new GameServerException('method无效');
        }
    }

    protected function getData($uri, Array $params = [])
    {
        try {
            $res = $this->guzzle->request('GET', $uri, [
                'query' => $params,
            ])
                ->getBody()
                ->getContents();
        } catch (\Exception $exception) {
            throw new GameServerException('调用游戏服接口失败：' . $exception->getMessage(), $exception);
        }

        $result = $this->decodeResponse($res);

        $this->checkResult($result);

        return $result;
    }

    protected function postData($uri, Array $params = [])
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
            throw new GameServerException('调用游戏服接口失败：' . $exception->getMessage(), $exception);
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
        if (empty($result) or $result['code'] < 0) {
            throw new GameServerException('调用接口成功，但是游戏服返回的结果错误：' . json_encode($result));
        }
        return true;
    }
}