<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 小程序公共接口
 */
namespace platform\apis\controller\common;
use base\ApiController;
use base\model\SystemUser;
use apixx\filesystem\facade\Filesystem;
use util\Dir;

class Weapp extends ApiController
{
    //需要登录验证
    protected $acl = ['setUserProfile'];

    /**
     * 设置头像和昵称
     */
    public function setUserProfile(){
        if(IS_POST){
            $face     = $this->request->param('face/s');
            $nickname = $this->request->param('nickname/s');
            if(empty($face) && empty($nickname)){
                return enjson(10002,'未更新头像或昵称');
            }
            if(!empty($face)){
                $data['face'] = $face;
            }
            if(!empty($nickname)){
                $data['nickname'] = $nickname;
            }
            SystemUser::apps()->where(['id' => $this->request->user->id])->update($data);
            return enjson(200);
        }else{
            return enjson(401);
        }
    }

    /**
     * 绑定微信手机号
     * @return void
     */
    public function bindPhone(){
        if (IS_POST) {
            $code = $this->request->param('code');
            $response = app('wechat')->data(['code' =>$code])->postJson('wxa/business/getuserphonenumber');
            $phone = $response['phone_info']['phoneNumber'];
            if ($this->request->user->phone == $phone) {
                return enjson(403,'手机号相同不用更换');
            }
            $condition['phone'] = $phone;
            $rel = SystemUser::apps()->where($condition)->field('id')->find();
            if($rel){
                return enjson(403,'手机号已被占用');
            }
            $result  = SystemUser::where(['id' =>$this->request->user->id])->update(['phone' => $phone]);
            if ($result) {
                return enjson(200,'手机号绑定成功',['phone' => $phone]);
            }
            return enjson(403,'手机号绑定失败');
        }
    }

     /**
     * 获取绑定手机验证码
     * @return void
     */
    public function getPhoneSms(){
        if (IS_POST) {
            if(empty($this->request->user->phone)){
                return enjson(302,'请先绑定手机号');
            }
            $code = $this->request->param('code');
            $response = app('wechat')->data(['code' =>$code])->postJson('wxa/business/getuserphonenumber');
            $phone    = $response['phone_info']['phoneNumber'];
            if ($this->request->user->phone != $phone) {
                return enjson(403,'手机号验证失败');
            }
            $sms = app('sms')->getCode($this->request->user->phone);
            return enjson(200,'成功',['code' => $sms['code']]);
        }
    }

    //获取二维码地址
    protected  function getQrcodePath(){
        $appname = $this->request->app?$this->request->app->appname:'storage';
        $apps_id = $this->request->apps?DS.idcode($this->request->apps->id,false):'';
        return PATH_RES.$appname.$apps_id.DS.'qrcode';
    }

    /**
     * 获取小程序码
     * 适用于需要的码数量较少的业务场景。通过该接口生成的小程序码，永久有效，有数量限制
     * https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/qrcode-link/qr-code/getQRCode.html
     */
    public function getCode(){
        if(IS_POST){
            $scene = $this->request->param('scene/a');
            $page  = $this->request->param('page/s','pages/index');
            $param = $page.'?'.http_build_query($scene);
            //二维码路径与名称
            $rootpath = $this->getQrcodePath();
            $filename = md5($param).'.jpg';
            //判断路径是否存在,不存在创建路径
            if(Dir::mkDirs($rootpath)){
                $filepath = $rootpath.DS.$filename;
                $configs  = Filesystem::getConfig();
                $savepath = str_replace('\\','/', substr($filepath,strlen($configs['disks']['public']['root'])));
                $url      = $configs['disks']['public']['url']??$this->request->root(true).'/res';
                //请求微信
                $response = app('wechat')->data(['path' =>$param])->postJson('wxa/getwxacode');
                $response->saveAs($filepath);
                return enjson(200,['url' =>$url.$savepath]);
            }else{
                return enjson(403,'目录无权限');
            }
        }
    } 

    /**
     * 获取不限制的小程序码
     * 适用于需要的码数量极多的业务场景。通过该接口生成的小程序码，永久有效，数量暂无限制
     * https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/qrcode-link/qr-code/getUnlimitedQRCode.html
     */
    public function getCodes(){
        if(IS_POST){
            $scene = $this->request->param('scene/a');
            $page  = $this->request->param('page/s','pages/index');
            $param = $page.'?'.http_build_query($scene);
            //二维码路径与名称
            $rootpath = $this->getQrcodePath();
            $filename = md5($param).'.jpg';
            //判断路径是否存在,不存在创建路径
            if(Dir::mkDirs($rootpath)){
                $filepath = $rootpath.DS.$filename;
                $configs  = Filesystem::getConfig();
                $savepath = str_replace('\\','/', substr($filepath,strlen($configs['disks']['public']['root'])));
                $url      = $configs['disks']['public']['url']??$this->request->root(true).'/res';
                //请求微信
                $response = app('wechat')->data(['page' =>$page,'scene' => http_build_query($scene)])->postJson('wxa/getwxacodeunlimit');
                $response->saveAs($filepath);
                return enjson(200,['url' =>$url.$savepath]);
            }else{
                return enjson(403,'目录无权限');
            }
        }
    } 

    /**
     * 获取小程序二维码
     * 适用于需要的码数量较少的业务场景。通过该接口生成的小程序码，永久有效，有数量限制
     * https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/qrcode-link/qr-code/createQRCode.html
     */
    public function getQrcode(){
        if(IS_POST){
            $scene = $this->request->param('scene/a');
            $page  = $this->request->param('page/s','pages/index');
            $param = $page.'?'.http_build_query($scene);
            //二维码路径与名称
            $rootpath = $this->getQrcodePath();
            $filename = md5($param).'.jpg';
            //判断路径是否存在,不存在创建路径
            if(Dir::mkDirs($rootpath)){
                $filepath = $rootpath.DS.$filename;
                $configs  = Filesystem::getConfig();
                $savepath = str_replace('\\','/', substr($filepath,strlen($configs['disks']['public']['root'])));
                $url      = $configs['disks']['public']['url']??$this->request->root(true).'/res';
                //请求微信
                $response = app('wechat')->data(['path' => $param])->postJson('cgi-bin/wxaapp/createwxaqrcode');
                $response->saveAs($filepath);
                return enjson(200,['url' =>$url.$savepath]);
            }else{
                return enjson(403,'目录无权限');
            }
        }
    } 
}