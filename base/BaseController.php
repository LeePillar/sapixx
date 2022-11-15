<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 控制器基类
 **/
declare(strict_types=1);

namespace base;

use think\App;
use think\Validate;
use think\Response;
use think\exception\ValidateException;
use think\exception\HttpResponseException;
use think\facade\Config;
use think\facade\Request;
use think\facade\View;

/**
 * 控制器基础类
 */
abstract class BaseController
{

    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 默认翻页参数
     * @var array
     */
    protected $pages = [];
    protected $page  = 0;  //第几页
    /**
     * 请求的参数
     * @var array
     */
    protected $param;

    /**
     * 构造方法
     * @access public
     * @param  App
     */
    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;
        $this->initRequestConfig();
        $this->initControllerParam();
        $this->initialize();
    }

    /**
     * 初始化
     * @return void
     */
    protected function initialize(){
        //...控制器初始化方法
    }
    
    /**
     * 控制器中添加中间件
     * @param string|object $middleware
     * @param ...$params
     * @return objcet
     */
    protected function middleware($middleware, ...$params)
    {
        $options = [];
        $this->middleware[] = [
            'middleware' => [$middleware, $params],
            'options'    => &$options,
        ];
        return new class($options) {
            protected $options;
            public function __construct(array &$options){
                $this->options = &$options;
            }
            public function only($methods){
                $this->options['only'] = is_array($methods) ? $methods : func_get_args();
                return $this;
            }
            public function except($methods){
                $this->options['except'] = is_array($methods) ? $methods : func_get_args();
                return $this;
            }
        };
    }

    /**
     * 设置控制器常用参数
     * @return void
     */
    public function initControllerParam()
    {
        $this->param = $this->request->param();
        $this->pages = $this->setPage();
        $this->page  = $this->request->param('page/d',1);
    }

    /**
     * 空的控制器方法
     * @param string $method
     * @param array  $args
     * @return void
     */
    public function __call($method, $args){
        abort(404,'未找到你请求的URL "'.$method.'" 页面资源');
    }

    /**
     * 设置分页信息
     * @param integer $num
     * @param array $query
     * @return array
     */
    public function setPage(int $num = 10, array $query = []): array
    {
        return ['list_rows' => $num, 'query' => $query ?: $this->param]; //默认翻页参数
    }

    /**
     * 初始化请求配置
     */
    protected function initRequestConfig()
    {
        // 定义是否GET请求
        defined('IS_GET') or define('IS_GET', $this->request->isGet());
        // 定义是否POST请求
        defined('IS_POST') or define('IS_POST', $this->request->isPost());
        // 定义是否AJAX请求
        defined('IS_AJAX') or define('IS_AJAX', $this->request->isAjax());
        // 定义是否PAJAX请求
        defined('IS_PJAX') or define('IS_PJAX', $this->request->isPjax());
        // 定义是否PUT请求
        defined('IS_PUT') or define('IS_PUT', $this->request->isPut());
        // 定义是否DELETE请求
        defined('IS_DELETE') or define('IS_DELETE', $this->request->isDelete());
        // 定义是否HEAD请求
        defined('IS_HEAD') or define('IS_HEAD', $this->request->isHead());
        // 定义是否PATCH请求
        defined('IS_PATCH') or define('IS_PATCH', $this->request->isPatch());
        // 定义是否为手机访问
        defined('IS_MOBILE') or define('IS_MOBILE', $this->request->isMobile());
        // 定义是否为cli
        defined('IS_CLI') or define('IS_CLI', $this->request->isCli());
        // 定义是否为cgi
        defined('IS_CGI') or define('IS_CGI', $this->request->isCgi());
        // 控制器名称
        defined('APP_NAME') or define('APP_NAME', app('http')->getName());
        // 定义控制器名
        defined('CTRL_NAME') or define('CTRL_NAME', $this->request->controller());
        // 定义操作方法名
        defined('ACT_NAME') or define('ACT_NAME', $this->request->action());
        //访问的URL
        defined('URL') or define('URL', APP_NAME . '/' . strtolower($this->request->controller() . '/' . $this->request->action()));
    }

    /**
     * 获取当前的 response 输出类型
     * @access protected
     * @return string
     */
    protected function getResponseType()
    {
        return IS_AJAX ? Config::get('app.default_ajax_return') : Config::get('app.default_return_type');
    }

    /**
     * 操作成功跳转的快捷方法
     * @access protected
     * @param mixed  $msg    提示信息
     * @param string $url    跳转的 URL 地址
     * @param mixed  $data   返回的数据
     * @param int    $wait   跳转等待时间
     * @param array  $header 发送的 Header 信息
     * @return void
     * @throws HttpResponseException
     */
    protected function success(string $msg = '操作成功',string $title ='操作成功', string $url = null)
    {
        if (is_null($url) && !is_null(Request::server('HTTP_REFERER'))) {
            $url = Request::server('HTTP_REFERER');
        } elseif ('' !== $url && !strpos($url, '://') && 0 !== strpos($url, '/')) {
            $url = url($url);
        }
        $type = $this->getResponseType();
        $result = ['code' => 200, 'msg'  => $msg,'title'  => $title,'url'  => $url];
        if ('html' == strtolower($type)) {
            $result = View::layout(false)->fetch(Config::get('app.return_tmpl'), $result);
        }
        $response = Response::create($result, $type);
        throw new HttpResponseException($response);
    }

    /**
     * 操作错误跳转的快捷方法
     * @access protected
     * @param mixed  $msg    提示信息
     * @param string $url    跳转的 URL 地址
     * @param mixed  $data   返回的数据
     * @param int    $wait   跳转等待时间
     * @param array  $header 发送的 Header 信息
     * @return void
     * @throws HttpResponseException
     */
    protected function error($msg = '操作失败',string $title ='操作失败', string $url = null)
    {
        if (is_null($url)) {
            $url = Request::isAjax() ? '' : 'javascript:history.back(-1);';
        } elseif ('' !== $url && !strpos($url, '://') && 0 !== strpos($url, '/')) {
            $url = url($url);
        }
        $type = $this->getResponseType();
        $result = ['code' => 404, 'msg'  => $msg,'title'  => $title,'url'  => $url];
        if ('html' == strtolower($type)) {
            $result = View::layout(false)->fetch(Config::get('app.return_tmpl'),$result);
        }
        $response = Response::create($result, $type);
        throw new HttpResponseException($response);
    }

    /**
     * 操作跳转的快捷方法
     * @access protected
     * @param mixed  $msg    提示信息
     * @param string $url    跳转的 URL 地址
     * @param mixed  $data   返回的数据
     * @param int    $wait   跳转等待时间
     * @param array  $header 发送的 Header 信息
     * @return void
     * @throws HttpResponseException
     */
    protected function jump($msg = '操作失败', string $url = null, $data = [], $wait = 3)
    {
        if (is_null($url)) {
            $url = Request::isAjax() ? '' : 'javascript:history.back(-1);';
        } elseif ('' !== $url && !strpos($url, '://') && 0 !== strpos($url, '/')) {
            $url = url($url);
        }
        $type = $this->getResponseType();
        $result = ['code' => 302, 'msg'  => $msg, 'data' => $data, 'url'  => $url, 'wait' => $wait];
        if ('html' == strtolower($type)) {
            $result = View::layout(false)->fetch(Config::get('app.jump_tmpl'),$result);
        }
        $response = Response::create($result, $type);
        throw new HttpResponseException($response);
    }

    /**
     * 验证数据
     * @access protected
     * @param  array        $data     数据
     * @param  string|array $validate 验证器名或者验证规则数组
     * @param  array        $message  提示信息
     * @param  bool         $batch    是否批量验证
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate(array $data, $validate, array $message = [], bool $batch = false)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v     = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }
        $v->message($message);
        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }
        return $v->failException(true)->check($data);
    }
}
