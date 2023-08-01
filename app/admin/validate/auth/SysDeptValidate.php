<?php

declare(strict_types=1);

namespace app\admin\validate\auth;

use app\common\validate\BaseValidate;

class SysDeptValidate extends BaseValidate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = [
        'dept_id'           => 'require|number',
        'parent_id'         => 'require|number',
        'dept_name'         => 'require',
        'sort'              => 'number|max:999|min:0',
        'status'            => 'require'
    ];


    protected $field = [
        'dept_id'           => '部门ID',
        'parent_id'         => '上级部门',
        'dept_name'         => '部门名称',
        'sort'              => '排序',
        'status'            => '状态'
    ];


    protected $scene = [
        'create'        => ['parent_id', 'dept_name', 'sort', 'status'],
        'update'        => ['dept_id', 'parent_id', 'dept_name', 'sort', 'status'],
        'get'           => ['dept_id'],
        'del'           => ['dept_id'],
    ];
}
