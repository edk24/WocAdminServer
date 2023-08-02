<?php

namespace app\admin\logic\auth;

use app\admin\model\auth\SysMenuModel;
use app\common\enums\StatusType;
use RuntimeException;
use think\facade\Db;

class SysMenuLogic
{
    protected static string $field = "menu_id, menu_name, parent_id, sort, path, component, `query`, is_frame, is_cache, menu_type, visible, status, ifnull(perms,'') as perms, icon, create_time";


    /**
     * 查询系统菜单列表
     *
     * @param integer $userId
     * @return array
     */
    public static function lists(array $params, int $userId): array
    {
        if (SysUserLogic::isSuperAdmin($userId)) {
            return self::listAll($params);
        } else {
            return self::listByUserId($params, $userId);
        }
    }

    /**
     * 查询全部菜单列表
     *
     * @param array $params
     * @return array
     */
    public static function listAll(array $params): array
    {
        $where = [];

        if (isset($params['status'])) {
            $where[] = ['m.status', '=', $params['status']];
        }

        if (isset($params['visible'])) {
            $where[] = ['m.visible', '=', $params['visible']];
        }

        if (isset($params['menu_name'])) {
            $where[] = ['m.menu_name', 'LIKE', implode('', ['%', $params['menu_name'], '%'])];
        }

        $list = (new SysMenuModel)->alias('m')->dataScope('d')->where($where)->order('m.parent_id, m.sort')->field(self::$field)->select();

        return $list->toArray();
    }

    /**
     * 通过用户ID查看菜单列表
     *
     * @param array $params
     * @param integer $userId
     * @return array
     */
    public static function listByUserId(array $params, int $userId): array
    {
        $where = [];

        if (isset($params['status'])) {
            $where[] = ['status', '=', $params['status']];
        }

        if (isset($params['visible'])) {
            $where[] = ['visible', '=', $params['visible']];
        }

        if (isset($params['menu_name'])) {
            $where[] = ['menu_name', 'LIKE', implode('', ['%', $params['menu_name'], '%'])];
        }

        $join = [
            ['sys_role_menu rm', 'm.menu_id = rm.menu_id', 'LEFT'],
            ['sys_user_role ur', 'ur.role_id = rm.role_id', 'LEFT'],
            ['admin u', 'u.user_id = ur.user_id', 'LEFT'],
            ['sys_role ro', 'ro.role_id = ur.role_id', 'LEFT'],
        ];
        $where['ur.user_id'] = $userId;

        // field
        $field = str_replace('menu_id', 'm.menu_id', $this->field);
        $field = str_replace('sort', 'm.sort', $field);
        $field = str_replace('status', 'm.status', $field);
        $field = str_replace('create_time', 'm.create_time', $field);


        $list = (new SysMenuModel())->dataScope('u', 'u')->alias('m')->join($join)->where($where)->order('m.parent_id, m.sort')->field($field)->select();

        return $list->toArray();
    }


    /**
     * 通过UserId查询菜单权限数组
     *
     * @param integer $userId
     * @return array
     */
    public static function listPermsByUserId(int $userId): array
    {
        // select distinct m.perms
        // from sys_menu m
        // 	 left join sys_role_menu rm on m.menu_id = rm.menu_id
        // 	 left join sys_user_role ur on rm.role_id = ur.role_id
        // 	 left join sys_role r on r.role_id = ur.role_id
        // where m.status = '0' and r.status = '0' and ur.user_id = #{userId}

        $join = [
            ['sys_role_menu rm', 'rm.menu_id = m.menu_id', 'left'],
            ['sys_user_role ur', 'ur.role_id = rm.role_id', 'left'],
            ['sys_role r', 'r.role_id = ur.role_id', 'left'],
        ];

        $where['m.status'] = StatusType::NORMAL->value;
        $where['r.status'] = StatusType::NORMAL->value;
        $where['ur.user_id'] = $userId;
        $perms = SysMenuModel::alias('m')->join($join)->where($where)->column('distinct m.perms');

        return $perms;
    }


    /**
     * 通过RoleId查询权限数组
     *
     * @param integer $roleId
     * @return array
     */
    public function listPermsByRoleId(int $roleId): array
    {
        // select distinct m.perms
        // from sys_menu m
        // 	 left join sys_role_menu rm on m.menu_id = rm.menu_id
        // where m.status = '0' and rm.role_id = #{roleId}

        $join = [
            ['sys_role_menu rm', 'rm.menu_id = m.menu_id', 'left'],
        ];

        $where['m.status'] = StatusType::NORMAL->value;
        $where['rm.role_id'] = $roleId;
        $perms = SysMenuModel::alias('m')->join($join)->where($where)->column('distinct m.perms');

        return $perms;
    }


    /**
     * 创建菜单
     *
     * @param array $params
     * @return integer
     */
    public static function create(array $params): int
    {
        $menu = new SysMenuModel();
        $menu->set('menu_name', $params['menu_name']);
        $menu->set('parent_id', $params['parent_id']);
        $menu->set('sort', $params['sort'] ?? 999);
        $menu->set('path', $params['path']);
        $menu->set('component', $params['component']);
        $menu->set('query', isset($params['query']) ? $params['query'] : '');
        $menu->set('is_frame', $params['is_frame']);
        $menu->set('is_cache', $params['is_cache']);
        $menu->set('menu_type', $params['menu_type']);
        $menu->set('visible', $params['visible']);
        $menu->set('perms', $params['perms']);
        $menu->set('icon', $params['icon']);
        $menu->set('status', $params['status'] ?? StatusType::NORMAL->value);
        $success = $menu->save();
        if (!$success) {
            throw new RuntimeException('创建菜单失败, 请稍后再试~');
        }

        return intval($menu->getData('menu_id') ?? -1);
    }

    /**
     * 修改菜单
     *
     * @param array $params
     */
    public static function update(array $params)
    {
        $menu = SysMenuModel::where('menu_id', $params['menu_id'])->lock(true)->find();
        if ($menu == null) {
            throw new RuntimeException('该菜单不存在!');
        }
        $menu->set('menu_name', $params['menu_name']);
        $menu->set('parent_id', $params['parent_id']);
        $menu->set('sort', $params['sort'] ?? 999);
        $menu->set('path', $params['path']);
        $menu->set('component', $params['component']);
        $menu->set('query', isset($params['query']) ? $params['query'] : '');
        $menu->set('is_frame', $params['is_frame']);
        $menu->set('is_cache', $params['is_cache']);
        $menu->set('menu_type', $params['menu_type']);
        $menu->set('visible', $params['visible']);
        $menu->set('perms', $params['perms']);
        $menu->set('icon', $params['icon']);
        $menu->set('status', $params['status'] ?? StatusType::NORMAL->value);
        $success = $menu->save();
        if (!$success) {
            throw new RuntimeException('修改菜单失败, 请稍后再试~');
        }
    }

    /**
     * 通过ID查询菜单
     *
     * @param integer $menuId
     */
    public static function getById(int $menuId)
    {
        $menu = SysMenuModel::where('menu_id', $menuId)->find();
        if ($menu == null) {
            throw new RuntimeException('该菜单不存在!');
        }
        return $menu;
    }

    /**
     * 通过ID删除菜单
     *
     * @param integer $menuId
     */
    public static function delById(int $menuId)
    {
        $menu = SysMenuModel::where('menu_id', $menuId)->find();
        if ($menu == null) {
            throw new RuntimeException('该菜单不存在!');
        }

        $success = $menu->delete();
        if ($success == false) {
            throw new RuntimeException('删除失败, 请稍后再试~');
        }
    }
}
