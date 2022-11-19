<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 扩展商店
 */
namespace platform\tenant\controller;
use base\model\SystemPlugin;
use base\model\SystemPlugins;
use base\model\SystemTenant;
use base\model\SystemTenantBill;
use base\model\SystemTenantGroup;
use util\Ary;

class Plugin extends Common{

    /**
     * 高级权限控制
     * @return void
     */
    protected function initialize(){
        $this->middleware('platform\tenant\middleware\AppsManage');
    }

    /**
     * 扩展商店
     * @access public
     */
    public function index(){
        $view['list'] = SystemPlugins::where(['apps_id' => $this->request->apps->id,'tenant_id' => $this->request->tenant->id])->order('id desc')->paginate($this->pages);
        $this->bread([['name' =>'扩展中心'],['name' =>'我的扩展']]);
        return view()->assign($view);
    }

    /**
     * 扩展商店
     * @access public
     */
    public function store(){
        $plugin_id    = SystemPlugins::where(['apps_id' => $this->request->apps->id,'tenant_id' => $this->request->tenant->id])->column('plugin_id');
        $view['list'] = SystemPlugin::where(['is_lock' => 0])->whereNotIn('id',$plugin_id)->order('sort desc,id desc')->paginate($this->pages);
        $this->bread([['name' =>'扩展中心'],['name' =>'扩展商店']]);
        return view()->assign($view);
    }

    /**
     * 权限组
     * @access public
     */
    public function group(int $id){
        $info = SystemPlugins::where(['id' => $id])->apps()->find();
        if(!$info){
            $this->error('未找到扩展');
        }
        if (IS_POST) {
            $group = $this->request->param('group/a');
            if (empty($group)) {
                return enjson(403,'最少要选择一个组');
            }
            $info->group_ids = Ary::array_int($group);
            $info->save();
            return enjson(200);
        }else{
            $view['list'] = SystemTenantGroup::apps()->select();
            $view['info'] = $info;
            return view()->assign($view);
        }
    }

    /**
     * 购买扩展
     * @param integer $id
     * @return void
     */
    public function onbuy(){
        if (request()->isPost()) {
            $data = [
                'plugin_id'    => $this->request->param('plugin_id/d'),
                'safepassword' => $this->request->param('safepassword/s'),
                'agree'        => $this->request->param('agree/d',0)
            ];
            $this->validate($data,'Apps.plugin');
            //判断安全密码是否正确
            if(!password_verify(md5($data['safepassword']),$this->request->tenant->safe_password)) {
                return enjson(403,'你输入的安全密码不正确');
            }
            //读取应用
            $plugin  = SystemPlugin::where(['id' => $data['plugin_id']])->find();
            if (empty($plugin)) {
                return enjson(403,'未找到要开通的扩展');
            }
            if(SystemPlugins::where(['apps_id' => $this->request->apps->id,'plugin_id' => $plugin->id,'tenant_id' => $this->request->tenant->id])->count()){
                return enjson(403,$this->request->apps->title.'应用下此扩展已启用过');
            }
            //判断帐号额度,如果价格<=0,就不在查询数据库
            if ($plugin->price > 0) {
                if (!SystemTenant::moneyDec($this->request->tenant->id,$plugin->price)) {
                    return enjson(403,'余额不足,请充值。');
                }
                SystemTenantBill::create([
                    'tenant_id' => $this->request->tenant->id,
                    'state'     => 1,
                    'money'     => -$plugin->price,
                    'message'   => '应用「'.$this->request->apps->title.'」启用「'.$plugin->title.'」扩展',
                    'order_sn'  => uuid()
                ]);
            }
            //创建开通扩展
            SystemPlugins::create([
                'plugin_id' => $plugin->id,
                'apps_id'   => $this->request->apps->id,
                'tenant_id' => $this->request->tenant->id
            ]);
            return enjson(202,'扩展已启用成功');
        } else {
            $view['info'] = SystemPlugin::where(['id' => $this->request->param('id/d')])->find();
            if(!$view['info']){
                $this->error('未找到扩展');
            }
            return view()->assign($view);
        }
    }
}