<?php

namespace app\home\model;

use think\Model;

class UserModel extends Model
{

    //自定义初始化
    protected function initialize()
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
        //TODO:自定义的初始化
    }

    // 设置当前模型对应的完整数据表名称
    protected $table = 'rent_user';

    public function getLoginLastTimeAttr($value)
    {
        return DateDiff(date("Y-m-d H:i:s", time()), date('Y-m-d H:i:s', $value));
    }

    public function getAvatarAttr($value)
    {
        if ($value == '') {
            return input('server.REQUEST_SCHEME') . '://' . input('server.SERVER_NAME') . '/images/default.png';
        } else {
            return $value;
        }
    }
}

?>