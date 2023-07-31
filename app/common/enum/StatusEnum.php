<?php

namespace app\common\enum;


/**
 * 状态枚举
 */
enum StatusEnum: string
{
    /** 正常 */
    case NORMAL = 'normal';

    /** 禁用 */
    case DISABLE = 'disable';


    public function text(): string
    {
        return match ($this) {
            self::NORMAL => '正常',
            self::DISABLE => '禁用',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::NORMAL => 'success',
            self::DISABLE => 'danger',
        };
    }
}
