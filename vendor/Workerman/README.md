workerman-chat
=======
基于workerman的GatewayWorker框架开发的一款高性能支持分布式部署的聊天室系统。

GatewayWorker框架文档：http://www.workerman.net/gatewaydoc/

 特性
======
 * 使用websocket协议
 * 多浏览器支持（浏览器支持html5或者flash任意一种即可）
 * 多房间支持
 * 私聊支持
 * 掉线自动重连
 * 支持多服务器部署
 * 业务逻辑全部在一个文件中，快速入门可以参考这个文件[Applications/Chat/Event.php](https://github.com/walkor/workerman-chat-for-win/blob/master/Applications/Chat/Event.php)   

安装
=====
无需安装，只要php环境ok即可运行。

要求 php>=5.3.3 并且配置好了php环境变量

windows系统php环境配置参考 http://www.workerman.net/windows
  
启动停止
=====
启动

双击start_for_win.bat

停止

ctrl+c 停止

注意：  
=======
windows系统下无法使用 stop reload status 等命令  

测试
=======
浏览器访问 http://服务器ip或域:55151,例如http://127.0.0.1:55151

 [更多请访问www.workerman.net](http://www.workerman.net/workerman-chat)
