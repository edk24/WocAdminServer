<?php

namespace library;

use think\facade\Config;

/**
 * 树
 */
class Tree
{
    /**
     * 获取树结构
     *
     * @param array $arr
     * @param integer $pid
     * @param string $pkid
     * @param string $pidname
     * @return array
     */
    public static function getTreeArray(array $arr = [], int $pid = 0, string $pkid = 'id', string $pidname = 'parent_id'): array
    {
        $tree = array();
        foreach ($arr as $row) {
            if ($row[$pidname] == $pid) {
                $tmp = self::getTreeArray($arr, $row[$pkid], $pkid, $pidname);
                if ($tmp) {
                    $row['children'] = $tmp;
                }
                $tree[] = $row;
            }
        }
        return $tree;
    }
}
