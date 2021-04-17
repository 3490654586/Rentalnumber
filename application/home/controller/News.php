<?php

namespace app\home\controller;

use think\Loader;
use think\Controller;

class News extends Controller
{
    /**
     * 新闻快报详情
     * @return [type] [description]
     */
    public function detail()
    {
        $id = input('DetailId'); // 新闻快报ID

        $news = [];
        $newsList = [];
        if ($id) {
            // 新闻快报
            $news = Loader::model('NewsModel');
            $news = $news->where('id', $id)
                ->find();
        } else {
            // 新闻快报列表
            $newsList = Loader::model('NewsModel');
            $newsList = $newsList->order('add_time desc')
                ->paginate(10, false);
        }

        return view('news/news', [
            'news' => $news,
            'newsList' => $newsList,

        ]);
    }
}
