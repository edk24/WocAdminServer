<?php

namespace app\admin\enums;


enum DataScopeType: int
{
    /** 全部数据 */
    case ALL = 1;

    /** 自定数据 */
    case CUSTOM = 2;

    /** 本部门 */
    case DEPT = 3;

    /** 本部门及下级部门 */
    case DEPT_AND_CHILD = 4;

    /** 仅自己 */
    case SELF = 5;
}
