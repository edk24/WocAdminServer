<?php

declare(strict_types=1);

namespace app\admin\library;

use app\admin\exception\LoginException;
use app\admin\logic\auth\SysMenuLogic;
use app\admin\logic\auth\SysUserLogic;
use app\admin\model\auth\SysUserModel;
use library\jwt\Jwt;
use library\jwt\JwtException;
use library\jwt\JwtPayload;
use library\Tree;
use RuntimeException;
use think\facade\Db;

/**
 * 鉴权
 */
class Auth
{

    /** 单例 */
    private static ?Auth $_instance = null;

    /** 当前登录用户模型 */
    private ?SysUserModel $user = null;

    /** token */
    private string $token;

    /** Jwt 加密的 Key, 建议随机生成使用 */
    const JwtKey = '99b51ba514e19c5b387608a9873e70b5';

    /**
     * 获取实例
     *
     * @return Auth
     */
    public static function getInstance(): Auth
    {
        if (!self::$_instance) {
            self::$_instance = new Auth();
        }

        return self::$_instance;
    }


    /**
     * 验证 Token
     *
     * @param string $token
     * @return void
     * @throws JwtException
     */
    public function verifyToken(string $token)
    {
        $this->token = $token;

        $jwt = new Jwt(self::JwtKey);
        $result = $jwt->verifyToken($this->token);

        $account = $result['sub'];
        $this->user = (new SysUserModel())->with(['dept'])->where('account', $account)->find();
        if ($this->user == null) {
            throw new JwtException('登录已失效, 请重新登录');
        }
    }


    /**
     * 登录
     *
     * @param string $account
     * @param string $password
     * @throws LoginException
     * @return string
     */
    public function login(string $account, string $password): string
    {
        $user = SysUserModel::with(['dept'])->where('account', $account)->find();

        if ($user == null) {
            throw new LoginException(sprintf('用户 %s 不存在', $account));
        }

        if (md5($password . $user->salt) != $user->password) {
            throw new LoginException('密码不正确');
        }

        // 更新信息
        $user->set('last_login_ip', $_SERVER['REMOTE_ADDR']);
        $user->set('last_login_time', time());
        $user->save();

        $this->user = $user;

        // 生成 Jwt Token
        $jwt = new Jwt(self::JwtKey);
        $payload = new JwtPayload();
        $payload->iat = time();
        $payload->exp = time() + 7200;
        $payload->nbf = time();
        $payload->iss = '赫尔斯科技'; // 签发者
        $payload->sub = $user->account;
        $payload->jti = md5(uniqid('JWT') . time());
        $token = $jwt->getToken($payload);

        $this->token = $token;

        return $token;
    }


    /**
     * 获取登录用户模型
     */
    public function getUserModel()
    {
        return $this->user;
    }

    /**
     * 是否登录
     *
     * @return boolean
     */
    public function isLogin()
    {
        return $this->user ? true : false;
    }

    /**
     * 获取当前登录用户信息
     *
     * @return array
     */
    public function getUserInfo(): array
    {
        // routers
        $menuList = SysMenuLogic::lists([], $this->getUserId());
        $menuTree = Tree::getTreeArray($menuList, 0, 'menu_id', 'parent_id');
        $routers = SysMenuLogic::buildRouters($menuTree);

        // permission
        $perms = SysMenuLogic::listPermsByUserId($this->getUserId());

        return [
            'user' => [
                'nickname'              => $this->user->nickname,
                'avatar'                => $this->user->avatar ?? letter_avatar($this->user->nickname ?? $this->user->account),
                'user_id'               => $this->user->user_id,
                'account'               => $this->user->account,
                'last_login_time'       => $this->user->last_login_time,
                'token'                 => $this->token,
                'dept_id'               => $this->user->dept_id,
                'dept_name'             => $this->user->dept->dept_name
            ],
            'routers'       => $routers,
            'permissions'   => $perms
        ];
    }


    /**
     * 获取当前用户ID
     *
     * @return integer
     */
    public function getUserId(): int
    {
        return intval($this->user->user_id);
    }


    /**
     * 获取当前用户角色ID
     *
     * @return integer
     */
    public function getRoleId(): int
    {
        if ($this->user == null) {
            throw new RuntimeException('用户数据异常');
        }

        $userId = $this->getUserId();

        $roleId = Db::name('sys_user_role')->where('user_id', $userId)->value('role_id');
        if ($roleId == null) {
            throw new RuntimeException('用户数据异常');
        }

        return intval($roleId);
    }

    /**
     * 获取部门ID
     *
     * @return integer
     */
    public function getDeptId(): int
    {
        return intval($this->getUserModel()->getData('dept_id'));
    }

    /**
     * 是否为超级管理员
     *
     * @return boolean
     */
    public function isSuperAdmin(): bool
    {
        return SysUserLogic::isSuperAdmin($this->getUserId());
    }
}
