<?php

namespace app\admin\controller;

use think\Controller;

class News extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        return view('news/news_list');
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function addNews()
    {
        return view('news/news_add');
    }
}
