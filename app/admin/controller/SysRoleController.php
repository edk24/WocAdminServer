<?php

declare(strict_types=1);

namespace app\admin\controller;

use app\admin\logic\system\SysRoleLogic;
use app\admin\validate\SysRoleValidate;
use Exception;
use think\facade\Db;
use think\Request;

class SysRoleController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(Request $request)
    {
        $params = $request->param();
        return resp_data(SysRoleLogic::lists($params));
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create(Request $request)
    {
        $params = $request->post();
        $validate = new SysRoleValidate();
        if ($validate->scene('create')->check($params) == false) {
            return resp_fail($validate->getError());
        }

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


    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request)
    {
        $params = $request->post();
        $validate = new SysRoleValidate();
        if ($validate->scene('edit')->check($params) == false) {
            return resp_fail($validate->getError());
        }

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
     * 通过ID查询用户信息.
     *
     */
    public function get(Request $request)
    {
        $id = intval($request->get('role_id'));
        try {
            $role = SysRoleLogic::getId($id);
        } catch (Exception $e) {
            return resp_fail($e->getMessage());
        }

        return resp_success(null, '查询成功');
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
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
