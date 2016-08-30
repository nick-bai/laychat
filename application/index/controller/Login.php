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
    	$uname = input('param.uname');
    	$userinfo = db('user')->where('uname', $uname)->find();
    	if( empty($userinfo) ){
    		$this->error("用户不存在");
    	}
    	
    	//设置为登录状态
    	db('user')->where('uname', $uname)->setField('status', 'online');
    	
    	session( 'uid', $userinfo['id'] );
    	session( 'uname', $userinfo['uname'] );

    	$this->redirect(url('index/index'));
    }
    
}
