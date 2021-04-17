<?php

namespace app\admin\controller;

use think\Controller;

class Login extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        return view('login/login');
    }
}
