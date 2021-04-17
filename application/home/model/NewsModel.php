<?php

namespace app\home\model;

use think\Model;

class NewsModel extends Model
{

    //自定义初始化
    protected function initialize()
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
        //TODO:自定义的初始化
    }

    // 设置当前模型对应的完整数据表名称
    protected $table = 'rent_consult';

    // 日期转换
    public function getAddTimeAttr($value)
    {
        $add_time = date('Y-m-d H:i:s', $value);
        return $add_time;
    }
}

?>