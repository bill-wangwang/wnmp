<?php

namespace app\index\controller;

use app\common\exception\ObjectExistException;
use app\common\exception\ObjectNotFoundException;
use app\common\exception\ParamsException;
use app\common\exception\VerifyCodeException;
use app\common\service\SmsService;
use app\common\service\tools\UtilService;
use app\common\service\UserService;

class IndexController extends CommonController {
    public function index() {
        return view();
    }

    public function succ() {
        return view();
    }

    public function reg() {
        $mobile = input('param.mobile/s', '', 'trim');
        $invite_mobile = input('param.invite_mobile/s', '', 'trim');
        $pwd = input('param.pwd/s', '', 'trim');
        if(!$pwd){
            throw new ParamsException("密码不能为空");
        }
        $sms_code = input('param.sms_code/s', '', 'trim');
        if(!$sms_code){
            throw new ParamsException("短信验证码不能为空");
        }
        $parent_id = 0;
        if ($invite_mobile) {
            if (!UtilService::isMobile($invite_mobile)) {
                throw new ParamsException("请输入正确的邀请者的11位手机号码。");
            }
            //是否是有效的邀请者
            $object = UserService::getByMobile($invite_mobile);
            if (!$object) {
                throw new ObjectNotFoundException("无效的邀请人手机号");
            }
            $parent_id = $object['id'];
        }
        if (!UtilService::isMobile($mobile)) {
            throw new ParamsException("请输入正确的11位手机号码。");
        }
        if ($mobile === $invite_mobile) {
            throw new ParamsException("邀请人手机号不能和注册人手机号一样。");
        }
        $object = UserService::getByMobile($mobile);
        if ($object) {
            throw new ObjectExistException("该手机号已经注册过了");
        }
        //验证短信验证码
        SmsService::checkVerifyCode('register', $mobile,$sms_code);
        $data = [
            'mobile'    => $mobile,
            'parent_id' => $parent_id,
            'pwd'       => md5($pwd),
            'create_ip'=>get_client_ip()
        ];
        $object = UserService::create($data);
        $this->responseSuccess(['redirect_url'=>url('Index/succ'), 'id'=>$object->id]);
    }

    public function getSmsCode() {
        $mobile = input('param.mobile/s', '', 'trim');
        $verify_code = input('post.verify_code/s', '', 'trim');
        if (!captcha_check($verify_code)) {
            throw new VerifyCodeException("图片验证码错误");
        }
        if (!UtilService::isMobile($mobile)) {
            throw new ParamsException("请输入正确的11位手机号码。");
        }
        //是否已经注册了
        //发送短信
        $code = SmsService::getVerifyCode('register', $mobile);
        SmsService::send('register', $mobile, [$code]);
        $this->responseSuccess();
    }
}