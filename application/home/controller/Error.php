<?php

namespace app\home\controller;
class Error
{
    /*
     * 加载指定Error页面
     * @return view
     */
    public function index($param)
    {
        return view("error/" . $param);
    }
}