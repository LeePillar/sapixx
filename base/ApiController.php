<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * API接口访问基础控制器
 */
namespace base;

class ApiController extends BaseController
{

    protected $middleware = ['api'];

    //强制登录验证
    protected $acl    = [];

    //免于登录控制
    protected $aclOff = [];

    /**
     * 
     * 初始化
     * @return void
     */
    protected function initialize(){
        /**
         * 方法1
         * 可以向中间件传参数,是否需要可以根据中间件方法handle中第三个参数决定
         * $this->middleware('acl','参数1','参数2','参数3') 
         * 正确使用方法 only('生效控制器方法1','生效控制器方法2')或->except('不生效制器方法1','不生效制器方法2'); 
         * $this->middleware('acl')->only('fn1','fn2')或->except('fn3','fn4'); 
         */
        //方法2
        if(!empty($this->acl) || !empty($this->aclOff)){
            if(empty($this->acl)){
                $this->middleware['base\middleware\ApiAcl::class'] = ['except' => $this->aclOff];
            }else{
                $this->middleware['base\middleware\ApiAcl::class'] = ['only' => $this->acl];
            }
        }
    }

    /**
     * SaaS引擎版本检查
     */
    public function checkVar()
    {
        return enjson(200,SYSNAME.BASEVER);
    }
}