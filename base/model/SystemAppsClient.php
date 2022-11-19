<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 租户应用ID管理
 */
namespace base\model;
use base\BaseModel;

class SystemAppsClient extends BaseModel
{

    protected $json          = ['config'];
    protected $jsonAssoc     = true;
    protected static $title  = [
        'web'       => 'Web',
        'app'       => 'APP',
        'wechatmp'  => '公众号',
        'wechatapp' => '小程序',
        'alipayapp' => '支付宝',
        'douyinapp' => '字节跳动'
    ];
    protected static $logo = [
        'web'       => DAMAIN.'common/img/web.svg',
        'app'       => DAMAIN.'common/img/app.svg',
        'wechatmp'  => DAMAIN.'common/img/wechatmp.svg',
        'wechatapp' => DAMAIN.'common/img/wechatapp.svg',
        'douyinapp' => DAMAIN.'common/img/douyinapp.svg',
        'alipayapp' => DAMAIN.'common/img/alipay.svg'
    ];

    /**
     * 关联系统应用
     */
    public function app()
    {
        return $this->hasOne('SystemApp', 'id','app_id')->cache(true);
    }

     /**
     * 关联租户应用
     */
    public function apps()
    {
        return $this->hasOne('SystemApps', 'id','apps_id')->cache(true);
    }

     /**
     * 读取对应终端的用户ID
     */
    public function ruid()
    {
        return $this->hasOne('SystemUserRuid','client_id','id');
    }

     /**
     * 判断租户应用状态
     */
    public function release()
    {
        return $this->hasOne('SystemAppsRelease', 'client_id','id');
    }

    //ID加密
    public function getIdCodeAttr($value,$data)
    {
        return idcode($data['id'],false);
    }

    /**
     * 应用类型
     * @param string $value
     * @param array $data
     * @return void
     */
    public static function getNameAttr($value,$data)
    {
        return self::$title[$data['title']??'web']??'异常应用';
    }

  
    /**
     * 判断应用配置类型和名称
     * @param string $value
     * @return array
     */
    public static function getTypes($value):array
    {
        return ['logo' => self::$logo[$value],'title' => self::$title[$value??'web']??'异常应用'];
    }

    /**
     * Logo
     * @param $value
     * @param array $data
     * @return void
     */
    public static function getLogoAttr($value,$data)
    {
        $title = $data['title']??'web';
        return self::$logo[$title];
    }


   /**
     * 获取配置参数
     * @param  string $title
     */
    public static function configs(string $title){
        return self::where(['title' => $title])->apps()->find();
    }

    /**
     * 编辑修改
     * @param $param
     * @return object
     */
    public static function edit($param){
        $data['appid']       = $param['appid'];
        if(isset($param['secret'])){
            $data['secret']  = $param['secret'];
        }
        if(isset($param['token'])){
            $data['token']   = $param['token']; 
        }
        if(isset($param['aes_key'])){
            $data['aes_key'] = $param['aes_key']; 
        }
        if(isset($param['config'])){
            $data['config'] = $param['config']; 
        }
        $data['update_time'] = time(); 
        $rel = self::where(['title' => $param['title']])->apps()->find();
        if($rel){
            $data['id'] = $rel->id;
            SystemAppsClient::update($data);
            return $rel;
        }else{
            $data['title']      = $param['title'];
            $data['apps_id']    = $param['apps_id'];
            $data['app_id']     = $param['app_id'];
            $data['api_id']     = uuid(0);
            $data['api_secret'] = uuid(2);
            return SystemAppsClient::create($data);
        }
    }
}