<?php

namespace library\jwt;

class JwtPayload
{

    /**
     * 签发者 (如: 赫尔斯科技)
     * 
     * @var string
     */
    public $iss;

    /**
     * 签发时间 (如: time())
     * 
     * @var int
     */
    public $iat;

    /**
     * 过期时间 (如: time() + 7200)
     * 
     * @var int
     */
    public $exp;

    /**
     * 生效时间 (如: time())
     *
     * @var int
     */
    public $nbf;

    /**
     * 面向用户 (如用户ID / 账户名)
     */
    public $sub;

    /**
     * 唯一标识 (如: md5(uniqid('JWT') . time()) )
     *
     * @var string
     */
    public $jti;
}
