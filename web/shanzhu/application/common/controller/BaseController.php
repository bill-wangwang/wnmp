<?php

namespace app\common\controller;

use app\common\service\ConfigService;
use think\Controller;
use think\facade\Request;

class BaseController extends Controller {

    protected $create_ip = '';
    protected $create_time = 0;

    public function initialize() {
        parent::initialize();
        $this->create_ip = Request::ip();
        $this->create_time = time();
        $dbSystem = ConfigService::getDbConfig();
        $fileSystem = config('system.');
        $system = array_merge($fileSystem, $dbSystem);
        $config = config();
        $config['system'] = $system;
        config($config);
    }

    private function _response($code = 0, $message, $data = []) {
        if ( Request::isAjax() ) {
            exit(json_encode(['code' => $code, 'message' => $message, 'data' => $data]));
        } else {
            if ($code == 0) {
                $this->success($message);
            } else {
                $this->error($message ?: '请求错误');
            }
        }
    }

    public function responseSuccess($data=[], $message='success') {
        return $this->_response(0, $message, $data);
    }

    public function responseError($code, $message) {
        return $this->_response($code, $message, []);
    }

    public function responseParamsError($code=10001, $message='参数错误!') {
        return $this->_response($code, $message, []);
    }
}
