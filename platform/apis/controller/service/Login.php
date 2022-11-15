<?php
/**
 * @copyright   Copyright (c) 2017-2030  https://www.sapixx.com All rights reserved.
 * @license Licensed (https://www.gnu.org/licenses/gpl-3.0.txt).
 * @link https://www.sapixx.com
 * 微信认证服务
 */
namespace platform\apis\controller\service;
use base\ApiController;
use base\model\SystemUser;
use base\model\SystemUserRuid;

class Login extends ApiController{

 /**
     * 小程序登录接口
     * @return void
     */
    public function weapp(){
        if(IS_POST){
            if(!$this->request->client){
                return enjson(11005);
            }
            $param = [
                'code'   => $this->request->param('code/s'),
                'invite' => $this->request->param('invite/s',''),
            ];
            $this->validate($param,'Login.weapp');
            $data = [
                'appid'      => $this->request->client->appid,
                'js_code'    => $param['code'],
                'grant_type' => 'authorization_code',
            ];
            $is_open_wechat = $this->request->app->config['is_open_wechat']??false;
            if($is_open_wechat){
                $app = app('wechat')->openPlatform();
                $data['component_appid']        = $app->getAccount()->getAppId();
                $data['component_access_token'] = $app->getComponentAccessToken()->getToken();
                $response = $app->getClient()->get('sns/component/jscode2session',$data);
                if ($response->isFailed()) {
                    return enjson(10001,$response['errmsg']);
                }
            }else{
                $data['secret'] = $this->request->client->secret;
                $response = app('wechat')->data($data)->get('sns/jscode2session');
            }
            $data  = [];
            $data['openid']      = $response['openid'];
            $data['session_key'] = $response['session_key'];
            $data['unionid']     = $response['unionid']??$response['openid'];
            //判断是登录还是注册
            $rel = SystemUser::addWechatUser(['unionid' => $data['unionid'],'apps_id' => $this->request->apps->id,'invite' => $param['invite']]);
            if(empty($rel)){
                return enjson(11106);
            }
            //获取(注册/登录)数据
            $result = SystemUserRuid::addWechatUserRuid([
                'uid'       => $rel->id,
                'client_id' => $this->request->client->id,
                'apps_id'   => $this->request->apps->id,
                'login_id'  => $data['openid'],
                'secret'    => $data['session_key']
            ]);
            if(empty($result)){
                return enjson(403,'用户认证失败');
            }
            return enjson(200,[
                'uid'               => $rel->id,
                'token'             => app('jwt')->enJwt($rel->id),
                'is_getUserProfile' => ($rel->getData('nickname') && $rel->getData('face'))?true:false, //兼容微信新接口,判断是否设置了用户头像或昵称,用于客户端判断
                'ucode'             => $rel->invite_code,
                'session_id'        => app('session')->getId()
            ]);
        }
        return enjson(403);
    }

    /**
     * 公众号接口
     * @return void
     */
    public function official(){
        if(!$this->request->client){
            return enjson(11005);
        }  
        $param = [
            'get'    => $this->request->param('get/s'),
            'code'   => $this->request->param('code/s'),
            'state'  => $this->request->param('state/s'),
            'invite' => $this->request->param('invite/s',''),
        ];
        $this->validate($param,'Login.wemp');
        try {
            $response = app('wechat')->new()->getOauth()->userFromCode($param['code']);
        } catch (\Exception $e) {
            return enjson(10001,$e->getMessage());
        }
        if(empty($response['unionid'])){
            $response['unionid'] = $response->getId();
        }
        //判断是登录还是注册
        $rel = SystemUser::addWechatUser([
            'nickname' => $response->getNickname(),
            'face'     => $response->getAvatar(),
            'unionid'  => $response['unionid'],
            'apps_id'  => $this->request->apps->id,
            'invite'   => $param['invite']
        ]);
        if(empty($rel)){
            return enjson(11106);
        }
        //获取(注册/登录)数据
        $result = SystemUserRuid::addWechatUserRuid([
            'uid'       => $rel->id,
            'client_id' => $this->request->client->id,
            'apps_id'   => $this->request->apps->id,
            'login_id'  => $response->getId(),
            'secret'    => $param['state']
        ]);
        if(empty($result)){
            return enjson(403,'用户认证失败');
        }
        $this->app->user->setLogin($rel->id);
        return redirect((string)url('web/'.$param['get'].'/index',['token' => app('jwt')->enJwt($rel->id)]));
    }
}