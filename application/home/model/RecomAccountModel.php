<?php

namespace app\home\model;

use think\Model;
use traits\model\SoftDelete;

class RecomAccountModel extends Model
{
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    //自定义初始化
    protected function initialize()
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
        //TODO:自定义的初始化
    }

    // 设置当前模型对应的完整数据表名称
    protected $table = 'rent_pubgame_account';

    // 服务器游戏名称
    public function Game()
    {
        return $this->hasOne('RecomGameModel', 'id', 'game_id', ['Game'], 'left')->field('id,game_name,cid');
    }

    // 服务器
    public function GameServer()
    {
        return $this->hasOne('ServerModel', 'id', 'servers')->field('id,server_name');
    }

    // 大区
    public function Areas()
    {
        return $this->hasOne('AreaModel', 'id', 'area')->field('id,area_name');
    }
}

?>