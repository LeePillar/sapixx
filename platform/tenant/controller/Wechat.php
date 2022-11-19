<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 微信应用配置
 */
namespace platform\tenant\controller;
use base\model\SystemAppsClient;
use base\model\SystemAppsRelease;
use base\model\SystemApps;
use EasyWeChat\Kernel\Form\File;
use EasyWeChat\Kernel\Form\Form;

class Wechat extends Common{

    /**
     * 高级权限控制
     * @return void
     */
    protected function initialize(){
        $this->middleware('platform\tenant\middleware\AppsManage');
    }

 
    /**
     * 微信小程序
     */
    public function wechatapp(){
        $view['info'] = $this->request->client['wechatapp']??[];
        $view['send'] = [];
        if(isset($this->request->client['wechatapp'])){
            $view['send'] = SystemAppsRelease::where(['client_id' => $this->request->client['wechatapp']['id']])->apps()->find();
        }
        $this->bread([['name' =>'关于应用','url'=>(string)url('apps/index')],['name' =>'小程序']]);
        return view()->assign($view);
    }

    /**
     * 微信公众号
     */
    public function wechatmp(){
        $view['info'] = $this->request->client['wechatmp']??[];
        $this->bread([['name' =>'关于应用','url'=>(string)url('apps/index')],['name' =>'公众号']]);
        return view()->assign($view);
    }
    
    /**
     * 同步小程序信息
     */
    public function updateSetting(){
        if(IS_POST){
            if(empty($this->request->client['wechatapp']) || empty($this->request->client['wechatapp']['appid'])){
                return enjson(0,'未授权,禁止设置同步小程序信息');
            }
            try {
                $app = app('wechat')->openPlatform();
                $data['json'] = [
                    'component_access_token' => $app->getComponentAccessToken()->getToken(),
                    'component_appid'        => $app->getAccount()->getAppId(),
                    'authorizer_appid'       => $this->request->client['wechatapp']['appid']
                ];
                $response = $app->getClient()->post('cgi-bin/component/api_get_authorizer_info',$data);
                if(($response['errcode']??0) != 0){
                    return enjson(0,$response['errmsg']);
                } 
            } catch (\Exception $e) {
                return enjson(500,$e->getMessage());
            }
            SystemApps::where(['id' => $this->request->apps->id])->update([
                'title'      => $response['authorizer_info']['nick_name'],
                'logo'       => $response['authorizer_info']['head_img'],
                'qrcode_url' => $response['authorizer_info']['qrcode_url'],
                'about'      => $response['authorizer_info']['signature']]
            );
            return enjson(200,'同步小程序信息成功');
        }
        return enjson(10004);
    }

    /**
     * 设置小程序安全域名
     */
    public function setDomain(){
        if(IS_POST){
            if(empty($this->request->client['wechatapp'])){
                return enjson(0,'未授权,禁止设置小程序安全域名信息');
            }
            $app = app('wechat')->client('wechatapp');
            $app->data(array_merge(['action' => 'set'],config('config.wechat_open.domain')))->postJson('wxa/modify_domain');
            $response = $app->data(['action' => 'set','webviewdomain' => config('config.wechat_open.webview')])->postJson('wxa/setwebviewdomain');
            if(($response['errcode']??0) != 0){
                return enjson(0,$response['errmsg']);
            }
            $rel = SystemAppsRelease::where(['client_id' => $this->request->client['wechatapp']['id']])->apps()->find();
            if(empty($rel)){
                $data['apps_id']   = $this->request->apps->id;
                $data['client_id'] = $this->request->client['wechatapp']['id'];
                $data['is_commit'] = 0;
                $data['state']     = 0;
                SystemAppsRelease::create($data);
            }
            return enjson(200,'服务器域名设置成功');
        }
        return enjson(10004);
    }

    /**
     * 上传小程序代码
     */
    public function updataCode(){
        if(IS_POST){
            if(empty($this->request->client['wechatapp'])){
                return enjson(0,'未授权,禁止设置小程序安全域名信息');
            }
            $rel = SystemAppsRelease::where(['client_id' => $this->request->client['wechatapp']['id']])->apps()->find();
            if(empty($rel)){
                return enjson(403,'请先[设置域名]');
            }
            $data['template_id']  = (string)$this->request->app['config']['open_wechat_tpl_id'];
            $data['user_version'] = (string)$this->request->app['config']['var'];
            $data['user_desc']    = '应用发布或升级';
            $data['ext_json']     = json_encode([
                'extAppid' => (string)$this->request->client['wechatapp']['appid'],
                'ext' => [
                    "name" => (string)$this->request->apps->title,
                    "attr" => [
                        'host'       => (string)(config('api.api_sub_domain')?:'https://'.$this->request->host()),
                        'api_id'     => (string)$this->request->client['wechatapp']['api_id'],
                        'api_secret' => (string)$this->request->client['wechatapp']['api_secret'],
                    ]
                ]
            ],JSON_UNESCAPED_UNICODE);
            //提交审核
            $response = app('wechat')->client('wechatapp')->data($data)->postJson('wxa/commit');
            if(($response['errcode']??0) != 0){
                return enjson(0,$response['errmsg']);
            }
            //有审核中的小程序,不修正状态
            if($rel->is_commit < 2){
                $rel->is_commit = 1;
                $rel->save();
            }
            return enjson(200,'最新代码上传成功');
        }
        return enjson(10004);
    }

    /**
     * 获取小程序体验码
     */  
    public function getQrcode(){
        if(empty($this->request->client['wechatapp'])){
            return enjson(403,'未授权,禁止设置小程序安全域名信息');
        }
        $rel = SystemAppsRelease::where(['client_id' => $this->request->client['wechatapp']['id']])->apps()->find();
        if(empty($rel)){
            return enjson(403,'请先[设置域名]');
        }
        $response = app('wechat')->client('wechatapp')->data()->get('wxa/get_qrcode');
        return response($response->getContent())->contentType('image/jpg');
    }

     /**
     * 提交小程序微信审核
     */  
    public function submitWechat(){
        if(empty($this->request->client['wechatapp'])){
            $this->error('未授权,禁止设置小程序安全域名信息');
        }
        $release  = SystemAppsRelease::where(['client_id' => $this->request->client['wechatapp']['id']])->apps()->find();
        if(empty($release)){
            $this->error('请先[设置域名]');
        }
        if($release->is_commit >= 2 && $release->state == 1){
            $this->error('禁止重复提交审核');
        }
        if(IS_POST){
            $param = [
                'category'         => $this->request->param('category/d',0),
                'feedback_info'    => $this->request->param('feedback_info/s'),
                'feedback_stuff'   => $this->request->param('feedback_stuff/a',[]),
                'scene'            => $this->request->param('scene/a'),
                'method'           => $this->request->param('method/a'),
                'has_audit_team'   => $this->request->param('has_audit_team/d'),
                'audit_desc'       => $this->request->param('audit_desc/s'),
            ];
            $this->validate($param,'wechat.submitWechat');
            if($param['scene'][0] == 0 && ($param['scene'][1]??0) >=1){
                return enjson(0,'UGC场景“不涉及”禁止和其它同时选择'); 
            }
            $app = app('wechat')->client('wechatapp');
            $data = [];
            //读取选择的类目
            $category = $app->data()->get('wxa/get_category');
            $data['item_list'] = [$category['category_list'][$param['category']]];
            //UGC场景
            if($param['scene'][0] >= 1){
                $data['ugc_declare'] = [];
                $data['ugc_declare']['scene']          = $param['scene'];
                $data['ugc_declare']['method']         = $param['method'];
                $data['ugc_declare']['has_audit_team'] = $param['has_audit_team'];
                if(empty($param['audit_desc'] )){
                    return enjson(0,'说明对UGC内容的审核机制必须填写'); 
                }
                $data['ugc_declare']['audit_desc']     = $param['audit_desc'];
            }
            //反馈内容
            if(!empty($release->reason) && !empty($param['feedback_info'])){
                $data['feedback_info'] = $param['feedback_info'];
            }
            //小程序截图
            if(!empty($release->reason) && !empty($param['feedback_stuff'])){
                $feedback_stuff = [];
                foreach ($param['feedback_stuff'] as $value) {
                    if (0 === strpos($value, '/')) {
                        $value = substr($value, 1);
                    }
                    $file = str_replace(DS,'/',PATH_PUBLIC.$value);
                    if(is_file($file)){
                        $upload = $app->data(Form::create(['media' => File::fromPath($file)])->toArray())->post('cgi-bin/media/upload?type=image');
                        $feedback_stuff[] = $upload['media_id'];
                    }
                }
                if(!empty($feedback_stuff)){
                    $data['feedback_stuff'] = implode('|',$feedback_stuff);
                }
            }
            //提交成功
            $response = app('wechat')->client('wechatapp')->data($data)->postJson('wxa/submit_audit');
            if(($response['errcode']??null) != 0){
                return enjson(0,$response['errmsg']);
            }
            $release->auditid   = $response['auditid'];
            $release->is_commit = 2;
            $release->state     = 1;
            $release->save();
            return enjson(200,'提交审核成功');
        }else{
            $app = app('wechat')->client('wechatapp');
            $response = $app->data()->get('wxa/get_category');
            $category = [];
            foreach ($response['category_list'] as $key => $value) {
                $category[$key]['name'] = empty($value['third_class']) ? $value['first_class'].' > '.$value['second_class'] : $value['first_class'].'>'.$value['second_class'].'>'.$value['third_class'];
                $category[$key]['id']   = empty($value['third_id']) ? $value['first_id'].' > '.$value['second_id'] : $value['first_id'].'>'.$value['second_id'].'>'.$value['third_id'];
            }
            $view['category'] = $category;
            $view['release']  = $release;
            return view()->assign($view);
        }
    } 

     /**
     * 撤销审核成功
     */
    public function undocodeaudit(){
        if(IS_POST){
            if(empty($this->request->client['wechatapp'])){
                return enjson(403,'未授权,禁止设置小程序安全域名信息');
            }
            $response = app('wechat')->client('wechatapp')->data()->get('wxa/undocodeaudit');
            if(($response['errcode']??null) != 0){
                return enjson(0,$response['errmsg']);
            }
            $rel = SystemAppsRelease::where(['client_id' => $this->request->client['wechatapp']['id']])->apps()->find();
            $rel->is_commit = 0;
            $rel->state     = 0;
            $rel->save();
            return enjson(200,'成功撤销小程序审核');
        }
    }

    /**
     * 发布审核通过的小程序
     */
    public function release(){
        if(IS_POST){
            if(empty($this->request->client['wechatapp'])){
                return enjson(403,'未授权,禁止设置小程序安全域名信息');
            }
            $rel = SystemAppsRelease::where(['client_id' => $this->request->client['wechatapp']['id']])->apps()->find();
            if(empty($rel)){
                return enjson(403,'请先点击[服务器域名]');
            }
            if($rel->is_commit == 2 && $rel->state == 0){
                $response = app('wechat')->client('wechatapp')->data()->postJson('wxa/release');
                if(($response['errcode']??null) != 0){
                    return enjson(0,$response['errmsg']);
                }
                $rel->is_commit = 3;
                $rel->state     = 0;
                $rel->save();
            }else{
                return enjson(403,'审核未通过,禁止发布');
            }
        }
    }

    /**
     * 微信开放平台授权
     */
    public function authorize(int $type = 1){
        try {
            $app = app('wechat')->openPlatform();
            $queries = [
                'component_appid' => $app->getAccount()->getAppId(),
                'pre_auth_code'   => $app->createPreAuthorizationCode()['pre_auth_code'],
                'redirect_uri'    => apis('service/tenant/redirect',['type' => $type== 1?1:2]),
                'auth_type'       => $type== 1?1:2,
            ];
            return redirect(urldecode('https://mp.weixin.qq.com/cgi-bin/componentloginpage?'.http_build_query($queries)));
        }catch (\Exception $e) {
            $this->error($e->getMessage());
        } 
    }

    /**
     * 授权回调处理
     */
    public function getAuth(int $type = 1){
        try {
            switch ($type) {
                case 1:
                    $title = 'wechatmp';
                    break;
                case 2:
                    $title = 'wechatapp';
                    break;
                default:
                    $this->error('授权应用失败');
                    break;
            }
            $auth_code = $this->request->param('auth_code');
            if(empty($auth_code)){
                $this->error('授权码,获取不成功,请重新授权!');
            }
            $response = app('wechat')->openPlatform()->getAuthorization($auth_code);
            $param = [
                'title'   => $title, 
                'appid'   => $response['authorization_info']['authorizer_appid'],
                'app_id'  => $this->request->apps->app_id,
                'apps_id' => $this->request->apps->id,
                'config'  => [
                    'authorizer_appid'         => $response['authorization_info']['authorizer_appid'],
                    'authorizer_access_token'  => $response['authorization_info']['authorizer_access_token'],
                    'authorizer_refresh_token' => $response['authorization_info']['authorizer_refresh_token'],
                    'expires_in'               => $response['authorization_info']['expires_in'],
                    'auth_code'                => $auth_code
                ]
            ];
            $client = SystemAppsClient::edit($param);
            //如果两次授权变更,将会清空发布状态
            $rel = SystemAppsRelease::where(['client_id' => $client->id])->apps()->find();
            if(!empty($rel) && $client->appid != $param['appid']){
                $rel->is_commit = 0;
                $rel->tpl_id    = 0;
                $rel->state     = 0;
                $rel->auditid   = null;
                $rel->reason    = null;
                $rel->save();
            }
            return redirect((string)url('tenant/apps/index'));
        }catch (\Exception $e) {
            $this->error($e->getMessage());
        } 
    }
}