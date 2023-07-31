<?php

declare(strict_types=1);

namespace app\admin\model\system;

use app\admin\library\trait\DataScope;
use think\Model;

/**
 * 管理员模型
 */
class SysUserModel extends Model
{
    use DataScope;

    protected $name = 'sys_user';


    // 获取部门名称
    public function getDeptNameAttr($value, $data)
    {
        return SysDeptModel::where('dept_id', $data['dept_id'])->value('dept_name');
    }

    public function dept()
    {
        return $this->belongsTo(SysDeptModel::class, 'dept_id', 'dept_id', [], 'LEFT');
    }
}
