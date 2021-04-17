<?php

use think\Db;

function GameTypeGame()
{

    $game_type = Db::name('rent_game_cate')->field('id,cate_name')->order('id asc')->select();
    foreach ($game_type as $k => $v) {
        $game = Db::name('rent_game')->field('id,game_name')->where('cid', $v['id'])->order('id asc')->select();
        $game_type[$k]['game'] = $game;
    }

    return $game_type;
}