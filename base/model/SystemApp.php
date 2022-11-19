<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 应用商店 
 */
namespace base\model;
use base\BaseModel;
use util\Ary;
use util\Dir;

class SystemApp extends BaseModel{

    protected $json          = ['theme'];
    protected $jsonAssoc     = true;
    protected static $title  = [
        'web'       => 'Web',
        'app'       => 'APP',
        'wechatmp'  => '公众号',
        'wechatapp' => '小程序',
        'alipayapp' => '支付宝',
        'douyinapp' => '字节跳动'
    ];

    //获取器应用类型
    public function getConfigAttr($value,$data)
    {
        $config = app('configs')->version($data['appname']);
        if(empty($config)){
            return [];
        }
        return self::getAppConfig($config);
    }

    //应用状态
    public function getLockTextAttr($value,$data)
    {
        $status = [0 => '上架',1 => '下架'];
        return $status[$data['is_lock']];
    }

    //应用Logo
    public function getLogoAttr($value,$data)
    {
        if(empty($value)){
            if(file_exists(PATH_STATIC.$data['appname'].DS.'logo.png')){
                return DAMAIN.'static/'.$data['appname'].'/logo.png';
            }else{
                return DAMAIN.'static/error.png';
            }
        }else{
            return $value;
        }
    }

    //处理应用配置内容
    public static function getAppConfig($config):array
    {
        //应用类型
        $types = [];
        foreach (explode('|',$config['types']) as $value) {
            $types[$value?:'none'] = self::$title[$value]??'无终端';
        }
        $config['types']   = $types;
        //支付方式
        $config['payment'] = [];
        if($config['is_wechatpay']){
            $config['payment'][]= '微信支付';
        }
        if($config['is_alipay']){
            $config['payment'][]= '支付宝';
        }
        if(empty($config['payment'])){
            $config['payment'][]= '无支付服务';
        }
        //接入方式
        $wechat_text = [0 => '自助配置',1 => '一键授权'];
        $config['open_wechat_text'] = $wechat_text[$config['is_open_wechat']];
        return $config;
    }

    /**
     * 待安装APP列表
     * @param  array $param 
     */
    public static function offApp(){
        $install_app = self::column('appname');
        $waiting_app = array_values(Ary::array_remove_empty(array_diff(Dir::getDir(PATH_APP,FORBIDEN),$install_app)));
        $system_app  = array_values(Ary::array_remove_empty(array_diff(Dir::getDir(PATH_SAPIXX, ['admin','tenant','apis','install']),$install_app)));
        $install     = array_merge($waiting_app,$system_app);
        $app = [];
        foreach ($install as $key => $value) {
            $config = app('configs')->version($value);
            if(!empty($config)){
                $app[$key] = self::getAppConfig($config);
                $app[$key]['appname'] = $value;
                $app[$key]['logo']    = DAMAIN.'static/offline.png';
            }
        }
        return $app;
    }

    /**
     * 管理小程序
     * @param  array $param 
     */
    public static function edit(array $param){
        $data['title']         = $param['title'];
        $data['logo']          = $param['logo']??'';
        $data['about']         = $param['about']??'';
        $data['expire_day']    = $param['expire_day']??7;
        $data['price']         = $param['price']??0;
        $data['qrcode']        = $param['qrcode']??'';
        $data['theme']         = $param['theme']??[];
        $data['update_time']   = time();
        if(isset($param['id']) && $param['id'] > 0){
            $data['id'] = $param['id'];
            return self::update($data);
        }else{
            $data['appname']     = strtolower($param['appname']);
            $data['create_time'] = time();
            return self::create($data);
        }
    }
}