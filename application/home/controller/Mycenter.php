<?php

namespace app\home\controller;

use app\home\model\ApplyRechargeModel;
use app\home\model\UserModel;
use think\Session;
use app\home\model\OrderModel;
use app\home\model\CollectModel;
use app\home\model\RecomAccountModel;
use app\home\model\WithdrawalModel;
use app\common\api\Apipublic;
use app\home\model\ComplaintModel;

class Mycenter extends Base
{
    /**
     * 个人中心
     *
     * @return \think\Response
     */
    public function index()
    {
        $uid = Session::get('userData')['id']; // 用户id
        $UserModel = new UserModel;
        $data = $UserModel->where('id', $uid)->field('nickname,phone,login_last_time,avatar,money,examine_money')->find();
        // 我正在租的订单
        $orderModel = new OrderModel();
        $order = $orderModel->with('Games')
            ->where('payType', 2)
            ->where('uid', $uid)
            ->paginate(10, false);

        $RecomAccountModel = new RecomAccountModel();
        //出租订单数量
        $rentOrderLength = $RecomAccountModel->where("uid", $uid)
            ->where("lease_type", 1)
            ->count();
        //出租账号的的数量
        $pubgameAccountLength = $RecomAccountModel->where("uid", $uid)->count("id");
        //租号订单的数量
        $orderLength = $orderModel->where("uid", $uid)
            ->where("payType", '2')
            ->count();
        return view('user/user', [
            'data' => $data,
            'order' => $order,
            "rentOrderLength" => $rentOrderLength,
            "pubgameAccountLength" => $pubgameAccountLength,
            "orderLength" => $orderLength
        ]);
    }

    /*
     * 修改头像
     * 用户单独修改头像
     */
    public function PortraitEditor()
    {
        $avatar = input('avatar'); // 用户头像
        $uid = Session::get('userData')['id']; // 用户id
        if (!$uid) {
            return $this->error('用户获取失败');
        }
        $UserModel = new UserModel;
        $execute = $UserModel->where('id', $uid)->update([
            'avatar' => $avatar
        ]);
        if ($execute > 0) {
            // Session里头像也更新一下
            Session::set('userData.avatar', $avatar);
            return json([
                'code' => 0,
                'msg' => '头像修改成功'
            ]);
        } else {
            return json([
                'code' => -1,
                'msg' => '头像修改失败'
            ]);
        }
    }

    /*
     * 修改昵称
     * 用户单独修改昵称
     */
    public function ChangeNickname()
    {
        $nickname = input('nickname'); // 用户昵称
        $uid = Session::get('userData')['id']; // 用户id
        if (!$uid) {
            return $this->error('用户获取失败');
        }
        $UserModel = new UserModel;
        $execute = $UserModel->where('id', $uid)->update([
            'nickname' => $nickname
        ]);
        if ($execute > 0) {
            // Session里昵称也更新一下
            Session::set('userData.nickname', $nickname);
            return json([
                'code' => 0,
                'msg' => '昵称修改成功'
            ]);
        } else {
            return json([
                'code' => -1,
                'msg' => '昵称修改失败'
            ]);
        }
    }

    /**
     * 发布账号
     *
     * @return \think\Response
     */
    public function pushAccount()
    {
        return view('haozu/push');
    }

    /**
     * 充值余额页面
     *
     * @return \think\Response
     */
    public function recharge()
    {
        // 查询用户的余额
        $UserModel = new UserModel;
        $uid = Session::get('userData')['id'];
        $userMoney = $UserModel->where('id', $uid)->value('money');
        return view('user/recharge', [
            'userMoney' => $userMoney
        ]);
    }

    /*
     * 去充值余额
     * 这边是个接口
     * 对的就是个接口
     */
    public function doPayMoney()
    {
        $uid = Session::get('userData')['id']; // 用户id
        // 判断用户当前时间是否还可以提交充值表单
        $doPay = $this->FindUserRechage($uid);
        if ($doPay == -1) {
            return json([
                'code' => 101,
                'msg' => '您还有一笔充值订单未处理,请不要频繁提交,请联系客服'
            ]);
        }

        $ApplyRechargeModel = new ApplyRechargeModel;
        $moneyDataArr['uid'] = $uid; // 用户唯一标识
        $moneyDataArr['money'] = input('money'); // 需要充值的金额
        $moneyDataArr['status'] = 1; // 用户充值申请中
        $moneyDataArr['add_time'] = time(); // 申请充值开始时间
        $moneyDataArr['end_time'] = $moneyDataArr['add_time'] + 15 * 60; // 申请充值结束时间
        $moneyDataArr['payType'] = input('payType'); // 付款类型
        $doResult = $ApplyRechargeModel->data($moneyDataArr)->save(); // 申请入表中

        if ($doResult > 0) {
            return json([
                'code' => 0,
                'msg' => '申请成功'
            ]);
        } else {
            return json([
                'code' => -1,
                'msg' => '申请失败'
            ]);
        }
    }

    /*
     * 这边是个极限查询防止用户重复点击申请充值
     * 内部调用此接口,外部一律不能访问
     */
    private function FindUserRechage($uid)
    {
        $ApplyRechargeModel = new ApplyRechargeModel;
        $userApplyEndTime = $ApplyRechargeModel->where('uid', $uid)
            ->where('status', 1)
            ->order('add_time desc')
            ->limit(1)
            ->find();
        // 如果没有充值记录
        if ($userApplyEndTime == NULL) {
            return 1;
        } else {
            if (time() >= $userApplyEndTime['end_time']) {
                if ($userApplyEndTime['status'] == 1) {
                    $ApplyRechargeModel->where('id', $userApplyEndTime['id'])->delete(); // 删除此次充值记录
                }
                return 1;
            } else {
                return -1;
            }
        }

    }

    /**
     * 我的账号列表
     *
     * @return \think\Response
     */
    public function accountList()
    {
        return view('haozu/accountlist');
    }

    /**
     * 编辑账号
     *
     * @return \think\Response
     */
    public function editAccount()
    {
        $id = input('id');

        return view('haozu/upd', [
            'id' => $id
        ]);
    }

    /**
     * 编辑账号按钮点击Ajax初步接口
     */
    public function clickEdit()
    {
        $id = input("id");
        if (preventRentError($id) == 1)
        {
            return Apipublic::jsonError("操作失败");
        }
        return Apipublic::jsonSuccess("操作成功");
    }
    /**
     * 充值记录
     */
    public function tranRec()
    {
        $allArray = [];
        $ApplyRechargeModel = new ApplyRechargeModel;
        $uid = Session::get('userData')['id'];
        $startTime = input('startTime') ? input('startTime') : '';
        $endTime = input('endTime') ? input('endTime') : '';
        $type = input('type') ? input('type') : 'cons';
        $where = [];

        if ($type == 'cons') {
            if ($startTime && $endTime) {
                $where['payTime'] = ['between', "$startTime,$endTime"];
            }
        }
        if ($type == 'withdrawal') {
            if ($startTime && $endTime) {
                $where['add_time'] = ['between', "$startTime,$endTime"];
            }
        }
        if ($type == 'recharge') {
            if ($startTime && $endTime) {
                $start = strtotime($startTime);
                $end = strtotime($endTime);
                $where['add_time'] = ['between', "$start,$end"];
            }
        }
        $this->assign('startTime', $startTime);
        $this->assign('endTime', $endTime);
        $sumMoney = 0;
        if ($type == 'cons') {
            // 消费记录
            $OrderModel = new OrderModel;
            $consHistory = $OrderModel->with('Games')
                ->where('uid', $uid)
                ->where('payType', 'in', [2, 3, 4])
                ->where($where)
                ->paginate(10, false, ['query' => input()]);
            $sumMoney = $OrderModel->SumAmounts($OrderModel, $uid, $where);
            $allArray = $consHistory;
        }
        if ($type == 'withdrawal') {
            //提现记录
            $WithdrawalModel = new WithdrawalModel();
            $allArray = $WithdrawalModel->where("uid", $uid)->where($where)->paginate(10, false, ['query' => input()]);
        }
        if ($type == 'recharge') {
            // 充值记录
            $rechargeHistory = $ApplyRechargeModel->where('uid', $uid)->where($where)->paginate(10, false, ['query' => input()]);
            $allArray = $rechargeHistory;
        }

        return view('money/tran_rec', [
            'allArray' => $allArray,
            'type' => $type,
            'sumMoney' => $sumMoney
        ]);
    }

    /**
     * 账户明细
     */
    public function accounting()
    {
        return view('money/accounting');
    }

    /**
     * 提现
     */
    public function withdrawal()
    {
        $userModel = new UserModel();
        $withDrawal = new WithdrawalModel();
        // 查询用户可用余额,不可用余额
        $uid = Session::get("userData")["id"];
        $userInfo = $userModel->where("id", $uid)->field("money,examine_money")->find();
        // 查询本月剩余提现次数
        $countTx = $withDrawal->where("uid", $uid)->whereTime("add_time", "month")->count();
        $surplusTx = floatval(4 - $countTx);
        return view("money/withdrawal", [
            "userInfo" => $userInfo,
            "surplusTx" => $surplusTx
        ]);
    }

    /**
     * 收藏
     */
    public function like()
    {
        $uid = Session::get('userData')['id'];
        $collectModel = new CollectModel();
        $aids = $collectModel->account($uid);
        $accountModel = new RecomAccountModel();
        $serialNumber = input('serialNumber');
        $where = [];
        if ($serialNumber) {
            $where['id'] = ['eq', $serialNumber];
        } else {
            $where['id'] = ['in', $aids];
        }
        $goods = $accountModel->with('Game,GameServer,Areas')
            ->field(['id', 'account_title', 'game_id', 'servers', 'area', 'game_nickname', 'price', 'lease_type'])
            ->where($where)
            ->paginate(10, false, ['query' => input()]);
        foreach ($goods as $k => $v) {
            $goods[$k]['gameName'] = $goods[$k]['game']['game_name'];
            $goods[$k]['gameServers'] = $goods[$k]['game_server']['server_name'];
            $goods[$k]['gameArea'] = $goods[$k]['areas']['area_name'];
            unset($goods[$k]['game_id']);
            unset($goods[$k]['servers']);
            unset($goods[$k]['area']);
            unset($goods[$k]['game']);
            unset($goods[$k]['game_server']);
            unset($goods[$k]['areas']);
        }
        $this->assign('serialNumber', $serialNumber);
        return view("zuke/like", [
            'goods' => $goods
        ]);
    }

    /**
     * 我租的订单
     */
    public function zuhao()
    {
        return view("zuke/zuhao");
    }

    /*
     * 余额提现
     * @param $uid 用户id
     * @param $amount 提现总金额
     * @return json
     */
    public function doWithDrawal()
    {
        $withdrawal = new WithdrawalModel();
        $userModel = new UserModel();
        // 第一步,获取用户提交的数据
        $uid = Session::get("userData")["id"];
        $amount = input("money");
        // 第二步,查询金额是否满50,50才可提现
        $userMoney = $userModel->where("id", $uid)->value("money");
        if ($userMoney < 20) {
            return Apipublic::jsonError("余额不足");
        }
        if ($amount > $userMoney) {
            return Apipublic::jsonError("提现金额不能大于可用余额");
        }
        // 第三步,查询提现次数还剩几次,是否满足4次
        $countTx = $withdrawal->where("uid", $uid)->whereTime("add_time", "month")->count();
        if ($countTx == 4) {
            return Apipublic::jsonError("当月提现次数已用完");
        }
        // 第三步,提交表单,数据追加到提现表里面
        $doWithdrawal = $withdrawal->insert([
            "uid" => $uid,
            "payType" => input("payType"),
            "account" => input("account"),
            "payee" => input("name"),
            "amount" => $amount,
            "add_time" => date("Y-m-d H:i:s", time())
        ]);
        // 第四步,如果追加成功,就等待管理员审核给他转账
        if ($doWithdrawal > 0) {
            // 第五步,减去余额,余额进入不可用余额
            $userModel->where("id", $uid)->setDec("money", $amount);
            $userModel->where("id", $uid)->setInc("examine_money", $amount);
            return Apipublic::jsonSuccess("提现成功");
        } else {
            return Apipublic::jsonError("提现失败");
        }
    }

    /*
     * 个人中心撤单
     * 撤单需要:
     * 1.把当前这个单子状态改成4,即为已撤单的状态
     * 2.游戏账号出租状态改为2,即为未出租的状态
     * 接收参数:
     * orderId : 订单ID
     * goodsId : 商品ID
     */
    public function cancelTheOrder()
    {
        // 投诉状态，内容，账号状态，id
        $complaints_type = input("type");
        $complaints_info = input("info");
        $complaints_payType = input("payType");
        $complaints_id = input("id");
        //判断，如果状态数是5，禁止下面操作
        if ($complaints_payType == 5) {
            return Apipublic::jsonError("不能重复投诉");
        } else {
            $orderModel = new OrderModel();
            //修改订单的状态值
            $orderData = $orderModel::find($complaints_id);
            $orderData->payType = 5;
            $isOrderUpdate = $orderData->save();
            //如果状态改变成功，将数据写入投诉表中
            if ($isOrderUpdate) {
                $ComplaintModel = new ComplaintModel();
                $ComplaintModel->save([
                    'orderId' => $complaints_id,
                    'content' => $complaints_info,
                    'type' => $complaints_type,
                    'add_time' => time()
                ]);
                return Apipublic::jsonSuccess("投诉成功，后台正在审核中");
            }
        }
    }

    /**
     * 我租的订单删除订单功能
     * @param $id 订单id
     * @return Apipublic
     */
    public function deleteOrder()
    {
        $id = input("id");
        $orderModel = new OrderModel();
        $isDelete = $orderModel->destroy($id);
        if ($isDelete == 1) {
            return Apipublic::jsonSuccess("删除成功");
        } else {
            return Apipublic::jsonError("删除失败");
        }
    }


    /* 
        我的出租订单
    */
    public function rentGoodsList()
    {
        return view("haozu/goods");
    }

    /**
     * 我的出租账号上下架功能
     * @param $accountId 账号ID
     * @param $buttonType 1 上架 2 下架
     * @return json
     */
    public function accountDown()
    {
        $accountId = input("accountId");
        $buttonType = input("buttonType");
        // 判断账号是否被出租
        if(preventRentError($accountId) == 1)
        {
            return Apipublic::jsonError("操作失败");
        }
        $updateGrounding = (new RecomAccountModel)->save(["is_grounding" => $buttonType],["id" => $accountId]);
        // 根据状态，返回
        if ($updateGrounding > 0)
        {
            if ($buttonType == 2) {
                return Apipublic::jsonSuccess("下架成功");
            } else {
                return Apipublic::jsonSuccess("上架成功");
            }
        }else{
            return Apipublic::jsonError("操作失败");
        }
    }

    /**
     * 我的出租账号删除功能
     * @param $accountId 账号id
     * @return \think\response\Json
     */
    public function accountDelete()
    {
        //获取账号的id
        $accountId = input("accountId");
        if (preventRentError($accountId) == 1)
        {
            return Apipublic::jsonError("操作失败");
        }
        $RecomAccountModel = new RecomAccountModel();
        $delAccount = $RecomAccountModel->destroy($accountId); // 软删除
        if ($delAccount > 0) {
            return json([
                "code" => 0,
                "msg" => '删除成功'
            ]);
        } else {
            return json([
                "code" => 1,
                "msg" => '删除失败',
            ]);
        }
    }

    //账号安全设置
    public function accountSet()
    {
        return view("user/accset");
    }

    /*
     * 用户修改密码
     * @param $uid 用户ID
     * @param $reNewPassword 再次输入的新密码
     * @param $encryNewPassword MD5后的新密码
     * @return json
     */
    public function changePassword()
    {
        $userModel = new UserModel();
        $uid = Session::get("userData")["id"];
        $reNewPassword = input("reNewPassword");
        $encryNewPassword = md5($reNewPassword);
        if (!$uid) {
            return Apipublic::jsonError("需修改密码的用户不存在");
        }
        // 查询用户的旧密码
        $userOldPassword = $userModel->where("id", $uid)->value("password");
        if (md5(input("oldPassword")) != $userOldPassword) {
            return Apipublic::jsonError("请输入正确的旧密码");
        }
        if (input("newPassword") != $reNewPassword) {
            return Apipublic::jsonError("新密码两次输入不一致");
        }
        if ($encryNewPassword == $userOldPassword) {
            return Apipublic::jsonError("新密码不能与旧密码相同");
        }
        $updateUserPwd = $userModel->where("id", $uid)->update([
            "password" => $encryNewPassword
        ]);
        if ($updateUserPwd > 0) {
            Session::delete("userData");
            return Apipublic::jsonSuccess("密码修改成功");
        }
        return Apipublic::jsonError("密码修改失败");
    }

    /*
     * 修改游戏账号密码
     * @param $accountPwd 游戏账号密码
     * @param $id 游戏账号主键id
     * @return json
     */
    public function editGameAccountPassword()
    {
        $accountPwd = input("accountPwd");
        $id = input("id");
        if (preventRentError($id) == 1)
        {
            return Apipublic::jsonError("操作失败");
        }
        if (!$accountPwd) {
            return Apipublic::jsonError("请输入密码");
        }
        $doEditAccountPwd = RecomAccountModel::where("id", $id)->update([
            "pwd" => $accountPwd
        ]);
        if ($doEditAccountPwd > 0) {
            return Apipublic::jsonSuccess("密码修改成功,请重新登录");
        } else {

            return Apipublic::jsonError("不能与原密码相同");
        }
    }
}