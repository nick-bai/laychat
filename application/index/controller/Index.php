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
		if( empty(cookie('uid')) ){
			$this->redirect( url('login/index'), 302 );
		}
	}
	
    public function index()
    {
    	$mine = db('chatuser')->where('id', cookie('uid'))->find();
    	$this->assign([
    			'uinfo' => $mine
    	]);
        return $this->fetch();
    }
    
    //获取列表
    public function getList()
    {
    	//查询自己的信息
    	$mine = db('chatuser')->where('id', cookie('uid'))->find();
    	$other = db('chatuser')->select();

        //查询当前用户的所处的群组
        $groupArr = [];
        $groups = db('groupdetail')->field('groupid')->where('userid', cookie('uid'))->group('groupid')->select();
        if( !empty( $groups ) ){
            foreach( $groups as $key=>$vo ){
                $ret = db('chatgroup')->where('id', $vo['groupid'])->find();
                if( !empty( $ret ) ){
                    $groupArr[] = $ret;
                }
            }
        }
        unset( $ret, $groups );

        $online = 0;
        $group = [];  //记录分组信息
        $userGroup = config('user_group');
        $list = [];  //群组成员信息
        $i = 0;
        $j = 0;

        foreach( $userGroup as $key=>$vo ){
            $group[$i] = [
                'groupname' => $vo,
                'id' => $key,
                'online' => 0,
                'list' => []
            ];
            $i++;
        }
        unset( $userGroup );

        foreach( $group as $key=>$vo ){

            foreach( $other as $k=>$v ) {

                if ($vo['id'] == $v['groupid']) {

                    $list[$j]['username'] = $v['username'];
                    $list[$j]['id'] = $v['id'];
                    $list[$j]['avatar'] = $v['avatar'];
                    $list[$j]['sign'] = $v['sign'];

                    if ('online' == $v['status']) {
                        $online++;
                    }

                    $group[$key]['online'] = $online;
                    $group[$key]['list'] = $list;

                    $j++;
                }
            }
            $j = 0;
            $online = 0;
            unset($list);
        }
       //print_r($group);die;
        unset( $other );		
    			
        $return = [
       		'code' => 0,
       		'msg'=> '',
       		'data' => [
       			'mine' => [
	       				'username' => $mine['username'],
	       				'id' => $mine['id'],
	       				'status' => 'online',
       					'sign' => $mine['sign'],
       					'avatar' => $mine['avatar']	
       			],
       			'friend' => $group,
				'group' => $groupArr
       		],
        ];

    	return json( $return );

    }
    
    //获取组员信息
    public function getMembers()
    {
    	$id = input('param.id');
    	
    	//群主信息
    	$owner = db('chatgroup')->field('owner_name,owner_id,owner_avatar,owner_sign')->where('id = ' . $id)->find();
    	//群成员信息
    	$list = db('groupdetail')->field('userid id,username,useravatar avatar,usersign sign')
    	->where('groupid = ' . $id)->select();
    	
    	$return = [
    			'code' => 0,
    			'msg' => '',
    			'data' => [
    				'owner' => [
    						'username' => $owner['owner_name'],
    						'id' => $owner['owner_id'],
    						'owner_id' => $owner['owner_avatar'],
    						'sign' => $owner['owner_sign']
    				],
    				'list' => $list	
    			]
    	];
    	
    	return json( $return );
    }
    
    
    
}
