<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2021 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( https://www.gnu.org/licenses/gpl-3.0.txt )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace base\provider;

use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\facade\View;
use think\Response;
use Throwable;

/**
 * 应用异常处理类
 */
class ExceptionHandle extends Handle
{

    protected const HTTP_SUCCESS = 200;

    /**
     * 不需要记录信息（日志）的异常类列表
     * @var array
     */
    protected $ignoreReport = [
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        DataNotFoundException::class,
        ValidateException::class,
    ];

    /**
     * 记录异常信息（包括日志或者其它方式记录）
     *
     * @access public
     * @param  Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void
    {
        // 使用内置的方式记录异常日志
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @access public
     * @param \think\Request   $request
     * @param Throwable $e
     * @return Response
     */
    public function render($request, Throwable $e): Response
    {
        // 添加自定义异常处理机制
        if (method_exists($e, "getStatusCode")) {
            $httpCode = $e->getStatusCode();
        } else {
            $httpCode = self::HTTP_SUCCESS;
        }

        // 参数验证错误
        if ($e instanceof ValidateException) {
            return enjson(403, $e->getError());
        }

        //请求异常
        if ($e instanceof HttpException && ($request->isAjax() || $request->isJson())) {
            return enjson(403, $e->getMessage());
        }
        
        View::assign(['message' => $e->getMessage(),'title' => '友情提示', 'code' => $httpCode ?: self::HTTP_SUCCESS]);
        return parent::render($request,$e);
    }
}
