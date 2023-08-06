<?php

declare(strict_types=1);

namespace app\admin\model\auth;

use app\admin\library\trait\DataScope;
use app\common\enums\StatusType;
use think\facade\Db;
use think\Model;

/**
 * 管理员模型
 */
class SysUserModel extends Model
{
    use DataScope;

    protected $pk = 'user_id';
    protected $name = 'sys_user';

    protected $type = [
        'last_login_time'       => 'timestamp'
    ];

    public function getStatusTextAttr($value, $data)
    {
        if (isset($data['status']) && !empty($data['status'])) {
            return StatusType::from($data['status'])->text();
        }

        return '';
    }

    public function getStatusColorAttr($value, $data)
    {
        if (isset($data['status']) && !empty($data['status'])) {
            return StatusType::from($data['status'])->color();
        }

        return 'danger';
    }


    // 获取部门名称
    public function getDeptNameAttr($value, $data)
    {
        return SysDeptModel::where('dept_id', $data['dept_id'])->value('dept_name');
    }

    // 获取角色名称
    public function getRoleNameAttr($value, $data)
    {
        $roleId = Db::name('sys_user_role')->where('user_id', $data['user_id'])->value('role_id');
        return SysRoleModel::where('role_id', $roleId)->value('role_name');
    }

    public function getRoleIdAttr($value, $data)
    {
        $roleId = Db::name('sys_user_role')->where('user_id', $data['user_id'])->value('role_id');
        return $roleId;
    }

    public function dept()
    {
        return $this->belongsTo(SysDeptModel::class, 'dept_id', 'dept_id', [], 'LEFT');
    }
}
