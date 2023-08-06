<?php

namespace app\admin\logic\auth;

use app\admin\model\auth\SysRoleModel;
use app\common\enums\StatusType;

/**
 * 角色逻辑
 */
class SysRoleLogic
{
    // 查询字段
    protected static array $field = [
        'role_id',
        'role_key',
        'role_name',
        'sort',
        'data_scope',
        'status',
        'create_time',
        'update_time'
    ];

    /**
     * 查询角色列表
     */
    public static function lists(array $params): array
    {
        $where = array();
        if (isset($params['role_key'])) {
            $where[] = ['role_key', 'LIKE', "%{$params['role_key']}%"];
        }

        if (isset($params['role_name'])) {
            $where[] = ['role_name', 'LIKE', "%{$params['role_name']}%"];
        }

        $limit = intval($params['limit'] ?? 10);
        $result = SysRoleModel::where($where)->field(self::$field)->paginate($limit);
        return [
            'total'         => $result->total(),
            'rows'         => $result->items()
        ];
    }

    /**
     * 创建角色
     */
    public static function create(array $params): int
    {
        if (self::existByKey($params['role_key'])) {
            throw new \RuntimeException(sprintf('角色权限 %s 已存在,请不要重复添加', $params['role_key']));
        }

        if (self::existByName($params['role_key'])) {
            throw new \RuntimeException(sprintf('角色名称 %s 已存在,请不要重复添加', $params['role_name']));
        }

        $role = new SysRoleModel();
        $role->set('role_key', $params['role_key']);
        $role->set('role_name', $params['role_name']);
        $role->set('sort', $params['sort'] ?? 999);
        $role->set('data_scope', $params['data_scope']);
        $role->set('status', $params['status'] ?? StatusType::NORMAL->value);
        $success = $role->save();
        if (!$success) {
            throw new \RuntimeException('创建角色失败,请稍后重试');
        }
        return intval($role->getData('role_id') ?? -1);
    }

    /**
     * 修改用户角色
     */
    public static function update(array $params)
    {
        $role = SysRoleModel::where('role_id', $params['role_id'])->find();
        if ($role == null) {
            throw new \RuntimeException(sprintf('用户 %s 不存在', $params['role_name']));
        }
        if (isset($params['role_key'])) {
            $role->set('role_key', $params['role_key']);
        }

        if (isset($params['role_name'])) {
            $role->set('role_name', $params['role_name']);
        }

        if (isset($params['sort'])) {
            $role->set('sort', $params['sort']);
        }

        if (isset($params['data_scope'])) {
            $role->set('data_scope', $params['data_scope']);
        }

        if (isset($params['status'])) {
            $role->set('status', $params['status']);
        }

        if (!$role->getChangedData()) {
            throw new \RuntimeException('没有任何改变');
        }
    }

    /**
     * 通过id查询角色
     */
    public static function getId(int $role_id): SysRoleModel
    {
        $role = SysRoleModel::where('role_id', $role_id)->find();
        if ($role == null) {
            throw new \RuntimeException('角色不存在');
        }

        return $role;
    }

    /**
     * 通过id删除角色
     */
    public static function delId(int $role_id)
    {
        $role = SysRoleModel::where('role_id', $role_id)->find();
        if ($role == null) {
            throw new \RuntimeException('角色不存在');
        }

        if ($role->delete() == false) {
            throw new \RuntimeException('删除角色失败');
        }
    }

    /**
     * 角色名称是否存在
     */
    public static function existByKey(string $role_key): bool
    {
        $roleKey = SysRoleModel::where('role_key', $role_key)->find();
        return $roleKey ? true : false;
    }

    /**
     * 角色名称是否存在
     */
    public static function existByName(string $role_name): bool
    {
        $roleName = SysRoleModel::where('role_name', $role_name)->find();
        return $roleName ? true : false;
    }
}
