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

class Groupdetail extends Model
{
    protected $table = 'snake_groupdetail';

    /**
     * 根据搜索条件获去用户列表信息
     * @param $id
     * @param $offset
     * @param $limit
     */
    public function getDetailByWhere($id, $offset, $limit )
    {
        return $this->where('groupid', $id)->limit($offset, $limit)->select();
    }

    /**
     * 根据搜索条件获取所有的用户
     * @param $where
     */
    public function getAllDetail()
    {
        return $this->count();
    }
    /**
     * 查看指定分组中的用户id
     * @param $where
     */
    public function checkUserDetail( $groupid )
    {
        $res = $this->field('userid')->where('groupid', $groupid)->select();
        if( empty($res) ){
            return [];
        }

        foreach( $res as $key=>$vo ){
            $ids[] = $vo['userid'];
        }

        return $ids;
    }

    /**
     * 移除用户
     * @param $id
     */
    public function removeUser($id)
    {
        return $this->where('userid', $id)->delete();
    }
}