<?php

namespace app\admin\controller;

use app\admin\library\Auth;
use app\common\enums\ApiCodeEnum;
use app\Controller;
use think\exception\HttpResponseException;

class BaseController extends Controller
{

    /** 不需要登录的方法 */
    protected array $noNeedLogin = [];

    /** 鉴权 */
    protected Auth $auth;


    /** 初始化 */
    protected function initialize()
    {
        $actionName = $this->request->action(true);

        // 登录验证
        $this->auth = Auth::getInstance();

        $needLogin = !$this->noNeedLogin($actionName);

        if ($needLogin) { // 需要登录

            try {
                $token = $this->request->header('token');

                if ($token) {
                    $this->auth->verifyToken($token);
                }
            } catch (\Exception $e) {
                throw new HttpResponseException(resp_fail($e->getMessage(), ApiCodeEnum::TOKEN_INVALID));
            }


            if ($this->auth->isLogin() == false) { // 并且没有登录
                throw new HttpResponseException(resp_fail('请先登录', ApiCodeEnum::NEED_LOGIN));
            }
        }
    }

    /**
     * 是否不需要登录
     *
     * @param string $actionName
     * @return bool
     */
    private function noNeedLogin(string $actionName)
    {
        $whiteList = array_map(function (string $func) { // 遍历数组, 方法名转换小写
            return strtolower($func);
        }, $this->noNeedLogin);

        return in_array($actionName, $whiteList);
    }
}
