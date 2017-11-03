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
directory=/data/www/mengchen_new_sz   ;低版本不支持此指令
command=/usr/bin/php /data/www/mengchen_new_sz/artisan queue:work --delay=3 --sleep=1 --tries=3 --timeout=60
autostart=true
autorestart=true
startretries=3
user=nginx
numprocs=1
redirect_stderr=true
stdout_logfile=/data/log/supervisor/%(program_name)s.log
stdout_logfile_maxbytes=100MB
stdout_logfile_backups=10
```  
- node & npm

```
安装node和npm环境：
curl --silent --location https://rpm.nodesource.com/setup_8.x | sudo bash -
yum -y install nodejs
```

## 单元测试与代码覆盖率
测试并生成代码覆盖率报表：  
```
cd ${code_ducument_root}
./vendor/bin/phpunit --coverage-html public/test/
```
查看代码覆盖率：
```
URI: /test/index.html
```

## 生产环境代码发布  

```
cd ${code_ducument_root}
git pull                #获取最新代码
composer install        #安装laravle依赖
cp .env.example .env    #配置文件(根据生产环境配置对应的参数)
php artisan storage:link    #创建符号链接到文件上传目录
chmod -R {phpfpm_runner}.{phpfpm_runner} ./ #更改代码目录的权限为phpfpm程序的运行用户
chmod +x vendor/phpunit/phpunit/phpunit #添加执行权限
./vendor/bin/phpunit    #代码测试

cd client       #进入js开发目录
npm install     #安装npm包
npm run build   #编译js代码
```  

### cron计划任务
```
crontab -e
* * * * * php /data/www/{code_ducument_root}/artisan schedule:run >> /dev/null 2>&1  

#注意：.env里面正确配置好日志输出文件"CRON_TASK_LOG"
```  

**任务列表**  

| 任务名   | 运行时间  | 描述 |     
| ----  | :-----: | ----------: |
| admin:generate-daily-statement | 每日00:00 | 统计上一天的数据报表并入库 |

### 使用post-merge钩子脚本  
```
#!/bin/sh

codeDir=$(cd $(dirname $0); pwd)'/../../'

service supervisord restart     #重启队列

cd $codeDir
composer install

cd client
npm install
npm run build
```

## 开发环境规范
### 开发环境使用pre-push钩子
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
| game/player | GET | 玩家列表 |
| statement/summary | GET | 获取数据报表总览 |
| statement/real-time | GET | 获取实时报表数据 |
| gm/records | GET | 根据玩家id战绩其查询 |
| gm/record-info/{recId} | GET | 根据战绩id查询战绩详情 |
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
| top-up/admin | GET | 管理员给代理商充值记录 |
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
