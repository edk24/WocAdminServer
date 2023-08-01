<?php

namespace app\admin\controller;

use app\admin\exception\LoginException;
use app\admin\validate\auth\LoginValidate;
use app\common\enums\ApiCodeEnum;
use think\Request;

/**
 * 鉴权登录相关
 */
class AuthController extends BaseController
{

    protected array $noNeedLogin = ['login'];

    /**
     * 登录
     *
     * @param Request $req
     */
    public function login(Request $req)
    {
        $data = $req->post();

        // 验证
        $validate = new LoginValidate();

        if (!$validate->scene('accountAndPwd')->check($data)) {

            return resp_fail($validate->getError(), ApiCodeEnum::FAIL);
        }

        // 登录
        try {
            $token = $this->auth->login($data['account'] ?? '', $data['password'] ?? '');

            return resp_success(['token' => $token], '登录成功');
        } catch (LoginException $e) {

            return resp_fail($e->getMessage(), ApiCodeEnum::FAIL);
        }
    }


    /**
     * 获取用户信息
     */
    public function getInfo()
    {
        return resp_data($this->auth->getUserInfo());
    }
}
