# 梦晨游戏管理后台
## 环境依赖
- php >= 5.6  
- nginx打开ssi  
- redis >= 2.8
- composer  
- supervisor  

```
supervisor配置文件模版：
[program:laravel-queue-worker]
process_name=%(program_name)s_%(process_num)02d
;directory=/data/www/mengchen_new   ;低版本不支持此指令
command=/usr/bin/php /data/www/mengchen_new/artisan queue:work --delay=3 --sleep=1 --tries=3 --timeout=60
autostart=true
autorestart=true
startretries=3
user=www
numprocs=8
redirect_stderr=true
stdout_logfile=/data/log/supervisor/%(program_name).log
stdout_logfile_maxbytes=100MB
stdout_logfile_backups=10
```  
- node & npm

```
安装node和npm环境：
curl --silent --location https://rpm.nodesource.com/setup_8.x | sudo bash -
yum -y install nodejs
```

## 生产环境代码发布  

```
cd ${code_ducument_root}
git pull                #获取最新代码
composer install        #安装laravle依赖
chmod +x vendor/phpunit/phpunit/phpunit #添加执行权限
./vendor/bin/phpunit    #代码测试

cd client       #进入js开发目录
npm install     #安装npm包
npm run build   #编译js代码
```  
### 使用post-merge钩子脚本  
```
#!/bin/sh

codeDir=$(cd $(dirname $0); pwd)'/../../'

service supervisord restart     #重启队列

cd $codeDir
composer install
./vendor/bin/phpunit

cd client
npm install
npm run build
```

## 开发环境使用pre-push钩子
```
#!/bin/sh

codeDir=$(cd $(dirname $0); pwd)'/../../'
cd $codeDir
./vendor/bin/phpunit
```

## 后端接口列表
### 管理员接口
> **前缀/admin/api/**

| URI   | Method  | Description |     
| ----  | :-----: | ----------: |
| self/password | PUT | 更新密码 |
| home/summary | GET | 首页数据总览 |
| statement/hourly | GET | 每小时报表数据 |
| statement/daily | GET | 每天报表数据 |
| statement/monthly | GET | 每月报表数据 |
| statement/hourly-chart | GET | 每小时流水图表数据 |
| game/player | GET | 玩家列表 |
| game/notification/marquee | GET | 跑马灯公告列表 |
| game/notification/marquee | POST | 新建跑马灯公告 |
| game/notification/marquee/{id} | PUT | 编辑更新跑马灯公告 |
| game/notification/marquee/{id} | DELETE | 删除跑马灯公告 |
| game/notification/marquee/enable/{id} | PUT | 启用跑马灯公告 |
| game/notification/marquee/disable/{id} | PUT | 禁用跑马灯公告 |
| game/notification/login | GET | 登录公告列表 |
| game/notification/login | POST | 新建登录公告 |
| game/notification/login/{id} | PUT | 编辑更新登录公告 |
| game/notification/login/{id} | DELETE | 删除登录公告 |
| game/notification/login/enable/{id} | PUT | 启用登录公告 |
| game/notification/login/disable/{id} | PUT | 禁用登录公告 |
| game/room/friend | GET | 好友房列表 |
| game/room/friend/{ownerId} | DELETE | 解散好友房 |
| game/room/coin | GET | 金币房列表 |
| game/room/coin/{roomId} | DELETE | 解散金币房 |
| stock | POST | 申请库存 |
| stock/list | GET | 库存申请列表 |
| stock/history | GET | 库存审批记录 |
| stock/approval/{id} | POST | 审批通过 |
| stock/decline/{id} | POST | 审批拒绝 |
| agent | GET | 代理商列表 |
| agent | POST | 新建代理商 |
| agent/{id} | DELETE | 删除代理商 |
| agent/{id} | PUT | 更新代理商信息 |
| agent/pass/{id} | PUT | 更新代理商密码 |
| top-up/top-agent | GET | 管理员给代理商充值记录 |
| top-up/agent | GET | 代理商给下级代理商充值记录 |
| top-up/player | GET | 给玩家的充值记录 |
| top-up/agent/{receiver}/{type}/{amount} | POST | 给代理商充值 |
| top-up/player/{player}/{type}/{amount} | POST | 给玩家充值 |
| system/log | GET | 系统操作日志记录 |  

### 代理商接口
> **前缀/agent/api/**  

| URI   | Method  | Description |     
| ----  | :-----: | ----------: |
| self/info | PUT | 更新个人信息 |
| self/password | PUT | 更新个人密码 |
| self/agent-type | GET | 获取代理商代理级别 |
| stock | POST | 申请库存 |
| stock/history | GET | 库存申请记录 |
| subagent | GET | 子代理商列表 |
| subagent | POST | 创建子代理商 |
| subagent/{id} | DELETE | 删除子代理商 |
| subagent/{id} | PUT | 更新子代理商信息(包括密码) |
| top-up/child/{receiver}/{type}/{amount} | POST | 给子代理商充值 |
| top-up/player/{player}/{type}/{amount} | POST | 给玩家充值 |
| top-up/child | GET | 给自代理商的充值记录 |
| top-up/player | GET | 给玩家的充值记录 |

### 公共接口
| URI   | Method  | Description |     
| ----  | :-----: | ----------: |
| /api/info | GET | 获取用户个人信息 |

## 游戏服接口
> **前缀: ?action={Action}**

| Action | Method | Description |
| ----  | :-----: | ----------: |
| Room.getRooms | GET | 获取金币房列表 |
| Room.dismissRoomById| POST | 解散金币房 |
| FriendRoom.forceClearRoom | POST | 解散好友房 |
| notice.systemSendNOticeToAll | POST | 同步登录和跑马灯公告 |