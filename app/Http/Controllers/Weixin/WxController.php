<?php

namespace App\Http\Controllers\Weixin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Weixin\WxModel;
use Illuminate\Support\Facades\Redis;
use GuzzleHttp\Client;

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
        print_r($userInfo);
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
    //创建自定义菜单
    public function createMenu()
    {
        //自定义菜单接口
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' . $this->getAccessToken();
        //菜单数据
        $menu_data = [
            'button' => [
                [
                    'name' => '菜单',
                    'button' => [
                        'type' => 'view',
                        'name' => '百度一下',
                        'url' => 'www.baidu.com'
                    ],
                    [
                        'type' => 'click',
                        'name' => '点赞',
                        'key' => 'menu_key001'
                    ]
                ],
                [
                    "type" => "pic_sysphoto",
                    "name" => "拍照",
                    "key" => "rselfmenu_1_0",
                    "sub_button" => [ ]
                ],
                [
                    "name" => "发送位置",
                    "type" => "location_select",
                    "key" => "rselfmenu_2_0"
                ]
            ]
        ];

        $json_str = json_encode($menu_data,JSON_UNESCAPED_UNICODE);   //处理中文编码
        //发送请求
        $client = new Client();
        $response = $client->request('POST',$url,[
            'body' => $json_str
        ]);

        //处理响应
        $res_str = $response->getBody();
        $res = json_decode($res_str,true);

        //判断信息
        if($res['errcode']>0){
            echo '创建菜单成功';
        }else{
            echo '创建菜单失败';
        }


    }
    //获取access_token
    public function getAccessToken()
    {
        $key = 'wx_access_token';
        $token = Redis::get($key);
        if(!$token){
            echo 'cache';
        }else{
            echo 'Nocache';
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.env('WX_APPID').'&secret='.env('WX_SECRET').'';
            $response = file_get_contents($url);
            $arr = json_decode($response);

            Redis::set($key,$arr->access_token);
            Redis::expire($key,3600);

            $token = $arr->access_token;
        }
        return $token;

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
