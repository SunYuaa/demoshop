<?php

namespace App\Http\Controllers\Weixin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WxController extends Controller
{
    //微信第一次测试连接
    public function valid()
    {
        echo $_GET['echostr'];
    }
    //微信接口
    public function event()
    {
        //接受服务器推送
        $content = file_get_contents('php://input');
        //将事件写入日志
        $time = date('Y/m/d H:i:s');
        $str = $time . $content . "<br/>";
        file_put_contents('logs/wx_event.logs',$str,FILE_APPEND);
        
        $data = simplexml_load_string($content);
        echo 'ToUserName:'.$data->ToUserName;echo '<br/>';
        echo 'FromUserName:'.$data->FromUserName;echo '<br/>';
        echo 'CreateTime:'.$data->CreateTime;echo '<br/>';
        echo 'MsgType:'.$data->MsgType;echo '<br/>';
        echo 'Content:'.$data->Content;echo '<br/>';
        echo 'MsgId:'.$data->MsgId;echo '<br/>';
        
    }

    //获取access_token
    public function getAccessToken()
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.env('WX_APPID').'&secret='.env('WX_SECRET').'';
        $response = file_get_contents($url);
        $arr = json_decode($response);
        return $arr['access_token'];
    }


    //ceshi
    public function test()
    {
        echo 111;
    }



}
