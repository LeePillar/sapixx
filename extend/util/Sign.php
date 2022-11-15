<?php
/**
 * @copyright 2022 https://www.sapixx.com All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.txt
 * @link https://www.sapixx.com
 * @author pillar<ltmn@qq.com>
 * 正式解密解密
 */
namespace util;

class Sign {

    /**
     * 微信平台证书
     * decryptCiphertext  AEAD_AES_256_GCM 解密加密后的证书内容得到平台证书的明文
     * sodium_crypto_aead_aes256gcm_decrypt >=7.2版本 
     * 去php.ini里面开启下libsodium扩展就可以，之前版本需要安装libsodium扩展，具体查看php.net
     * （ps.使用这个函数对扩展的版本也有要求哦，扩展版本 >=1.08）
     * @param $data
     * @param $key
     * @return string
     */
    public static function decryptCiphertext(array $data,string $aesKey){
        $check_sodium_mod = extension_loaded('sodium'); 
        if($check_sodium_mod === false){ 
            echo '没有安装sodium模块';die; 
        } 
        $check_aes256gcm = sodium_crypto_aead_aes256gcm_is_available(); 
        if($check_aes256gcm === false){ 
            echo '当前不支持AES256GCM';die; 
        }
        if (strlen($aesKey) != 32) {
            echo '无效的ApiV3Key，长度应为32个字节';die; 
        }
        $encryptCertificate = $data['encrypt_certificate'];//证书内容
        $ciphertext         = $encryptCertificate['ciphertext'];//加密后的证书内容;
        $associated_data    = $encryptCertificate['associated_data'];//加密证书的随机串,固定值:certificate; 
        $nonce              = $encryptCertificate['nonce'];//加密证书的随机串,加密证书的随机串
        return sodium_crypto_aead_aes256gcm_decrypt(base64_decode($ciphertext),$associated_data,$nonce,$aesKey); 
    }
}