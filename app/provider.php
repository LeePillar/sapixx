<?php
/**
 * @copyright   Copyright (c) 2022 https://www.sapixx.com All rights reserved.
 * @license Licensed (https://www.gnu.org/licenses/gpl-3.0.txt).
 * @link https://www.sapixx.com
 * @author: pillar <ltmn@qq.com>
 * 批量绑定对象到容器中
 */
use base\provider\ExceptionHandle;
use base\provider\Request;
use base\provider\Paginator;
use time\Time,tree\Tree,filter\Filter,code\Code,util\Ary,util\Dir,util\Util;
return [
    'think\Request' => Request::class,
    'think\exception\Handle' => ExceptionHandle::class,
    'think\Paginator' => Paginator::class,
    'time' => Time::class, 
    'tree' => Tree::class,
    'filter' => Filter::class,
    'code' => Code::class,
    'ary' => Ary::class,
    'dir' => Dir::class,
    'util' => Util::class
];