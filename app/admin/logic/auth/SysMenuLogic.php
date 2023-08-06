<?php

namespace app\admin\logic\auth;

use app\admin\library\Auth;
use app\admin\model\auth\SysMenuModel;
use app\common\enums\StatusType;
use library\Tree;
use library\Url;
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

        // $join = [
        //     ['sys_role_menu rm', 'm.menu_id = rm.menu_id', 'LEFT'],
        //     ['sys_user_role ur', 'ur.role_id = rm.role_id', 'LEFT'],
        //     ['admin u', 'u.user_id = ur.user_id', 'LEFT'],
        //     ['sys_role ro', 'ro.role_id = ur.role_id', 'LEFT'],
        // ];
        $where['ur.user_id'] = $userId;

        // field
        $field = str_replace('menu_id', 'm.menu_id', self::$field);
        $field = str_replace('sort', 'm.sort', $field);
        $field = str_replace('status', 'm.status', $field);
        $field = str_replace('create_time', 'm.create_time', $field);


        $list = (new SysMenuModel())->dataScope('u', 'u')
            ->alias('m')
            ->leftJoin(['sys_role_menu' => 'rm'], 'm.menu_id = rm.menu_id')
            ->leftJoin(['sys_user_role' => 'ur'], 'ur.role_id = rm.role_id')
            ->leftJoin(['sys_user' => 'u'], 'u.user_id = ur.user_id')
            ->leftJoin(['sys_role' => 'ro'], 'ro.role_id = ur.role_id')
            ->where($where)
            ->order('m.parent_id, m.sort')
            ->field($field)
            ->select();

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

        // $join = [
        //     ['sys_role_menu rm', 'rm.menu_id = m.menu_id', 'left'],
        //     ['sys_user_role ur', 'ur.role_id = rm.role_id', 'left'],
        //     ['sys_role r', 'r.role_id = ur.role_id', 'left'],
        // ];

        $where['m.status'] = StatusType::NORMAL->value;
        $where['r.status'] = StatusType::NORMAL->value;

        if (!SysUserLogic::isSuperAdmin($userId)) {
            $where['ur.user_id'] = $userId;
        }

        $perms = SysMenuModel::alias('m')
            ->leftJoin(['sys_role_menu' => 'rm'], 'rm.menu_id = m.menu_id')
            ->leftJoin(['sys_user_role' => 'ur'], 'ur.role_id = rm.role_id')
            ->leftJoin(['sys_role' => 'r'], 'r.role_id = ur.role_id')->where($where)->column('distinct m.perms');

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








    // router
    // ================================

    public static function buildRouters(array $menuTree)
    {
        $routerArr = [];
        foreach ($menuTree as $menu) {
            $meta = array(
                'title'             => $menu['menu_name'],
                'icon'              => $menu['icon'],
                'noCache'           => $menu['is_cache'] === 1,
            );

            if (Url::isHttp($menu['path'] ?? '')) {
                $meta['link']       = $menu['path'];
            }

            $router = array(
                'hidden'            => $menu['visible'] === 0,
                'name'              => self::getRouteName($menu),
                'path'              => self::getRouterPath($menu),
                'component'         => self::getComponent($menu),
                'query'             => $menu['query'],
                'meta'              => $meta,
            );

            if (isset($menu['children']) && count($menu['children']) >= 0 && $menu['menu_type'] == 'D') { // 目录 && 有子级
                $router['always_show'] = true;
                // $router['redirect'] = 'noRedirect';

                if (count($menu['children']) == 1) { // 只有一个下级, 只展示下级
                    unset($router['meta']);
                    $router['always_show'] = false;
                }

                $router['children'] = self::buildRouters($menu['children']);
            } else if (self::isMenuFrame($menu)) { // 非外链菜单
                $router['meta'] = null;
                $router['children'] = self::buildRouters($menu);
            } else if ($menu['parent_id'] == 0 && self::isInnerLink($menu)) { // 内链组件
                $router['meta'] = ['title' => $menu['menu_name'], 'icon' => $menu['icon']];
                $router['path'] = self::innerLinkReplaceEach($menu['path']);
                // $router['component'] = '固定';





                // TODO 摆着, 内联组件
                // List<RouterVo> childrenList = new ArrayList<RouterVo>();
                // RouterVo children = new RouterVo();
                // String routerPath = innerLinkReplaceEach(menu.getPath());
                // children.setPath(routerPath);
                // children.setComponent(UserConstants.INNER_LINK);
                // children.setName(StringUtils.capitalize(routerPath));
                // children.setMeta(new MetaVo(menu.getMenuName(), menu.getIcon(), menu.getPath()));
                // childrenList.add(children);
                // router.setChildren(childrenList);
            }
            array_push($routerArr, $router);
        }
        return $routerArr;
    }

    protected static function getRouteName($menu): string
    {
        $routerName = ucfirst($menu['path']);
        // 非外链并且是一级目录（类型为目录）
        if (self::isMenuFrame($menu)) {
            $routerName = "";
        }
        return $routerName;
    }

    /**
     * 获取路由地址
     */
    protected static function getRouterPath($menu): string
    {
        $routerPath = $menu['path'];
        // 内链打开外网方式
        if ($menu['parent_id'] == 0 && self::isInnerLink($menu)) {
            $routerPath = self::innerLinkReplaceEach($routerPath);
        }
        // 非外链并且是一级目录（类型为目录）
        if ($menu['parent_id'] == 0 && $menu['menu_type'] == 'D' && $menu['is_frame'] == 0) {
            $routerPath = "/" . $menu['path'];
        }
        // 非外链并且是一级目录（类型为菜单）
        else if (self::isMenuFrame($menu)) {
            $routerPath = "/";
        }
        return $routerPath;
    }


    /**
     * 获取组件信息
     */
    protected static function getComponent($menu)
    {
        $component = 'Layout';
        if (!empty($menu['component']) && !self::isMenuFrame($menu)) {
            $component = $menu['component'];
        } else if ($menu['component'] == '' && $menu['parent_id'] == 0 && self::isInnerLink($menu)) {
            $component = 'InnerLink';
        } else if ($menu['component'] == '' && self::isParentView($menu)) {
            $component = 'ParentView';
        }
        return $component;
    }



    /**
     * 是否为parent_view组件
     */
    protected static function isParentView($menu)
    {
        return $menu['parent_id'] != 0 && $menu['menu_type'] == 'D';
    }

    /**
     * 是否为菜单内部跳转
     */
    protected static function isMenuFrame(array $menu): bool
    {
        return $menu['parent_id'] == 0 && $menu['menu_type'] == 'M' && $menu['is_frame'] == 0;
    }


    /**
     * 是否为内链组件
     *
     * @param array $menu
     * @return boolean
     */
    protected static function isInnerLink(array $menu): bool
    {
        return $menu['is_frame'] == 1 && Url::isHttp($menu['path']);
    }

    /**
     * 内链域名特殊字符替换
     * 
     * @return
     */
    protected static function innerLinkReplaceEach($path)
    {
        $path = str_replace('http', '', $path);
        $path = str_replace('https', '', $path);
        $path = str_replace('www', '', $path);
        $path = str_replace('.', '/', $path);
        return $path;
    }
}
