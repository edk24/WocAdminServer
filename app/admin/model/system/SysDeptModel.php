<?php

declare(strict_types=1);

namespace app\admin\model\system;

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


    public static function init()
    {
        // self::onBeforeWrite(function (self $row) {
        //     $changeData = $row->getChangedData();

        //     // 更新祖籍ids
        //     if (isset($changeData['parent_id'])) {
        //         $row->set('ancestors', implode(',', $row->getAncestors()));
        //     }
        // });
    }
}
