<?php
/**
 * @copyright   Copyright (c) 2017-2030  https://www.sapixx.com All rights reserved.
 * @license Licensed (https://www.gnu.org/licenses/gpl-3.0.txt).
 * @link https://www.sapixx.com
 * 平台应用消息事件
 */
declare (strict_types = 1);
namespace base\event;
use base\model\SystemTenant;

class WechatPlatform
{

   /**
     *默认消息处理 
     * @param string $param = ['message' =>[],'client' => []]
     * @return void
    */
    public function onMessage($param)
    {
        return config('config.wechat_account.text');
    }

    /**
     * 扫码打开公众号
     * 判断认证EventKey的token码是否超过了38位,超过了是登录否则就是绑定
     * @param string $param = ['message' =>[],'client' => []]
     * @return void
    */
    public function onScan($param)
    {
        try {
            $text = config('config.wechat_account.text');
            $message = $param['message'];
            if(empty($param['message']['EventKey'])){
                return $text;
            }
            $tencent_id = app('code')->de($message['EventKey']);
            if(!$tencent_id){
                return $text;
            }
            if(strlen($tencent_id) == 18){
                //登录
                $tenant = SystemTenant::where(['wechat_id' => $message['FromUserName']])->field('id,ticket,wechat_id')->find();//登录
                if(empty($tenant)){
                    return $text;
                }
                $tenant->ticket = $tencent_id;
            }else{
                //绑定
                $tenant = SystemTenant::where(['id' => $tencent_id])->field('id,ticket,wechat_id')->find();//绑定
                if(empty($tenant)){
                    return $text;
                }
                $tenant->wechat_id = $message['FromUserName'];
                $tenant->ticket    = $message['EventKey'];
            }
            $tenant->save();
            return $text;
        } catch (\Exception $e) {
            return '扫码成功,但帐号认证失败';
        }
    }

    /**
     * 关注公众号
     * 判断认证EventKey的token码是否超过了18位,超过了是登录否则就是绑定
     * @param array $param = ['message' =>[],'client' => []]
     * @return void
     */
    public function onSubscribe($param)
    {
        try {
            $text = config('config.wechat_account.text');
            $message = $param['message'];
            if(empty($param['message']['EventKey'])){
                return $text;
            }
            $eventey = substr($message['EventKey'],8);
            $tencent_id = app('code')->de($eventey);
            if(!$tencent_id){
                return $text;
            }
            if(strlen($tencent_id) == 18){
                //登录
                $tenant = SystemTenant::where(['wechat_id' => $message['FromUserName']])->field('id,ticket,wechat_id')->find();//登录
                if(empty($tenant)){
                    return $text;
                }
                $tenant->ticket = $tencent_id;
            }else{
                //绑定
                $tenant = SystemTenant::where(['id' => $tencent_id])->field('id,ticket,wechat_id')->find();//绑定
                if(empty($tenant)){
                    return $text;
                }
                $tenant->wechat_id = $message['FromUserName'];
                $tenant->ticket    = $eventey;
            }
            $tenant->save();
            return config('config.wechat_account.text');
        } catch (\Exception $e) {
            return '关注成功,但帐号认证失败';
        }
    }

    /**
     * 取消公众号
     * @param array $param = ['message' =>[],'client' => []]
     * @return void
    */
    public function onUnsubscribe($param)
    {
        try {
            $text = config('config.wechat_account.text');
            if(empty($param['message']['FromUserName'])){
                return $text;
            }
            $wecaht_id = $param['message']['FromUserName'];
            $tenant = SystemTenant::where(['wechat_id' => $wecaht_id])->field('id,ticket,wechat_id')->find();
            if(empty($tenant)){
                return $text;
            }
            $tenant->wechat_id = NULL;
            $tenant->save();
            return $text;
        } catch (\Exception $e) {
            return '取消订阅';
        }
    }
}
