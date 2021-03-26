<?php

namespace app\admin\controller\cw;

use app\admin\model\CwPayment;
use app\common\controller\AdminController;
use EasyAdmin\annotation\ControllerAnnotation;
use EasyAdmin\annotation\NodeAnotation;
use think\App;
use app\admin\model\CwSupplier;
use think\facade\Db;

/**
 * @ControllerAnnotation(title="cw_payable")
 */
class Payable extends AdminController
{

    use \app\admin\traits\Curd;

    public function __construct(App $app)
    {
        parent::__construct($app);

        $this->model = new \app\admin\model\CwPayable();
        
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

            // 处理一下服务期间
            foreach($list as $key=>$row){
                $list[$key]['service_time'] = sprintf('%s - %s', $row['service_start'], $row['service_end']);
                unset($list[$key]['service_start']);
                unset($list[$key]['service_end']);
            }

            // 需要对数据进行汇总
            $totals = $this->model->where($where)->field('sum(fee) as fee,sum(payed_fee) as payed_fee, sum(unpay_fee) as unpay_fee')->select()->toArray();

            $data = [
                'code'  => 0,
                'msg'   => '',
                'count' => $count,
                'data'  => $list,
                'totalRow' => [
                    'id' => '汇总：',
                    'fee' => sprintf('%0.2f', $totals[0]['fee']),
                    'payed_fee' => sprintf('%0.2f', $totals[0]['payed_fee']),
                    'unpay_fee' => sprintf('%0.2f', $totals[0]['unpay_fee']),
                ]
            ];
            return json($data);
        }

        // get options
        $suppliers = $this->_getCol('supplier_name');
        $projects = $this->_getCol('project');

        $this->assign('suppliers', $suppliers);
        $this->assign('projects', $projects);
        return $this->fetch();
    }

    /**
     * @NodeAnotation(title="按客户汇总")
     */
    public function bysupplier()
    {
        $tbname = 'ea_cw_payable';
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
        $tbname = 'ea_cw_payable';
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
            list($start, $end) = explode(' - ', $post['service_time']);
            $post['service_start'] = $start;
            $post['service_end'] = $end;
            unset($post['service_time']);

            // 处理金额
            $post['unreceive_fee'] = $post['fee'];
            $post['add_time'] = date('Y-m-d H:i:s');

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

        $projects = $this->_getCol('project');
        $types = $this->_getCol('type');

        $this->assign('suppliers', $suppliers);
        $this->assign('types', $types);
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
            list($start, $end) = explode(' - ', $post['service_time']);
            $post['service_start'] = $start;
            $post['service_end'] = $end;
            unset($post['service_time']);

            $post['unreceive_fee'] = $post['fee'] - $row['received_fee'];

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
        $projects = $this->_getCol('project');
        $types = $this->_getCol('type');

        $this->assign('suppliers', $suppliers);
        $this->assign('types', $types);
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

    public function newpayment($id)
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $rule = [];

            // 获得源数据 
            $row = $this->model->find($id);
            if( !$row ){
                $this->error('save faild, record not found');
            }

            // 写入付款明细的数据
            $data = array(
                'fee' => $post['fee'],
                // 'service_start' => $row['service_start'],
                // 'service_end' => $row['service_end'],
                'type' => $row['type'],
                'supplier_name' => $row['supplier_name'],
                'account' => $post['account'],
                'project' => $row['project'],
                'pay_date' => $post['pay_date'],
                'remark' => '',
                'handler' => $post['handler'],
                'from_payable' => $row['id'],
                'add_time' => date('Y-m-d H:i:s'),
            );
            $m = new CwPayment();
            $m->save($data);

            // 更新应收的数据
            $payed = $row['payed_fee'] + $post['fee'];
            $unpay = $row['fee'] - $payed;
            $row->save(array(
                'payed_fee' => $payed,
                'unpay_fee' => $unpay,
            ));

            $this->success('保存成功');
        }

        // get options
        $accounts = $this->_getCol('account', new CwPayment());
        $handlers = $this->_getCol('handler', new CwPayment());

        $this->assign('accounts', $accounts);
        $this->assign('handlers', $handlers);
        return $this->fetch();
    }
}