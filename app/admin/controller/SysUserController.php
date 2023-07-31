<?php

namespace app\admin\controller;

use app\admin\logic\system\SysUserLogic;
use app\admin\validate\SysUserValidate;
use Exception;
use RuntimeException;
use think\facade\Db;
use think\Request;

class SysUserController extends BaseController
{
    public function lists(Request $request)
    {
        $params = $request->param();
        return resp_data(SysUserLogic::lists($params));
    }

    public function create(Request $request)
    {
        $params = $request->post();

        $validate = new SysUserValidate();
        if ($validate->scene('create')->check($params) == false) {
            return resp_fail($validate->getError());
        }

        Db::startTrans();
        try {
            $uid = SysUserLogic::create($params);

            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            return resp_fail($e->getMessage());
        }

        return resp_success(['uid' => $uid], '操作成功');
    }

    public function update(Request $request)
    {
        $params = $request->post();

        $validate = new SysUserValidate();
        if ($validate->scene('edit')->check($params) == false) {
            return resp_fail($validate->getError());
        }

        Db::startTrans();
        try {
            SysUserLogic::update($params);

            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            return resp_fail($e->getMessage());
        }

        return resp_success(null, '操作成功');
    }


    /**
     * 修改密码
     *
     * @param Request $request
     */
    public function changePwd(Request $request)
    {
        $params = $request->post();

        $validate = new SysUserValidate();
        if ($validate->scene('changePwd')->check($params) == false) {
            return resp_fail($validate->getError());
        }

        Db::startTrans();
        try {
            SysUserLogic::update($params);

            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            return resp_fail($e->getMessage());
        }

        return resp_success(null, '操作成功');
    }

    /**
     * 通过ID查询用户信息
     *
     * @param Request $request
     */
    public function get(Request $request)
    {
        $id = intval($request->get('id'));
        try {
            $user = SysUserLogic::getById($id);
        } catch (Exception $e) {
            return resp_fail($e->getMessage());
        }

        return resp_success($user, '查询成功');
    }

    /**
     * 删除用户
     *
     * @param Request $request
     */
    public function delete(Request $request)
    {
        $id = intval($request->post('id'));

        Db::startTrans();
        try {
            SysUserLogic::delById($id);
            Db::commit();
        } catch (Exception $e) {
            DB::rollback();
            return resp_fail($e->getMessage());
        }

        return resp_success(null, '操作成功');
    }
}
