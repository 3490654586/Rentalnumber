<?php

namespace app\index\controller;

use think\Db;

use think\Controller;

use think\Session;

class Index extends Controller
{
    // 验证token
    private function tokenIsok($token)
    {

        $is_inAccount = Db::name('rent_admin')->where('token', $token)->count();

        if ($is_inAccount == 0) {

            return -1;
        } else {

            return 1;
        }
    }

    // 后台管理员登录
    public function backstageSignin()
    {

        $token = input('token');

        if ($token) {

            $isOk = $this->tokenIsok($token);
            // 验证成功
            if ($isOk == 1) {

                return json(array('code' => 0, 'msg' => '登录成功', 'token' => $token));
            } else {

                return json(array('code' => 1, 'msg' => 'token验证失败,清除缓存Token重新登录'));
            }
        }
        $account_num = trim(input('account_num'));

        $password = input('password');

        if ($account_num == '' || $account_num == null) {

            return json(array('code' => -1, 'msg' => '账号为空'));
        } else if ($password == '' || $password == null) {

            return json(array('code' => -1, 'msg' => '密码为空'));
        } else {

            $_execute = Db::name('rent_admin')->where('account_num', $account_num)->find();

            if (!empty($_execute)) {

                if ($_execute['password'] == md5($password)) {

                    $token = $_execute['token'];

                    return json(array('code' => 0, 'msg' => '登录成功', 'token' => $token));
                } else {

                    return json(array('code' => -1, 'msg' => '密码错误'));
                }
            } else {

                return json(array('code' => -1, 'msg' => '账号不存在'));
            }
        }
    }

    // 后台添加游戏分类
    public function addgameCate()
    {

        $cate_name = trim(input('cate_name'));

        $add_time = time();

        $GameCateIcon = input('cate_backdrop');


        $is_recom = input('is_recom');

        $hall_is_recom = input('hall_is_recom');

        $descs = input('descs');

        if ($cate_name == '' || $cate_name == null) {

            return json(array('code' => -1, 'msg' => '分类名称为空'));
        } else if (!$GameCateIcon) {

            return json(array('code' => -1, 'msg' => '分类图标为空'));
        } else {

            $data = [

                'cate_name' => $cate_name,

                'add_time' => $add_time,

                'cate_backdrop' => $GameCateIcon,

                'is_recom' => $is_recom,

                'hall_is_recom' => $hall_is_recom,

                'descs' => $descs

            ];

            $is_add = Db::name('rent_game_cate')->where('cate_name', $cate_name)->find();

            if (empty($is_add)) {

                $_execute = Db::name('rent_game_cate')->insert($data);

                if ($_execute > 0) {

                    return json(array('code' => 0, 'msg' => '游戏分类添加成功'));
                } else {

                    return json(array('code' => -1, 'msg' => '游戏分类添加失败'));
                }
            } else {

                return json(array('code' => -1, 'msg' => '游戏分类名称已存在'));
            }
        }
    }

    // 后台编辑游戏分类
    public function editgameCate()
    {

        $id = input('id');

        $cate_name = trim(input('cate_name'));

        $add_time = time();

        $GameCateIcon = input('cate_backdrop');

        $is_recom = input('is_recom');

        $hall_is_recom = input('hall_is_recom');

        $descs = input('descs');

        if ($id == null || $id == '' || $id == 'undefined' || $id == 0) {

            return json(array('code' => -1, 'msg' => '游戏分类id参数不正确'));
        } else if ($cate_name == '' || $cate_name == null) {

            return json(array('code' => -1, 'msg' => '分类名称为空'));
        } else if (!$GameCateIcon) {

            return json(array('code' => -1, 'msg' => '分类图标为空'));
        } else {

            $data = [

                'cate_name' => $cate_name,

                // 'add_time' => $add_time,

                'cate_backdrop' => $GameCateIcon,

                'is_recom' => $is_recom,

                'hall_is_recom' => $hall_is_recom,

                'descs' => $descs

            ];

            $is_add = Db::name('rent_game_cate')->where('cate_name', $cate_name)->find();
            $thatCateName = Db::name('rent_game_cate')->where('id', $id)->value('cate_name');
            if (empty($is_add)) {

                $_execute = Db::name('rent_game_cate')->where('id', $id)->update($data);

                if ($_execute > 0) {

                    return json(array('code' => 0, 'msg' => '游戏分类编辑成功'));
                } else {

                    return json(array('code' => -1, 'msg' => '游戏分类编辑失败'));
                }
            } else {

                if ($cate_name == $thatCateName) {
                    $_execute = Db::name('rent_game_cate')->where('id', $id)->update($data);

                    if ($_execute > 0) {

                        return json(array('code' => 0, 'msg' => '游戏分类编辑成功'));
                    } else {

                        return json(array('code' => -1, 'msg' => '游戏分类编辑失败'));
                    }
                } else {
                    return json(array('code' => -1, 'msg' => '游戏分类名称已存在'));
                }
            }
        }
    }

    // 后台删除游戏分类
    public function delgameCate()
    {

        $id = input('id');

        if ($id == null || $id == '' || $id == 'undefined' || $id == 0) {

            return json(array('code' => -1, 'msg' => '游戏分类id参数不正确'));
        } else {
            // 查询所有游戏id
            $gameIds = Db::name('rent_game')->where('cid', $id)->column('id');
            // 查询所有服务器id
            $serverIds = Db::name('rent_server')->where('game_id', 'in', $gameIds)->column('id');
            // 删除游戏分类
            $_execute = Db::name('rent_game_cate')->where('id', $id)->delete();
            // 删除游戏
            Db::name('rent_game')->where('cid', $id)->delete();
            // 删除游戏服务器
            Db::name('rent_server')->where('game_id', 'in', $gameIds)->delete();
            // 删除游戏区服
            Db::name('rent_area')->where('rid', 'in', $serverIds)->delete();
            // 删除游戏账号
            Db::name('rent_pubgame_account')->where('game_id', 'in', $gameIds)->delete();

            if ($_execute > 0) {

                return json(array('code' => 0, 'msg' => '游戏分类删除成功'));
            } else {

                return json(array('code' => -1, 'msg' => '游戏分类删除失败'));
            }
        }
    }

    // 后台游戏分类列表
    public function gamecatelist()
    {

        $page = input('page');

        $limit = input('limit');

        // 搜索条件
        $searchCateName = input('cate_name');

        $searchDate = input('add_time');

        $where = [];

        $whereTime = '';

        if ($searchCateName) {

            $where['cate_name'] = ['like', '%' . $searchCateName . '%'];
        }

        if ($searchDate) {

            $whereTime = "FROM_UNIXTIME(add_time,'%Y-%m-%d')='" . $searchDate . "'";
        }


        if (!$page) {

            return json(array('code' => 1, 'msg' => '缺少当前页参数'));
        } else {

            $_execute = Db::name('rent_game_cate')->page($page)->limit($limit)->where($where)->where($whereTime)->order('id asc')->select();

            $count = Db::name('rent_game_cate')->where($where)->where($whereTime)->count();

            foreach ($_execute as $k => $v) {

                $_execute[$k]['add_time'] = date('Y-m-d', $v['add_time']);

                $_execute[$k]['cate_backdrop'] = allUrl($v['cate_backdrop']);

                $_execute[$k]['game'] = Db::name('rent_game')->where('cid', $v['id'])->field('game_name as cate_name,add_time,id,hall_is_recom')->select();

                foreach ($_execute[$k]['game'] as $kk => $vv) {

                    $_execute[$k]['game'][$kk]['add_time'] = date('Y-m-d H:i:s', $vv['add_time']);

                    $_execute[$k]['game'][$kk]['id'] = 'ZH' . $vv['id'];

                    $_execute[$k]['game'][$kk]['game_id'] = $vv['id'];
                }
            }

            return json(array('code' => 0, 'msg' => '游戏分类列表数据获取成功', 'list' => $_execute, 'count' => $count));
        }
    }

    // 后台添加游戏
    public function addgame()
    {

        $cid = input('cid');

        $game_name = trim(input('game_name'));

        $thumb = input('thumb');

        $descs = input('descs');

        $is_recom = input('is_recom');

        $is_hot = input('is_hot');

        $hall_is_recom = input('hall_is_recom');

        if (!$cid) {

            return json(array('code' => -1, 'msg' => '未选择游戏分类'));
        } else if (!$game_name) {

            return json(array('code' => -1, 'msg' => '游戏名称为空'));
        } else if (!$thumb) {

            return json(array('code' => -1, 'msg' => '游戏封面图为空'));
        } else if (!$descs) {

            return json(array('code' => -1, 'msg' => '描述内容为空'));
        } else {
            $is_add = Db::name('rent_game')->where('game_name', $game_name)->find();

            if (empty($is_add)) {

                $data = [
                    'game_name' => $game_name,
                    'cid' => $cid,
                    'add_time' => time(),
                    'thumb' => $thumb,
                    'descs' => $descs,
                    'is_recom' => $is_recom,
                    'is_hot' => $is_hot,
                    'hall_is_recom' => $hall_is_recom
                ];

                $_execute = Db::name('rent_game')->insert($data);
                $game_id = Db::name('rent_game')->getLastInsID();
                if ($_execute > 0) {
                    if ($data['game_name'] == '王者荣耀') {
                        Db::name('rent_server')->insertAll([
                            ['server_name' => 'QQ安卓', 'game_id' => $game_id],
                            ['server_name' => 'QQ苹果', 'game_id' => $game_id],
                            ['server_name' => '微信安卓', 'game_id' => $game_id]
                        ]);
                    }
                    if ($data['game_name'] == '生死狙击') {
                        Db::name('rent_server')->insertAll([
                            ['server_name' => '电信', 'game_id' => $game_id],
                            ['server_name' => '联通', 'game_id' => $game_id],
                            ['server_name' => '双线', 'game_id' => $game_id]
                        ]);
                    }
                    if ($data['game_name'] == '穿越火线') {
                        Db::name('rent_server')->insertAll([
                            ['server_name' => '电信区', 'game_id' => $game_id],
                            ['server_name' => '网通区', 'game_id' => $game_id],
                            ['server_name' => '其他大区', 'game_id' => $game_id],
                            ['server_name' => '全区全服', 'game_id' => $game_id],
                        ]);
                    }
                    if ($data['game_name'] == '和平精英') {
                        Db::name('rent_server')->insertAll([
                            ['server_name' => 'QQ账号', 'game_id' => $game_id],
                            ['server_name' => '国际服', 'game_id' => $game_id],
                            ['server_name' => '微信账号', 'game_id' => $game_id]
                        ]);
                    }
                    if ($data['game_name'] == '火影忍者') {
                        Db::name('rent_server')->insertAll([
                            ['server_name' => 'QQ账号', 'game_id' => $game_id],
                            ['server_name' => '微信账号', 'game_id' => $game_id]
                        ]);
                    }
                    if ($data['game_name'] == '英雄联盟') {
                        Db::name('rent_server')->insertAll([
                            ['server_name' => '电信', 'game_id' => $game_id],
                            ['server_name' => '网通', 'game_id' => $game_id],
                            ['server_name' => '全网络大区', 'game_id' => $game_id]
                        ]);
                    }
                    if ($data['game_name'] == '枪战王者') {
                        Db::name('rent_server')->insertAll([
                            ['server_name' => 'QQ账号', 'game_id' => $game_id],
                            ['server_name' => '微信账号', 'game_id' => $game_id]
                        ]);
                    }
                    if ($data['game_name'] == '地下城勇士') {
                        Db::name('rent_server')->insertAll([
                            ['server_name' => '跨一', 'game_id' => $game_id],
                            ['server_name' => '跨二', 'game_id' => $game_id],
                            ['server_name' => '跨三A', 'game_id' => $game_id],
                            ['server_name' => '跨三B', 'game_id' => $game_id],
                            ['server_name' => '跨四', 'game_id' => $game_id],
                            ['server_name' => '跨五', 'game_id' => $game_id],
                            ['server_name' => '跨六', 'game_id' => $game_id],
                            ['server_name' => '跨七A', 'game_id' => $game_id],
                            ['server_name' => '跨七B', 'game_id' => $game_id],
                            ['server_name' => '跨八', 'game_id' => $game_id],
                            ['server_name' => '体验服', 'game_id' => $game_id]
                        ]);
                    }
                    return json(array('code' => 0, 'msg' => '游戏添加成功'));
                } else {

                    return json(array('code' => -1, 'msg' => '游戏添加失败'));
                }
            } else {

                return json(array('code' => -1, 'msg' => '游戏名称已存在'));
            }
        }
    }

    // 导入服务器时选择游戏大区
    public function gameServer()
    {
        $gameCate = Db::name('rent_game_cate')->field('id,cate_name')->select();
        $game = Db::name('rent_game')->field('id,cid,game_name')->select();
        $server = Db::name('rent_server')->field('id,game_id,server_name')->select();
        return json(array('code' => 0, 'msg' => '数据获取成功', 'gameCate' => $gameCate, 'game' => $game, 'server' => $server));
    }

    // 开始导入游戏大区
    public function importGameServer()
    {
        $rid = input('rid'); // 区服id
        $area_name = input('area_name/a'); // 区服名
        foreach ($area_name as $k => $v) {
            $add = Db::name('rent_area')->insert([
                'rid' => $rid,
                'area_name' => $v
            ]);
        }
        if ($add > 0) {
            return json([
                'code' => 0,
                'msg' => '导入成功'
            ]);
        } else {
            return json([
                'code' => 0,
                'msg' => '导入失败'
            ]);
        }
    }

    // 查看当前服务器下面的所有区服
    public function thatAllArea()
    {
        $rid = input('rid');
        $data = Db::name('rent_area')->where('rid', $rid)->paginate(10, false);
        return json(['code' => 0, 'data' => $data, 'msg' => '获取成功']);
    }

    // 删除当前对应的区服
    public function delArea()
    {
        $id = input('id');
        $del = Db::name('rent_area')->where('id', $id)->delete();
        if ($del > 0) {
            return json([
                'code' => 0,
                'msg' => "删除成功"
            ]);
        } else {
            return json([
                'code' => -1,
                'msg' => '删除失败'
            ]);
        }
    }

    // 编辑当前对应的区服
    public function editArea()
    {
        $id = input('id');
        $area_name = input('area_name');
        $edit = Db::name('rent_area')->where('id', $id)->update([
            'area_name' => $area_name
        ]);
        if ($edit > 0) {
            return json([
                'code' => 0,
                'msg' => "修改成功"
            ]);
        } else {
            return json([
                'code' => -1,
                'msg' => '修改失败'
            ]);
        }
    }

    // 添加游戏选择的游戏分类
    public function addgameAllgameCate()
    {

        $list = Db::name('rent_game_cate')->field('id,cate_name')->select();

        return json(array('code' => 0, 'msg' => '游戏分类数据获取成功', 'list' => $list));
    }

    // 游戏列表
    public function gamelist()
    {

        $page = input('page');

        $limit = input('limit');

        // 搜索条件
        $searchCateName = input('game_name');

        $searchDate = input('add_time');

        $where = [];

        $whereTime = '';

        if ($searchCateName) {

            $where['game_name'] = ['like', '%' . $searchCateName . '%'];
        }

        if ($searchDate) {

            $whereTime = "FROM_UNIXTIME(add_time,'%Y-%m-%d')='" . $searchDate . "'";
        }
        if (!$page) {

            return json(array('code' => 1, 'msg' => '缺少当前页参数'));
        } else {

            $_execute = Db::name('rent_game')->page($page)->limit($limit)->order('id asc')->where($where)->where($whereTime)->select();

            $count = Db::name('rent_game')->where($where)->where($whereTime)->count();

            foreach ($_execute as $k => $v) {

                $_execute[$k]['add_time'] = date('Y-m-d', $v['add_time']);

                $_execute[$k]['thumb'] = allUrl($v['thumb']);

                $_execute[$k]['cate_name'] = Db::name('rent_game_cate')->where('id', $v['cid'])->value('cate_name');
            }


            return json(array('code' => 0, 'msg' => '游戏列表数据获取成功', 'list' => $_execute, 'count' => $count));
        }
    }

    // 删除游戏
    public function gamedel()
    {

        $id = input('id');

        if (!$id) {

            return json(array('code' => -1, 'msg' => '缺少id'));
        } else {
            // 查询服务器id
            $serverIds = Db::name('rent_server')->where('game_id', $id)->column('id');
            // 游戏删除
            $_execute = Db::name('rent_game')->where('id', $id)->delete();
            // 游戏服务器删除
            Db::name('rent_server')->where('game_id', $id)->delete();
            // 游戏区服删除
            Db::name('rent_area')->where('rid', 'in', $serverIds)->delete();
            // 游戏账号删除
            Db::name('rent_pubgame_account')->where('game_id', $id)->delete();
            if ($_execute > 0) {

                return json(array('code' => 0, 'msg' => '删除成功'));
            } else {

                return json(array('code' => -1, 'msg' => '删除失败'));
            }
        }
    }

    // 编辑游戏
    public function gameedit()
    {

        $id = input('id');

        $cid = input('cid');

        $game_name = trim(input('game_name'));

        $thumb = input('thumb');

        $descs = input('descs');

        $is_hot = input('is_hot');

        $is_recom = input('is_recom');

        $hall_is_recom = input('hall_is_recom');

        if (!$id) {

            return json(array('code' => -1, 'msg' => '缺少id参数'));
        } else if (!$cid) {

            return json(array('code' => -1, 'msg' => '缺少cid参数'));
        } else if (!$game_name) {

            return json(array('code' => -1, 'msg' => '游戏名称为空'));
        } else if (!$thumb) {

            return json(array('code' => -1, 'msg' => '游戏封面图为空'));
        } else if (!$descs) {

            return json(array('code' => -1, 'msg' => '描述内容为空'));
        } else {
            $is_add = Db::name('rent_game')->where('game_name', $game_name)->find();
            $ThatGameName = Db::name('rent_game')->where('id', $id)->value('game_name');
            if (empty($is_add)) {
                $data = [
                    'game_name' => $game_name,
                    'cid' => $cid,
                    // 'add_time' => time(),
                    'thumb' => $thumb,
                    'descs' => $descs,
                    'is_hot' => $is_hot,
                    'is_recom' => $is_recom,
                    'hall_is_recom' => $hall_is_recom
                ];

                $_execute = Db::name('rent_game')->where('id', $id)->update($data);

                if ($_execute > 0) {

                    return json(array('code' => 0, 'msg' => '游戏编辑成功'));
                } else {

                    return json(array('code' => -1, 'msg' => '游戏编辑失败'));
                }
            } else {
                if ($game_name == $ThatGameName) {
                    $data = [
                        'game_name' => $game_name,
                        'cid' => $cid,
                        // 'add_time' => time(),
                        'thumb' => $thumb,
                        'descs' => $descs,
                        'is_hot' => $is_hot,
                        'is_recom' => $is_recom,
                        'hall_is_recom' => $hall_is_recom
                    ];

                    $_execute = Db::name('rent_game')->where('id', $id)->update($data);

                    if ($_execute > 0) {

                        return json(array('code' => 0, 'msg' => '游戏编辑成功'));
                    } else {

                        return json(array('code' => -1, 'msg' => '游戏编辑失败'));
                    }
                } else {

                    return json(array('code' => -1, 'msg' => '游戏名称已存在'));
                }
            }
        }
    }

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
        $area = input('area'); // 大区
        $servers = input('servers'); // 服务器id
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
        $is_grounding = 1; // 后台发布 默认上架
        $is_recommend = 2; // 后台发布 默认不推荐
        $uid = 0; // 0表示后台发布
        $audit = 2; // 后台发布默认显示审核成功
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
            $AccountArr = [
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
            $AccountArr = [
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
            $editAccount = $this->editGameAccount($id, $AccountArr);
            if ($editAccount == 0) {
                return json(array('code' => 0, 'msg' => '编辑成功'));
            } else {
                return json(array('code' => -1, 'msg' => '编辑失败'));
            }
        }
        $_execute = Db::name('rent_pubgame_account')->insert($AccountArr);
        if ($_execute > 0) {
            return json(array('code' => 0, 'msg' => '发布成功'));
        } else {
            return json(array('code' => -1, 'msg' => '发布失败'));
        }
    }

    // 编辑游戏账号
    private function editGameAccount($id, $AccountArr)
    {
        $_execute = Db::name('rent_pubgame_account')->where('id', $id)->update($AccountArr);
        if ($_execute > 0) {
            return 0;
        } else {
            return -1;
        }
    }

    // 删除游戏账号
    public function delGameAccount()
    {

        $id = input('id');

        if ($id) {

            $_execute = Db::name('rent_pubgame_account')->where('id', $id)->delete();

            if ($_execute > 0) {

                return json(array('code' => 0, 'msg' => '删除成功'));
            } else {

                return json(array('code' => -1, 'msg' => '删除失败'));
            }
        } else {

            return json(array('code' => -1, 'msg' => '缺少ID参数'));
        }
    }

    // 添加账号选择的游戏
    public function DoAllGame()
    {

        $_execute = Db::name('rent_game')->field('id,game_name')->order('id desc')->select();

        return json(array('code' => 0, 'msg' => '添加账号选择的游戏获取成功', 'data' => $_execute));
    }

    // 后台个人中心
    public function PersonalCenter()
    {

        $token = input('token');

        if (!$token) {

            return json(array('code' => -1, 'msg' => 'token为空'));
        } else {

            $_execute = Db::name('rent_admin')->where('token', $token)->find();

            $_execute['head_sculpture'] = allUrl($_execute['head_sculpture']);

            $_execute['add_time'] = date('Y-m-d H:i:s', $_execute['add_time']);

            unset($_execute['id']);

            unset($_execute['password']);

            unset($_execute['token']);

            return json(array('code' => 0, 'msg' => '个人中心获取成功', 'data' => $_execute));
        }
    }

    // 个人中心修改资料
    public function editpersonaldata()
    {

        $fortoken = input('token');

        if (!$fortoken) {

            return json(array('code' => -1, 'msg' => 'token为空'));
        } else {

            $password = input('password');

            if (!$password) {

                $data = [
                    'head_sculpture' => input('head_sculpture'),
                    'mobile_number' => input('mobile_number'),
                    'mailbox' => input('mailbox'),
                    'nickname' => input('nickname')
                ];
            } else {
                $account_num = input('account_num');
                $token = md5($account_num . $password);

                $data = [
                    'head_sculpture' => input('head_sculpture'),
                    'password' => md5($password),
                    'token' => $token,
                    'mobile_number' => input('mobile_number'),
                    'mailbox' => input('mailbox'),
                    'nickname' => input('nickname')
                ];
            }

            $_execute = Db::name('rent_admin')->where('token', $fortoken)->update($data);

            if ($_execute > 0) {

                return json(array('code' => 0, 'msg' => '个人中心修改成功'));
            } else {

                return json(array('code' => -1, 'msg' => '个人中心修改失败'));
            }
        }
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

        return json(array('code' => 0, 'msg' => 'ok', 'list' => $list));
    }

    // 查询账号列表
    public function FindAccountList()
    {

        $page = input('page');

        $limit = input('limit');

        $where = [];

        $account = input('account'); // 游戏账号

        $account_title = input('account_title'); // 账号标题

        $game_nickname = input('game_nickname'); // 角色昵称

        $lease_type = input('lease_type'); // 出租状态

        if ($account) {

            $where['account'] = ['like', '%' . $account . '%'];
        }

        if ($account_title) {

            $where['account_title'] = ['like', '%' . $account_title . '%'];
        }

        if ($game_nickname) {

            $where['game_nickname'] = ['like', '%' . $game_nickname . '%'];
        }

        if ($lease_type) {

            $where['lease_type'] = ['eq', $lease_type];
        }
        // 查询数据
        $list = Db::name('rent_pubgame_account')->where($where)->page($page)->limit($limit)->select();

        $userName = '后台管理员发布';

        $audit = '不需审核';

        foreach ($list as $k => $v) {
            // 发布人昵称
            if ($list[$k]['uid'] != '0') {

                $list[$k]['nickname'] = Db::name('rent_user')->where('id', $v['uid'])->value('nickname');

            } else {

                $list[$k]['nickname'] = $userName;

            }


            if ($list[$k]['uid'] != '0') {
                if ($list[$k]['audit'] == 1) {
                    $list[$k]['audit'] = '待审核';
                } else if ($list[$k]['audit'] == 2) {
                    $list[$k]['audit'] = '审核成功';
                } else if ($list[$k]['audit'] == 3) {
                    $list[$k]['audit'] = '审核不通过';
                }
            } else {
                $list[$k]['audit'] = $audit; // 审核状态
            }


            $GameName = Db::name('rent_game')->where('id', $v['game_id'])->value('game_name');

            $list[$k]['GameName'] = $GameName; // 游戏名称

            $ServerName = Db::name('rent_server')->where('id', $v['servers'])->value('server_name');

            $list[$k]['ServerName'] = $ServerName; // 服务器名称

            // 大区
            $areaName = Db::name('rent_area')->where('id', $v['area'])->value('area_name');
            $list[$k]['areaName'] = $areaName;

            $list[$k]['lease_type'] = $v['lease_type'] == 1 ? "出租中" : "未出租"; // 出租状态

            $list[$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']); // 游戏发布时间

            $list[$k]['is_grounding'] = $v['is_grounding'] == 1 ? "上架" : "下架"; // 是否上架

            $list[$k]['is_recommend'] = $v['is_recommend'] == 1 ? "推荐" : "不推荐"; // 是否推荐

            $list[$k]['vip_level'] = $v['vip_level'] ? $v['vip_level'] : "未知"; // VIP等级

            $list[$k]['screenshotsone'] = allUrl($v['screenshotsone']); // 截图1

            $list[$k]['screenshotstwo'] = allUrl($v['screenshotstwo']); // 截图2

            $list[$k]['screenshotsthree'] = allUrl($v['screenshotsthree']); // 截图3

            $list[$k]['screenshotsfour'] = allUrl($v['screenshotsfour']); // 截图4

            $list[$k]['screenshotsfive'] = allUrl($v['screenshotsfive']); // 截图5

            $list[$k]['experience'] = $v['experience'] == 1 ? "参与" : "不参与"; // 0元体验

            $list[$k]['reg_type'] = $v['reg_type'] == 1 ? "明文登录" : "远程登录"; // 登录方式

            $list[$k]['rank'] = $v['rank'] == 1 ? "允许" : "不允许"; // 排位
        }
        // 查询数据总数
        $count = Db::name('rent_pubgame_account')->where($where)->count();

        return json(array('code' => 0, 'msg' => '账号列表获取成功', 'list' => $list, 'count' => $count));
    }

    // 后台管理员列表
    public function AdminList()
    {

        $page = input('page');

        $limit = input('limit');

        $where = [];

        $nickname = input('nickname'); // 昵称

        $account_num = input('account_num'); // 账号

        $mobile_number = input('mobile_number'); // 手机号码

        $mailbox = input('mailbox'); // 邮箱

        if ($nickname) {

            $where['nickname'] = ['like', '%' . $nickname . '%'];
        }

        if ($account_num) {

            $where['account_num'] = ['like', '%' . $account_num . '%'];
        }

        if ($mobile_number) {

            $where['mobile_number'] = ['like', '%' . $mobile_number . '%'];
        }

        if ($mailbox) {

            $where['mailbox'] = ['like', '%' . $mailbox . '%'];
        }
        $list = Db::name('rent_admin')->where($where)->page($page)->limit($limit)->order('id asc')->select();

        foreach ($list as $k => $v) {
            $list[$k]['head_sculpture'] = allUrl($v['head_sculpture']);
            $list[$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
        }
        // 查询数据总数
        $count = Db::name('rent_admin')->where($where)->count();

        return json(array('code' => 0, 'msg' => '管理员列表获取成功', 'list' => $list, 'count' => $count));
    }

    // 添加管理员
    public function addAdmin()
    {

        $randHead = rand(0, 5); // 0到5随机数,获取默认随机管理员头像

        $head_sculpture = 'AdminAvatar/' . $randHead . '.jpg';

        $data = [
            'nickname' => input('nickname'),
            'account_num' => input('account_num'),
            'password' => md5(input('password')),
            'mobile_number' => input('mobile_number'),
            'mailbox' => input('mailbox'),
            'token' => md5(input('account_num') . input('password')),
            'add_time' => time(),
            'head_sculpture' => $head_sculpture
        ];

        $_execute = Db::name('rent_admin')->insert($data);

        if ($_execute > 0) {

            return json(array('code' => 0, 'msg' => '添加成功'));
        } else {

            return json(array('code' => -1, 'msg' => '添加失败'));
        }
    }

    // 编辑管理员
    public function editAdmin()
    {

        $id = input('id');
        $password = input('password'); // 密码

        if ($password) {
            $data = [
                'nickname' => input('nickname'),
                'password' => md5(input('password')),
                'mobile_number' => input('mobile_number'),
                'mailbox' => input('mailbox'),
                'token' => md5(input('account_num') . input('password'))
            ];
        } else {
            $data = [
                'nickname' => input('nickname'),
                'mobile_number' => input('mobile_number'),
                'mailbox' => input('mailbox')
            ];
        }

        $_execute = Db::name('rent_admin')->where('id', $id)->update($data);

        if ($_execute > 0) {

            return json(array('code' => 0, 'msg' => '编辑成功'));
        } else {

            return json(array('code' => -1, 'msg' => '编辑失败'));
        }
    }

    // 删除管理员
    public function Admindel()
    {

        $id = input('id'); // 管理员id

        $_execute = Db::name('rent_admin')->where('id', $id)->delete();

        if ($_execute > 0) {

            return json(array('code' => 0, 'msg' => '删除成功'));
        } else {

            return json(array('code' => -1, 'msg' => '删除失败'));
        }
    }

    // 轮播图列表
    public function BannerList()
    {

        $page = input('page');

        $limit = input('limit');

        $list = Db::name('rent_banner')->page($page)->limit($limit)->order('id asc')->select();

        foreach ($list as $k => $v) {
            $list[$k]['thumb'] = allUrl($v['thumb']);
            $list[$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
        }
        // 查询数据总数
        $count = Db::name('rent_banner')->count();

        return json(array('code' => 0, 'msg' => '轮播图列表获取成功', 'list' => $list, 'count' => $count));
    }

    // 添加轮播图
    public function addBanner()
    {

        $data = [
            'banner_name' => input('banner_name'),
            'thumb' => input('thumb'),
            'link' => input('link'),
            'add_time' => time(),
            'desc' => input('desc')
        ];

        $_execute = Db::name('rent_banner')->insert($data);

        if ($_execute > 0) {

            return json(array('code' => 0, 'msg' => '添加成功'));
        } else {

            return json(array('code' => -1, 'msg' => '添加失败'));
        }
    }

    // 编辑轮播图
    public function editBanner()
    {
        $id = input('id');

        $data = [
            'banner_name' => input('banner_name'),
            'thumb' => input('thumb'),
            'link' => input('link'),
            'desc' => input('desc')
        ];


        $_execute = Db::name('rent_banner')->where('id', $id)->update($data);

        if ($_execute > 0) {

            return json(array('code' => 0, 'msg' => '编辑成功'));
        } else {

            return json(array('code' => -1, 'msg' => '编辑失败'));
        }
    }

    // 删除轮播图
    public function Bannerdel()
    {

        $id = input('id'); // 轮播图id

        $_execute = Db::name('rent_banner')->where('id', $id)->delete();

        if ($_execute > 0) {

            return json(array('code' => 0, 'msg' => '删除成功'));
        } else {

            return json(array('code' => -1, 'msg' => '删除失败'));
        }
    }

    // 推荐游戏账号
    public function recommendAccount()
    {

        $id = input('id');

        $status = input('status');

        switch ($status) {
            case 1:
                $result = Db::name('rent_pubgame_account')->where('id', $id)->update(['is_recommend' => 2]);
                break;

            default:
                $result = Db::name('rent_pubgame_account')->where('id', $id)->update(['is_recommend' => 1]);
                break;
        }

        if ($result > 0) {

            return json(array('code' => 0, 'msg' => '操作成功'));
        } else {

            return json(array('code' => -1, 'msg' => '操作失败'));
        }
    }

    // 上架游戏账号
    public function GroundingAccount()
    {

        $id = input('id');

        $status = input('status');

        switch ($status) {
            case 1:
                $result = Db::name('rent_pubgame_account')->where('id', $id)->update(['is_grounding' => 2]);
                break;

            default:
                $result = Db::name('rent_pubgame_account')->where('id', $id)->update(['is_grounding' => 1]);
                break;
        }

        if ($result > 0) {

            return json(array('code' => 0, 'msg' => '操作成功'));
        } else {

            return json(array('code' => -1, 'msg' => '操作失败'));
        }
    }

    // layui富文本编辑器上传图片接口
    public function LayEditUploadImage()
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

                $banner_img = allUrl($banner_img);
                return json(array('code' => 0, 'msg' => '上传成功', 'data' => ["src" => $banner_img]));
            } else {
                // 上传失败获取错误信息
                return json($file->getError());
            }
        }
    }

    // 新增新闻公告
    public function addNotice()
    {

        $title = input('title'); // 标题
        $descs = input('descs'); // 描述
        $content = input('content'); // 内容
        $add_time = time(); // 添加时间
        $readnum = 0; // 发布默认阅读次数为0

        $data = Db::name('rent_consult')->insert([
            'title' => $title,
            'descs' => $descs,
            'content' => $content,
            'add_time' => $add_time,
            'readnum' => $readnum
        ]);

        if ($data > 0) {

            return json(array('code' => 0, 'msg' => '发布成功'));
        } else {

            return json(array('code' => -1, 'msg' => '发布失败'));
        }
    }

    // 编辑新闻公告
    public function editNotice()
    {
        $id = input('id');
        $title = input('title'); // 标题
        $descs = input('descs'); // 描述
        $content = input('content'); // 内容

        $data = Db::name('rent_consult')->where('id', $id)->update([
            'title' => $title,
            'descs' => $descs,
            'content' => $content
        ]);

        if ($data > 0) {

            return json(array('code' => 0, 'msg' => '编辑成功'));
        } else {

            return json(array('code' => -1, 'msg' => '编辑失败'));
        }
    }

    // 新闻公告列表
    public function ConsultList()
    {

        $page = input('page');

        $limit = input('limit');

        $title = input('title');

        $where = [];

        if ($title) {

            $where['title'] = ['like', '%' . $title . '%'];
        }
        $data = Db::name('rent_consult')->page($page)->limit($limit)->where($where)->select();
        foreach ($data as $k => $v) {
            $data[$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
        }
        // 查询数据总数
        $count = Db::name('rent_consult')->where($where)->count();

        return json(array('code' => 0, 'msg' => '新闻公告列表获取成功', 'list' => $data, 'count' => $count));
    }

    // 删除新闻公告
    public function delNotice()
    {

        $id = input('id');

        $_execute = Db::name('rent_consult')->where('id', $id)->delete();

        if ($_execute > 0) {

            return json(array('code' => 0, 'msg' => '删除成功'));
        } else {

            return json(array('code' => -1, 'msg' => '删除失败'));
        }
    }

    // 修改网站基本信息
    public function ModifyTheBasicInformationOfTheWebsite()
    {

        $theme_bkg_color = input('theme_bkg_color'); // 主题背景色
        $theme_font_color = input('theme_font_color'); // 主题文字颜色
        $abbreviation = input('abbreviation'); // 网站简称
        $logo = input('logo'); // 网站logo
        $icon = input('icon'); // 地址栏图标
        $home_title = input('home_title'); // 首页标题
        $home_keyword = input('home_keyword'); // 首页关键词
        $home_describe = input('home_describe'); // 首页描述
        $keep_on_record = input('keep_on_record'); // 备案号
        $telephone = input('telephone'); // 客服电话
        $status = input('status'); // 网站状态
        $id = 1; // 修改id

        $_execute = Db::name('rent_base')->where('id', $id)->update([
            'theme_bkg_color' => $theme_bkg_color,
            'theme_font_color' => $theme_font_color,
            'abbreviation' => $abbreviation,
            'logo' => $logo,
            'icon' => $icon,
            'home_title' => $home_title,
            'home_keyword' => $home_keyword,
            'home_describe' => $home_describe,
            'keep_on_record' => $keep_on_record,
            'telephone' => $telephone,
            'status' => $status
        ]);

        if ($_execute > 0) {

            return json([
                'code' => 0,
                'msg' => '网站信息修改成功'
            ]);

        } else {

            return json([
                'code' => -1,
                'msg' => '网站信息修改失败'
            ]);

        }
    }

    // 网站基本信息
    public function BasicInformationOfWebsite()
    {

        $_execute = Db::name('rent_base')->where('id', 1)->find();
        $_execute['logoshort'] = $_execute['logo']; // 网站logo短路径
        $_execute['iconshort'] = $_execute['icon']; // 网站icon短路径
        $_execute['logo'] = allUrl($_execute['logo']); // 网站logo
        $_execute['icon'] = allUrl($_execute['icon']); // 网站icon

        return json([
            'code' => 0,
            'msg' => '网站基本信息获取成功',
            'data' => $_execute
        ]);

    }

    // 账号审核成功
    public function AccountApproved()
    {
        $id = input('id');

        $_execute = Db::name('rent_pubgame_account')->where('id', $id)->update([
            'audit' => 2,
            'is_grounding' => 1
        ]);

        if ($_execute > 0) {

            return json([
                'code' => 0,
                'msg' => '账号审核成功'
            ]);

        } else {

            return json([
                'code' => 0,
                'msg' => '账号审核失败'
            ]);

        }


    }

    // 账号审核驳回
    public function AccountNumberAuditRejected()
    {
        $id = input('id');

        $_execute = Db::name('rent_pubgame_account')->where('id', $id)->update([
            'audit' => 3
        ]);

        if ($_execute > 0) {

            return json([
                'code' => 0,
                'msg' => '账号审核不通过成功'
            ]);

        } else {

            return json([
                'code' => 0,
                'msg' => '账号审核不通过失败'
            ]);

        }


    }

    // 用户申请充值列表
    public function UserApplicationRechargeList()
    {

        $page = input('page'); // 当前页

        $limit = input('limit'); // 一页数量

        $list = Db::name('rent_apply_recharge')->alias('a')
            ->join('rent_user b', 'b.id = a.uid', 'left')
            ->group('a.id')
            ->order('a.add_time desc')
            ->field('a.*,b.phone')
            ->page($page)
            ->limit($limit)
            ->select();

        foreach ($list as $k => $v) {
            if ($list[$k]['status'] == 1) {
                $list[$k]['status'] = "申请充值中";
            } else if ($list[$k]['status'] == 2) {
                $list[$k]['status'] = "申请充值被拒绝";
            } else if ($list[$k]['status'] == 3) {
                $list[$k]['status'] = "申请充值成功";
            } else {
                $list[$k]['status'] = "发生错误...";
            }

            $list[$k]['add_time'] = date('Y-m-d H:i:s', $list[$k]['add_time']);
            $list[$k]['end_time'] = date('Y-m-d H:i:s', $list[$k]['end_time']);
            $list[$k]['payType'] = $list[$k]['payType'] == 0 ? "支付宝" : "微信";
        }

        return json([
            'code' => 0,
            'list' => $list,
            'msg' => 'success',
            'count' => Db::name('rent_apply_recharge')->count()
        ]);
    }

    // 申请充值审核
    public function ApplicationRechargeAudit()
    {

        $id = input('id');

        $type = input('type');

        if ($type == 2) {

            $_execute = Db::name('rent_apply_recharge')->where('id', $id)->update([
                'status' => $type
            ]);

        } else if ($type == 3) {

            $_execute = Db::name('rent_apply_recharge')->where('id', $id)->update([
                'status' => $type
            ]);
            $uid = Db::name('rent_apply_recharge')->where('id', $id)->value('uid');
            $money = Db::name('rent_apply_recharge')->where('id', $id)->value('money');
            // 给用户充值金额
            Db::name('rent_user')->where('id', $uid)->setInc('money', $money);
        } else {

            return json([
                'code' => -1,
                'msg' => '发生错误...'
            ]);

        }


        if ($_execute > 0) {

            return json([
                'code' => 0,
                'msg' => '操作成功'
            ]);

        } else {

            return json([
                'code' => -1,
                'msg' => '操作失败'
            ]);

        }
    }

    // 后台统计
    public function BackgroundStatistics()
    {

        // 注册用户量
        $week_user = Db::name('rent_user')->whereTime('add_time', 'week')->count(); //查询本周
        $all_user = Db::name('rent_user')->count(); // 查询全部

        // 租号量
        $orderCountWeek = Db::name("rent_order")->whereTime("addTime","week")
            ->where("payType","in",[2,3])
            ->count(); // 查询本周
        $allOrderCount = Db::name("rent_order")->where("payType","in",[2,3])->count(); // 查询全部
        // 收入
        $weekMoneySum = Db::name("rent_order")->whereTime("addTime","week")
            ->where("payType","in",[2,3])
            ->sum("amounts");
        $moneySum = Db::name("rent_order")->where("payType","in",[2,3])->sum("amounts"); // 总收入
        // 活跃用户
        $active_user = Db::name('rent_user')->whereTime('login_last_time', 'month')->count(); //查询本月
        // 活跃用户百分比
        $Percent_user = ceil($active_user / $all_user) * 100;
        // 本月活跃用户列表
        $activeUserList = Db::name('rent_user')->whereTime('login_last_time', 'month')->field('id,nickname,login_last_time,status,fabulous')->limit(7)->order("login_last_time desc")->select();
        foreach ($activeUserList as $k => $v) {
            $activeUserList[$k]['login_last_time'] = date('Y-m-d H:i:s', $activeUserList[$k]['login_last_time']);
            $activeUserList[$k]['status'] = $activeUserList[$k]['status'] == 2 ? '离线' : '在线';
        }
        // 最新订单信息
        $orderInfo = Db::name("rent_order")->order("id desc")->limit(7)->field(["orderNum","startTime","startTime","endTime","payType"])->select();
        foreach ($orderInfo as $k => $v)
        {
            $orderInfo[$k]['startTime'] = strtotime($orderInfo[$k]['startTime']);
            $orderInfo[$k]['endTime'] = strtotime($orderInfo[$k]['endTime']);
            $orderInfo[$k]['time'] = round((($orderInfo[$k]['endTime'] - $orderInfo[$k]['startTime']) / 60) / 60);
            unset($orderInfo[$k]['startTime']);
            unset($orderInfo[$k]['endTime']);
        }
        return json([
            'code' => 0,
            'data' => [
                'reg_user' => [
                    'week_user' => $week_user,
                    'all_user' => $all_user
                ],
                'active_user' => [
                    'active_user' => $active_user,
                    'Percent_user' => $Percent_user
                ],
                'orderCount' => [
                    'weekOrderCount' => $orderCountWeek,
                    'allOrderCount' => $allOrderCount
                ],
                'orderSum' => [
                    'weekMoney' => $weekMoneySum,
                    'allMoney' => $moneySum
                ],
                'activeUserList' => $activeUserList,
                "newOrderList" => $orderInfo
            ],
            'msg' => 'success'
        ]);
    }

    // 新增新手帮助
    public function addHelp()
    {

        $title = input('title'); // 标题
        $content = input('content'); // 内容
        $create_time = date('Y-m-d H:i:s', time()); // 添加时间
        $page_view = 0; // 发布默认阅读次数为0

        $data = Db::name('rent_help')->insert([
            'title' => $title,
            'content' => $content,
            'create_time' => $create_time,
            'page_view' => $page_view
        ]);

        if ($data > 0) {

            return json(array('code' => 0, 'msg' => '发布成功'));
        } else {

            return json(array('code' => -1, 'msg' => '发布失败'));
        }
    }

    // 新手帮助列表
    public function helpList()
    {

        $page = input('page');

        $limit = input('limit');

        $title = input('title');

        $where = [];

        if ($title) {

            $where['title'] = ['like', '%' . $title . '%'];
        }
        $data = Db::name('rent_help')->page($page)->limit($limit)->where($where)->select();
        // 查询数据总数
        $count = Db::name('rent_help')->where($where)->count();

        return json(array('code' => 0, 'msg' => '新手帮助列表获取成功', 'list' => $data, 'count' => $count));
    }

    // 编辑新手帮助
    public function editHelp()
    {
        $id = input('id');
        $title = input('title'); // 标题
        $content = input('content'); // 内容

        $data = Db::name('rent_help')->where('id', $id)->update([
            'title' => $title,
            'content' => $content
        ]);

        if ($data > 0) {

            return json(array('code' => 0, 'msg' => '编辑成功'));
        } else {

            return json(array('code' => -1, 'msg' => '编辑失败'));
        }
    }

    // 删除新手帮助
    public function delHelp()
    {

        $id = input('id');

        $_execute = Db::name('rent_help')->where('id', $id)->delete();

        if ($_execute > 0) {

            return json(array('code' => 0, 'msg' => '删除成功'));
        } else {

            return json(array('code' => -1, 'msg' => '删除失败'));
        }
    }

    // 添加账号,游戏服务器区服的选择
    public function gameServerArea()
    {
        // 分类
        $cate = Db::name('rent_game_cate')->field('id,cate_name')->select();
        // 游戏
        $game = Db::name('rent_game')->field('id,cid,game_name')->select();
        // 服务器
        $server = Db::name('rent_server')->field('id,server_name,game_id')->select();
        // 区服
        $area = Db::name('rent_area')->field('id,area_name,rid')->select();
        return json([
            'code' => 0,
            'cate' => $cate,
            'game' => $game,
            'server' => $server,
            'area' => $area
        ]);
    }

    // 用户申请充值列表
    public function withDrawList()
    {
        $data = Db::name("rent_withdrawal")->alias('a')
            ->join("rent_user b", "b.id = a.uid", "left")
            ->field(["a.*", "b.nickname"])
            ->order("a.add_time desc")
            ->paginate(10, false);
        $jsonData = [
            "code" => 0,
            "data" => $data,
            "msg" => "数据请求成功"
        ];
        return json($jsonData);
    }

    // 同意提现
    public function agreeToWithdraw()
    {
        $id = input("id"); // 提现主键id
        // 第一步,查询该提现主要信息
        $txInfo = Db::name("rent_withdrawal")->where("id", $id)->find();
        // 第二步,改变该提现的审核状态
        $updateStatus = Db::name("rent_withdrawal")->where("id", $id)->update([
            "status" => 2
        ]);
        if ($updateStatus > 0) {
            // 第三步,扣去用户所申请提现的对应的不可用余额
            $userExamineMoney = Db::name("rent_user")->where("id", $txInfo["uid"])->value("examine_money");
            if ($userExamineMoney < $txInfo["amount"]) {
                $updateStatus = Db::name("rent_withdrawal")->where("id", $id)->update([
                    "status" => 1
                ]);
                return json([
                    "code" => -1,
                    "msg" => "扣除不可用余额失败,用户不可用余额不足提现金额"
                ]);
            }
            $updateUserExamineMoney = Db::name("rent_user")->where("id", $txInfo["uid"])->setDec("examine_money", $txInfo["amount"]);
            if ($updateUserExamineMoney == true) {
                return json([
                    "code" => 0,
                    "msg" => "已同意,扣取不可用余额成功"
                ]);
            } else {
                return json([
                    "code" => -1,
                    "msg" => "已同意,扣取不可用余额失败(可能用户不可用余额不充足)"
                ]);
            }
        } else {
            return json([
                "code" => -1,
                "msg" => "失败"
            ]);
        }
    }

    // 拒绝提现
    public function refuseToWithdrawCash()
    {
        $id = input("id"); // 提现主键id
        // 第一步,查询该提现主要信息
        $txInfo = Db::name("rent_withdrawal")->where("id", $id)->find();
        // 第二步,改变该提现的审核状态
        $updateStatus = Db::name("rent_withdrawal")->where("id", $id)->update([
            "status" => 3
        ]);
        if ($updateStatus > 0) {
            // 第三步,扣去用户所申请提现的对应的不可用余额回到可用余额
            $userExamineMoney = Db::name("rent_user")->where("id", $txInfo["uid"])->value("examine_money");
            if ($userExamineMoney < $txInfo["amount"]) {
                $updateStatus = Db::name("rent_withdrawal")->where("id", $id)->update([
                    "status" => 1
                ]);
                return json([
                    "code" => -1,
                    "msg" => "扣除不可用余额失败,用户不可用余额不足提现金额"
                ]);
            }
            $updateUserExamineMoney = Db::name("rent_user")->where("id", $txInfo["uid"])->setDec("examine_money", $txInfo["amount"]);
            $updateUserMoney = Db::name("rent_user")->where("id", $txInfo["uid"])->setInc("money", $txInfo["amount"]);
            if ($updateUserExamineMoney == true && $updateUserMoney == true) {
                return json([
                    "code" => 0,
                    "msg" => "已拒绝,扣取不可用余额成功(已回到可用余额)"
                ]);
            } else {
                return json([
                    "code" => -1,
                    "msg" => "已拒绝,扣取不可用余额失败(可能用户不可用余额或者可用余额不充足)"
                ]);
            }
        } else {
            return json([
                "code" => -1,
                "msg" => "失败"
            ]);
        }
    }
}
