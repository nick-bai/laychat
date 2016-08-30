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

class Index extends Controller
{
	public function _initialize()
	{
		if( empty(session('uid')) ){
			$this->redirect( url('login/index'), 302 );
		}
	}
	
    public function index()
    {
    	$mine = db('user')->where('id', session('uid'))->find();
    	$this->assign([
    			'uinfo' => $mine
    	]);
        return $this->fetch();
    }
    
    //获取列表
    public function getList()
    {
    	//查询自己的信息
    	$mine = db('user')->where('id', session('uid'))->find();
    	$other = db('user')->where('id != ' . session('uid'))->select();
    	
        $online = 1;
        foreach( $other as $key=>$vo ){
    				
    		$list[$key]['username'] = $vo['uname'];
    		$list[$key]['id'] = $vo['id'];
    		$list[$key]['avatar'] = $vo['avatar'];
    		$list[$key]['sign'] = $vo['sign'];
    		if( 'online' == $vo['status'] ){
    			$online++;
    		}			
        }

        unset( $other );		
    			
        $return = [
       		'code' => 0,
       		'msg'=> '',
       		'data' => [
       			'mine' => [
	       				'username' => $mine['uname'],
	       				'id' => $mine['id'],
	       				'status' => $mine['status'],
       					'sign' => $mine['sign'],
       					'avatar' => $mine['avatar']	
       			],
       			'friend' => [
       					[
       						'groupname' => '阿里在线',
       						'id' => 1,
       						'online' => $online,
       						'list' => $list
       					]	
       			],
       		],
        ];
    	return json( $return );

    }
    
    //获取组员信息
    public function getMembers()
    {
    	
    	return ;
    }
    
    
    
}
