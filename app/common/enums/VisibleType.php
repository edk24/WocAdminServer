<?php

namespace app\common\enums;


/**
 * 显示枚举
 */
enum VisibleType: string
{
    /** 显示 */
    case VISIBLE = 'visible';

    /** 隐藏 */
    case HIDDEN = 'hidden';


    public function text(): string
    {
        return match ($this) {
            self::VISIBLE => '显示',
            self::HIDDEN => '隐藏',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::VISIBLE => 'primary',
            self::HIDDEN => 'default',
        };
    }
}
