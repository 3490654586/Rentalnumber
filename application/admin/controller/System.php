<?php

namespace app\admin\controller;

use think\Controller;

class System extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        return view('system/basic');
    }

    /**
     * 管理员列表.
     *
     * @return \think\Response
     */
    public function adminList()
    {
        return view('system/admin_list');
    }

    /**
     * 新增管理员.
     *
     * @return \think\Response
     */
    public function adminAdd()
    {
        return view('system/admin_add');
    }
}
