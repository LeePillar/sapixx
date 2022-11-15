<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 文件管理
 */
namespace base\logic;
use think\exception\ValidateException;
use think\Request;
use think\facade\Filesystem;
use think\facade\Config;
use base\model\SystemAppsConfig;
use Hashids\Hashids;

class Upload {

    protected $rootpath;
    protected $config;

    public function __construct(Request $request) {
        $this->request = $request;
        $this->config  = Filesystem::getConfig();
    }

    /**
     * 文件上传
     * @param boolean $private
     * @param string $bucket
     * @param string $file
     * @return void
     */
    public function start($private = false,string $type = 'tenant',string $file = 'file'){
        if($type == 'admin'){
            $bucket = 'platform';
        }else{
            $hashids = new Hashids(config('api.jwt_salt'),6,config('api.safeid_meta'));
            $appname = ($this->request->app?$this->request->app->appname:'storage');
            $apps_id = $this->request->apps?'/'.$hashids->encode($this->request->apps->id):'';
            $bucket  = $appname.$apps_id;
        }
        //储存方式配置读取
        $uploadDriver = $this->config['default'];
        if($private || $uploadDriver == 'local'){
            $uploadDriver = 'local';
        }else{
            if($type != 'admin'){
                $uploadDriver = $this->getAppsConfig();
            }
        }
        $uploadConfig = $this->config['disks'][$uploadDriver];
        if(empty($uploadConfig )){
            exitjson(403,'未找到配置参数');
        }
        try {
            $files = $this->request->file($file);
            if(empty($files)){
                abort(403,'未找到上传资源');
            }
            validate(['file' => ['fileSize' => $this->config['filesize']*1024*1024,'fileExt'  => $this->config['fileext'],'fileMime' => $this->config['filemime']]])->check(['file' => $files]);
            $result  = Filesystem::disk($uploadDriver)->putFile($bucket,$files);
            return ['url' => ($uploadConfig['url']??'').'/'.str_replace('\\','/',$result)];
        }catch (ValidateException $e) {
            exitjson(403,$e->getError());
        } catch (\Exception $e) {
            exitjson(403,$e->getMessage());
        }
    }

    /**
     * 本地资源
     * @param boolean $private
     * @param string $bucket
     * @param string $file
     * @return void
     */
    public function local(){
        $hashids = new Hashids(config('api.jwt_salt'),6,config('api.safeid_meta'));
        $appname = ($this->request->app?$this->request->app->appname:'storage');
        $apps_id = $this->request->apps?'/'.$hashids->encode($this->request->apps->id):'';
        $bucket  = $appname.$apps_id;
        $uploadConfig = $this->config['disks']['public'];
        if(empty($uploadConfig )){
            exitjson(403,'未找到配置参数');
        }
        try {
            $files = $this->request->file('file');
            if(empty($files)){
                abort(403,'未找到上传资源');
            }
            validate(['file' => ['fileSize' => $this->config['filesize']*1024*1024,'fileExt'  => $this->config['fileext'],'fileMime' => $this->config['filemime']]])->check(['file' => $files]);
            $result  = Filesystem::disk('public')->putFile($bucket,$files);
            return ['url' => ($uploadConfig['url']??'').'/'.str_replace('\\','/',$result)];
        }catch (ValidateException $e) {
            exitjson(403,$e->getError());
        } catch (\Exception $e) {
            exitjson(403,$e->getMessage());
        }
    }

    /**
     * 读取应用的独立上传配置
     **/
    public function getAppsConfig(){
        //读取判断是否用户自主配置
        try {
            $config = SystemAppsConfig::configs('storage',true);
            if(!empty($config)){
                switch ($config['driver']) {
                    case 'aliyun':
                        $appconfig = [
                            'type'         => 'aliyun',
                            'endpoint'     => $config['endpoint'],
                            'accessId'     => $config['aes_key'],
                            'accessSecret' => $config['secret'],
                            'bucket'       => $config['bucket'],
                            'url'          => $config['url']
                        ];
                        break;
                    case 'qiniu':
                        $appconfig = [
                            'type'      => 'qiniu',
                            'accessKey' => $config['aes_key'],
                            'secretKey' => $config['secret'],
                            'bucket'    => $config['bucket'],
                            'url'       => $config['url']
                        ];
                        break;
                    case 'qcloud':
                        $appconfig = [
                            'type'      => 'qcloud','scheme' => 'https','timeout' => 60,'connect_timeout' => 60,'read_from_cdn' => false,
                            'region'    => $config['endpoint'],
                            'appId'     => $config['appid'],
                            'secretId'  => $config['aes_key'],
                            'secretKey' => $config['secret'],
                            'bucket'    => $config['bucket'],
                            'url'       => $config['url']
                        ];
                        break;
                    default:
                        return $this->config['default'];
                        break;
                }
                //设置应用独立配置
                Config::set(['disks' => [$config['driver'] => $appconfig]],'filesystem');
                //获取应用独立配置,并重写函数
                $this->config  = Filesystem::getConfig();
                return $config['driver'];
            }
            return $this->config['default'];
        } catch (\Exception $e) {
            exitjson(403,'自助储存配置出错');
        }
    }
}