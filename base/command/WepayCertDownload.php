<?php
/**
 * @copyright  Copyright (c) 2022 https://www.sapixx.com All rights reserved.
 * @license Licensed (https://www.gnu.org/licenses/gpl-3.0.txt).
 * @link https://www.sapixx.com
 * @author: pillar <ltmn@qq.com>
 * 微信支付平台证书下载
 */
namespace base\command;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;
use think\facade\Config;
use EasyWeChat\Pay\Application;
use util\Dir;
use util\Sign;

class WepayCertDownload extends Command
{
    protected function configure()
    {
        $this->setName('cert:wechat')->addArgument('name',Argument::OPTIONAL)->setDescription('微信平台证书下载工具,服务商证书加sp参数');
    }

    protected function execute(Input $input, Output $output)
    {
        try {
            $name = trim($input->getArgument('name'));
            $config = $name?Config::get('config.wechat_sp'):Config::get('config.wechat_pay');
            if (strlen($config['secretv3']) != 32) {
                $output->writeln("无效的ApiV3Key，长度应为32个字节");die; 
            }
            if(!is_file($config['certkey'])){
                $output->writeln("支付证书必须配置");die; 
            }
            if(!is_file($config['cert'])){
                $output->writeln("支付证书秘钥必须配置");die; 
            }
            if(!Dir::isDirs(PATH_RUNTIME)){
                $output->writeln("目录Runtime无写权限");die; 
            }
            $storage = PATH_STORAGE.DS.'sapixx'.DS;
            if(!Dir::isDirs($storage)){
                Dir::mkDirs($storage);
            }
            $setting['mch_id']        = $config['mchid'];
            $setting['secret_key']    = $config['secretv3'];
            $setting['private_key']   = $config['certkey'];
            $setting['certificate']   = $config['cert'];
            $setting['http']['throw'] = false;
            $app = new Application($setting);
            $response = $app->getClient()->get('v3/certificates');
            if ($response->isFailed()) {
                $output->writeln($response['message']);die; 
            }
            if(empty($response['data'][0])){
                $output->writeln('平台证书下载失败');die; 
            }
            $data = $response['data'][0];
            $ciphertext = Sign::decryptCiphertext($data,$setting['secret_key']);
            $cert = $storage.$data['serial_no'].'.pem';
            if(!Dir::fileBody($cert,$ciphertext)){
                $output->writeln("平台正式保存失败,请查看runtime是否有写权限");die; 
            }
            $output->writeln('证书名称：'.$data['serial_no'].'.pem');
            $output->writeln('开始日期：'.$data['effective_time']);
            $output->writeln('结束日期：'.$data['expire_time']);
            $output->writeln("微信支付3.0平台证书 -- 同步成功");
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());
        }
    }
}