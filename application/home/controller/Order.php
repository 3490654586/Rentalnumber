<?php

namespace app\home\controller;

use app\home\model\AreaModel;
use app\home\model\RecomGameModel;
use app\home\model\ServerModel;
use think\Loader;
use think\Controller;
use app\home\model\UserModel;
use app\home\model\OrderModel;
use think\Session;
use app\home\service\Smsmsg;
use app\home\model\RecomAccountModel;
use app\common\api\Apipublic;

class Order extends Controller
{
    public function index()
    {
        $orderType = input('orderType'); // 用户选择的租用类型 0 : 时租 1 : 日租 2 : 包夜
        $account_id = input('id');
        $user = Loader::model("RecomAccountModel");
        $resList = $user->with('Game,GameServer,Areas')
            ->where("id", $account_id)
            ->find();
        // 区服
        $resList['server'] = $resList['game']['game_name'] . '/' . $resList['game_server']['server_name'] . '/' . $resList['areas']['area_name'];
        $resList['prices'] = $resList['price'] * 24; // 日租
        $resList['pack_night_prices'] = $resList['pack_night_price'] * 10; // 包夜总价
        $resList['rank'] = $resList['rank'] == 1 ? '允许' : '不允许'; // 排位

        if ($orderType == 1) { // 日租
            $resList['hours'] = 24;
            $resList['amount'] = $resList['day_price'] + $resList['deposit'];
        } else if ($orderType == 2) { // 包夜
            $resList['hours'] = 10;
            $resList['amount'] = $resList['pack_night_price'] + $resList['deposit'];
        } else { // 时租
            $resList['hours'] = 1;
            // 总金额
            $resList['amount'] = $resList['price'] + $resList['deposit'];
        }
        return view('order/order', [
            'resList' => $resList,
            'orderType' => $orderType
        ]);
    }

    /*
     * 下单(包含续费功能)
     * @param $uid 下单用户ID
     * @param $price 商品单价
     * @param $goodsTime 租用小时数
     * @param $deposit 商品押金
     * @param $amount 订单总金额
     * @param $goodsId 商品ID
     * @return json
     */
    public function doPay()
    {
        $orderModel = new OrderModel();
        // 查询用户的余额是否充足
        $uid = Session::get('userData')['id'];
        $userMoney = UserModel::where("id", $uid)->value("money");
        // 总金额
        $price = input('price'); // 单价
        $goodsTime = input('goodsTime'); // 租用时间
        $deposit = input('deposit'); // 押金
        $amount = round($price * $goodsTime, 2) + $deposit;
        if (floatval($userMoney) < $amount) {
            return json([
                "status" => 101,
                "msg"    => "您的余额不足"
            ]);
        }
        // 查询我是否有已购买的此游戏账号,如若有的话,那再次下单就是续费 注:余额充足的情况下
        $goodsId = input('goodsId'); // 商品ID
        $lastEndTime = $orderModel->where("goodsId", $goodsId)->where("uid", $uid)->where("payType", 2)
            ->value("endTime"); // 上一次结束时间
        // 没有的话就是新的下单
        if (!$lastEndTime) {
            $leaseType = RecomAccountModel::where('id', $goodsId)->value('lease_type');
            if ($leaseType == 1) {
                return Apipublic::jsonError("该游戏账号正在出租中");
            }
            $orderData['orderNum'] = 'FX' . date('YmdHis', time()) . $goodsTime . rand(10000, 99999) . $uid;
            // 如果没有被出租,先生成了订单
            $accountInfo = RecomAccountModel::where("id", $goodsId)
                ->field(["account_title", "game_id", "servers", "area", "deposit", "price","game_nickname","shortest_time","account","pwd","screenshotsone"])
                ->find();
            $gameName = RecomGameModel::where("id", $accountInfo["game_id"])->value("game_name");
            $serverName = ServerModel::where("id", $accountInfo["servers"])->value("server_name");
            $areaName = AreaModel::where("id", $accountInfo["area"])->value("area_name");
            $accountInfoArray = [
                "account_title" => $accountInfo["account_title"],
                "game_name" => $gameName,
                "server_name" => $serverName,
                "area_name" => $areaName,
                "deposit" => $accountInfo["deposit"],
                "price" => $accountInfo["price"],
                "game_nickname" => $accountInfo["game_nickname"],
                "shortest_time" => $accountInfo["shortest_time"],
                "account" => $accountInfo["account"],
                "pwd" => $accountInfo["pwd"],
                "screenshotsone" => $accountInfo["screenshotsone"]
            ];
            $createOrder = $orderModel->save([
                "orderNum" => 'FX' . date('YmdHis', time()) . $goodsTime . rand(10000, 99999) . $uid,
                "goodsId" => $goodsId,
                "amounts" => $amount,
                "addTime" => date('Y-m-d H:i:s', time()),
                "payType" => 1,
                "uid" => $uid,
                "goodsInfo" => serialize($accountInfoArray)
            ]);

            // 如果订单创建成功了
            if ($createOrder > 0) {
                // 执行用户扣款
                $cutPayment = UserModel::where('id', $uid)->setDec('money', $amount);
                if ($cutPayment == false) // 如果扣款失败,就不往下走
                {
                    return Apipublic::jsonError("扣款失败,请尝试重新下单");
                }
                // 扣款成功后,发送短信给商户
                $merchantUid = RecomAccountModel::where('id', $goodsId)->value('uid');
                $nickName = UserModel::where('id', $merchantUid)->value('nickname'); // 用户昵称
                // 货架号 $goodsId
                $startTime = time(); // 租号开始时间
                $endTime = $startTime + ($goodsTime * 60) * 60; // 租号结束时间
                $phone = UserModel::where("id", $merchantUid)->value("phone"); // 用户手机号 // 发送短信的手机号码

                if ($merchantUid == 0) {
                    $nickName = '张老板(生意兴隆)';
                    $phone = 18173121347;
                }
                $perform = smsMsg::sendOrderShortMessage($phone, $nickName, $goodsId, $startTime, $endTime); // 执行发送短信
                $conversion = object_to_array($perform["acsResponse"]); //对象转成数组
                if ($conversion['Code'] == 'OK') // 短信发送成功
                {
                    // 执行操作 : 改变商品状态,
                    RecomAccountModel::where("id", $goodsId)->update([
                        "lease_type" => 1
                    ]);
                    // 账号出租次数增加
                    RecomAccountModel::where("id", $goodsId)->setInc("lease_num", 1);
                    // 更新订单状态和时间
                    $orderModel::where('id', $orderModel->id)->update([
                        'startTime' => date('Y-m-d H:i:s', $startTime),
                        'endTime' => date('Y-m-d H:i:s', $endTime),
                        'payTime' => date('Y-m-d H:i:s', time()),
                        'payType' => 2
                    ]);
                    return Apipublic::jsonSuccess("恭喜您下单成功,请前往个人中心查看吧O(∩_∩)O~",$orderModel->id);
                }
                return Apipublic::jsonError("如果您看见这个提示,请您联系客服");
            }
        } else {
            // 续费
            // 更新账号结束时间
            $orderModel->save([
                "endTime" => date("Y-m-d H:i:s", strtotime("+$goodsTime hour", strtotime($lastEndTime)))
            ], ["goodsId" => $goodsId]);
            return Apipublic::jsonSuccess("恭喜您,续费成功,继续畅玩吧");
        }
    }

    /**
     * 下单成功页面
     *
     */
    public function succ()
    {
        $orderId = input("orderId");
        $data = OrderModel::where("id",$orderId)->find();
        $regType = RecomAccountModel::where("id",$data['goodsId'])->value('reg_type');
        return view("order/orderSuccess",[
            "data" => $data,
            'regType' => $regType // 账号登录方式 1:明文 2:远程登录
        ]);
    }
}


