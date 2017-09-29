<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 9/28/17
 * Time: 14:11
 * Func: 从老后台迁移代理商数据到新后台下
 */

namespace App\Services;

use App\Models\Inventory;
use App\Models\TopUpAdmin;
use App\Models\TopUpPlayer;
use App\Models\User;
use GuzzleHttp\Client;
use App\Services\GameServer;
use App\Exceptions\CustomException;

class DataMigrator
{
    protected $agentListApi = 'http://hzht.max78.com:81/Admin/Index/dai.html';
    protected $topUp2AgentHistoryApi = 'http://hzht.max78.com:81/Admin/Index/ka.html';
    protected $topUp2PlayerHistoryApi = 'http://hzht.max78.com:81/Admin/Index/dka.html';
    protected $defaultPass = '$2y$10$8.opeLMmot48vhGY8CcuAuAb99zgvEziQn5ZI8zc2fshNDy4CyqLK';    //majiang123
    protected $agentMap = [
        '钻石代理' => 3,
        '金牌代理' => 4,
    ];              //老平台的代理商映射到新后台的代理商id
    protected $httpClient;
    protected $ifLogin = false;

    public function __construct()
    {
        $client = new Client([
            'connect_timeout' => '3',
            'cookies' => true,
        ]);

        $this->httpClient = $client;
    }

    protected function doLogin()
    {
        if (! $this->ifLogin) {
            $this->httpClient->get('http://hzht.max78.com:81/Admin/user/token', [
                'query' => [
                    'username' => 'admin888',
                    'password' => 'mengchen2017',
                ],
            ]);
        }
        $this->ifLogin = true;
    }

    protected function removeBom($text)
    {
        if (preg_match('/^\xEF\xBB\xBF/',$text)) {
            return substr($text, 3);
        }
        return $text;
    }

    public function getAgentList()
    {
        $this->doLogin();
        $res = $this->httpClient->get($this->agentListApi)
            ->getBody()
            ->getContents();
        return json_decode($this->removeBom($res), true);
    }

    public function getTopUp2AgentHistory()
    {
        $this->doLogin();
        $res = $this->httpClient->get($this->topUp2AgentHistoryApi)
            ->getBody()
            ->getContents();
        return json_decode($this->removeBom($res), true);
    }

    public function getTopUp2PlayerHistory()
    {
        $this->doLogin();
        $res = $this->httpClient->get($this->topUp2PlayerHistoryApi)
            ->getBody()
            ->getContents();
        return json_decode($this->removeBom($res), true);
    }

    public function migrateAgentListData()
    {
        $data = $this->getAgentList();
        $data = array_reverse($data);
        $agentList = $this->transAgentList($data);

        foreach ($agentList as $agent) {
            //创建用户
            User::create([
                'id' => $agent['id'],
                'name' => $agent['name'],
                'account' => $agent['account'],
                'password' => $agent['password'],
                'group_id' => $agent['group_id'],
                'parent_id' => $agent['parent_id'],
                'created_at' => $agent['date'],
            ]);
            echo "create user {$agent['account']} done." . PHP_EOL;

            //创建库存
            array_walk($agent['stock_amount'], function ($v, $k) use ($agent) {
                Inventory::create([
                    'user_id' => $agent['id'],
                    'item_id' => $k,
                    'stock' => $v,
                ]);

                echo "create inventory for {$agent['account']} done." . PHP_EOL;
            });
        }
    }

    protected function transAgentList($data)
    {
        $initId = 500;      //初始ID
        foreach ($data as $k => $entry) {
            $data[$k]['id'] = $initId;
            $data[$k]['account'] = $entry['username'];
            $data[$k]['password'] = $this->defaultPass;

            switch ($entry['role_name']) {
                case '钻石代理':
                    $data[$k]['group_id'] = $this->agentMap[$entry['role_name']];
                    break;
                case '金牌代理':
                    $data[$k]['group_id'] = $this->agentMap[$entry['role_name']];
                    break;
                default:
                    exit('未知代理商');
            }

            $initId += 1;   //递增
        }

        $result = $data;
        //填充parent_id
        foreach ($data as $k => $entry) {
            array_walk($result, function ($value) use (&$data, $k, $entry) {
                if ($entry['parent_name'] === $value['username']) {
                    $data[$k]['parent_id'] = $value['id'];
                } else if ($entry['parent_name'] === '壹壹惠州麻将') {
                    $data[$k]['parent_id'] = 1;
                }
            });
        }

        return $data;
    }

    public function migrateTopUp2AgentHistory()
    {
        $data = $this->getTopUp2AgentHistory();
        $data = array_reverse($data);

        foreach ($data as $entry) {
            $id = $this->getUserId($entry['to_user']);

            //创建充值记录
            TopUpAdmin::create([
                'provider_id' => 1,
                'receiver_id' => $id,
                'type' => $entry['type'],
                'amount' => $entry['amount'],
                'created_at' => $entry['date'],
            ]);

            echo "create agent top up record for {$entry['to_user']} done." . PHP_EOL;
        }
    }

    protected function getUserId($account)
    {
        if (empty(User::where('account', $account)->first())) {
            echo "user {$account} not exist, create it." . PHP_EOL;
            //创建此用户
            $id = User::create([
                'name' => $account,
                'account' => $account,
                'password' => $this->defaultPass,
                'group_id' => 3,
                'parent_id' => 1
            ])->id;
        }

        $id = User::where('account', $account)->first()->id;

        return $id;
    }

    public function migrateTopUp2PlayerHistory()
    {
        $data = $this->getTopUp2PlayerHistory();
        $data = $this->transPlayerId($data);
        $data = array_reverse($data);

        foreach ($data as $entry) {
            $id = $this->getUserId($entry['from_user']);

            //创建充值记录
            TopUpPlayer::create([
                'provider_id' => $id,
                'player' => $entry['to_user'],
                'type' => $entry['type'],
                'amount' => $entry['amount'],
                'created_at' => $entry['date'],
            ]);

            echo "create player top up record for {$entry['from_user']} done." . PHP_EOL;
        }
    }

    //将老平台的充值的玩家id转换成新平台的id，通过nickname关联
    protected function transPlayerId($data)
    {
        $gameServer = new GameServer();

        try {
            $newPlatformUserList = $gameServer->request('GET', 'users.php')['accounts'];
        } catch (\Exception $e) {
            throw new CustomException($e->getMessage());
        }

        $result = $data;
        foreach ($data as $k => $entry) {
            array_walk($newPlatformUserList, function ($value, $key) use ($k, $entry, &$result) {
                if ($entry['nickname'] === base64_decode($value['nickname'])) {
                    $result[$k]['to_user'] = $value['uid'];
                }
            });
        }

        return $result;
    }
}