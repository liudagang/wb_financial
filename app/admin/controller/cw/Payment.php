<?php

namespace app\admin\controller\cw;

use app\admin\model\CwPayable;
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
        $this->allowModifyFields = ['fee'];  
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
            // 检查，是否有关联的应收，同时
            $data = $row->toArray()[0];
            if( $data['from_payable'] ){
                $m = new CwPayable();
                $r = $m->find($data['from_payable']);
                if( $r ){
                    // reset
                    $r->save(array(
                        'payed_fee' => $r['payed_fee'] - $data['fee'],
                        'unpay_fee' => $r['unpay_fee'] + $data['fee'],
                    ));
                }
            }

            $save = $row->delete();
        } catch (\Exception $e) {
            $this->error('删除失败');
        }
        $save ? $this->success('删除成功') : $this->error('删除失败');
    }

    /**
     * @NodeAnotation(title="属性修改")
     */
    public function modify()
    {
        $post = $this->request->post();
        $rule = [
            'id|ID'    => 'require',
            'field|字段' => 'require',
            'value|值'  => 'require',
        ];
        $this->validate($post, $rule);
        $row = $this->model->find($post['id']);
        if (!$row) {
            $this->error('数据不存在');
        }
        if (!in_array($post['field'], $this->allowModifyFields)) {
            $this->error('该字段不允许修改：' . $post['field']);
        }
        try {
            if( $post['field'] == 'fee' ){
                // 还要更新应收
                if( $row['from_payable'] ){
                    $m = new \app\admin\model\CwPayable();
                    $r = $m->find($row['from_payable']);
                    if( $r ){
                        $payed_fee = $r['payed_fee'] + $post['value'] - $row['fee'];
                        $unpay_fee = $r['fee'] - $payed_fee;
                        $r->save([
                            'payed_fee' => $payed_fee,
                            'unpay_fee' => $unpay_fee,
                        ]);
                    }
                }
            }

            $row->save([
                $post['field'] => $post['value'],
            ]);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('保存成功');
    }


    public function payable($id){
        if ($this->request->isAjax()) {
            if (input('selectFields')) {
                return $this->selectList();
            }

            list($page, $limit, $where) = $this->buildTableParames();
            $where[] = ['from_payable', '=', $id];
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

        $this->assign('id', $id);
        return $this->fetch();
    }
}