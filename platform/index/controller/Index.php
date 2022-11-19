<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 商业授权认证
 */
namespace platform\index\controller;
use base\BaseController;
use base\model\SystemApp;
use platform\index\model\IndexCategory;

class Index extends BaseController{


    /**
     * 主页
     */
    public function index()
    {
        $view['web'] = config('config')['site']??[];
        $view['list'] = SystemApp::where(['is_lock' => 0])->order('sort asc,id desc')->paginate($this->setPage(12));
        $view['cate'] = IndexCategory::where(['is_show' => 1])->order('sort asc,id desc')->select();
        return view()->assign($view);
    }
}