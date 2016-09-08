<?php
// +----------------------------------------------------------------------
// | snake
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 http://baiyf.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: NickBai <1902822973@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\admin\model\Chatuser;

class Layeruser extends Base
{
    //laychat用户列表
    public function index()
    {
        if(request()->isAjax()){

            $param = input('param.');

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $user = new Chatuser();
            $selectResult = $user->getUserByWhere($offset, $limit);

            foreach($selectResult as $key=>$vo){
                $selectResult[$key]['avatar'] = "<img src='".$vo['avatar']."' width='50px' height='50px'>";
                $operate = [
                    '编辑' => url('layeruser/userEdit', ['id' => $vo['id']]),
                    '删除' => "javascript:userDel('".$vo['id']."')"
                ];
                $selectResult[$key]['operate'] = showOperate($operate);

            }

            $return['total'] = $user->getAllUser();  //总数据
            $return['rows'] = $selectResult;

            return json($return);
        }

        return $this->fetch();
    }

    //添加用户
    public function roleAdd()
    {
        if(request()->isPost()){

            $param = input('param.');
            $param = parseParams($param['data']);

            $role = new Chatuser();
            $flag = $role->insertRole($param);

            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }

        return $this->fetch();
    }

    //编辑用户
    public function roleEdit()
    {
        $role = new UserType();

        if(request()->isPost()){

            $param = input('post.');
            $param = parseParams($param['data']);

            $flag = $role->editRole($param);

            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }

        $id = input('param.id');
        $this->assign([
            'role' => $role->getOneRole($id)
        ]);
        return $this->fetch();
    }

    //删除用户
    public function roleDel()
    {
        $id = input('param.id');

        $role = new UserType();
        $flag = $role->delRole($id);
        return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
    }
}