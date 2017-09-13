# 梦晨游戏管理后台
## 环境依赖
- php >= 5.6  
- nginx打开ssi  
- redis  
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
- npm  

```
安装node和npm环境：
curl --silent --location https://rpm.nodesource.com/setup_8.x | sudo bash -
yum -y install nodejs

生产环境生成前端页面js代码：
cd ${code_ducument_root}/client
npm install     #安装npm包
npm run build   #编译生成js文件
```
