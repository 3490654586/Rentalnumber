<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\Db;
// 应用公共文件s

function allUrl($url)
{

    $url = input('server.REQUEST_SCHEME') . '://' . input('server.SERVER_NAME') . $url;

    return $url;
}

// 对象转数组
function object_to_array($obj)
{

    $obj = (array)$obj;

    foreach ($obj as $k => $v) {

        if (gettype($v) == 'resource') {

            return;
        }

        if (gettype($v) == 'object' || gettype($v) == 'array') {

            $obj[$k] = (array)object_to_array($v);
        }
    }
    return $obj;
}


/**

 * 验证输入的手机号码

 * @access  public

 * @param   string      $user_mobile      需要验证的手机号码

 * @return bool

 */

function is_mobile($user_mobile)
{

    $chars = "/^((\(\d{2,3}\))|(\d{3}\-))?1(3|5|7|8|9)\d{9}$/";

    if (preg_match($chars, $user_mobile)) {

        return true;
    } else {

        return false;
    }
}

/**
 * 计算时间相差多少天，多少小时
 * edit www.jbxue.com
 */
function DateDiff($startDay, $endDay)
{
    $start = strtotime($startDay);
    $end = strtotime($endDay);
    $diff = abs($start - $end);
    $day = '';
    $vals = array('天' => '86400', '时' => '3600', '分' => '60', '秒' => '1');
    foreach ($vals as $key => $value) {
        if ($diff >= $value) {
            $d = round($diff / $value);
            $diff %= $value;
            $day .= $d . $key;
        }
    }
    return $day . '前';
}

// 随机生成首页热门游戏租用账号的用户信息
function randomMobile($n)
{
    $tel_arr = array(
        '130','131','132','133','134','135','136','137','138','139','144','147','150','151','152','153','155','156','157','158','159','176','177','178','180','181','182','183','184','185','186','187','188','189',
    );
    for($i = 0; $i < $n; $i++) {
        $phone[] = $tel_arr[array_rand($tel_arr)].mt_rand(1000,9999).mt_rand(1000,9999);
    }
    for($j = 0; $j < $n; $j++) {
        $hour[] = rand(1,10);
    }
    for($d = 0; $d < $n; $d++) {
        $money[] = randMoney(1,10);
    }
    $newArr = [
        "phone" => array_unique($phone),
        "hour" => $hour,
        "money" => $money
    ];
    return $newArr;
}

// 生成随机金额
function randMoney($min, $max){
    $num = mt_rand($min, $max);
    $point = randFloat();
    $point = floor($point * 100) / 100;
    $money = $num + $point;
    return $money;
}

// 生成随机金额
function randFloat($min=0, $max=1){
    return $min + mt_rand()/mt_getrandmax() * ($max-$min);
}

/**
 * 判断账号是否被出租,防止用户停留未出租页面继续使用功能
 */
function preventRentError($id)
{
    $leaseType = Db::name("rent_pubgame_account")->where("id",$id)->value("lease_type");
    return $leaseType;
}