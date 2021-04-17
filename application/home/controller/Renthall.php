<?php

namespace app\home\controller;

use think\Controller;
use think\Loader;
use think\Request;
use app\home\model\RecomAccountModel;
use app\home\model\RecomGameModel;
use app\home\model\ServerModel;
use app\home\model\AreaModel;
use think\Session;
use app\home\model\CollectModel;

class Renthall extends Controller
{
    /**
     * 租号大厅
     * @return [type] [description]
     */
    public function index(Request $request)
    {
        // 游戏分类id
        $gameCateId = $request->post('Cateid');
        // 游戏id
        $gameId = $request->post('Gameid');
        // 排序
        $sort = $request->post('sort');
        // 大厅筛选
        $hallGameId = $request->post('game_id'); // 游戏
        $hallServers = $request->post('servers'); // 服务器
        $hallArea = $request->post('area'); // 大区
        // 标题搜索
        $accountTitle = $request->post('accountTitle'); // 标题搜索
        // 清空大厅筛选
        $clear = false;
        $clear = $request->post('clear');
        if ($clear == true) {
            $hallGameId = '';
            $hallServers = '';
            $hallArea = '';
        }
        // 搜索条件
        $hasWhere = []; // 关联
        $where = []; // 主
        if ($gameCateId) {
            $hasWhere['cid'] = ['eq', $gameCateId];
        }
        if ($gameId) {
            $where['game_id'] = ['eq', $gameId];
        }
        if ($hallGameId && $hallServers && $hallArea) {
            $where['game_id'] = ['eq', $hallGameId];
            $where['servers'] = ['eq', $hallServers];
            $where['area'] = ['eq', $hallArea];
            $game = new RecomGameModel();
            $gameName = $game->where('id', $hallGameId)->value('game_name');
            $server = new ServerModel();
            $serverName = $server->where('id', $hallServers)->value('server_name');
            $area = new AreaModel();
            $areaName = $area->where('id', $hallArea)->value('area_name');
            $this->assign('gameID', $hallGameId);
            $this->assign('serversID', $hallServers);
            $this->assign('areaID', $hallArea);
            $this->assign('hallGameId', $gameName);
            $this->assign('hallServers', $serverName);
            $this->assign('hallArea', $areaName);
        }
        if ($accountTitle) {
            $where['account_title'] = ['like', '%' . $accountTitle . '%'];
            $this->assign('accountTitle', $accountTitle);
        }
        $order = ''; // 排序
        if ($sort) {
            if ($sort == 1) { // 综合排序
                $order = 'lease_num desc,price desc';
            } else if ($sort == 2) { // 最新上架
                $order = 'add_time desc';
            } else if ($sort == '3d') { // 销量倒序
                $order = 'lease_num desc';
            } else if ($sort == '3a') { // 销量
                $order = 'lease_num asc';
            } else if ($sort == '4d') { // 价格倒序
                $order = 'price desc';
            } else if ($sort == '4a') { // 销量
                $order = 'price asc';
            }
            $this->assign('sort', $sort);
        } else {
            $order = 'add_time desc';
            $this->assign('sort', '');
        }
        // 账号列表
        $rentHall = Loader::model('RecomAccountModel');
        $rentHall = $rentHall->hasWhere('Game', $hasWhere)
            ->with('Game,GameServer,Areas')
            ->where('audit', 2)
            ->where('is_grounding', 1)
            ->where($where)
            ->order($order)
            ->paginate(10, false, ['query' => $request->get()]);
        $recomGameCate = $this->recomGameCate(); // 推荐游戏分类
        $everyRent = $this->everyRent();     // 大家都在租
        return view('hall/hall', [
            'rentHall' => $rentHall,
            'recomGameCate' => $recomGameCate,
            'everyRent' => $everyRent
        ]);
    }

    // 推荐游戏分类
    private function recomGameCate()
    {
        $recomGameCate = Loader::model('RecomGameCateModel');
        $recomGameCate = $recomGameCate->with('Game')
            ->where('hall_is_recom', 1)
            ->limit(2)
            ->order('add_time desc')
            ->select();
        return $recomGameCate;
    }

    // 大家都在租
    private function everyRent()
    {
        $everyRent = Loader::model('RecomAccountModel');
        $everyRent = $everyRent->where('audit', 2)
            ->where('is_grounding', 1)
            ->order('lease_num desc')
            ->limit(4)
            ->select();
        return $everyRent;
    }

    /**
     * 预览账号
     *
     * @return \think\Response
     */
    public function LookAccount()
    {
        $account = new RecomAccountModel();
        $id = input('id');
        $data = $account->with('Game,GameServer,Areas')->where('id', $id)->find();
        if ($data['reg_type'] == 1) {
            $data['reg_type'] = '明文登录';
        } else {
            $data['reg_type'] = '远程登录';
        }
        // 日租价
        $data['day_prices'] = round($data['day_price'] / 24);
        // 包夜价
        $data['pack_night_prices'] = round($data['pack_night_price'] / 10);
        // 是否排位
        if ($data['rank'] == 1) {
            $data['rank'] = '允许';
        } else {
            $data['rank'] = '不允许';
        }
        // 查询用户是否收藏此账号
        $collectStatus = -1;
        $collectModel = new CollectModel();
        $uid = Session::get('userData')['id'];
        $userIsCollectAccount = $collectModel->where('uid', $uid)->where('aid', $data['id'])->count();
        if ($userIsCollectAccount > 0) {
            $collectStatus = 1; // 已收藏
        } else {
            $collectStatus = 2; //未收藏
        }
        // 查询该账号被收藏的人气
        $popularity = $collectModel->where('aid', $data['id'])->count();
        return view('detail/detail', [
            'data' => $data,
            'collectStatus' => $collectStatus,
            'popularity' => $popularity
        ]);
    }

    /**
     * 收藏账号
     *
     * @return \think\Response
     */
    public function collectAccount()
    {
        $goodsId = input('goodsId');
        $uid = Session::get('userData')['id'];
        if (!$uid) {
            return json([
                'code' => 3,
                'msg' => '登陆后再收藏'
            ]);
        }
        $add_time = time();
        $collectModel = new CollectModel();
        $thisAccountIsCollect = $collectModel->where('uid', $uid)->where('aid', $goodsId)->find();
        if ($thisAccountIsCollect == NULL) {
            $collect = $collectModel->insert([
                'uid' => $uid,
                'aid' => $goodsId,
                'add_time' => $add_time
            ]);
            if ($collect > 0) {
                // 查询该账号被收藏的人气
                $popularity = $collectModel->where('aid', $goodsId)->count();
                return json([
                    'code' => 1,
                    'msg' => '收藏成功',
                    'popularity' => $popularity
                ]);
            } else {
                // 查询该账号被收藏的人气
                $popularity = $collectModel->where('aid', $goodsId)->count();
                return json([
                    'code' => -1,
                    'msg' => '收藏失败',
                    'popularity' => $popularity
                ]);
            }
        } else {
            $canelCollect = $collectModel->where('id', $thisAccountIsCollect['id'])->delete();
            if ($canelCollect > 0) {
                // 查询该账号被收藏的人气
                $popularity = $collectModel->where('aid', $goodsId)->count();
                return json([
                    'code' => 2,
                    'msg' => '取消收藏成功',
                    'popularity' => $popularity
                ]);
            } else {
                // 查询该账号被收藏的人气
                $popularity = $collectModel->where('aid', $goodsId)->count();
                return json([
                    'code' => -1,
                    'msg' => '取消收藏失败',
                    'popularity' => $popularity
                ]);
            }
        }
    }
}
