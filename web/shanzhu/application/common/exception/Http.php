<?php

namespace app\common\exception;

use think\exception\Handle;
use think\facade\Log;
use think\facade\Request;
use think\facade\Response;

class Http extends Handle {

    public function render(\Exception $e){
        Log::error(['message' => $e->getMessage(), 'code' => $e->getCode(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
        $code = $e->getCode();
        $message = $e->getMessage();
        if ($code === 0) {
            $code = 500;
            if (config('app_debug') === true) {
                $message = '<h1>发生异常了</h1><b>消息 =></b> ' . $e->getMessage()
                    . '<br/><b>代号 =></b> ' . $e->getCode()
                    . '<br/><b>文件 =></b> ' . basename($e->getFile())
                    . '<br/><b>行数 =></b> ' . $e->getLine()
                    . '<br/><b>IP =></b> ' . get_local_by_ip($_SERVER['REMOTE_ADDR'], 1)
                    . '<br/><b>时间 =></b> ' . date('Y-m-d H:i:s');
//                    . '<br/><div style="display:none;">' . var_export($e, 1) . '</div> ';
            } else {
                $message = "抱歉，服务器累了";
            }
        }
        if (Request::isAjax()) {
            return json(['code' => $code, 'message' => $message, 'timeStamp' => time()]);
        } else {
            $template = config('http_exception_template_404');
            $dialogError = input('param.dialogError/d', 0, 'intval');
            if($dialogError){
                $template = config('http_exception_template_dialog404');
            }
            return Response::create($template, 'view', 200)->assign(['message' => $message]);
        }
    }
}