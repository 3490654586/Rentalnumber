<?php

namespace app\home\service;

ini_set("display_errors", "on");

require_once ROOT_PATH . 'public/shortmessage/api_sdk/vendor/autoload.php';


use app\common\api\Apipublic;
use think\Controller;
use Aliyun\Core\Config;
use Aliyun\Core\Profile\DefaultProfile;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;

// 加载区域结点配置
Config::load();

class Smsmsg extends Controller
{
    static $acsClient = null;

    /**
     * 取得AcsClient
     *
     * @return DefaultAcsClient
     */
    public static function getAcsClient()
    {
        //产品名称:云通信短信服务API产品,开发者无需替换
        $product = "Dysmsapi";
        //产品域名,开发者无需替换
        $domain = "dysmsapi.aliyuncs.com";
        // TODO 此处需要替换成开发者自己的AK (https://ak-console.aliyun.com/)
        $accessKeyId = "LTAI4GJDsThxxZJdfvf1QHUm"; // AccessKeyId
        $accessKeySecret = "51vOo6dG20ZZ4jnUnajRYxwSqWkS7f"; // AccessKeySecret
        // 暂时不支持多Region
        $region = "cn-hangzhou";
        // 服务结点
        $endPointName = "cn-hangzhou";
        if (static::$acsClient == null) {
            //初始化acsClient,暂不支持region化
            $profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);
            // 增加服务结点
            DefaultProfile::addEndpoint($endPointName, $region, $product, $domain);
            // 初始化AcsClient用于发起请求
            static::$acsClient = new DefaultAcsClient($profile);
        }
        return static::$acsClient;
    }

    /**
     * 发送验证码短信
     * @return stdClass
     */
    public static function sendSms()
    {
        // 初始化SendSmsRequest实例用于设置发送短信的参数
        $request = new SendSmsRequest();
        // 必填，设置短信接收号码
        $phone = input('phone');
        $request->setPhoneNumbers($phone);
        // 必填，设置签名名称，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $request->setSignName("飞讯租号");
        // 必填，设置模板CODE，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $request->setTemplateCode("SMS_200694369");
        // 可选，设置模板参数, 假如模板中存在变量需要替换则为必填项
        $code = rand(100000, 999999);
        $request->setTemplateParam(json_encode(["code" => $code], JSON_UNESCAPED_UNICODE));
        try {
            // 发起访问请求
            $acsResponse = static::getAcsClient()->getAcsResponse($request);
            $arr = [
                'code' => $code,
                'acsResponse' => $acsResponse
            ];
            return $arr;
        } catch (\Exception $e) {
            return [
                "code" => -1,
                "msg" => "短信发送请求失败"
            ];
        }
    }

    /**
     * 发送下单成功的短信
     * @return stdClass
     * $phone 短信接收号码
     * $nickName 用户昵称
     * $startTime 租号开始时间
     * $endTime 租号结束时间
     */
    public static function sendOrderShortMessage($phone, $nickName, $num, $startTime, $endTime)
    {
        // 初始化SendSmsRequest实例用于设置发送短信的参数
        $request = new SendSmsRequest();
        // 必填，设置短信接收号码
        $request->setPhoneNumbers($phone);
        // 必填，设置签名名称，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $request->setSignName("忘忧网络");
        // 必填，设置模板CODE，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $request->setTemplateCode("SMS_205391401");
        // 可选，设置模板参数, 假如模板中存在变量需要替换则为必填项
        $request->setTemplateParam(json_encode(array(  // 短信模板中字段的值
            "name" => $nickName,
            "num" => $num,
            "startTime" => date('Y-m-d H:i:s', $startTime),
            "endTime" => date('Y-m-d H:i:s', $endTime)
        ), JSON_UNESCAPED_UNICODE));
        try{
            // 发起访问请求
            $acsResponse = static::getAcsClient()->getAcsResponse($request);
            $arr = [
                'acsResponse' => $acsResponse
            ];
            return $arr;
        }catch (\Exception $e){
            return Apipublic::jsonError("下单通知发送失败");
        }
    }

    /**
     * 发送租号结束成功的短信
     * @return stdClass
     * $phone 短信接收号码
     * $nickName 用户昵称
     * $startTime 租号开始时间
     * $endTime 租号结束时间
     */
    public static function sendOrderShortMessageIsOk($phone, $name, $num)
    {
        // 初始化SendSmsRequest实例用于设置发送短信的参数
        $request = new SendSmsRequest();

        // 必填，设置短信接收号码

        $request->setPhoneNumbers($phone);

        // 必填，设置签名名称，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $request->setSignName("忘忧网络");

        // 必填，设置模板CODE，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $request->setTemplateCode("SMS_205391709");

        // 可选，设置模板参数, 假如模板中存在变量需要替换则为必填项
        $request->setTemplateParam(json_encode(array(  // 短信模板中字段的值
            "name" => $name,
            "num" => $num,
        ), JSON_UNESCAPED_UNICODE));
        try{
            // 发起访问请求
            $acsResponse = static::getAcsClient()->getAcsResponse($request);

            $arr = [
                'acsResponse' => $acsResponse
            ];
            return $arr;
        }catch (\Exception $e){
            return Apipublic::jsonError("订单结束通知发送失败");
        }

    }

    /**
     * 发送撤单短信
     * @param $name 号主
     * @param $num 货架号
     */
    public static function sendOrderCancellationsMessage($name,$num)
    {
        $request = new SendSmsRequest();
        $request->setSignName("忘忧网络");
        $request->setTemplateCode("SMS_206550104");
        $request->setTemplateParam(
            json_encode([
                "name" => $name,
                "num"  => $num
            ],JSON_UNESCAPED_UNICODE)
        );
        try{
            $acsResponse = static::getAcsClient()->getAcsResponse($request);
            $arr = [
                'acsResponse' => $acsResponse
            ];
            return $arr;
        }catch (\Exception $e){
            return Apipublic::jsonError("撤单通知发送失败");
        }
    }
}