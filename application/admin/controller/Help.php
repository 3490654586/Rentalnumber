<?php

namespace app\admin\controller;

use think\Controller;

class Help extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        return view('help/help_list');
    }

    /**
     * 新增页面
     *
     * @return \think\Response
     */
    public function helpAdd()
    {
        return view('help/help_add');
    }
}
