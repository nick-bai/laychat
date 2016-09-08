<?php
// +----------------------------------------------------------------------
// | snake 采集规则管理
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 http://baiyf.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: NickBai <1902822973@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\admin\model\Rule;

class Rulelist extends Base
{
    //规则列表
    public function index()
    {
        if(request()->isAjax()){

            $param = input('param.');

            $limit = $param['pageSize'];
            $offset = ($param['pageNumber'] - 1) * $limit;

            $where = [];
            if (isset($param['searchText']) && !empty($param['searchText'])) {
                $where['rulename'] = ['like', '%' . $param['searchText'] . '%'];
            }
            $rule = new Rule();
            $selectResult = $rule->getRuleByWhere($where, $offset, $limit);

            foreach($selectResult as $key=>$vo){

                $operate = [
                    '编辑' => url('rulelist/ruleEdit', ['id' => $vo['id']]),
                    '删除' => "javascript:del('".$vo['id']."')"
                ];
                $selectResult[$key]['operate'] = showOperate($operate);

            }

            $return['total'] = $rule->getAllRule($where);  //总数据
            $return['rows'] = $selectResult;

            return json($return);
        }

        return $this->fetch();
    }

    //添加规则
    public function ruleAdd()
    {
        if(request()->isPost()){

            $param = input('param.');
            $param = parseParams($param['data']);

            $param['addtime'] = time();
            $rule = new Rule();
            $flag = $rule->insertRule($param);

            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }

        $this->assign([
            'cjstatus' => config('cj_status')
        ]);
        return $this->fetch();
    }

    //编辑采集规则
    public function ruleEdit()
    {
        $rule = new Rule();

        if(request()->isPost()){

            $param = input('post.');
            $param = parseParams($param['data']);

            $flag = $rule->editRule($param);

            return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
        }

        $id = input('param.id');
        $this->assign([
            'rule' => $rule->getOneRule($id),
            'cjstatus' => config('cj_status')
        ]);
        return $this->fetch();
    }

    //删除采集规则
    public function ruleDel()
    {
        $id = input('param.id');

        $rule = new Rule();
        $flag = $rule->delRule($id);
        return json(['code' => $flag['code'], 'data' => $flag['data'], 'msg' => $flag['msg']]);
    }
}