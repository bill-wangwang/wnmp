<?php
return [
    'qcloudsms'=>[ //腾讯短信
           'access_key_id'     => 1400107545,
           'access_key_secret' => '0c6850a28b44b240b88897f411e46d97',
           'sign_name'=>'', //这里是副签名，留空则取web控制台的默认主签名，如果不为空则需要
           'template'          => [
               'register'            => [
                   'title'       => '用户注册',
                   'template_id' => '158185',
                   'params'      => ['{1}'],
                   'template'    => '您好，欢迎注册山竹，您的手机验证码是：{1}，若非本人操作，请忽略！'
               ],
              
        ]
    ],
    'local' => [ //仅本地记录
                 'public_verify_code' => \think\facade\Env::get('sms.public_verify_code', '7913'), //采用本地发送时的通用验证码。
                 'template'           => [
                     'register'            => [
                         'title'       => '用户注册',
                         'template_id' => '145349',
                         'params'      => ['{1}'],
                         'template'    => '您好，欢迎注册山竹，您的手机验证码是：{1}，若非本人操作，请忽略！'
                     ],
                     
                 ]
    ]
];