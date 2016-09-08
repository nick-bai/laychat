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
namespace app\admin\model;

use think\Model;

class Chatuser extends Model
{
    protected $table = 'snake_chatuser';

    /**
     * 根据搜索条件获去用户列表信息
     * @param $where
     * @param $offset
     * @param $limit
     */
    public function getUserByWhere( $offset, $limit )
    {
        return $this->limit($offset, $limit)->order('id desc')->select();
    }

    /**
     * 根据搜索条件获取所有的用户
     * @param $where
     */
    public function getAllUser()
    {
        return $this->count();
    }

    /**
     * 根据搜索条件获取所有的用户
     * @param $where
     */
    public function checkName( $name )
    {
        return $this->where('username', $name)->find();
    }

    /**
     * 根据搜索条件获取所有的用户
     * @param $where
     */
    public function checkNameEdit( $name, $id )
    {
        return $this->where("username = '".$name."' and id != $id")->find();
    }

    /**
     * 添加用户数据
     * @param $param
     */
    public function insertUser($param)
    {
        try{

            $result =  $this->save($param);
            if(false === $result){
                // 验证失败 输出错误信息
                return ['code' => -1, 'data' => '', 'msg' => $this->getError()];
            }else{

                return ['code' => 1, 'data' => $result, 'msg' => '添加用户成功'];
            }
        }catch( PDOException $e){

            return ['code' => -2, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    /**
     * 编辑用户数据
     * @param $param
     */
    public function editUser($param)
    {
        try{

            $result =  $this->save($param, ['id' => $param['id']]);

            if(false === $result){
                // 验证失败 输出错误信息
                return ['code' => 0, 'data' => '', 'msg' => $this->getError()];
            }else{

                return ['code' => 1, 'data' => '', 'msg' => '编辑用户成功'];
            }
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    /**
     * 根据id获取用户信息
     * @param $id
     */
    public function getOneUser($id)
    {
        return $this->where('id', $id)->find();
    }

    /**
     * 根据id字符串查询用户信息
     * @param $id
     */
    public function findUserByIds($ids)
    {
        return $this->where("id in ($ids)")->select();
    }

    /**
     * 删除用户
     * @param $id
     */
    public function delUser($id)
    {
        try{

            $this->where('id', $id)->delete();
            return ['code' => 1, 'data' => '', 'msg' => '删除用户成功'];

        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }
}