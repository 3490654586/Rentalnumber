<?php

namespace app\admin\controller;

use think\Controller;

class Game extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        return view('game/game_list');
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function Gameadd()
    {
        return view('game/game_add');
    }
}
