<?php

declare(strict_types=1);

namespace app\admin\validate;

use think\Validate;

class LoginValidate extends Validate
{

    protected $rule = [
        'account'       => 'require|max:16',
        'password'      => 'require'
    ];


    protected $message = [
        'account'       => '账号',
        'password'      => '密码'
    ];

    /** 验证场景 */
    protected $scene = [
        // 账号密码登录
        'accountAndPwd'         => ['account', 'password']
    ];
}
