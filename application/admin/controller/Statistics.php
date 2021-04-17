<?php

namespace app\admin\controller;

use think\Controller;

class Statistics extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        return view('statistics/statistics');
    }
}
