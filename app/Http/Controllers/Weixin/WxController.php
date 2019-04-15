<?php

namespace App\Http\Controllers\Weixin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Weixin\WxModel;

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
        $str = $time . $content . "<\n>";
        file_put_contents('logs/wx_event.logs',$str,FILE_APPEND);

        $data = simplexml_load_string($content);
//        echo '<br/>';echo 'ToUserName:'.$data->ToUserName;       //公众号id
//        echo '<br/>';echo 'FromUserName:'.$data->FromUserName;   //用户openid

        $openid = $data->FromUserName;

        //用户信息加入数据库
        $userInfo = $this->getUserInfo($openid);
//        print_r($userInfo);
        $info = [
            'openid' => $userInfo['openid'],
            'nickname' => $userInfo['nickname'],
            'sex' => $userInfo['sex'],
            'headimgurl' => $userInfo['headimgurl'],
            'subscribe_time' => $userInfo['subscribe_time'],
        ];
        $res = WxModel::insertGetId($info);
        if($res){
            echo 'ok';
        }else{
            echo 'no';
        }

    }

    //获取access_token
    public function getAccessToken()
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.env('WX_APPID').'&secret='.env('WX_SECRET').'';
        $response = file_get_contents($url);
        $arr = json_decode($response);
        return $arr->access_token;
    }


    //根据openid获取用户信息
    public function getUserInfo($openid)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$this->getAccessToken().'&openid='.$openid.'&lang=zh_CN';
        $userInfo = file_get_contents($url);
        $info = json_decode($userInfo,true);
        return $info;
    }



}
