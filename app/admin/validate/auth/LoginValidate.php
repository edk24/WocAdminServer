<?php

declare(strict_types=1);

namespace app\admin\validate\auth;

use app\common\validate\BaseValidate;

class LoginValidate extends BaseValidate
{

    protected $rule = [
        'account'       => 'require|max:16',
        'password'      => 'require'
    ];


    protected $field = [
        'account'       => '账号',
        'password'      => '密码'
    ];

    /** 验证场景 */
    protected $scene = [
        // 账号密码登录
        'accountAndPwd'         => ['account', 'password']
    ];
}
