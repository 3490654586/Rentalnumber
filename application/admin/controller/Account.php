<?php

namespace app\admin\controller;

use think\Controller;

class Account extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        return view('account/add_account');
    }

    /**
     * 添加游戏服务器
     *
     * @return \think\Response
     */
    public function addGameServer()
    {
        return view('account/add_game_server');
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function accountList()
    {
        return view('account/account_list');
    }

    /**
     * 查看账号.
     *
     * @return \think\Response
     */
    public function lookAccount()
    {
        return view('account/look_account');
    }
}
