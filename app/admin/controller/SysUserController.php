<?php

namespace app\admin\controller;

use app\admin\logic\auth\SysUserLogic;
use app\admin\validate\auth\SysUserValidate;
use Exception;
use think\facade\Db;
use think\Request;

class SysUserController extends BaseController
{
    public function lists(Request $request)
    {
        $params = $request->param();
        return resp_data(SysUserLogic::lists($params));
    }

    public function create()
    {
        $params = (new SysUserValidate)->post()->goCheck('create');

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

    public function update()
    {
        $params = (new SysUserValidate)->post()->goCheck('edit');

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
     */
    public function changePwd()
    {
        $params = (new SysUserValidate)->post()->goCheck('changePwd');

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
        $id = intval($request->post('user_id'));

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
