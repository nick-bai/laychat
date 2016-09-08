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

class CateModel extends Model
{
    protected $table = 'snake_category';

    /**
     * 分页获取不同分组下的类别信息
     * @param $where
     * @param $offset
     * @param $limit
     */
    public function getCateByWhere($where, $offset, $limit)
    {
        return $this->where($where)->limit($offset, $limit)->order('id desc')->select();
    }

    /**
     * 获取某个分组下的数据条数
     * @param $where
     */
    public function getAllCate($where)
    {
        return $this->where($where)->count();
    }

    /**
     * 添加分组信息
     * @param $param
     */
    public function insertCate($param)
    {
        try{

            $result =  $this->save($param);
            if(false === $result){
                // 验证失败 输出错误信息
                return ['code' => -1, 'data' => '', 'msg' => $this->getError()];
            }else{

                return ['code' => 1, 'data' => '', 'msg' => 'success'];
            }
        }catch( PDOException $e){

            return ['code' => -2, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    /**
     * 编辑分组信息
     * @param $param
     */
    public function editCate($param)
    {
        try{

            $result =  $this->save($param, ['id' => $param['id']]);

            if(false === $result){
                // 验证失败 输出错误信息
                return ['code' => 0, 'data' => '', 'msg' => $this->getError()];
            }else{

                return ['code' => 1, 'data' => '', 'msg' => 'success'];
            }
        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }

    /**
     * 编辑一条分组信息
     * @param $id
     */
    public function getOneCate($id)
    {
        return $this->where('id', $id)->find();
    }

    /**
     * 删除一条分类信息
     * @param $id
     */
    public function delCate($id)
    {
        try{

            $this->where('id', $id)->delete();
            return ['code' => 1, 'data' => '', 'msg' => 'success'];

        }catch( PDOException $e){
            return ['code' => 0, 'data' => '', 'msg' => $e->getMessage()];
        }
    }
}