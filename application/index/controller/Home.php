<?php

namespace app\index\controller;

use app\home\model\OrderModel;
use app\home\model\RecomAccountModel;
use think\Db;

use think\Controller;

use think\Session;

class Home extends Controller
{
    // 上传图片接口
    public function uploadImage()
    {
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('file');

        // 移动到框架应用根目录/public/uploads/ 目录下
        if ($file) {
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
            if ($info) {
                $imageType = $info->getExtension(); //输出的为图片后缀名(.jpg/.png)
                // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
                $banner_img = str_replace("\\", "/", $info->getSaveName()); // 转换斜杠
                if (explode('/', $info->getMime())[1] == 'jpg' || explode('/', $info->getMime())[1] == 'jpeg') {
                    // 获取完整路径
                    $image = ROOT_PATH . "public/uploads/" . $banner_img;
                    // 加载图片资源
                    $src = @imagecreatefromjpeg($image);
                    list($width, $height) = getimagesize($image); //获取图片的高度
                    $newwidth = $width;   //宽高可以设置
                    $newheight = $height;
                    $tmp = imagecreatetruecolor($newwidth, $newheight); //生成新的宽高
                    imagecopyresampled($tmp, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height); //缩放图像
                    $output = imagejpeg($tmp, $image, 70); //第三个参数(0~100);
                } else if (explode('/', $info->getMime())[1] == 'png') {   // png 压缩效率不稳定
                    $image = ROOT_PATH . "public/uploads/" . $banner_img;
                    $src = @imagecreatefrompng($image);
                    list($width, $height) = getimagesize($image);
                    $newwidth = $width;
                    $newheight = $height;
                    $tmp = imagecreatetruecolor($newwidth, $newheight);
                    /* --- 用以处理缩放png图透明背景变黑色问题 开始 --- */
                    $color = imagecolorallocate($tmp, 255, 255, 255);
                    imagecolortransparent($tmp, $color);
                    imagefill($tmp, 0, 0, $color);
                    /* --- 用以处理缩放png图透明背景变黑色问题 结束 --- */
                    imagecopyresampled($tmp, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
                    $output = imagepng($tmp, $image, 9);  //这个图片的第三参数(1~9)
                    // imagedestroy($tmp);
                }
                $banner_img = '/uploads/' . $banner_img;
                return json(array('code' => 0, 'msg' => '上传成功', 'url' => $banner_img));
            } else {
                // 上传失败获取错误信息
                return json($file->getError());
            }
        }
    }


    // 发布游戏账号
    public function pubGameAccount()
    {
        $game_id = input('game_id'); // 游戏id
        $servers = input('servers'); // 服务器
        $area = input('area'); // 大区
        $account = input('account'); // 账号
        $pwd = input('pwd'); // 密码
        $account_title = input('account_title'); // 账号标题
        $account_intro = input('account_intro'); // 账号描述
        $screenshotsone = input('screenshotsone'); // 截图1
        $screenshotstwo = input('screenshotstwo'); // 截图2
        $screenshotsthree = input('screenshotsthree'); // 截图3
        $screenshotsfour = input('screenshotsfour'); // 截图4
        $screenshotsfive = input('screenshotsfive'); // 截图5
        $experience = input('experience'); // 0元体验
        $deposit = input('deposit'); // 押金
        $price = input('price'); // 价格 出租单价
        $day_price = input('day_price'); // 日租价
        $pack_night_price = input('pack_night_price'); // 包夜价
        $shortest_time = input('shortest_time'); // 最短租时
        $is_grounding = 2; // 用户发布 默认下架
        $is_recommend = 2; // 用户发布 默认不推荐
        $uid = Session::get('userData')['id']; // 用户发布
        $audit = 1; // 用户发布默认显示待审核
        $lease_num = 0; // 默认出租次数为0
        $labelone = input('labelone'); // 标签1
        $labeltwo = input('labeltwo'); // 标签2
        $labelthree = input('labelthree'); // 标签3
        $labelfour = input('labelfour'); // 标签4
        $level = input('level'); // 段位等级 排位
        $vip_level = input('vip_level'); // VIP等级,贵族等级
        $lease_type = 2; // 发布默认为未出租
        $rank = input('rank'); // 是否允许排位
        $reg_type = input('reg_type'); // 登录方式
        $add_time = time(); // 账号发布时间
        $game_nickname = input('game_nickname'); // 角色游戏昵称
        $id = input('id'); // 账号ID

        if ($id) {
            $AccountArray = [
                'game_id' => $game_id,
                'servers' => $servers,
                'area' => $area,
                'account' => $account,
                'pwd' => $pwd,
                'account_title' => $account_title,
                'account_intro' => $account_intro,
                'screenshotsone' => $screenshotsone,
                'screenshotstwo' => $screenshotstwo,
                'screenshotsthree' => $screenshotsthree,
                'screenshotsfour' => $screenshotsfour,
                'screenshotsfive' => $screenshotsfive,
                'experience' => $experience,
                'deposit' => $deposit,
                'price' => $price,
                'day_price' => $day_price,
                'pack_night_price' => $pack_night_price,
                'shortest_time' => $shortest_time,
                'labelone' => $labelone,
                'labeltwo' => $labeltwo,
                'labelthree' => $labelthree,
                'labelfour' => $labelfour,
                'level' => $level,
                'vip_level' => $vip_level,
                'rank' => $rank,
                'reg_type' => $reg_type,
                'game_nickname' => $game_nickname
            ];
        } else {
            $AccountArray = [
                'game_id' => $game_id,
                'servers' => $servers,
                'area' => $area,
                'account' => $account,
                'pwd' => $pwd,
                'account_title' => $account_title,
                'account_intro' => $account_intro,
                'screenshotsone' => $screenshotsone,
                'screenshotstwo' => $screenshotstwo,
                'screenshotsthree' => $screenshotsthree,
                'screenshotsfour' => $screenshotsfour,
                'screenshotsfive' => $screenshotsfive,
                'experience' => $experience,
                'deposit' => $deposit,
                'price' => $price,
                'day_price' => $day_price,
                'pack_night_price' => $pack_night_price,
                'shortest_time' => $shortest_time,
                'is_grounding' => $is_grounding,
                'is_recommend' => $is_recommend,
                'uid' => $uid,
                'audit' => $audit,
                'lease_num' => $lease_num,
                'labelone' => $labelone,
                'labeltwo' => $labeltwo,
                'labelthree' => $labelthree,
                'labelfour' => $labelfour,
                'level' => $level,
                'vip_level' => $vip_level,
                'lease_type' => $lease_type,
                'rank' => $rank,
                'reg_type' => $reg_type,
                'add_time' => $add_time,
                'game_nickname' => $game_nickname
            ];
        }


        if ($id) {
            $editAccount = $this->editGameAccount($id, $AccountArray);

            if ($editAccount == 0) {

                return json(array('code' => 0, 'msg' => '编辑成功'));
            } else {

                return json(array('code' => -1, 'msg' => '编辑失败'));
            }
        }

        $_execute = Db::name('rent_pubgame_account')->insert($AccountArray);

        if ($_execute > 0) {

            return json(array('code' => 0, 'msg' => '发布成功'));
        } else {

            return json(array('code' => -1, 'msg' => '发布失败'));
        }
    }

    // 编辑游戏账号
    private function editGameAccount($id, $AccountArray)
    {

        $_execute = Db::name('rent_pubgame_account')->where('id', $id)->update($AccountArray);

        if ($_execute > 0) {

            return 0;
        } else {

            return -1;
        }
    }

    // 添加账号选择的游戏
    public function DoAllGame()
    {

        $_execute = Db::name('rent_game')->field('id,game_name')->order('id desc')->select();

        return json(array('code' => 0, 'msg' => '添加账号选择的游戏获取成功', 'data' => $_execute));
    }

    // 查询游戏服务器
    public function FindGameServer()
    {

        // 服务器类型
        $type = input('type');

        // 游戏名称
        $GameName = input('GameName');

        if ($GameName == '王者荣耀') {
            switch ($type) {
                case 'QQ安卓':
                    $list = Db::name('rent_game_server')->where('wzry_type', 1)->where('type', 'android')->where('game_type', 1)->field('id,area_name')->select();
                    break;
                case 'QQ苹果':
                    $list = Db::name('rent_game_server')->where('wzry_type', 1)->where('type', 'ios')->where('game_type', 1)->field('id,area_name')->select();
                    break;
                case '微信安卓':
                    $list = Db::name('rent_game_server')->where('wzry_type', 2)->where('type', 'android')->where('game_type', 1)->field('id,area_name')->select();
                    break;
                default:
                    break;
            }
        } else if ($GameName == '英雄联盟') {
            switch ($type) {
                case '电信':
                    $list = Db::name('rent_game_server')->where('lol_type', 1)->where('game_type', 2)->field('id,area_name')->select();
                    break;
                case '网通':
                    $list = Db::name('rent_game_server')->where('lol_type', 2)->where('game_type', 2)->field('id,area_name')->select();
                    break;
                case '全网络大区':
                    $list = Db::name('rent_game_server')->where('lol_type', 3)->where('game_type', 2)->field('id,area_name')->select();
                    break;
                default:
                    break;
            }
        } else if ($GameName == '生死狙击') {
            switch ($type) {
                case '电信':
                    $list = Db::name('rent_game_server')->where('ssjj_type', 1)->where('game_type', 3)->field('id,area_name')->select();
                    break;
                case '联通':
                    $list = Db::name('rent_game_server')->where('ssjj_type', 2)->where('game_type', 3)->field('id,area_name')->select();
                    break;
                case '双线':
                    $list = Db::name('rent_game_server')->where('ssjj_type', 3)->where('game_type', 3)->field('id,area_name')->select();
                    break;
                default:
                    break;
            }
        } else if ($GameName == '穿越火线') {
            switch ($type) {
                case '电信区':
                    $list = Db::name('rent_game_server')->where('cf_type', 1)->where('game_type', 4)->field('id,area_name')->select();
                    break;
                case '网通区':
                    $list = Db::name('rent_game_server')->where('cf_type', 2)->where('game_type', 4)->field('id,area_name')->select();
                    break;
                case '其他大区':
                    $list = Db::name('rent_game_server')->where('cf_type', 3)->where('game_type', 4)->field('id,area_name')->select();
                    break;
                case '全区全服':
                    $list = Db::name('rent_game_server')->where('cf_type', 4)->where('game_type', 4)->field('id,area_name')->select();
                    break;
                default:
                    break;
            }
        }

        return json(array('code' => 0, 'msg' => 'success', 'list' => $list));
    }

    // 我的出租账号
    /*
        1.全部账号
            发布了几个就是几个
        2.出租中
            发布的账号已经 审核成功 了并且 已经上架 的再加上状态是 出租中 的状态
        3.待租
            发布的账号已经 审核成功 了并且 已经上架 的再加上状态是 未出租 的状态
        4.待审核
            发布的账号就是未审核的状态
        5.审核失败
            发布的账号就是审核被驳回并没有审核成功的状态
        6.已下架
            发布的账号审核成功的并且下架的账号
    */
    public function MyRentalAccountNumber()
    {

        $page = input('page'); //当前页
        $limit = input('limit'); //一页数量
        $accountModel = new RecomAccountModel();
        $userData = Session::get('userData');
        //用户唯一标识
        $uid = $userData['id'];
        $AccountArray = []; //存放账号的数组
        // 真实返回给前端的总数count
        $countYes = 0;
        // 前端请求的是哪个类型
        $type = input('type');

        // 全部账号
        $AllAccounts = $accountModel->where('uid', $uid)
            ->page($page)
            ->limit($limit)
            ->field('id,account_title,game_id,servers,area,game_nickname,shortest_time,price,deposit,audit,is_grounding,lease_type')
            ->select();
        foreach ($AllAccounts as $kk => $vv) {
            $AllAccounts[$kk]['audit'] = $this->JudgeTheAccountAuditStatus($vv['audit']);
            // 服务器
            $GameName = Db::name('rent_game')->where('id', $vv['game_id'])->value('game_name'); // 游戏名称
            $AllAccounts[$kk]['game_id'] = $GameName;
            $servers = Db::name('rent_server')->where('id', $vv['servers'])->value('server_name'); // 服务器
            $AllAccounts[$kk]['serverName'] = $servers;
            $area = Db::name('rent_area')->where('id', $vv['area'])->value('area_name'); // 大区
            $AllAccounts[$kk]['areaName'] = $area;
            $AllAccounts[$kk]['theserver'] = $AllAccounts[$kk]['game_id'] . '-' . $AllAccounts[$kk]['serverName'] . '-' . $AllAccounts[$kk]['areaName']; // 合并
        }
        $AccountArray[0] = $AllAccounts;
        $countYes1 = Db::name('rent_pubgame_account')->where('uid', $uid)->count(); //总数，前端利于做分页

        // 出租中
        $Renting = $accountModel->where('uid', $uid)
            ->where('lease_type', 1)
            ->where('is_grounding', 1)
            ->where('audit', 2)
            ->page($page)->limit($limit)
            ->field('id,account_title,game_id,servers,area,game_nickname,shortest_time,price,deposit,audit,is_grounding,lease_type')
            ->select();
        foreach ($Renting as $kk => $vv) {
            $Renting[$kk]['audit'] = $this->JudgeTheAccountAuditStatus($vv['audit']);
            // 服务器
            $GameName = Db::name('rent_game')->where('id', $vv['game_id'])->value('game_name'); // 游戏名称
            $Renting[$kk]['game_id'] = $GameName;
            $servers = Db::name('rent_server')->where('id', $vv['servers'])->value('server_name'); // 服务器
            $Renting[$kk]['serverName'] = $servers;
            $area = Db::name('rent_area')->where('id', $vv['area'])->value('area_name'); // 大区
            $Renting[$kk]['areaName'] = $area;
            $Renting[$kk]['theserver'] = $Renting[$kk]['game_id'] . '-' . $Renting[$kk]['serverName'] . '-' . $Renting[$kk]['areaName']; // 合并
        }
        $AccountArray[1] = $Renting;
        $countYes2 = Db::name('rent_pubgame_account')->where('uid', $uid)->where('lease_type', 1)->where('is_grounding', 1)->where('audit', 2)->count(); //总数，前端利于做分页
        // 待租
        $Rentable = $accountModel->where('uid', $uid)
            ->where('lease_type', 2)
            ->where('is_grounding', 1)
            ->where('audit', 2)
            ->page($page)
            ->limit($limit)
            ->field('id,account_title,game_id,servers,area,game_nickname,shortest_time,price,deposit,audit,is_grounding,lease_type')
            ->select();
        foreach ($Rentable as $kk => $vv) {
            $Rentable[$kk]['audit'] = $this->JudgeTheAccountAuditStatus($vv['audit']);
            // 服务器
            $GameName = Db::name('rent_game')->where('id', $vv['game_id'])->value('game_name'); // 游戏名称
            $Rentable[$kk]['game_id'] = $GameName;
            $servers = Db::name('rent_server')->where('id', $vv['servers'])->value('server_name'); // 服务器
            $Rentable[$kk]['serverName'] = $servers;
            $area = Db::name('rent_area')->where('id', $vv['area'])->value('area_name'); // 大区
            $Rentable[$kk]['areaName'] = $area;
            $Rentable[$kk]['theserver'] = $Rentable[$kk]['game_id'] . '-' . $Rentable[$kk]['serverName'] . '-' . $Rentable[$kk]['areaName']; // 合并
        }
        $AccountArray[2] = $Rentable;
        $countYes3 = Db::name('rent_pubgame_account')->where('uid', $uid)->where('lease_type', 2)->where('is_grounding', 1)->where('audit', 2)->count(); //总数，前端利于做分页
        // 待审核
        $ToBeReviewed = $accountModel->where('uid', $uid)
            ->where('audit', 1)
            ->page($page)
            ->limit($limit)
            ->field('id,account_title,game_id,servers,area,game_nickname,shortest_time,price,deposit,audit,is_grounding,lease_type')
            ->select();
        foreach ($ToBeReviewed as $kk => $vv) {
            $ToBeReviewed[$kk]['audit'] = $this->JudgeTheAccountAuditStatus($vv['audit']);
            // 服务器
            $GameName = Db::name('rent_game')->where('id', $vv['game_id'])->value('game_name'); // 游戏名称
            $ToBeReviewed[$kk]['game_id'] = $GameName;
            $servers = Db::name('rent_server')->where('id', $vv['servers'])->value('server_name'); // 服务器
            $ToBeReviewed[$kk]['serverName'] = $servers;
            $area = Db::name('rent_area')->where('id', $vv['area'])->value('area_name'); // 大区
            $ToBeReviewed[$kk]['areaName'] = $area;
            $ToBeReviewed[$kk]['theserver'] = $ToBeReviewed[$kk]['game_id'] . '-' . $ToBeReviewed[$kk]['serverName'] . '-' . $ToBeReviewed[$kk]['areaName']; // 合并
        }
        $AccountArray[3] = $ToBeReviewed;
        $countYes4 = Db::name('rent_pubgame_account')->where('uid', $uid)->where('audit', 1)->count(); //总数，前端利于做分页
        // 审核失败
        $AuditFailure = $accountModel->where('uid', $uid)
            ->where('audit', 3)
            ->page($page)->limit($limit)
            ->field('id,account_title,game_id,servers,area,game_nickname,shortest_time,price,deposit,audit,is_grounding,lease_type')
            ->select();
        foreach ($AuditFailure as $kk => $vv) {
            $AuditFailure[$kk]['audit'] = $this->JudgeTheAccountAuditStatus($vv['audit']);
            // 服务器
            $GameName = Db::name('rent_game')->where('id', $vv['game_id'])->value('game_name'); // 游戏名称
            $AuditFailure[$kk]['game_id'] = $GameName;
            $servers = Db::name('rent_server')->where('id', $vv['servers'])->value('server_name'); // 服务器
            $AuditFailure[$kk]['serverName'] = $servers;
            $area = Db::name('rent_area')->where('id', $vv['area'])->value('area_name'); // 大区
            $AuditFailure[$kk]['areaName'] = $area;
            $AuditFailure[$kk]['theserver'] = $AuditFailure[$kk]['game_id'] . '-' . $AuditFailure[$kk]['serverName'] . '-' . $AuditFailure[$kk]['areaName']; // 合并
        }
        $AccountArray[4] = $AuditFailure;
        $countYes5 = Db::name('rent_pubgame_account')->where('uid', $uid)->where('audit', 3)->count(); //总数，前端利于做分页
        // 已下架
        $OffTheShelf = $accountModel->where('audit', 2)
            ->where('is_grounding', 2)
            ->page($page)
            ->limit($limit)
            ->field('id,account_title,game_id,servers,area,game_nickname,shortest_time,price,deposit,audit,is_grounding,lease_type,is_grounding')
            ->select();
        foreach ($OffTheShelf as $kk => $vv) {
            $OffTheShelf[$kk]['audit'] = $this->JudgeTheAccountAuditStatus($vv['audit']);
            // 服务器
            $GameName = Db::name('rent_game')->where('id', $vv['game_id'])->value('game_name'); // 游戏名称
            $OffTheShelf[$kk]['game_id'] = $GameName;
            $servers = Db::name('rent_server')->where('id', $vv['servers'])->value('server_name'); // 服务器
            $OffTheShelf[$kk]['serverName'] = $servers;
            $area = Db::name('rent_area')->where('id', $vv['area'])->value('area_name'); // 大区
            $OffTheShelf[$kk]['areaName'] = $area;
            $OffTheShelf[$kk]['theserver'] = $OffTheShelf[$kk]['game_id'] . '-' . $OffTheShelf[$kk]['serverName'] . '-' . $OffTheShelf[$kk]['areaName']; // 合并
        }
        $AccountArray[5] = $OffTheShelf;
        $countYes6 = Db::name('rent_pubgame_account')->where('uid', $uid)->where('audit', 2)->where('is_grounding', 2)->count(); //总数，前端利于做分页

        if ($type == 0) {
            $countYes = $countYes1;
        } else if ($type == 1) {
            $countYes = $countYes2;
        } else if ($type == 2) {
            $countYes = $countYes3;
        } else if ($type == 3) {
            $countYes = $countYes4;
        } else if ($type == 4) {
            $countYes = $countYes5;
        } else if ($type == 5) {
            $countYes = $countYes6;
        }

        return json([
            'code' => 0,
            'msg' => 'success',
            'list' => $AccountArray,
            'count' => $countYes
        ]);

    }


    // 判断账号审核状态
    private function JudgeTheAccountAuditStatus($audit)
    {

        if ($audit == 1) {

            return '待审核';
        } else if ($audit == 2) {

            return '审核成功';
        } else if ($audit == 3) {

            return '审核不通过';
        }
    }

    // 网站设置信息
    public function WebsiteSettingsInformation()
    {

        $_execute = Db::name('rent_base')->where('id', 1)->find();
        $_execute['logo'] = allUrl($_execute['logo']); // 网站logo
        $_execute['icon'] = allUrl($_execute['icon']); // 网站icon
        unset($_execute['id']); // 删除id
        return json([
            'code' => 0,
            'msg' => '网站基本信息获取成功',
            'data' => $_execute
        ]);

    }

    // 编辑发布账号查看详细信息
    public function EditAndPublishAccountToViewDetails()
    {

        $id = input('id'); // 游戏账号id

        $data = Db::name('rent_pubgame_account')->where('id', $id)->find();

        $data['screenshotsone'] = allUrl($data['screenshotsone']);
        $data['screenshotstwo'] = allUrl($data['screenshotstwo']);
        $data['screenshotsthree'] = allUrl($data['screenshotsthree']);
        $data['screenshotsfour'] = allUrl($data['screenshotsfour']);
        $data['screenshotsfive'] = allUrl($data['screenshotsfive']);

        return json([
            'code' => 0,
            'msg' => 'success',
            'data' => $data
        ]);

    }

    //我的租号订单
    public function MyAccountList()
    {
        $uid = Session::get('userData')['id']; // 用户id

        //模型查询
        $orderModel = new OrderModel();
        $order = $orderModel->where('uid', $uid)
            ->paginate(10, false);

        return json([
            "code" => 0,
            'msg' => 'success',
            "data" => $order
        ]);

    }

    /**
     * 我的出租订单接口数据
     */
    public function myRentGoodsList()
    {
        $page = 1; //当前页
        $limit = 10; //一页数量
        //获取用户id
        $userData = Session::get("userData");
        $uid = $userData['id'];
        $AccountArray = []; //存放账号的数组
        $AccountListId = [];  //存放已发布账号的id
        //返回前端的数据条数
        $countLength = 0;
        //用户发布的全部数据
        $AllAccounts = Db::name('rent_pubgame_account')->where('uid', $uid)->page($page)->limit($limit)->field('id,account_title,game_id,servers,area,game_nickname,shortest_time,price,deposit,audit')->select();
        foreach ($AllAccounts as $kk => $vv) {
            $AccountListId[$kk] = $vv['id'];
        }
        $rentList = Db::name("rent_order")->where("goodsId", "in", $AccountListId)->select();


        //查询租号方，将租号名字放进去,
        foreach ($rentList as $kk => $vv) {
            $userName = Db::name("rent_user")->where("id", $vv['uid'])->value('nickname');
            $rentList[$kk]["username"] = $userName;
            //修改时间
            $rentHour = floor((strtotime($vv["endTime"]) - strtotime($vv["startTime"])) / 3600);
            $rentList[$kk]['rent_hour'] = $rentHour;

            foreach ($AllAccounts as $kk2 => $vv2) {
                if ($vv['goodsId'] == $vv2['id']) {
                    // 将押金，价格，账号标题添加进去
                    $rentList[$kk]['deposit'] = $vv2['deposit'];
                    $rentList[$kk]['price'] = $vv2['price'];
                    $rentList[$kk]['account_title'] = $vv2['account_title'];
                }
            }
        }

        return json([
            "code" => 0,
            'msg' => 'success',
            "list" => $rentList,
            'count' => count($rentList)
        ]);
    }
}
