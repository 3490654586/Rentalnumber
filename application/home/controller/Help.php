<?php

namespace app\home\controller;

use think\Controller;

class Help extends Controller
{
    /*
     * 帮助中心
     * @return view
     */
    public function index()
    {
        return view("help/help");
    }
}