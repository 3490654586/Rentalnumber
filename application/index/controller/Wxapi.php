<?php

namespace app\index\controller;

use think\Db;

use think\Controller;

use think\Session;


class Wxapi extends Controller
{
//     public function index(){
//         $echoStr = $_GET["echostr"];
//         if($this->checkSignature()){
//           echo $echoStr;
//           exit;
//         }

//     }

//   private function checkSignature() {
//     $TOKEN = "v7w1zl4rbfeh6cv24q0f861er3jjk2uu";
//     $signature = $_GET["signature"];
//     $timestamp = $_GET["timestamp"];
//     $nonce = $_GET["nonce"];
//     $token = $TOKEN;
//     $tmpArr = array($token, $timestamp, $nonce);
//     sort($tmpArr);
//     $tmpStr = implode( $tmpArr );
//     $tmpStr = sha1( $tmpStr );
//     if( $tmpStr == $signature ) {
//       return true;
//     } else {
//       return false;
//     }
//   }

    public function index()
    {

        $poststr = file_get_contents('php://input');

        // log::write("微信传递的数据".$poststr);
        // log::write();
        //如果推送消息 或者推送事件存在,进行处理
        if (!empty($poststr)) {

            libxml_disable_entity_loader(true);
            $postObj = simplexml_load_string($poststr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $msgType = $postObj->MsgType;
            //判断事件类型,进行对应事件类型处理
            switch ($msgType) {
                //当回复公众号时
                case 'text':
                    $resultStr = $this->handleText($postObj);
                    break;
                case 'image':
                    $resultStr = $this->handleImage($postObj);
                    break;
                case 'voice':
                    $resultStr = $this->handleVoice($postObj);
                    break;
                case 'video':
                    $resultStr = $this->handleVideo($postObj);
                    break;
                case 'shortvideo':
                    $resultStr = $this->handleShortVideo($postObj);
                    break;
                case 'location':
                    $resultStr = $this->handleLocation($postObj);
                    break;
                case 'link':
                    $resultStr = $this->handleLink($postObj);
                    break;
                case 'event':
                    $resultStr = $this->handleEvent($postObj);
                    break;
                default:
                    $resultStr = "Unknow msg type: " . $msgType;
                    break;
            }


            return $resultStr;
        }

        return 123;
    }

    //
    //回复消息
    public function handleText($postObj)
    {

        $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $keyword = trim($postObj->Content);
        $time = time();
        $textTpl = "<xml>
                        <ToUserName><![CDATA[%s]]></ToUserName>
                        <FromUserName><![CDATA[%s]]></FromUserName>
                        <CreateTime>%s</CreateTime>
                        <MsgType><![CDATA[%s]]></MsgType>
                        <Content><![CDATA[%s]]></Content>
                    </xml>";
        if (!empty($keyword)) {
            $msgType = "text";
            $contentStr = "欢迎来到飞讯租号，详情请联系开发者QQ ：1327341524😍😍😍";

            $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
            // log::write("传回微信的数据".$resultStr);
            echo '12';
            echo $resultStr;
            // return $resultStr;
            // exit;
        } else {
            echo "Input something...";
        }

    }

}