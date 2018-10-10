<?php
return [
    'upload_path' => Env::get('think_path') . '../public/uploads/images',//确保该目录可写
    'base_url'    => '/uploads/images',
];
/*
    本地上传配置文件 请自行确保  upload_path 有写权限
    update at 2018-03-31 by bill
 */