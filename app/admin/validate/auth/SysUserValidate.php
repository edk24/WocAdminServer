<?php

declare(strict_types=1);

namespace app\admin\validate\auth;

use app\common\validate\BaseValidate;

class SysUserValidate extends BaseValidate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'account'           => 'require',
        'password'          => 'require',
        'nickname'          => 'require',
        'dept_id'           => 'require',
    ];


    protected $field = [
        'account'           => '账号',
        'password'          => '密码',
        'nickname'          => '昵称',
        'dept_id'           => '所属部门'
    ];

    protected $scene = [
        'create'         => ['account', 'password', 'nickname', 'dept_id'],
        'edit'         => ['account', 'password', 'nickname', 'dept_id'],
        'changePwd'         => ['account', 'password'],
    ];
}
