<?php

declare(strict_types=1);

namespace app\admin\validate;

use think\Validate;

class SysUserValidate extends Validate
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

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message = [
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
