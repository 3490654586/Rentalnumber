<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/11/15 0015
 * Time: 23:11
 */

namespace app\common\exception;

use Exception;
use think\exception\ErrorException;
use think\exception\Handle;
use think\exception\HttpException;

class BaseHandler extends Handle
{
    /**
     * 异常接管
     * @param Exception $e
     * @return \think\Response|\think\response\Redirect
     */
    public function render(Exception $e)
    {
        // 请求异常
        if (!config('app_debug')) {
            if ($e instanceof HttpException) {
                if ($e->getStatusCode() == 404) {
                    //这个好怪啊
                    return redirect(url('home/error/index', ['param' => '404']));
                }
            } elseif ($e instanceof ErrorException) {
                //这里跳500
                return redirect(url('home/error/index', ['param' => '500']));

            }
            //这条处理上边都没接管到的异常
            return redirect(url('home/error/index', ['param' => '500']));
        }
        return parent::render($e);
    }
}