<?php

namespace app\admin\controller;

use think\Controller;

class Banner extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        return view('banner/banner_list');
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function addBanner()
    {
        return view('banner/banner_add');
    }
}
