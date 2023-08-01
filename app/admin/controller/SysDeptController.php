<?php

declare(strict_types=1);

namespace app\admin\controller;

use app\admin\logic\auth\SysDeptLogic;
use app\admin\validate\auth\SysDeptValidate;
use Exception;
use think\facade\Db;
use think\Request;

class SysDeptController extends BaseController
{

    public function lists()
    {
        $items = SysDeptLogic::listAllDept([]);

        return resp_data([
            'total' => count($items),
            'rows'  => $items
        ]);
    }

    public function getTree()
    {
        $items = SysDeptLogic::listAllDept([]);

        $pid = $this->auth->isSuperAdmin() ? 0 : SysDeptLogic::getParentId($this->auth->getDeptId());
        $treeDept = SysDeptLogic::buildDeptTree($items, $pid);

        return resp_data($treeDept);
    }


    public function create()
    {
        $params = (new SysDeptValidate())->post()->goCheck('create');

        Db::startTrans();
        try {
            $dept_id = SysDeptLogic::create($params);

            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            return resp_fail($e->getMessage());
        }

        return resp_success(['dept_id' => $dept_id], '操作成功');
    }

    public function update()
    {
        $params = (new SysDeptValidate())->post()->goCheck('update');

        Db::startTrans();
        try {
            SysDeptLogic::update($params);

            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            return resp_fail($e->getMessage());
        }

        return resp_success(null, '操作成功');
    }

    /**
     * 删除指定资源
     *
     * @return \think\Response
     */
    public function delete()
    {
        $params = (new SysDeptValidate())->post()->goCheck('del');
        $dept_id = intval($params['dept_id']);

        Db::startTrans();
        try {
            SysDeptLogic::delById($dept_id);
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
        }

        return resp_success(null, '删除成功');
    }



    public function get()
    {
        $params = (new SysDeptValidate())->get()->goCheck('get');
        $dept_id = intval($params['dept_id']);
        return resp_data(SysDeptLogic::getById($dept_id));
    }
}
