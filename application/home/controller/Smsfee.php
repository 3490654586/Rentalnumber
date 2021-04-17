<?php

namespace app\home\controller;

use app\common\api\Apipublic;
use think\Controller;
use app\home\model\OrderModel;
use app\home\model\RecomAccountModel;
use app\home\model\UserModel;
use app\home\service\Smsmsg;

class Smsfee extends Controller
{
    /*
     * 租号实时访问数据推送短信,并且扣除号主余额4毛钱
     * 2020/11/1 22:32
    */
    public function fee()
    {
        // 第一步 : 查询所有订单的id,endTime结束时间
        $OrderModel = new OrderModel;
        $order = $OrderModel->withTrashed()->field('id,goodsId,endTime,goodsInfo')->where('payType', 'in', [2, 5])->select();
        if (count($order) == 0) {
            return Apipublic::jsonError('暂无符合要求的订单');
        }
        // 已完成的货架返回结果
        $done = [];
        // 未完成的货架返回结果
        $undone = [];
        // 第二步 : 循环所有订单
        foreach ($order as $k => $v) {
            // 如果所有订单中有租号结束时间有大于当前时间的,取出他的商品id
            if (time() >= strtotime($order[$k]['endTime'])) {
                // 查询商品号主id
                $RecomAccountModel = new RecomAccountModel;
                $haozhuId = $RecomAccountModel->where('id', $order[$k]['goodsId'])->value('uid');
                // 1 改变订单状态
                // 查询该订单的号主用户信息
                $UserModel = new UserModel;
                $name = $UserModel->where('id', $haozhuId)->value('nickname'); // 用户昵称
                $phone = $UserModel->where('id', $haozhuId)->value('phone'); // 用户手机号
                $num = $order[$k]['goodsId']; // 货架号
                if ($phone == NULL) {
                    $name = '小张';
                    $phone = 18173121347; // 测试数据
                }

                $Smsmsg = new Smsmsg;
                $pushSms = $Smsmsg->sendOrderShortMessageIsOk($phone, $name, $num); // 发送短信
                $doSmsmsgs = object_to_array($pushSms['acsResponse']);
                if ($doSmsmsgs['Code'] == 'OK') {
                    // 扣除号主的余额4毛钱
                    $updateStatus = $OrderModel->withTrashed()->where('id', $order[$k]['id'])->update([
                        'smsStatus' => 1,
                        'payType' => 3
                    ]);
                    // 改变账号状态为未租用
                    $updateAccountLeaseType = $RecomAccountModel->where('id', $num)->update([
                        'lease_type' => 2
                    ]);
                    // 增加一次账号租用次数
                    $addLeaseNum = $RecomAccountModel->where('id', $num)->setInc('lease_num', 1);
                    $done[$k] = $order[$k]['id'];
                    // 押金返还给租户
                    if ($v['goodsInfo']['deposit'] > 0){
                        $backMoney = $UserModel->where('id',$v['uid'])->setInc('money',$v['goodsInfo']['deposit']);
                    }
                    // 号主增加余额
                    $UserModel->where('id', $haozhuId)->setInc('money',$v['goodsInfo']['price']);
                    // 号主扣除4毛钱短信费
                    $UserModel->where('id', $haozhuId)->setDec('money', 0.4);
                }
            } else {
                $undone[$k] = $order[$k]['id'];
            }
        }
        $data = [
            "done" => $done,
            "undone" => $undone
        ];
        return Apipublic::jsonSuccess("订单Id返回成功",$data);
    }
}
