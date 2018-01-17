# laychat
layIM+workerman+thinkphp5的webIM即时通讯系统 v2.0正式版  

实现了功能:  
1、通过snake后台实现对聊天成员的增删改查，动态推送给在线的用户    
2、实现了群组的查找  
3、实现了创建我的群组,删除我的群组,添加群组成员，移除群组成员  
4、实现了离线用户登录后聊天记录推送  
5、实现了单聊，群聊功能  
6、实现了图片和文件的发送  
7、实现了单聊聊天记录和群聊聊天记录的查看  

# 注意事项:  
back文件加下有数据库备份文件，请建立数据库，并导入。同时配置项目中的config文件中的datebase.php的数据库信息。  
别忘了vendor/Workerman/Applications/Config/Db.php，workerman的数据库同步跟上。

# 关于LayIM
因为layIM不开源，要是商用的话，建议去http://layim.layui.com  这里，layUI的官网去授权吧  

# 数据库在哪里？  
back 文件夹下有一个 snake.sql 导入即可  

# 如何运行  
1、将代码下载到本地，并配置好虚拟域名，使 laychat 可以运行。（基于tp5框架，只要按照tp5框架的配置方式即可）  
  
2、导入 back 文件夹下的 snake.sql 表，数据库名 为 sanke （你可以自己改的，但是别忘了代码中更改）  
  
3、启动 getwayworker，本案例 基于的win平台的gatewayworker，如果您想在linux下部署，请先阅读 gatewayworker 文档有了基本的理解，然后下载 linux 版本的
gatewayworker，然后移植本程序的业务逻辑部分即可。如果您是win，请双击/vendor/Workerman/start_for_win.bat,然后不要关闭窗口。此外，如果您更改了数据库连接，请更改 vendor/Workerman/Applications/Config/Db.php 的配置   
  
4、访问聊天系统，进入前台，使用前台用户的 用户名，密码登录即可聊天。 请用两个浏览器打开，登录不同的账户互相聊天。 密码 默认为 admin  

5、在win下一定要记得双击 laychat/vendor/Workerman/start_for_win.bat 启动 workerman，不要关闭！！！

# 了解效果
http://www.thinkphp.cn/code/2289.html


