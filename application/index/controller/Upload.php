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

class Upload extends Controller
{

    //上传图片
    public function uploadImg()
    {
        $file = request()->file('file');
        if( $file->getInfo()['size'] > 3145728){
            // 上传失败获取错误信息
            return json( ['code' => -2, 'msg' => '文件超过3M', 'data' => ''] );
        }
        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
        if($info){
            $src =  '/uploads' . '/' . date('Ymd') . '/' . $info->getFilename();
            return json( ['code' => 0, 'msg' => '', 'data' => ['src' => $src ] ] );
        }else{
            // 上传失败获取错误信息
            return json( ['code' => -1, 'msg' => $file->getError(), 'data' => ''] );
        }
    }

    //上传文件
    public function uploadFile()
    {
        $file = request()->file('file');

        if( $file->getInfo()['size'] > 3145728){
            // 上传失败获取错误信息
            return json( ['code' => -2, 'msg' => '文件超过3M', 'data' => ''] );
        }
        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
        if($info){
            $src =  '/uploads' . '/' . date('Ymd') . '/' . $info->getFilename();
            return json( ['code' => 0, 'msg' => '', 'data' => ['src' => $src ] ] );
        }else{
            // 上传失败获取错误信息
            return json( ['code' => -1, 'msg' => $file->getError(), 'data' => ''] );
        }
    }
}