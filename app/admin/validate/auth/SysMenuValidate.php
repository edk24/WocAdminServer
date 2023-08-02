<?php

declare(strict_types=1);

namespace app\admin\validate\auth;

use app\common\validate\BaseValidate;

class SysMenuValidate extends BaseValidate
{

    protected $rule = [
        'menu_id'               => 'require|number',
        'menu_name'             => 'require',
        'parent_id'             => 'require|number',
        'menu_type'             => 'require|in:D,M,P',
        'path'                  => 'requireCallback:isRouter',
        'component'             => 'requireCallback:isRouter',
        'is_frame'              => 'require|number|in:0,1',
        'is_cache'              => 'require|number|in:0,1',
        'status'                => 'require|in:normal,disable',
        'visible'               => 'require|in:visible,hidden',
        'perms'                 => 'requireIf:menu_type,P',
        'icon'                  => 'requireCallback:isRouter',
        'sort'                  => 'number|max:999',
    ];

    protected $field = [
        'menu_id'           => '菜单ID',
        'menu_name'         => '菜单名称',
        'parent_id'         => '上级菜单',
        'menu_type'         => '菜单类型',
        'path'              => '路由地址',
        'component'         => '组件路径',
        'is_frame'          => '是否内嵌外链',
        'is_cache'          => '是否缓存页面',
        'status'            => '状态',
        'visible'           => '显示',
        'perms'             => '权限标识',
        'icon'              => '菜单图标',
        'sort'              => '排序'
    ];

    protected $scene = [
        'create'        => ['menu_name', 'parent_id', 'menu_type', 'path', 'component', 'is_frame', 'is_cache', 'status', 'visible', 'perms', 'icon', 'sort'],
        'edit'          => ['menu_id', 'menu_name', 'parent_id', 'menu_type', 'path', 'component', 'is_frame', 'is_cache', 'status', 'visible', 'perms', 'icon', 'sort'],
        'get'           => ['menu_id'],
        'delete'        => ['menu_id'],
    ];


    // 验证是否为路由
    protected function isRouter(mixed $value, array $data)
    {
        if ($data['menu_type'] == 'D' || $data['menu_type'] == 'M') {
            return true;
        }

        return false;
    }
}
