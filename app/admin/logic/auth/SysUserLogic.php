<?php

declare(strict_types=1);

namespace app\admin\logic\auth;

use app\admin\model\auth\SysUserModel;
use app\common\enums\StatusType;
use RuntimeException;

/**
 * 管理员逻辑
 */
class SysUserLogic
{
    // 查询字段
    protected static array $field = [
        'user_id',
        'account',
        'nickname',
        'avatar',
        'last_login_ip',
        'last_login_time',
        'create_time',
        'update_time',
        'dept_id'
    ];


    /**
     * 查询用户列表
     *
     * @param array $params
     */
    public static function lists(array $params): array
    {
        $where = array();

        // if (isset($params['mobile'])) {
        //     $where[] = ['mobile', 'LIKE', sprintf('%%s%', $params['mobile'])];
        // }

        if (isset($params['account'])) {
            $where[] = ['account', 'LIKE', "%{$params['account']}%"];
        }

        if (isset($params['nickname'])) {
            $where[] = ['nickname', 'LIKE', "%{$params['nickname']}%"];
        }

        $limit = intval($params['limit'] ?? 10);
        $result = SysUserModel::with(['dept'])->dataScope('sys_user_model', 'sys_user_model')->where($where)->field(self::$field)->order('id desc')->paginate($limit);

        $rows = $result->items();
        foreach ($rows as &$row) {
            $row->append(['deptName']);
        }

        return [
            'count'     => $result->total(),
            'rows'      => $rows
        ];
    }

    /**
     * 创建用户
     *
     * @param array $params
     * @return integer
     * @throws RuntimeException
     */
    public static function create(array $params): int
    {
        if (self::existByAccount($params['account'])) {
            throw new RuntimeException(sprintf('用户 %s 已存在, 请不要重复添加', $params['account']));
        }

        $salt = substr(uniqid(), 0, 8);

        $user = new SysUserModel();
        $user->set('account', $params['account']);
        $user->set('nickname', $params['nickname']);
        $user->set('password', md5($params['password'] . $salt));
        $user->set('salt', $salt);
        $user->set('dept_id', $params['dept_id']);
        $user->set('status', $params['status'] ?? StatusType::NORMAL->value);
        $success = $user->save();
        if (!$success) {
            throw new RuntimeException('创建用户失败, 请稍后再试~');
        }

        return intval($user->user_id ?? -1);
    }


    /**
     * 修改用户资料
     *
     * @param array $params
     * @throws RuntimeException
     */
    public static function update(array $params)
    {
        $user = SysUserModel::where('account', $params['account'])->find();
        if ($user == null) {
            throw new RuntimeException(sprintf('用户 %s 不存在', $params['account']));
        }

        if (isset($params['dept_id'])) {
            $user->set('dept_id', $params['dept_id']);
        }

        if (isset($params['status'])) {
            $user->set('status', $params['status']);
        }

        if (isset($params['password'])) {
            $salt = substr(uniqid(), 0, 8);
            $user->set('password', md5($params['password'] . $salt));
            $user->set('salt', $salt);
        }

        if (isset($params['nickname'])) {
            $user->set('nickname', $params['nickname']);
        }

        if (isset($params['avatar'])) {
            $user->set('avatar', $params['avatar']);
        }

        if (!$user->getChangedData()) {
            throw new RuntimeException('没有任何改变~');
        }

        $success = $user->save();
        if (!$success) {
            throw new RuntimeException('创建用户失败, 请稍后再试~');
        }
    }


    /**
     * 通过 ID 查询用户
     *
     * @param integer $id
     * @throws RuntimeException
     */
    public static function getById(int $id): SysUserModel
    {
        $user = SysUserModel::with(['dept'])->dataScope('sys_user_model', 'sys_user_model')->where('user_id', $id)->find();
        if ($user == null) {
            throw new RuntimeException('用户不存在');
        }
        return $user;
    }


    /**
     * 通过ID删除用户
     *
     * @param integer $id
     * @throws RuntimeException
     */
    public static function delById(int $id)
    {
        $user = SysUserModel::where('user_id', $id)->find();
        if ($user == null) {
            throw new RuntimeException('用户不存在');
        }

        if ($user->delete() == false) {
            throw new RuntimeException('删除用户失败!');
        }
    }

    /**
     * 用户是否存在
     *
     * @param string $account
     * @return boolean
     */
    public static function existByAccount(string $account): bool
    {
        $user = SysUserModel::where('account', $account)->find();
        return $user ? true : false;
    }


    /**
     * 是否为超级管理员
     *
     * @param integer $userId
     * @return boolean
     */
    public static function isSuperAdmin(int $userId): bool
    {
        // TODO 改为通过角色role_key == admin 判断
        return $userId === 1;
    }
}
