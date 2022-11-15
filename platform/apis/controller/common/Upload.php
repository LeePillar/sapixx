<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 全端公共接口
 */
namespace platform\apis\controller\common;
use base\ApiController;

class Upload extends ApiController
{
     /**
     * API文件统一上传服务
     */
    public function index(){
        if(IS_POST){
            $local  = $this->request->param('local/d',0);  //true强制本地
            $upload = $this->app->upload;
            $result = $local?$upload->local():$upload->start();
            return enjson(200,$result);
        }else{
            return enjson(403);
        }
    }
}