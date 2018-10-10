<?php
return [
    'AUTH_ON' => true, //认证开关
    'AUTH_TYPE' => 1, // 认证方式，1为时时认证；2为登录认证。
    'AUTH_GROUP' => 'boss_role', //用户组数据表名
    'AUTH_GROUP_ACCESS' => 'boss_role_user', //用户组明细表
    'AUTH_RULE' => 'boss_auth_rule', //权限规则表
    'AUTH_USER' => 'boss_admin',//用户信息表
    'AUTH_PUBLIC_RULE' => [
        'Admin-index',
        'Admin-logout',
        'Admin-modifyPwd',
        'Admin-doModifyPwd',
    ],  // 所有都可以访问的
    'forbidAuthRuleId' => [1, 2, 3, 4, 5, 6, 7],   // 禁止授权的菜单Id
    'administratorId' => [1], //超级管理员id，可以有多个，拥有的权限仅为 AUTH_PUBLIC_RULE 和 administratorAllowRule定义的
    'administratorTopId' => [1, 2],    // 超级管理员的菜单Id
    'administratorAllowRule' => [
        'AuthRule-index',
        'AuthRule-add',
        'AuthRule-edit',
        'AuthRule-doAdd',
        'AuthRule-doEdit',
        'AuthRule-modifyStatus',
        'AuthRule-delete',
        'AuthRule-batchSort',
        'Role-index',
        'Role-add',
        'Role-doAdd',
        'Role-edit',
        'Role-doEdit',
        'Role-modifyStatus',
        'Role-delete',
        'Role-auth',
        'Role-setAuth',
        'Role-view',
        'Admin-adminList',
        'Admin-add',
        'Admin-doAdd',
        'Admin-edit',
        'Admin-doEdit',
        'Admin-modifyStatus',
        'Admin-delete',
        'Log-index',
        'Config-edit',
        'Config-doEdit',
        'Log-agIndex',
        'Log-merIndex',
        'PaymentType-index',
        'PaymentType-doSave',
    ], //超级管理员能拥有的权限
    'LOGIN_SESSION'=>'boss',
    'LOGIN_CHECK_TYPE'=>'session', //db为实时（服务器压力大） 其它为session
];