### 智慧云夺宝答题

#### 运行环境要求

* php7.0
* mysql5.6
* apache / nginx
* 域名需要配置好https

#### 部署

建议使用宝塔环境一键配置，将后台代码上传至网站根目录。

修改数据库配置文件\data\conf\db.php。

用Navicat或者phpmyadmin将enve_dbdt.sql导入数据库。

打开你的网站https://***/index.php/admin/public/login

配置你的小程序后台吧

小程序前端对接：修改前端文件app.js

App({
  setConfig: {
    url: '',
    hb_appid: '',
    hb_appsecret: ''
  },

改成你的小程序信息即可

服务类目：选择教育-在线教育



