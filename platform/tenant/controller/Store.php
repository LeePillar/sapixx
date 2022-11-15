<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 应用商店
 */
namespace platform\tenant\controller;
use base\model\SystemTenant;
use base\model\SystemTenantBill;
use base\model\SystemApps;
use base\model\SystemApp;
use time\Time;

class Store extends Common{
    
    /**
     * 应用商店
     */
    public function index(){
        if($this->request->tenant->parent_id){
            $this->jump('子帐号无访问,应用商店的权限');
        }
        $view['breadcrumb'] = [['name'=>'应用商店','url' => (string)url('store/index')],['name' => '应用列表']];
        $view['list'] = SystemApp::where(['is_lock' => 0])->order('sort asc,id desc')->paginate($this->setPage(12));
        return view()->assign($view);
    }

    /**
     * 购买应用
     * @param integer $id
     * @return void
     */
    public function onbuy(){
        if (request()->isPost()) {
            $data = [
                'app_id'       => $this->request->param('app_id/d'),
                'title'        => $this->request->param('title/s'),
                'about'        => $this->request->param('about/s'),
                'safepassword' => $this->request->param('safepassword/s'),
                'agree'        => $this->request->param('agree/d',0)
            ];
            $this->validate($data,'Apps.onbuy');
            //判断安全密码是否正确
            if(!password_verify(md5($data['safepassword']),$this->request->tenant->safe_password)) {
                return enjson(403,'你输入的安全密码不正确');
            }
            //读取应用
            $buyapp  = SystemApp::where(['id' => $data['app_id'],'is_lock' => 0])->find();
            if (empty($buyapp)) {
                return enjson(403,'未找到要开通的应用');
            }
            //判断帐号额度,如果价格<=0,就不在查询数据库
            if ($buyapp->price > 0) {
                $price = $buyapp->price;
                if(!empty($this->request->tenant->agent)){
                    $price = money($buyapp->price*$this->request->tenant->agent->discount/10);
                }
                if (!SystemTenant::moneyDec($this->request->tenant->id,$price)) {
                    return enjson(403,'余额不足,请充值。');
                }
                //增加账单
                SystemTenantBill::create(['tenant_id' => $this->request->tenant->id,'state' => 1,'money' => -$price,'message' => '下单'.$buyapp->title.'应用','order_sn' => uuid()]);
            }
            //新增购买列表
            $order['app_id']    = $buyapp->id;
            $order['tenant_id'] = $this->request->tenant->id;
            $order['title']     = $data['title'];
            $order['about']     = $data['about'];
            $order['end_time']  = Time::daysAfter(365);
            $rel = SystemApps::create($order);
            return enjson(204,'应用成功开通',$rel);
        } else {
            $view['info'] = SystemApp::where(['id' => $this->request->param('id/d')])->find();
            if(!$view['info']){
                $this->error('未找到应用');
            }
            $view['breadcrumb'] = [['name'=>'应用商店','url' => (string)url('store/index')],['name' => '应用开通']];
            return view()->assign($view);
        }
    }


    /**
     * 开通体验应用
     * @param integer $id
     * @return void
     */
    public function tryout(){
        if (request()->isPost()) {
            $data = [
                'app_id'       => $this->request->param('app_id/d'),
                'title'        => $this->request->param('title/s'),
                'about'        => $this->request->param('about/s'),
                'safepassword' => $this->request->param('safepassword/s'),
                'agree'        => $this->request->param('agree/d',0)
            ];
            $this->validate($data,'Apps.onbuy');
            //判断安全密码是否正确
            if(!password_verify(md5($data['safepassword']),$this->request->tenant->safe_password)) {
                return enjson(403,'你输入的安全密码不正确');
            }
            //读取应用
            $buyapp  = SystemApp::where(['id' => $data['app_id'],'is_lock' => 0])->find();
            if (empty($buyapp)) {
                return enjson(403,'未找到要开通的应用');
            }
            if($buyapp->expire_day <= 0){
                return enjson(403,'当前应用,没有开通体验权限');
            }
            $rel = SystemApps::where(['app_id' => $buyapp->id,'tenant_id' => $this->request->tenant->id])->find();
            if ($rel) {
                return enjson(403,'这个应用你已体验过一次');
            }
            //新增购买列表
            $order['app_id']    = $buyapp->id;
            $order['tenant_id'] = $this->request->tenant->id;
            $order['title']     = $data['title'];
            $order['about']     = $data['about'];
            $order['end_time']  = Time::daysAfter($buyapp->expire_day);
            $rel = SystemApps::create($order);
            return enjson(204,'应用已开通'.$buyapp->expire_day.'天体验',$rel);
        } else {
            $view['info'] = SystemApp::where(['id' => $this->request->param('id/d')])->find();
            if(!$view['info']){
                $this->error('未找到应用');
            }
            $view['breadcrumb'] = [['name'=>'应用商店','url' => (string)url('store/index')],['name' => '体验应用']];
            return view()->assign($view);
        }
    }
}