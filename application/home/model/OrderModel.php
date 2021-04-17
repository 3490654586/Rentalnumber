<?php

namespace app\home\model;

use think\Model;
use traits\model\SoftDelete;
class OrderModel extends Model
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    //自定义初始化
    protected function initialize()
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
        // TODO:自定义的初始化
    }

    // 设置当前模型对应的完整数据表名称
    protected $table = 'rent_order';

    public function Games()
    {
        return $this->hasOne('RecomAccountModel', 'id', 'goodsId', 'left');
    }

    public function SumAmounts($query, $uid, $where)
    {
        return $query->where('uid', $uid)->where($where)->where('payType', 'in', [2, 3, 4])->sum('amounts');
    }

    public function getGoodsInfoAttr($value)
    {
        return unserialize($value);
    }
}

?>