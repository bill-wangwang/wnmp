<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: luofei614 <weibo.com/luofei614>　
// +----------------------------------------------------------------------
namespace auth;

use think\Db;
use think\facade\Session;

/**
 * 权限认证类
 * 功能特性：
 * 1，是对规则进行认证，不是对节点进行认证。用户可以把节点当作规则名称实现对节点进行认证。
 *      $auth=new Auth();  $auth->check('规则名称','用户id')
 * 2，可以同时对多条规则进行认证，并设置多条规则的关系（or或者and）
 *      $auth=new Auth();  $auth->check('规则1,规则2','用户id','and')
 *      第三个参数为and时表示，用户需要同时具有规则1和规则2的权限。 当第三个参数为or时，表示用户值需要具备其中一个条件即可。默认为or
 * 3，一个用户可以属于多个用户组(think_auth_group_access表 定义了用户所属用户组)。我们需要设置每个用户组拥有哪些规则(think_auth_group 定义了用户组权限)
 *
 * 4，支持规则表达式。
 *      在think_auth_rule 表中定义一条规则时，如果type为1， condition字段就可以定义规则表达式。 如定义{score}>5  and {score}<100  表示用户的分数在5-100之间时这条规则才会通过。
 * @category ORG
 * @package ORG
 * @subpackage Util
 * @author luofei614<weibo.com/luofei614>
 */

//数据库
/*
-- ----------------------------
-- think_auth_rule，规则表，
-- id:主键，name：规则唯一标识, title：规则中文名称 status 状态：为1正常，为0禁用，condition：规则表达式，为空表示存在就验证，不为空表示按照条件验证
-- ----------------------------
 DROP TABLE IF EXISTS `think_auth_rule`;
CREATE TABLE `think_auth_rule` (  
    `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,  
    `name` char(80) NOT NULL DEFAULT '',  
    `title` char(20) NOT NULL DEFAULT '',  
    `status` tinyint(1) NOT NULL DEFAULT '1',  
    `condition` char(100) NOT NULL DEFAULT '',  
    PRIMARY KEY (`id`),  
    UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- ----------------------------
-- think_auth_group 用户组表， 
-- id：主键， title:用户组中文名称， rules：用户组拥有的规则id， 多个规则","隔开，status 状态：为1正常，为0禁用
-- ----------------------------
 DROP TABLE IF EXISTS `think_auth_group`;
CREATE TABLE `think_auth_group` ( 
    `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT, 
    `title` char(100) NOT NULL DEFAULT '', 
    `status` tinyint(1) NOT NULL DEFAULT '1', 
    `rules` char(80) NOT NULL DEFAULT '', 
    PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- ----------------------------
-- think_auth_group_access 用户组明细表
-- uid:用户id，group_id：用户组id
-- ----------------------------
DROP TABLE IF EXISTS `think_auth_group_access`;
CREATE TABLE `think_auth_group_access` (  
    `uid` mediumint(8) unsigned NOT NULL,  
    `group_id` mediumint(8) unsigned NOT NULL, 
    UNIQUE KEY `uid_group_id` (`uid`,`group_id`),  
    KEY `uid` (`uid`), 
    KEY `group_id` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
 */

class Auth {

    //默认配置
    protected $_config = array();

    public function __construct() {
        $this->_config = array_merge($this->_config, config('auth.'));
    }

    private function allow_access($name, $data) {
        $name = strtolower($name);
        foreach ($data as $value) {
            if (strtolower($value) == $name) {
                return true;
            }
        }
        return false;
    }

    /**
     * 检查管理员的权限
     * @param string $name 规则标识
     * @return bool
     */
    private function _checkAdministrator($name) {
        $allow = config('auth.administratorAllowRule');
        return $this->allow_access($name, $allow);
    }

    //获得权限$name 可以是字符串或数组或逗号分割， uid为 认证的用户id， $or 是否为or关系，为true是， name为数组，只要数组中有一个条件通过则通过，如果为false需要全部条件通过。
    public function check($name, $uid, $relation = 'or') {
        if (!$this->_config['AUTH_ON']) return true;
        $publicAuthRule = $this->allow_access($name, $this->_config['AUTH_PUBLIC_RULE']);
        if ($publicAuthRule) { //如果在共有的权限有直接返回
            return true;
        }
        if (in_array($uid, $this->_config['administratorId'])) {
            return $this->_checkAdministrator($name);
        }
        $authList = $this->getAuthList($uid);
        if (is_string($name)) {
            $name = strtolower($name); //强制转为小写
            if (strpos($name, ',') !== false) {
                $name = explode(',', $name);
            } else {
                $name = array($name);
            }
        }
        $list = array(); //有权限的name
        foreach ($authList as $val) {

            $val = strtolower($val);
            if (in_array($val, $name)) $list[] = $val;
        }
        if ($relation == 'or' and !empty($list)) {
            return true;
        }
        $diff = array_diff($name, $list);
        if ($relation == 'and' and empty($diff)) {
            return true;
        }
        return false;
    }

    //获得用户组，外部也可以调用
    public function getGroups($uid) {
        static $groups = array();
        if (isset($groups[$uid])) return $groups[$uid];
        $user_groups = Db::table($this->_config['AUTH_GROUP_ACCESS'])->alias('a')->where([
            'a.user_id' => $uid,
            'g.status'  => 1
        ])->join($this->_config['AUTH_GROUP'] . ' g', 'a.role_id=g.id')->select();
        $groups[$uid] = $user_groups ? $user_groups : array();
        return $groups[$uid];
    }

    //获得权限列表
    protected function getAuthList($uid) {
        static $_authList = array();
        if (isset($_authList[$uid])) {
            return $_authList[$uid];
        }
        $sessionAuthList = Session::get('_AUTH_LIST_' . $uid);
        if ($this->_config['AUTH_TYPE'] == 2 && $sessionAuthList) {
            return $sessionAuthList;
        }
        //读取用户所属用户组
        $groups = $this->getGroups($uid);
        $ids = array();
        foreach ($groups as $g) {
            $ids = array_merge($ids, explode(',', trim($g['rules'], ',')));
        }
        $ids = array_unique($ids);
        if (empty($ids)) {
            $_authList[$uid] = array();
            return array();
        }
        //读取用户组所有权限规则
        $map = [
            ['id', 'in', $ids],
            ['status', '=', 1]
        ];
        $rules = Db::table($this->_config['AUTH_RULE'])->where($map)->whereNull('delete_time')->select();
        //循环规则，判断结果。
        $authList = array();
        foreach ($rules as $r) {
            //存在就通过
            $ruleName = explode(',', $r['name']);
            foreach ($ruleName as $v) {
                $v = trim($v);
                if (!empty($v)) {
                    $authList[] = $v;
                }
            }
        }
        $_authList[$uid] = $authList;
        if ($this->_config['AUTH_TYPE'] == 2) {
            //session结果
            Session::set('_AUTH_LIST_' . $uid, $authList);
        }
        return $authList;
    }

    //获得用户资料,根据自己的情况读取数据库
    protected function getUserInfo($uid) {
        static $userinfo = array();
        if (!isset($userinfo[$uid])) {
            $userinfo[$uid] = Db::table($this->_config['AUTH_USER'])->where(['id' => $uid])->find();
        }
        return $userinfo[$uid];
    }

    /**
     * 获取菜单
     * @param $uid
     * @return array
     */
    public function getMenu($uid) {
        if (in_array($uid, $this->_config['administratorId'])) {
            $where = [
                ['pid', '=', 0]
            ];
            if (sizeof($this->_config['administratorTopId']) > 0) {
                $where[] = ['id', 'in', $this->_config['administratorTopId']];
            }
            $menu = Db::table($this->_config['AUTH_RULE'])->where($where)->whereNull('delete_time')->order('display_order desc')->select();
            foreach ($menu as $k => $r) {
                $where = [
                    ['pid', '=', $r['id']],
                    ['status', '=', 1]
                ];
                $menu[$k]['sub_menu'] = Db::table($this->_config['AUTH_RULE'])->where($where)->whereNull('delete_time')->order('display_order desc')->select();
            }
        } else {
            $user_groups = Db::table($this->_config['AUTH_GROUP_ACCESS'])->alias('a')->where([
                'a.user_id' => $uid,
                'g.status'  => 1
            ])->join($this->_config['AUTH_GROUP'] . ' g', 'a.role_id=g.id')->field('rules')->select();
            $ids = [];
            foreach ($user_groups as $g) {
                $ids = array_merge($ids, explode(',', trim($g['rules'], ',')));
            }
            $ids = array_unique($ids);
            if (sizeof($ids) == 0) return array();
            $where = [
                ['pid', '=', 0],
                ['id', 'in', $ids]
            ];
            $menu = Db::table($this->_config['AUTH_RULE'])->where($where)->whereNull('delete_time')->order('display_order desc')->select();

            foreach ($menu as $k => $r) {
                $where = [
                    ['status', '=', 1],
                    ['pid', '=', $r['id']],
                    ['id', 'in', $ids],
                ];
                $menu[$k]['sub_menu'] = Db::table($this->_config['AUTH_RULE'])->where($where)->whereNull('delete_time')->order('display_order desc')->select();
            }
        }
        return $menu;
    }
}
