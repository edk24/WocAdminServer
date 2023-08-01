<?php

declare(strict_types=1);

namespace app\admin\controller;

use app\admin\logic\auth\SysRoleLogic;
use app\admin\validate\auth\SysRoleValidate;
use Exception;
use think\facade\Db;
use think\Request;

class SysRoleController
{

    public function lists(Request $request)
    {
        $params = $request->param();
        return resp_data(SysRoleLogic::lists($params));
    }


    public function create()
    {
        $params = (new SysRoleValidate)->post()->goCheck('create');

        Db::startTrans();
        try {
            $rid = SysRoleLogic::create($params);

            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            return resp_fail($e->getMessage());
        }

        return resp_success(['role_id' => $rid], '操作成功');
    }


    public function update()
    {
        $params = (new SysRoleValidate)->post()->goCheck('edit');

        db::startTrans();
        try {
            SysRoleLogic::update($params);
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            return resp_fail($e->getMessage());
        }

        return resp_success(null, '操作成功');
    }


    /**
     * 通过ID查询角色信息.
     */
    public function get(Request $request)
    {
        $id = intval($request->get('role_id'));
        try {
            $role = SysRoleLogic::getId($id);
        } catch (Exception $e) {
            return resp_fail($e->getMessage());
        }

        return resp_success($role, '查询成功');
    }

    /**
     * 删除指定资源
     *
     * @return \think\Response
     */
    public function delete(Request $request)
    {
        $id = intval($request->post('role_id'));

        Db::startTrans();

        try {
            SysRoleLogic::delId($id);
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            return resp_fail($e->getMessage());
        }

        return resp_success(null, '操作成功');
    }
}
