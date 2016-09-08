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

class Chatgroup extends Model
{
    protected $table = 'snake_chatgroup';

    /**
     * 根据搜索条件获去用户组列表信息
     * @param $where
     * @param $offset
     * @param $limit
     */
    public function getGroupByWhere( $offset, $limit )
    {
       return $this->field('snake_chatgroup.*, count(d.userid) as usernum')->join('snake_groupdetail d','snake_chatgroup.id = d.groupid', 'LEFT')
            ->limit($offset, $limit)->order('snake_chatgroup.id desc')
            ->group('snake_chatgroup.id')
            ->select();
    }

    /**
     * 根据搜索条件获取所有的用户组
     * @param $where
     */
    public function getAllGroup()
    {
        return $this->count();
    }

    /**
     * 获取用户组信息
     * @param $where
     */
    public function checkName( $name )
    {
        return $this->where('groupname', $name)->find();
    }

    /**
     * 添加用户组数据
     * @param $param
     */
    public function insertGroup($param)
    {
        try{

            $result =  $this->save($param);
            if(false === $result){
                // 验证失败 输出错误信息
                return ['code' => -1, 'data' => '', 'msg' => $this->getError()];
            }else{

                return ['code' => 1, 'data' => $result, 'msg' => '添加用户组成功'];
            }
        }catch( PDOException $e){

            return ['code' => -2, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    /**
     * 根据id获取用户组信息
     * @param $id
     */
    public function getOneGroup($id)
    {
        return $this->where('id', $id)->find();
    }

    /**
     * 删除用户组
     * @param $id
     */
    public function delGroup($id)
    {
        try{

            $this->where('id', $id)->delete();
            return ['code' => 1, 'data' => '', 'msg' => '删除用户组成功'];

        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }
}