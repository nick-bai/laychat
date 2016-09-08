<?php
// +----------------------------------------------------------------------
// | snake 测试采集规则
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2022 http://baiyf.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: NickBai <1902822973@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use curl\CURL;

class Tcollect extends Base
{
    //采集测试页面
    public function index()
    {
        return $this->fetch();
    }

    //列表页规则采集测试
    public function testList()
    {
        require APP_PATH . '../extend/phpquery/phpQuery.php';

        $param = input('param.');
        $param = parseParams($param['data']);

        $curl = new CURL();
        $url = $param['url'];
        $result = $curl->read($url);

        if(empty($result['content'])){
            return json(['code' => -1, 'data' => '', 'msg' => '采集规则有误']);
        }

        $charset = preg_match("/<meta.+?charset=[^\w]?([-\w]+)/i", $result['content'], $temp) ? strtolower($temp[1]) : "";
        \phpQuery::$defaultCharset = $charset;  //设置编码,解决乱码问题

        $html = \phpQuery::newDocumentHTML($result['content']);
        $li = $html[$param['range']];

        $list = [];
        foreach($li as $v){
            $v = pq($v);

            if( 'text' == $param['title'] ){
                $title = trim( $v->text() );
            }else{
                $title = trim( $v->attr($param['title'] ) );
            }

            $href = trim( $v->attr('href') );

            if( false === strpos($href, 'http') && false === strpos($href, 'https') ){

                $href = $param['baseurl'] . $href;
            }

            $list[] = [
                'title' => $title,
                'url' => $href
            ];
        }

        return json(['code' => 1, 'data' => $list, 'msg' => 'ok']);

    }

    //文章页规则采集测试
    public function testArc()
    {
        require APP_PATH . '../extend/phpquery/phpQuery.php';

        $param = input('param.');
        $param = parseParams($param['data']);

        $curl = new CURL();
        $url = $param['arcurl'];
        $result = $curl->read($url);

        if(empty($result['content'])){
            return json(['code' => -1, 'data' => '', 'msg' => '采集规则有误']);
        }

        $charset = preg_match("/<meta.+?charset=[^\w]?([-\w]+)/i", $result['content'], $temp) ? strtolower($temp[1]) : "";
        \phpQuery::$defaultCharset = $charset;  //设置编码,解决乱码问题

        $html = \phpQuery::newDocumentHTML($result['content']);
        $li = $html[$param['arcrange']];

        $list = '';
        foreach($li as $v){
            $v = pq($v);

            //文章内图片替换展示
            $imgs = $v->find('img');
            foreach ($imgs as $img) {

                $src = pq($img)->attr('src');
                if( false === strpos($src, 'http') && false === strpos($src, 'https') ){
                    $baseurl = explode( '/', $url);
                    pq($img)->attr('src', $baseurl['0'] . '//' . $baseurl['2'] . $src);
                }
            }

            $list = $v->html();
        }
        $list = mb_convert_encoding($list, 'utf-8', $charset);

        return json(['code' => 1, 'data' => $list, 'msg' => 'ok']);
    }
}