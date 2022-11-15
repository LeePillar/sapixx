<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 跨域支持
 */
declare (strict_types = 1);

namespace base\constant;
use Closure;
use think\Config;
use think\Request;
use think\Response;
use think\facade\Session;

/**
 * 跨域请求支持
 */
class InitCross
{

    protected $header = [
        'Access-Control-Allow-Credentials' => 'true',
        'Access-Control-Max-Age'           => 1800,
        'Access-Control-Allow-Methods'     => 'GET, POST, PATCH, PUT, DELETE, OPTIONS',
        'Access-Control-Allow-Headers'     => 'Authorization,Content-Type,If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-CSRF-TOKEN, X-Requested-With,sapixx-apiid, sapixx-token, sapixx-cookie',
    ];

    public function __construct(Config $config)
    {
        $this->cookieDomain = $config->get('cookie.domain', '');
    }

    /**
     * 允许跨域请求
     * @access public
     * @param Request $request
     * @param Closure $next
     * @param array   $header
     * @return Response
     */
    public function handle($request, Closure $next, ? array $header = [])
    {
        $header = !empty($header) ? array_merge($this->header, $header) : $this->header;
        //跨域问题
        if (!isset($header['Access-Control-Allow-Origin'])) {
            $origin = $request->header('origin');
            if ($origin && ('' == $this->cookieDomain || strpos($origin, $this->cookieDomain))) {
                $header['Access-Control-Allow-Origin'] = $origin;
            } else {
                $header['Access-Control-Allow-Origin'] = '*';
            }
        }
        //跨域SESSION共享问题
        $cookie_id = $request->header('sapixx-cookie');
        if (isset($cookie_id)) {
            if(preg_match('/^([a-zA-Z0-9]){18,32}$/',$cookie_id)){
                Session::setId(md5($cookie_id));
            }else{
                exitjson(403,'自定义Header参数sapixx-cookie仅支持18-32位字符串');
            }
        }
        return $next($request)->header($this->header);
    }
}
