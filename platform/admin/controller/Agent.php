<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 应用管理
 */
namespace platform\admin\controller;
use base\model\SystemTenant;
use base\model\SystemAgent;

class Agent extends Common{

     /**
     * 代理列表
     * @access public
     * @return bool
     */
    public function index(){
        $view['list'] = SystemAgent::withCount('tenant')->order('id desc')->paginate($this->pages);
        $view['breadcrumb'] = [['name' =>'控制面板','icon' =>'window'],['name' =>'代理管理','url' => (string)url('agent/index')]];
        return view()->assign($view);
    }

    /**
     * 添加和编辑
     */
    public function edit($id = 0){
        if(IS_AJAX){
            $data = [
                'id'                  => $id,
                'title'               => $this->request->param('title/s'),
                'price'               => $this->request->param('price/f'),
                'price_gift'          => $this->request->param('price_gift/d'),
                'recharge_price'      => $this->request->param('recharge_price/f'),
                'recharge_price_gift' => $this->request->param('recharge_price_gift/f'),
                'discount'            => $this->request->param('discount/d')
            ];
            $this->validate($data,'Admin.agent');
            $data['id'] ? SystemAgent::update($data) : SystemAgent::create($data);
            return enjson(200,['url' => (string)url('agent/index')]);
        }else{
            $view['info'] = SystemAgent::where(['id' => $id])->find();
            $view['breadcrumb'] = [['name' =>'控制面板','icon' =>'window'],['name' =>'代理管理','url' => (string)url('agent/index')],['name' =>'创建编辑']];
            return view()->assign($view);
        }
    }

    /**
     * 锁定授权
     * @param integer $id 用户ID
     */
    public function status(int $id){
        $result = SystemAgent::where(['id' => $id])->find();
        $result->is_enabled = $result->is_enabled ? 0 : 1;
        $result->save();
        return enjson(302);
    }

    /**
     * [删除]
     * @access public
     * @return bool
     */
    public function delete(int $id){
        $result = SystemTenant::where(['agent_id' => $id])->find();
        if($result){
            return enjson(403,'本代理级别已有代理,禁止删除');
        }
        SystemAgent::destroy($id);
        return enjson(204);
    }
}