<?php

declare(strict_types=1);

namespace app\admin\model\auth;

use app\admin\library\trait\DataScope;
use think\Model;

/**
 * 菜单模型
 */
class SysMenuModel extends Model
{
    use DataScope;

    protected $name = 'sys_menu';
}
