<?php

namespace App\Models;

trait GroupIdMap
{
    protected $lowestAgentGid = 4;  //最低级别的代理商的组id
    protected $adminGid = '1';        //管理员所在的组id
    protected $agentGids = [2, 3, 4];   //所有代理商的id列表
}