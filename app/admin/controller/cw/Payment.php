<?php

namespace app\admin\controller\cw;

use app\admin\model\CwSupplier;
use app\common\controller\AdminController;
use EasyAdmin\annotation\ControllerAnnotation;
use EasyAdmin\annotation\NodeAnotation;
use think\App;
use think\facade\Db;

/**
 * @ControllerAnnotation(title="cw_payment")
 */
class Payment extends AdminController
{

    use \app\admin\traits\Curd;

    public function __construct(App $app)
    {
        parent::__construct($app);

        $this->model = new \app\admin\model\CwPayment();
        
    }

    /**
     * @NodeAnotation(title="列表")
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            if (input('selectFields')) {
                return $this->selectList();
            }

            list($page, $limit, $where) = $this->buildTableParames();
            $count = $this->model
                ->where($where)
                ->count();

            $sort = $this->sort;
            if( isset($_GET['field']) ){
                $sort = [
                    $_GET['field'] => $_GET['order']
                ];
            }
            $list = $this->model
                ->where($where)
                ->page($page, $limit)
                ->order($sort)
                ->select();

            // 需要对数据进行汇总
            $totals = $this->model->where($where)->field('sum(fee) as fee')->select()->toArray();

            $data = [
                'code'  => 0,
                'msg'   => '',
                'count' => $count,
                'data'  => $list,
                'totalRow' => [
                    'id' => '汇总：',
                    'fee' => sprintf('%0.2f', $totals[0]['fee']),
                ]
            ];
            return json($data);
        }

        // get options
        $suppliers = $this->_getCol('supplier_name');
        $accounts = $this->_getCol('account');
        $projects = $this->_getCol('project');

        $this->assign('suppliers', $suppliers);
        $this->assign('accounts', $accounts);
        $this->assign('projects', $projects);
        return $this->fetch();
    }

    /**
     * @NodeAnotation(title="按客户汇总")
     */
    public function bysupplier()
    {
        $tbname = 'ea_cw_payment';
        if ($this->request->isAjax()) {
            if (input('selectFields')) {
                return $this->selectList();
            }

            list($page, $limit, $where) = $this->buildTableParames();
            // $count = $this->model
            //     ->where($where)
            //     ->count();
            $count = Db::table($tbname)->where($where)->field('supplier_name')->group('supplier_name')->count();

            $sort = ['fee'=>'desc'];
            if( isset($_GET['field']) ){
                $sort = [
                    $_GET['field'] => $_GET['order']
                ];
            }

            // $list = $this->model
            //     ->where($where)
            //     ->page($page, $limit)
            //     ->order($sort)
            //     ->select();

            $list = Db::table($tbname)->where($where)->field('supplier_name,sum(fee) as fee,count(*) as count')->group('supplier_name')->order($sort)->page($page, $limit)->select();


            // 需要对数据进行汇总
            $totals = $this->model->where($where)->field('sum(fee) as fee')->select()->toArray();

            $data = [
                'code'  => 0,
                'msg'   => '',
                'count' => $count,
                'data'  => $list,
                'totalRow' => [
                    'supplier_name' => '汇总',
                    'fee' => sprintf('%0.2f', $totals[0]['fee']),
                ]
            ];
            return json($data);
        }

        return $this->fetch();
    }


    /**
     * @NodeAnotation(title="按项目汇总")
     */
    public function byproject()
    {
        $tbname = 'ea_cw_payment';
        if ($this->request->isAjax()) {
            if (input('selectFields')) {
                return $this->selectList();
            }

            // where: income_date
            list($page, $limit, $where) = $this->buildTableParames();
            // $count = $this->model
            //     ->where($where)
            //     ->count();
            $count = Db::table($tbname)->where($where)->field('project')->group('project')->count();

            $sort = ['fee'=>'desc'];
            if( isset($_GET['field']) ){
                $sort = [
                    $_GET['field'] => $_GET['order']
                ];
            }

            // $list = $this->model
            //     ->where($where)
            //     ->page($page, $limit)
            //     ->order($sort)
            //     ->select();

            $list = Db::table($tbname)->where($where)->field('project,sum(fee) as fee,count(*) as count')->group('project')->order($sort)->page($page, $limit)->select();


            // 需要对数据进行汇总
            $totals = $this->model->where($where)->field('sum(fee) as fee')->select()->toArray();

            $data = [
                'code'  => 0,
                'msg'   => '',
                'count' => $count,
                'data'  => $list,
                'totalRow' => [
                    'project' => '汇总',
                    'fee' => sprintf('%0.2f', $totals[0]['fee']),
                ]
            ];
            return json($data);
        }

        return $this->fetch();
    }

    /**
     * @NodeAnotation(title="添加")
     */
    public function add()
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $rule = [];

            // 预处理


            $this->validate($post, $rule);
            try {
                $save = $this->model->save($post);
            } catch (\Exception $e) {
                $this->error('保存失败:'.$e->getMessage());
            }
            $save ? $this->success('保存成功') : $this->error('保存失败');
        }

        // get options
        $suppliers = $this->_getCol('name', new CwSupplier());
        $accounts = $this->_getCol('account');
        $projects = $this->_getCol('project');
        $types = $this->_getCol('type');
        $handlers = $this->_getCol('handler');

        $this->assign('suppliers', $suppliers);
        $this->assign('handlers', $handlers);
        $this->assign('types', $types);
        $this->assign('accounts', $accounts);
        $this->assign('projects', $projects);
        return $this->fetch();
    }

    /**
     * @NodeAnotation(title="编辑")
     */
    public function edit($id)
    {
        $row = $this->model->find($id);
        empty($row) && $this->error('数据不存在');
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $rule = [];

            // 预处理

            $this->validate($post, $rule);
            try {
                $save = $row->save($post);
            } catch (\Exception $e) {
                $this->error('保存失败');
            }
            $save ? $this->success('保存成功') : $this->error('保存失败');
        }
        $row['service_time'] = sprintf('%s - %s', $row['service_start'], $row['service_end']);
        $this->assign('data', $row);

        // get options
        $suppliers = $this->_getCol('name', new CwSupplier());
        $accounts = $this->_getCol('account');
        $projects = $this->_getCol('project');
        $types = $this->_getCol('type');
        $handlers = $this->_getCol('handler');

        $this->assign('suppliers', $suppliers);
        $this->assign('handlers', $handlers);
        $this->assign('types', $types);
        $this->assign('accounts', $accounts);
        $this->assign('projects', $projects);
        return $this->fetch();
    }

    /**
     * @NodeAnotation(title="删除")
     */
    public function delete($id)
    {
        $row = $this->model->whereIn('id', $id)->select();
        $row->isEmpty() && $this->error('数据不存在');
        try {
            $save = $row->delete();
        } catch (\Exception $e) {
            $this->error('删除失败');
        }
        $save ? $this->success('删除成功') : $this->error('删除失败');
    }
}