<?php

declare(strict_types=1);

namespace app\admin\model\auth;

use think\Model;

/**
 * 角色模型
 */
class SysRoleModel extends Model
{
    protected $pk = 'role_id';

    protected $name = 'sys_role';
}
