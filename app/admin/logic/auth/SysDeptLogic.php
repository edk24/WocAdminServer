<?php

declare(strict_types=1);

namespace app\admin\logic\auth;

use app\admin\model\auth\SysDeptModel;
use app\admin\model\auth\SysUserModel;
use app\common\enums\StatusType;
use library\Tree;
use RuntimeException;
use think\facade\Db;

/**
 * 部门逻辑
 */
class SysDeptLogic
{

    /**
     * 查询全部部门管理数据
     *
     * @param array $params
     * @return array
     */
    public static function listAllDept(array $params = []): array
    {
        $where = [];

        if (isset($params['status'])) {
            $where['status'] = $params['status'];
        }

        $result = SysDeptModel::where($where)->order('sort asc')->select();

        return $result->toArray();
    }

    /**
     * 查询部门管理数据
     *
     * @param array $params
     * @return array
     */
    public static function allDept(array $params = []): array
    {
        $where = [];

        if (isset($params['status'])) {
            $where[] =  ['status', '=', $params['status']];
        }
        // $join = [
        //     ['__ADMIN__ `u`', 'u.dept_id = d.dept_id', 'left']
        // ];

        $result = (new SysDeptModel)->dataScope('d')->alias('d')->where($where)->order('sort asc')->group('d.dept_id')->select();
        return $result->toArray();
    }


    /**
     * 构建前端所需要树结构
     *
     * @return array
     */
    public static function buildDeptTree(array $deptList, int $pid = 0): array
    {
        $result = Tree::getTreeArray($deptList, $pid, 'dept_id', 'parent_id');
        return $result;
    }


    /**
     * 获取指定部门所有下级部门ids
     *
     * @param integer $deptId
     * @return array
     */
    public static function getChildIdsByDeptId(int $deptId, bool $whitSelf = false): array
    {
        $childIds = SysDeptModel::whereRaw('FIND_IN_SET(:dept_id, ancestors)', ['dept_id' => $deptId])->column('dept_id');
        if ($whitSelf) {
            array_unshift($childIds, $deptId);
        }
        return $childIds;
    }

    /**
     * 是否存在子部门
     *
     * @param integer $deptId
     * @return boolean
     */
    public static function hasChildByDeptId(int $deptId): bool
    {
        $child = self::getChildIdsByDeptId($deptId, false);
        return count($child) > 0;
    }

    /**
     * 检查部门是否存在用户
     *
     * @param integer $deptId
     * @return boolean
     */
    public static function checkDeptExistUser(int $deptId): bool
    {
        $exist = SysUserModel::where('dept_id', $deptId)->limit(1)->value('1') == '1';
        return $exist;
    }

    // 检查部门名称是否存在 (同级别不允许重名)
    protected static function checkDeptNameUnique(string $deptName, int $parentId, ?int $selfDeptId = 0): bool
    {
        $where['dept_name'] = $deptName;
        $where['parent_id'] = $parentId;
        if ($selfDeptId) {
            $where['id'] = ['<>', $selfDeptId];
        }
        $exist = SysDeptModel::where($where)->find();
        return $exist ? true : false;
    }

    public static function create(array $data): int
    {
        $dept = new SysDeptModel();
        $dept->set('parent_id', $data['parent_id']);
        $dept->set('dept_name', $data['dept_name']);
        $dept->set('sort', $data['sort'] ?? 999);
        $dept->set('leader', isset($data['leader']) ? $data['leader'] : null);
        $dept->set('mobile', isset($data['mobile']) ? $data['mobile'] : null);
        $dept->set('status', $data['status'] ?? StatusType::NORMAL->value);

        $success = $dept->save();
        if (!$success) {
            throw new RuntimeException('创建部门失败, 请稍后再试~');
        }

        return $dept->getData('id');
    }

    public static function update(array $data)
    {
        $dept = SysDeptModel::where('dept_id', $data['dept_id'])->find();
        if ($dept == null) {
            throw new RuntimeException('找不到该部门数据~');
        }
        $dept->set('parent_id', $data['parent_id']);
        $dept->set('dept_name', $data['dept_name']);
        $dept->set('sort', $data['sort'] ?? 999);
        $dept->set('leader', isset($data['leader']) ? $data['leader'] : null);
        $dept->set('mobile', isset($data['mobile']) ? $data['mobile'] : null);
        $dept->set('status', $data['status'] ?? StatusType::NORMAL->value);

        $success = $dept->save();
        if (!$success) {
            throw new RuntimeException('修改部门失败, 请稍后再试~');
        }
    }

    public static function getById(int $id): SysDeptModel
    {
        $dept = SysDeptModel::where('dept_id', $id)->find();
        if ($dept == null) {
            throw new RuntimeException('找不到该数据');
        }
        return $dept;
    }

    public static function delById(int $id)
    {
        $dept = self::getById($id);
        $success = $dept->delete();

        if ($success == false) {
            throw new RuntimeException('删除失败');
        }
    }


    /**
     * 获取上级部门ID
     *
     * @param integer $deptId
     * @return integer
     */
    public static function getParentId(int $deptId): int
    {
        $pid = Db::name('sys_dept')->where('dept_id', $deptId)->value('parent_id', 0);
        return $pid;
    }
}
