<?php
/**
 * @copyright   Copyright (c) 2017-2030  https://www.sapixx.com All rights reserved.
 * @license Licensed (https://www.gnu.org/licenses/gpl-3.0.txt).
 * @link https://www.sapixx.com
 * 微信消息与开放平台认证服务
 */
namespace platform\apis\controller\service;
use base\ApiController;
use base\model\SystemAppsClient;
use think\facade\Event;
class Message extends ApiController{

    /**
     * 微信事件推送
     * @return json
     */
    public function __call($appid, $args){
        if (empty($appid) || !preg_match('/^[A-Za-z0-9][\w|\.]*$/',$appid)) {
            return abort(404,'微信APPID不正确');
        }
        //公众号和小程序开放平台接入验证测试APPID
        $test_app = ['wxd101a85aa106f53e','wxc39235c15087f6f3','wx7720d01d4b2a4500','wx05d483572dcd5d8b','wx5910277cae6fd970'];
        $test_mp  = ['wx570bc396a51b8ff8','wx9252c5e0bb1836fc','wx8e1097c5bc82cde9','wx14550af28c71a144','wxa35b9c23cfe664eb'];
        try {
            if (in_array($appid,array_merge($test_app,$test_mp))){
                $server = app('wechat')->testOpenPlatform(in_array($appid,$test_mp)?:false,$appid);
                $server->addMessageListener('text',function($message){
                    if(count(explode(':',$message->Content)) > 1){
                        return explode(':',$message->Content)[1]."_from_api";
                    }
                    if($message->Content == "TESTCOMPONENT_MSG_TYPE_TEXT") {
                        return 'TESTCOMPONENT_MSG_TYPE_TEXT_callback';
                    }
                });
            }else{
                //判断注册订阅事件
                if($this->request->app){
                    $wechat_message = PATH_APP.$this->request->app->appname.DS.'event'.DS.'WechatMessage.php';
                    $namespace = is_file($wechat_message)?"app\\{$this->request->app->appname}\\event\WechatMessage":'base\\event\\WechatMessage';
                }else{
                    //判断是否系统服务号
                    if($appid == config('config.wechat_account.appid')??''){
                        $namespace = 'base\\event\\WechatPlatform';
                    }else{
                        $client = SystemAppsClient::where(['appid' => $appid])->cache(true)->find();
                        if(empty($client)){
                            $namespace = 'base\\event\\WechatMessage';
                        }else{
                            $wechat_message = PATH_APP.$client->app->appname.DS.'event'.DS.'WechatMessage.php';
                            $namespace = is_file($wechat_message)?"app\\{$client->app->appname}\\event\\WechatMessage":'base\\event\\WechatMessage';
                        }
                    }
                }
                $client = $client??$this->request->client;
                $this->request->client = $client; 
                Event::subscribe($namespace);   //微信消息答复
                $app = app('wechat')->client($client?'tenant':'admin')->data(['appid' => $appid])->new();
                $server = $app->getServer();
                $server->addEventListener('subscribe',function($message) use ($client){
                    return Event::trigger('Subscribe',['message' =>$message,'client' => $client])[0]; 
                });
                $server->addEventListener('unsubscribe',function($message) use ($client){
                    return Event::trigger('Unsubscribe',['message' =>$message,'client' => $client])[0]; 
                });
                $server->addEventListener('SCAN',function($message) use ($client){
                    return Event::trigger('Scan',['message' =>$message,'client' => $client])[0]; 
                });
                $server->with(function($message) use ($client) {
                    return Event::trigger('Message',['message' =>$message,'client' => $client])[0]; 
                });
            }
            $response = $server->serve();
            return $response->getBody();
        } catch (\Exception $e) {
            return abort(404,'请求方式不正确:'.$e->getMessage());
        }
    }

    /**
     * 微信开放平台推送车票(1次/10分钟)
     * 有了车票要保存下来,获取授权时要用
     * @return json
     */
    public function ticket(){
        try {
            Event::subscribe('base\event\WechatTicket');  //动态注册订阅事件
            $server = app('wechat')->openPlatform()->getServer();
            //处理授权成功事件
            $server->handleAuthorized(function ($message) {
                Event::trigger('Authorize',$message); 
            });
            //处理授权更新事件
            $server->handleAuthorizeUpdated(function ($message) {
                Event::trigger('AuthorizeUpdated',$message); 
            });
            //处理授权取消事件
            $server->handleUnauthorized(function ($message) {
                Event::trigger('Unauthorized',$message); 
            });
            //处理 VerifyTicket 推送事件（已默认处理）
            $server->handleVerifyTicketRefreshed(function ($message) {
                Event::trigger('VerifyTicket',$message); 
            });
            //其它事件类型
            $server->with(function($message) {
                Event::trigger('WithMessage',$message); 
            });
            $response = $server->serve();
            return $response->getBody();
        } catch (\Exception $e) {
            return abort(404,'请求方式不正确:'.$e->getMessage());
        }
    }
}