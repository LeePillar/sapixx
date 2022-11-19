<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 商业授权认证
 */
namespace platform\admin\controller;
use base\model\SystemConfig;
use GuzzleHttp\Client;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use code\Code;

class License extends Common{

    /**
     * 关于应用
     */
    public function index()
    {
        $info = SystemConfig::where(['title' => 'license'])->field('config,update_time')->find();
        if(empty($info)){
            $param['id'] = uuid(4,true,$this->request->host().'#'.$this->request->server('SERVER_ADDR'));
            $info = SystemConfig::create(['config' => $param,'title' => 'license']);
        }
        $view['info']       = $info;
        $view['breadcrumb'] = [['name' =>'控制面板','icon' =>'window'],['name' =>'关于应用','url'=> (string)url('license/index')]];
        return view()->assign($view);
    }

    /**
     * 跳转授权中心
    */
    public function licenseUrl(){
        $token['client_domain'] = $this->request->host();
        $token['client_ip']     = $this->request->server('SERVER_ADDR');
        $rel = SystemConfig::where(['title' => 'license'])->find();
        if(empty($rel)){
            $param['id'] = uuid(4,true,$token['client_domain'].'#'.$token['client_ip']);
            SystemConfig::create(['config' => $param,'title' => 'license']);
        }else{
            $param['id'] = $rel->config['id'];
        }
        $param['token'] = Jwt::encode([
            'data' => Code::en(app('jwt')->toUrlParams($token),$param['id']),
            'iat'  => time(),
            'exp'  => time() + 300,
            'nbf'  => time() - 300],$param['id'],'HS256');
        return redirect(BASEURI.'/apixx?'.app('jwt')->toUrlParams($param));
    }

    /**
     * 获取认证秘钥
     */
    public function getkey()
    {  
        try {
            $client_key = $this->request->param('client_key/s');
            if(empty($client_key)){
                abort(403,'秘钥参数丢失');
            }
            $rel = SystemConfig::where(['title' => 'license'])->find();
            if(empty($rel->config['id'])){
                abort(403,'未找到Client_id,请重新点击"中台->关于应用->授权中心->创建秘钥"');
            }
            $jwt = (array)Jwt::decode($client_key, new Key($rel->config['id'],'HS256'));
            $config['id'] = $rel->config['id'];
            $config['key'] = Code::de($jwt['data'],$rel->config['id']);
            $rel->config = $config;
            $rel->save();
            abort(200,'应用秘钥创建成功');
        }catch (\Exception $e) {
            $this->jump($e->getMessage(),(string)url('admin/index/index'));
        }
    }

    /**
     * 检查是否最新版本
     */
    public function checkVar()
    {
        $client = new Client([
            'base_uri' => BASEURI,
            'timeout'  => 2.0,
        ]);
        $response = $client->get('/apis/service/index/checkvar');
        $body = json_decode($response->getBody()->getContents());
        return enjson(202,'最新版本为'.$body->message);
    }
}