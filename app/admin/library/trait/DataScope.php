<?php

namespace app\admin\library\trait;

use app\admin\enums\DataScopeType;
use app\admin\library\Auth;
use app\admin\logic\system\SysDeptLogic;
use think\facade\Db;

/**
 * @method \think\Db\Query dataScope(string $deptAlias = 'd', string $userAlias = 'u') 数据筛选
 * @author 余小波 <yuxiaobo64@gmail.com>
 * 📢 注意  在模型查询中, 必须把 dataScope() 放在连贯操作的最前面, 否则不生效!!!!
 */
trait DataScope
{
    protected $deptAlias = 'd';
    protected $userAlias = 'u';


    // 设置数据过滤
    protected function setDataScope(string $deptAlias = 'd', string $userAlias = 'u')
    {
        $this->deptAlias = $deptAlias;
        $this->userAlias = $userAlias;
    }


    public function __call($method, $args)
    {
        // 数据过滤魔术方法
        if ($method == 'dataScope') {
            // call setDataScope
            call_user_func_array([$this, 'setDataScope'], $args);
        }

        // call scopeDataScope
        return parent::__call($method, $args);
    }

    // 数据权限处理
    public function scopeDataScope(\think\Db\Query $query)
    {
        $auth = Auth::getInstance();

        $userId = $auth->getUserId();
        $deptId = $auth->getDeptId();
        $roleId = $auth->getRoleId();
        $dataScope = Db::name('sys_role')->where('role_id', $roleId)->value('data_scope');

        $deptKey = sprintf('%s.dept_id', $this->deptAlias);
        $userKey = sprintf('%s.user_id', $this->userAlias);

        $where = [];
        if ($dataScope == DataScopeType::ALL) {

            // 什么都不需要做 😄

        } else if ($dataScope == DataScopeType::DEPT) {

            $where[]  = [$deptKey, '=', $deptId];
        } else if ($dataScope == DataScopeType::DEPT_AND_CHILD) {

            $deptChild = SysDeptLogic::getChildIdsByDeptId($deptId, true);
            $where[]  = [$deptKey, 'IN', $deptChild];
        } else if ($dataScope == DataScopeType::SELF) {

            $where[]  = [$userKey, '=', $userId];
        } else {

            // 自定数据可以自己扩展哦 💐
            throw new \RuntimeException('角色数据异常');
        }

        $query->where($where);
    }
}
