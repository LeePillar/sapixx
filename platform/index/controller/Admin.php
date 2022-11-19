<?php
/**
 * @copyright Copyright (c) 2017-2030 SAPI++ All rights reserved.
 * @license https://www.sapixx.com/license.php
 * @link https://www.sapixx.com
 * @todo 知识库管理
 */
namespace platform\index\controller;

use base\AdminController;
use platform\index\model\IndexCategory;
use platform\index\model\IndexApp;

class Admin extends AdminController{

    /**
     * 列表
     */
    public function index(int $parent_id = 0){
        $view['lists'] = IndexCategory::order('sort desc,id desc')->select();
        return view()->assign($view);
    }

    //编辑
    public function edit(int $id = 0){
        if(IS_POST){
            $data = [
                'id'          => $this->request->param('id/d'),
                'logo'        => $this->request->param('logo/s',null),
                'title'       => $this->request->param('title/s'),
                'alias_title' => $this->request->param('alias_title/s'),
            ];
            $this->validate($data,'Category.edit');
            IndexCategory::edit($data);
            return enjson(200);
        }else{
            $view['info'] = IndexCategory::where(['id' => $id])->find();
            return view()->assign($view);
        }
    }

    /**
     * 是否显示
     */
    public function show(){
        if(IS_POST){
            $param = [
                'sort' => $this->request->param('sort/d'),
                'id'   => $this->request->param('id/d'),
            ];
            $this->validate($param,'Category.sort');
            $result = IndexCategory::where(['id' => $param['id']])->find();
            if(!$result){
                return enjson(403,'操作失败');
            }
            $result->is_show = $result->is_show?0:1;
            $result->save();
            return enjson(204);
        }
    }

    /**
     * 排序
     */
    public function sort(){
        if(IS_POST){
            $data = [
                'sort' => $this->request->param('sort/d'),
                'id'   => $this->request->param('id/d'),
            ];
            $this->validate($data,'Category.sort');
            $result = IndexCategory::update(['sort'=>$data['sort'],'id' => $data['id']]);
            return enjson($result?200:0);
        }
    }

    //删除
    public function delete(int $id){
        $result = IndexApp::where(['cate_id'=>$id])->find();
        if($result){
            return enjson(403,'类目中还有内容');
        }
        IndexCategory::where(['id' => $id])->delete();
        return enjson(204);
    }
}