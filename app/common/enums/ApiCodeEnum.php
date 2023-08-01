<?php

namespace app\common\enums;

/**
 * 接口 code 枚举
 */
enum ApiCodeEnum: int
{
    /** 业务正常 */
    case OK = 0;

    /** 业务错误 */
    case FAIL = 1;

    /** 需要登录 */
    case NEED_LOGIN = 4001;

    /** token 失效 */
    case TOKEN_INVALID = 4010;

    /** token 过期 */
    case TOKEN_EXPIRED = 4011;


    public function message(): string
    {
        return match ($this) {
            self::OK => 'Success',
            self::FAIL => '操作失败',
            self::NEED_LOGIN => '请先登录',
            self::TOKEN_EXPIRED => '登录已过期, 请重新登录',
            self::TOKEN_INVALID => 'Token 无效',
        };
    }
}
