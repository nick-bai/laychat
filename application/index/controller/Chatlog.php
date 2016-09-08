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

class Chatlog extends Controller
{
    //聊天记录
    public function index()
    {
        $id = input('id');
        $type = input('type');

        $this->assign([
            'id' => $id,
            'type' => $type
        ]);

        return $this->fetch();
    }

    //聊天记录详情
    public function detail()
    {
        $id = input('id');
        $type = input('type');

        $uid = cookie('uid');

        if( 'friend' == $type ){
            $result = db('chatlog')->where("((fromid={$uid} and toid={$id}) or (fromid={$id} and toid={$uid})) and type='friend'")
                ->order('timeline desc')
                ->select();

            if( empty($result) ){
                return json( ['code' => -1, 'data' => '', 'msg' => '没有记录'] );
            }

            return json( ['code' => 1, 'data' => $result, 'msg' => 'success'] );
        }else if('group' == $type){

            $result = db('chatlog')->where("toid={$id} and type='group'")
                ->order('timeline desc')
                ->select();
            
            if( empty($result) ){
                return json( ['code' => -1, 'data' => '', 'msg' => '没有记录'] );
            }

            return json( ['code' => 1, 'data' => $result, 'msg' => 'success'] );
        }


    }
}