<?php
/**
 * API的JWT方式签名秘钥配置
 */
return [
    //API独立访问域名,结尾不包含 /
    'api_sub_domain'        => '',

    //ID加密元码(禁止修改)
    'safeid_meta'           => 'abcdefghijklmnopqrstuvwxyz1234567890',

    //是否开启API接口签名认证
    'sign_auth_on'          => true,

    //API签名有效期
    'api_sige_time'         => 60 * 5,

    //jwt加密key(可以是完整正式秘钥)
    'jwt_secret_key'        => '5g85eNCZlBFDhpAaCCJTvuAafDzPn2J0Ad2W7nMS1j0eYPlV9Xj9770XuIARYpcQ',

    //默认加盐(64位)
    'jwt_salt'              => 'yoFmIDwPJhU8Sg1FWyb91znN18lBvVCB1Q1O4lYNObAtVkgHVRDEoIvTcYXR6mjO',

    //jwt算法,可配置的值取决于使用的jwt包支持哪些算法
    'jwt_algorithm'         => 'HS256',

    //用户登录有效期
    'jwt_logout_time'       => 60 * 60 * 24 * 7,
];
