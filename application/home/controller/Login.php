<?php

namespace app\home\controller;

use think\Controller;
use app\home\model\UserModel;
use think\Session;
use app\home\service\Smsmsg;
use think\cache\driver\Redis;
use app\common\api\Apipublic;

class Login extends Controller
{
    /*
     * 加载登录界面视图
     * @return view
     */
    public function index()
    {
        return view('login/login');
    }

    /*
     * 用户登录
     * @param $phone 手机号/账号
     * @param $password 密码
     * @param login_last_time 最后一次登录时间
     * @return json
     */
    public function FrontDeskUserLoginInterface()
    {
        $phone = input('phone');
        $password = input('password');
        $UserModel = new UserModel;
        $GoAndLand = $UserModel->where('phone', $phone)->find();
        if ($GoAndLand != NULL) {
            if (md5($password) != $GoAndLand['password']) {
                return Apipublic::jsonError("请输入正确的密码");
            }
            // 更新最后一次登录时间
            $UserModel->where('phone', $phone)->update([
                'login_last_time' => time(),
                'status' => 1
            ]);
            Session::set('userData', $GoAndLand);
            return Apipublic::jsonSuccess("登录成功");
        } else {
            return Apipublic::jsonError("请输入正确的账号");
        }
    }

    /*
     * 发送短信验证码
     * @param $phone 手机号码
     */
    public function VerificationCode()
    {
        $redis = new Redis(config("redis"));
        $phone = input('phone');
        $RegisterOrNot = UserModel::where('phone', $phone)->count(); // 查询改手机号是否被注册
        if ($RegisterOrNot > 0) {
            return Apipublic::jsonError("该手机号已被注册");
        }
        if (!$phone) {
            return Apipublic::jsonError("请输入您的手机号");
        }
        if (is_mobile($phone) == false) {
            return Apipublic::jsonError("请输入正确的手机号");
        }
        // 如果缓存不存在验证码则发送短信
        if ($redis->get("smsCode" . $phone) == false) {
            $doSmsMsg = (new Smsmsg())->sendSms($phone);
            $doSmsMsgToArr = object_to_array($doSmsMsg['acsResponse']);
            if ($doSmsMsgToArr['Code'] == 'isv.BUSINESS_LIMIT_CONTROL') {
                return Apipublic::jsonError("由于服务器限制，您已经发送短信超过最大限制，请明天再尝试");
            }
            $redis->set("smsCode" . $phone, $doSmsMsg['code'], 300); // 5分钟过期
            return Apipublic::jsonSuccess("短信验证码已发送,请注意查收");
        }
        return Apipublic::jsonError("验证码5分钟内有效,请5分钟后再次尝试获取新的验证码");
    }

    /*
     * 用户注册
     * @param $phone 手机号
     * @param $code 前台传来的短信验证码
     * @param $password 密码
     * @nickname 昵称
     * @add_time 注册时间
     * @login_last_time 最后一次登录时间
     * @avatar 头像
     * @money 可用金额
     * @examine_money 不可用金额
     * @return json
     */
    public function FrontDeskRegistrationUserInterface()
    {
        $redis = new Redis(config("redis"));
        $phone = input('phone'); // 手机号
        $UserModel = new UserModel;
        if (!$phone) {
            Apipublic::jsonError("请输入手机号");
        }
        if (is_mobile($phone) == false) {
            Apipublic::jsonError("请输入正确的手机号");
        }
        $VerificationCode = $redis->get("smsCode" . $phone); // 验证码
        if (!$VerificationCode) { // 防止查询验证码时为空的情况下
            Apipublic::jsonError("短信验证码已过期,请重新获取");
        }
        $code = input('code'); // 前台传来的code
        if ($code != $VerificationCode) {
            Apipublic::jsonError("请输入正确的短信验证码");
        }
        $password = input('password'); // 密码
        if (!$password) {
            Apipublic::jsonError("请输入密码");
        }
        $ToRegister = $UserModel->data([
            'nickname' => 'FX' . substr($phone, 0, 3) . '****' . substr($phone, 7),
            'phone' => $phone,
            'password' => md5($password),
            'add_time' => time(),
            'login_last_time' => 0,
            'avatar' => '',
            'money' => 0,
            'examine_money' => 0
        ])->save();
        if ($ToRegister > 0) {
            Apipublic::jsonSuccess("恭喜您,注册成功");
        } else {
            Apipublic::jsonError("注册失败");
        }
    }

    /*
     * 退出登录
     * @id 用户ID
     * @return json
     */
    public function loginOut()
    {
        $doLoginOut = UserModel::where('id', Session::get("userData")["id"])
            ->update(['status' => 2]); // 改为离线状态
        if ($doLoginOut > 0) {
            Session::delete('userData'); // 删除SESSION
            return Apipublic::jsonSuccess("退出成功");
        } else {
            Session::delete('userData'); // 删除SESSION
            return Apipublic::jsonError("退出失败");
        }
    }

    /**
     * 找回密码的发送短信
     * @return Apipublic
     * @throws \think\Exception
     */
    public function sendBackPassword()
    {
        $redis = new Redis(config("redis"));
        $phone = input('phone');
        if (!$phone) {
            return Apipublic::jsonError("请输入您的手机号");
        }
        if (is_mobile($phone) == false) {
            return Apipublic::jsonError("请输入正确的手机号");
        }
        $RegisterOrNot = UserModel::where('phone', $phone)->count(); // 查询改手机号是否存在
        if (!$RegisterOrNot) {
            return Apipublic::jsonError("该手机号未注册");
        }
        // 如果缓存不存在验证码则发送短信
        if ($redis->get("smsCode" . $phone) == false) {
            $doSmsMsg = (new Smsmsg())->sendSms($phone);
            $doSmsMsgToArr = object_to_array($doSmsMsg['acsResponse']);
            if ($doSmsMsgToArr['Code'] == 'isv.BUSINESS_LIMIT_CONTROL') {
                return Apipublic::jsonError("由于服务器限制，您已经发送短信超过最大限制，请明天再尝试");
            }
            $redis->set("backPassword" . $phone, $doSmsMsg['code'], 300); // 5分钟过期
            return Apipublic::jsonSuccess("短信验证码已发送,请注意查收");
        }
        return Apipublic::jsonError("验证码5分钟内有效,请5分钟后再次尝试获取新的验证码");
    }

    /**
     * 找回密码
     * @return Apipublic
     */
    public function retrievePassword()
    {
        $redis = new Redis(config("redis"));
        $phone = input('phone'); // 手机号
        $UserModel = new UserModel;
        if (!$phone) {
            Apipublic::jsonError("请输入手机号");
        }
        if (is_mobile($phone) == false) {
            Apipublic::jsonError("请输入正确的手机号");
        }
        $VerificationCode = $redis->get("backPassword" . $phone); // 验证码
        if (!$VerificationCode) { // 防止查询验证码时为空的情况下
            Apipublic::jsonError("短信验证码已过期,请重新获取");
        }
        $code = input('code'); // 前台传来的code
        if ($code != $VerificationCode) {
            Apipublic::jsonError("请输入正确的短信验证码");
        }
        $password = input('password'); // 密码
        if (!$password) {
            Apipublic::jsonError("请输入密码");
        }
        $updatePassword = $UserModel->where('phone',$phone)->update([
            'password' => md5($password)
        ]);
        if ($updatePassword > 0){
            return Apipublic::jsonSuccess('密码找回成功,去登录吧');
        }else{
            return Apipublic::jsonError('密码找回失败,请重试');
        }
    }
}