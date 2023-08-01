<?php

declare(strict_types=1);

namespace app\admin\model\auth;

use app\admin\library\trait\DataScope;
use think\facade\Db;
use think\Model;

/**
 * 部门模型
 */
class SysDeptModel extends Model
{
    use DataScope;

    protected $name = 'sys_dept';


    // 关联用户
    public function user()
    {
        return $this->hasOne(SysUserModel::class, 'dept_id', 'dept_id')->joinType('left');
    }


    /**
     * 取祖籍ids
     *
     * @return array
     */
    public function getAncestors(): array
    {
        $parentIds = [];

        $pid = $this->getData('parent_id');
        array_push($parentIds, $pid);
        while ($pid != 0) {
            $pid = Db::name('sys_dept')->where('dept_id', $pid)->value('parent_id');
            array_push($parentIds, $pid);
        }
        return $parentIds;
    }



    public static function onBeforeWrite(self $model)
    {
        $changeData = $model->getChangedData();

        // 更新祖籍ids
        if (isset($changeData['parent_id'])) {
            $model->set('ancestors', implode(',', $model->getAncestors()));
        }
    }
}
