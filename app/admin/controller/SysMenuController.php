<?php

declare(strict_types=1);

namespace app\admin\controller;

use app\admin\logic\auth\SysMenuLogic;
use app\admin\validate\auth\SysMenuValidate;
use Exception;
use library\Tree;
use think\facade\Db;

/**
 * 菜单
 */
class SysMenuController extends BaseController
{
    public function getTree()
    {
        $items = SysMenuLogic::listAll([]);
        // dd($items);

        $treeMenu = Tree::getTreeArray($items, 0, 'menu_id', 'parent_id');

        return resp_data($treeMenu);
    }

    public function create()
    {
        $params = (new SysMenuValidate)->post()->goCheck('create');

        Db::startTrans();
        try {
            $menu_id = SysMenuLogic::create($params);

            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            return resp_fail($e->getMessage());
        }

        return resp_success(['menu_id' => $menu_id], '操作成功');
    }

    public function update()
    {
        $params = (new SysMenuValidate)->post()->goCheck('edit');

        Db::startTrans();
        try {
            SysMenuLogic::update($params);

            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            return resp_fail($e->getMessage());
        }

        return resp_success(null, '操作成功');
    }

    public function delete()
    {
        $params = (new SysMenuValidate())->post()->goCheck('delete');
        $dept_id = intval($params['menu_id']);

        Db::startTrans();
        try {
            SysMenuLogic::delById($dept_id);
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
        }

        return resp_success(null, '删除成功');
    }

    public function get()
    {
        $params = (new SysMenuValidate())->get()->goCheck('get');
        $dept_id = intval($params['menu_id']);
        try {
            $item = SysMenuLogic::getById($dept_id);
        } catch (Exception $e) {
            return resp_fail($e->getMessage());
        }
        return resp_data($item);
    }
}
