<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 小程序统一接管
 */
namespace platform\admin\validate;
use think\Validate;

class App extends Validate
{

    protected $rule = [
        'id'         => 'require|number',
        'sort'       => 'require|number',
        'appname'    => 'require|alphaNum',
        'title'      => 'require',
        'logo'       => 'require',
        'about'      => 'require',
        'expire_day' => 'require|number',
        'price'      => 'require|float',
    ];

    protected $message = [
        'id'         => 'ID丢失',
        'sort'       => '排序必须填写',
        'appname'    => '目录只能填写字母',
        'title'      => '名称必须填写',
        'logo'       => 'Logo必须设置',
        'about'      => '简述必须填写',
        'expire_day' => '体验天数必须填写,且必须是整数',
        'price'      => '购买价格必须填写'
    ];

    protected $scene = [
        'sort'    => ['id','sort'],
        'edit'    => ['id','title','about','expire_day','price'],
        'install' => ['logo','appname','title','about','expire_day','price'],
        'plugin'  => ['logo','appname','title','logo','about','price'],
    ];
}
