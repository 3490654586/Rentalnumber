<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/11/27 0027
 * Time: 11:25
 * 统一返回处理类（ajax）允许跨域
 *
 * 使用： **
 *      use Gucci\ServerResponse;
 *      return ServerResponse::createBySuccess("成功");
 *      ServerResponse::createByError("断点失败了");
 */

namespace app\common\api;

use think\Config;
use think\exception\HttpResponseException;
use think\Response;

class Apipublic
{
    public static $successCode = 0;
    public static $errorCode = -1;

    //构造函数
    public function __construct($status, $msg = "", $data = [])
    {
        $result["status"] = $status;
        $msg ? $result["msg"] = $msg : null;
        $data ? $result["data"] = $data : null;

        $type = $this->getResponseType();
        // 允许跨域
        $header["Access-Control-Allow-Origin"] = "*";
        $header["Access-Control-Allow-Headers"] = "X-Requested-With,Content-Type";
        $header["Access-Control-Allow-Methods"] = "GET,POST,PATCH,PUT,DELETE,OPTIONS";
        $response = Response::create($result, $type)->header($header);
        throw new HttpResponseException($response);
    }

    /*
     * 成功
     * @param $msg
     * @return ServerResponse
     */
    public static function jsonSuccess($msg, $data = null)
    {
        return new Apipublic(self::$successCode, $msg, $data);
    }

    /*
     * 失败
     * @$param $msg
     * @return ServerResponse
     */
    public static function jsonError($msg)
    {
        return new Apipublic(self::$errorCode, $msg);
    }

    /*
     * 获取当前的response 输出类型
     * @access protected
     * @return string
     */
    private function getResponseType()
    {
        return Config::get("default_ajax_return");
    }
}