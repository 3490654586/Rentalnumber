<?php

namespace app\home\controller;

use think\Loader;

class Index
{
    /**
     * 首页
     * @return [type] [description]
     */
    public function index()
    {
        // 轮播图
        $banner = Loader::model('BannerModel');
        $banner = $banner->select();

        // 推荐游戏
        $recomGame = Loader::model('RecomGameModel');
        $recomGame = $recomGame->where('is_recom', 1)
            ->order('add_time desc')
            ->limit(4)
            ->select();
        // 推荐游戏分类
        $recomeGameCate = Loader::model('RecomGameCateModel');
        $recomeGameCate = $recomeGameCate->where('is_recom', 1)
            ->order('add_time desc')
            ->limit(3)
            ->select();
        // 推荐游戏账号
        $recomAccount = Loader::model('RecomAccountModel');
        $recomAccount = $recomAccount->where(['is_recommend' => 1, 'is_grounding' => 1, 'audit' => 2])
            ->order('add_time desc')
            ->limit(3)
            ->select();
        // 热门游戏
        $hotGame = Loader::model('RecomGameModel');
        $hotGame = $hotGame->where('is_hot', 1)
            ->order('add_time desc')
            ->limit(8)
            ->select();

        // 新闻快报
        $news = Loader::model('NewsModel');
        $news = $news->order('add_time desc')
            ->limit(4)
            ->select();
        //	热门游戏随机下单用户信息
        foreach ($hotGame as $k => $v) {
            $userOrderInfo = randomMobile(10);
            foreach ($userOrderInfo["phone"] as $k2 => $v2) {
                $userOrderInfo["phone"][$k2] = substr_replace($v2, '****', 3, 4);
            }
            $hotGame[$k]["userOrderInfo"] = $userOrderInfo;
        }
        return view('index/index', [
            'banner' => $banner,
            'recomGame' => $recomGame,
            'recomeGameCate' => $recomeGameCate,
            'recomAccount' => $recomAccount,
            'news' => $news,
            'hotGame' => $hotGame
        ]);
    }
}
