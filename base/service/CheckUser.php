<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 用户登录支持token模式和Session两种方式
 */
declare(strict_types=1);

namespace base\service;
use think\facade\Session;
use think\Request;
use base\model\SystemUser;
use code\Code;

class CheckUser
{

    /**
     * Session名称
     * @var string
     */
    protected $user_session = 'sapixx_user';
    protected $user_key     = 'sapixx_user_key';

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct(Request $request)
    {
        $this->request  = $request;
        if(isset($this->request->apps)){
            $this->user_session = uuid(4,false,$request->host(true)."user_apps_id_".$request->apps->id);
            $this->user_key     = uuid(4,false,$request->host(true)."user_key_apps_id_".$request->apps->id);
        }
    }

    /**
     * ##########################################
     * 判断是否登录
     * @access public
     */
    public function getLogin(string $token = null)
    {
        if (Session::has($this->user_session)) {
            $uid = Code::de(Session::get($this->user_session),$this->user_key);
            if ($uid) {
                return SystemUser::with(['client' => fn($query) => $query->where(['client_id'=> $this->request->client->id])])->where(['id' => $uid, 'is_lock' => 0,'is_delete' => 0])->hidden(['password','safe_password'])->cache(120)->find();
            }
        }else{
            return $this->getToken($token);
        }
        return false;
    }

    /**
     * getLogin 别名
     * @return void
     */
    public function getUser()
    {
        $user = $this->getLogin();
        if(!$user){
            exitjson(11102);
        }
        return $user;
    }
    
    /**
     * 登录后台管理Session
     * @access public
     */
    public function setLogin($id)
    {
        Session::set($this->user_session,Code::en($id,$this->user_key));
        Session::save();
    }

    /**
     * 退出后台管理Session
     * @access public
     */
    public function clearLogin()
    {
        Session::delete($this->user_session);
        Session::save();
    }

    /**
     * 通过token判断登录
     * @return void
     */
    protected function getToken(string $token = null)
    {
        if(empty($token)){
            $header = $this->request->header();
            if (empty($header['sapixx-token'])) {
                return false;
            }
            $token = $header['sapixx-token'];
        }
        $uid = app('jwt')->deJwt($token);
        if(!$uid){
            return false;
        }
        return SystemUser::with(['client' => fn($query) => $query->where(['client_id'=> $this->request->client->id])])->where(['id' => $uid, 'is_lock' => 0,'is_delete' => 0])->hidden(['password','safe_password'])->cache(120)->find();
    }
}