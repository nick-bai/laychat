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

class Layuser extends Base
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

            $group = config('user_group');

            foreach($selectResult as $key=>$vo){
                $selectResult[$key]['avatar'] = "<img src='".$vo['avatar']."' width='50px' height='50px'>";
                $operate = [
                    '编辑' => "javascript:edit(".$vo['id'].")",
                    '删除' => "javascript:userDel('".$vo['id']."')"
                ];
                $selectResult[$key]['groupid'] = $group[$vo['groupid']];
                $selectResult[$key]['operate'] = showOperate($operate);

            }

            $return['total'] = $user->getAllUser();  //总数据
            $return['rows'] = $selectResult;

            return json($return);
        }

        return $this->fetch();
    }

    //添加用户
    public function userAdd()
    {
        $add_data = '';
        if(request()->isPost()){

            $param = input('post.');

            $user = new Chatuser();
            if ( empty($param['username']) ){
                return json( ['code' => -1, 'data' => '', 'msg' => '用户名不能为空'] );
            }

            if ( empty($param['groupid']) ){
                return json( ['code' => -2, 'data' => '', 'msg' => '所属分组不能为空'] );
            }

            if ( empty($param['pwd']) ){
                return json( ['code' => -3, 'data' => '', 'msg' => '登录密码不能为空'] );
            }

            if ( empty($param['sign']) ){
                return json( ['code' => -4, 'data' => '', 'msg' => '个性签名不能为空'] );
            }

            $has = $user->checkName( $param['username'] );
            if ( !empty( $has ) ) {
                return json( ['code' => -5, 'data' => '', 'msg' => '用户名重复'] );
            }

            $this->_getUpFile( $param );  //处理上传图片

            $param['pwd'] = md5( $param['pwd'] );
            $param['status'] = 'outline';

            $flag = $user->insertUser( $param );

            if( 1 != $flag['code'] ){
                return json( ['code' => -6, 'data' => '', 'msg' => '添加用户失败'] );
            }

            //socket data
            $add_data = '{"type":"addUser", "data" : {"avatar":"' . $param['avatar'] . '","username":"' . $param['username'] . '",';
            $add_data .= '"groupid":"' . $param['groupid'] . '", "id":"' . $flag['data'] . '","sign":"' . $param['sign'] . '"}}';

            return json( ['code' => 1, 'data' => $add_data, 'msg' => '添加用户成功'] );
        }

        $this->assign([
            'group' => config('user_group'),
            'add_data' => $add_data
        ]);
        return $this->fetch();
    }

    //编辑用户
    public function userEdit()
    {
        $user = new Chatuser();
        if( request()->isPost() ){

            $param = input('post.');

            if ( empty($param['username']) ){
                return json( ['code' => -1, 'data' => '', 'msg' => '用户名不能为空'] );
            }

            if ( empty($param['groupid']) ){
                return json( ['code' => -2, 'data' => '', 'msg' => '所属分组不能为空'] );
            }

            if ( empty($param['sign']) ){
                return json( ['code' => -3, 'data' => '', 'msg' => '个性签名不能为空'] );
            }

            $has = $user->checkNameEdit( $param['username'], $param['id'] );
            if ( !empty( $has ) ) {
                return json( ['code' => -4, 'data' => '', 'msg' => '您修改后的用户名已经存在'] );
            }

            $this->_getUpFile( $param );  //处理上传头像
            //处理密码问题
            if( empty( $param['pwd'] ) ){
                unset( $param['pwd'] );
            }else{
                $param['pwd'] = md5( $param['pwd'] );
            }

            $flag = $user->editUser( $param );
            if( 0 == $flag['code'] ){
                return json( ['code' => -5, 'data' => '', 'msg' => '编辑用户失败'] );
            }

            return json( ['code' => 1, 'data' => '', 'msg' => '编辑用户成功'] );
        }

        $id = input('param.id');

        $this->assign([
            'user' => $user->getOneUser($id),
            'group' => config('user_group')
        ]);
        return $this->fetch();
    }

    //删除用户
    public function userDel()
    {
        $id = input('param.id');

        $user = new Chatuser();
        $flag = $user->delUser($id);

        return json(['code' => $flag['code'], 'data' => '', 'msg' => $flag['msg']]);
    }

    /**
     * 上传图片方法
     * @param $param
     */
    private function _getUpFile(&$param)
    {
        // 获取表单上传文件
        $file = request()->file('avatar');

        // 移动到框架应用根目录/public/uploads/ 目录下
        if( !is_null( $file ) ){

            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
            if($info){
                // 成功上传后 获取上传信息
                $param['avatar'] =  '/uploads' . '/' . date('Ymd') . '/' . $info->getFilename();
            }else{
                // 上传失败获取错误信息
                echo $file->getError();
            }
        }else{
            unset( $param['avatar'] );
        }

    }
}