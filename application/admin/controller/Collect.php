<?php
// +----------------------------------------------------------------------
// | snake 采集数据主方法
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 http://baiyf.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: NickBai <1902822973@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use curl\CURL;

class Collect extends Base
{
    //采集的文章列表
    public function index()
    {

        return $this->fetch();
    }

    //开始采集
    public function startCollect()
    {
        require APP_PATH . '../extend/phpquery/phpQuery.php';

        /*$curl = new CURL();
        $url = 'http://www.thinkphp.cn';
        $result = $curl->read($url);

        $html = \phpQuery::newDocumentHTML($result['content']);
        $li = $html['.index-bd .list li a'];
        foreach($li as $v){
            $v = pq($v);
            $param = [
                'title' => $v->text(),
                'url' => $url . $v->attr('href')
            ];
            db('archives')->insert($param);
        }*/

        $curl = new CURL();
        $result = db('archives')->select();
        foreach($result as $key=>$vo){

            $result = $curl->read($vo['url']);
            $html = \phpQuery::newDocumentHTML($result['content']);

            $box = $html['.wrapper .detail-box'];
            foreach($box as $v){
                $v = pq($v)->find('.detail-bd');

                $imgs = $v->find('img');
                foreach ($imgs as $img) {

                    $src = pq($img)->attr('src');

                    pq($img)->attr('src', 'http://www.thinkphp.cn' . $src);
                }

                $param = [
                    'body' => $v->html()
                ];

                db('articleinfo')->insert($param);
            }
        }
    }

}