<?php

declare(strict_types=1);

namespace app\admin\validate\auth;

use app\common\validate\BaseValidate;

class SysRoleValidate extends BaseValidate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'role_key'              => 'require',
        'role_name'             => 'require',
        'data_scope'            => 'require'
    ];


    protected $field = [
        'role_key'          => '角色权限',
        'role_name'         => '角色名称',
        'data_scope'        => '数据范围'
    ];

    protected $scene = [
        'create'            => ['role_key', 'role_name', 'data_scope'],
        'edit'              => ['role_key', 'role_name', 'data_scope']
    ];
}
