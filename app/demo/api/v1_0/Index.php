<?php

/**
 * @copyright   Copyright (c) 2017-2030  https://www.sapixx.com All rights reserved.
 * @license Licensed (https://www.gnu.org/licenses/gpl-3.0.txt).
 * @link https://www.sapixx.com
 * 应用API
 */

namespace app\demo\api\v1_0;

use base\ApiController;

class Index extends ApiController
{

    /**
     * 默认API
     */
    public function index()
    {
        return response('APIv1.0签名接口');
    }
}
