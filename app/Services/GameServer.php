<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 9/25/17
 * Time: 18:12
 */

namespace App\Services;

use GuzzleHttp;
use App\Exceptions\GameServerException;

class GameServer
{
    protected $apiAddress;
    protected $guzzle;      //guzzle client

    public function __construct($apiAddress, $guzzleHandler = null)
    {
        $this->apiAddress = $apiAddress;
        $this->guzzle = new GuzzleHttp\Client([
            'timeout' => 5,
            'handler' => $guzzleHandler,
        ]);
    }

    public function request($method, Array $params = null)
    {
        switch ($method) {
            case 'GET':
                return $this->getData($params);
                break;
            case 'POST':
                return $this->postData($params);
                break;
            default:
                throw new GameServerException('method无效');
        }
    }

    protected function getData($params = null)
    {
        try {
            $res = $this->guzzle->request('GET', $this->apiAddress, [
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

    protected function postData($params = null)
    {
        try {
            $res = $this->guzzle->request('POST', $this->apiAddress, [
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

    protected function checkResult($result)
    {
        if (empty($result['result'])) {
            throw new GameServerException('调用接口成功，但是游戏服返回的结果错误：' . json_encode($result));
        }
        return true;
    }

    protected function decodeResponse($res)
    {
        return json_decode($res, true);
    }
}