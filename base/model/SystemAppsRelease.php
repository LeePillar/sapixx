<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 小程序提交状态
 */
namespace base\model;
use base\BaseModel;
use think\Request;

class SystemAppsRelease extends BaseModel{

    //应用状态
    public function getStateTextAttr($value,$data,)
    {
        switch ($data['is_commit']) {
            case 1:
                return '待提审';
            case 2:
                return $data['is_commit']?'审核中':'待发布';
                break;
            case 3:
                return $this->invoke(function(Request $request)  use($value,$data) {
                    if($request->configs->open_wechat_tpl_id > $data['tpl_id']){
                        return '待升级';
                    }else{
                        return '已发布';
                    }
                });
                break;
            default:
                return '待上传';
                break;
        }
    }
}