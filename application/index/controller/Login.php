<?php
// +----------------------------------------------------------------------
// | layerIM + Workerman + ThinkPHP5 即时通讯
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2012 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: NickBai <1902822973@qq.com>
// +----------------------------------------------------------------------
namespace app\index\controller;

use think\Controller;

class Login extends Controller
{
    public function index()
    {
        return $this->fetch();
    }
    
    public function doLogin()
    {
    	$uname = input('param.username');
    	$userinfo = db('chatuser')->where('username', $uname)->find();

    	if( empty($userinfo) ){
    		$this->error("用户不存在");
    	}

        $pwd = input('param.pwd');
		if( md5($pwd) != $userinfo['pwd'] ){
            $this->error("密码不正确");
        }
    	
    	//设置为登录状态
    	db('chatuser')->where('username', $uname)->setField('status', 'online');
    	
    	cookie( 'uid', $userinfo['id'] );
    	cookie( 'username', $userinfo['username'] );
        cookie( 'avatar', $userinfo['avatar'] );
        cookie( 'sign', $userinfo['sign'] );

    	$this->redirect(url('index/index'));
    }
    
}
