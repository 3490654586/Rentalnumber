<?php

namespace app\home\controller;

use think\Session;
use think\exception\HttpResponseException;
use think\Controller;

class Base extends Controller
{
    /**
     * 判断用户有没有登录,未登录一些公共操作
     * @return [type] [description]
     */
    public function _initialize()
    {
        $userData = Session::get('userData');
        if (!$userData) { // session没有用户数据,自动回到首页
            return $this->redirects(url('home/index/index'));
        }
    }

    public function redirects($url)
    {
        throw new HttpResponseException(redirect($url));
    }

}
