<?php

namespace app\admin\controller;

use think\Controller;

class Gamecate extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        return view('gamecate/cate_list');
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function cateAdd()
    {
        return view('gamecate/cate_add');
    }
}
