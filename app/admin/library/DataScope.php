<?php

namespace app\admin\library;

use Attribute;

/**
 * 数据权限注解
 */
#[Attribute(Attribute::TARGET_ALL)]
class DataScope
{
    public ?string $userAlias;
    public ?string $deptAlias;

    public function __construct(?string $userAlias = null, ?string $deptAlias = null)
    {
        $this->userAlias = $userAlias;
        $this->deptAlias = $deptAlias;
    }
}
