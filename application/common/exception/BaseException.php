<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/11/15 0015
 * Time: 23:07
 */

namespace app\common\exception;

use think\Exception;

class BaseException extends Exception
{
    public function __construct($message = "", $code = 0)
    {
        parent::__construct($message, $code);
    }

}