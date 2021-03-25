<?php

namespace app\admin\controller\cw;

use app\common\controller\AdminController;
use EasyAdmin\annotation\ControllerAnnotation;
use EasyAdmin\annotation\NodeAnotation;
use think\App;
use app\admin\model\CwClient;
use app\admin\model\CwIncome;
use think\facade\Db;

/**
 * @ControllerAnnotation(title="cw_receivable")
 */
class Receivable extends AdminController
{

    use \app\admin\traits\Curd;

    public function __construct(App $app)
    {
        parent::__construct($app);

        $this->model = new \app\admin\model\CwReceivable();
        
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
                if( $_GET['field'] == 'service_time' ) {
                    $sort = ['service_start' => $_GET['order']];
                } else {
                    $sort = [
                        $_GET['field'] => $_GET['order']
                    ];
                }
            }
            $list = $this->model
                ->where($where)
                ->page($page, $limit)
                ->order($sort)
                ->select();
            // var_dump($this->model->getLastSql());exit;
            // 处理一下服务期间
            foreach($list as $key=>$row){
                $list[$key]['service_time'] = sprintf('%s - %s', $row['service_start'], $row['service_end']);
                unset($list[$key]['service_start']);
                unset($list[$key]['service_end']);
            }

            // 需要对数据进行汇总
            $totals = $this->model->where($where)->field('sum(fee) as fee,sum(received_fee) as received_fee, sum(unreceive_fee) as unreceive_fee')->select()->toArray();

            $data = [
                'code'  => 0,
                'msg'   => '',
                'count' => $count,
                'data'  => $list,
                'totalRow' => [
                    'id' => '汇总：',
                    'fee' => sprintf('%0.2f', $totals[0]['fee']),
                    'received_fee' => sprintf('%0.2f', $totals[0]['received_fee']),
                    'unreceive_fee' => sprintf('%0.2f', $totals[0]['unreceive_fee']),
                ]
            ];
            return json($data);
        }

        // get options
        $clients = $this->_getCol('client_name');
        $projects = $this->_getCol('project');

        $this->assign('clients', $clients);
        $this->assign('projects', $projects);
        return $this->fetch();
    }

    /**
     * @NodeAnotation(title="按客户汇总")
     */
    public function byclient()
    {
        $tbname = 'ea_cw_receivable';
        if ($this->request->isAjax()) {
            if (input('selectFields')) {
                return $this->selectList();
            }

            // where: income_date
            list($page, $limit, $where) = $this->buildTableParames();
            if( $where ){
                $where[0][1] = '<=';
            }
            // $count = $this->model
            //     ->where($where)
            //     ->count();
            $count = Db::table($tbname)->where($where)->field('client_name')->group('client_name')->count();

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

            $list = Db::table($tbname)->where($where)->field('client_name,sum(fee) as fee,count(*) as count')->group('client_name')->order($sort)->page($page, $limit)->select();


            // 需要对数据进行汇总
            $totals = $this->model->where($where)->field('sum(fee) as fee')->select()->toArray();

            $data = [
                'code'  => 0,
                'msg'   => '',
                'count' => $count,
                'data'  => $list,
                'totalRow' => [
                    'client_name' => '汇总：',
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
        $tbname = 'ea_cw_receivable';
        if ($this->request->isAjax()) {
            if (input('selectFields')) {
                return $this->selectList();
            }

            // where: income_date
            list($page, $limit, $where) = $this->buildTableParames();
            if( $where ){
                $where[0][1] = '<=';
            }
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
                    'project' => '汇总：',
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
        $clients = $this->_getCol('name', new CwClient());
        $projects = $this->_getCol('project');
        $types = $this->_getCol('type');

        $this->assign('clients', $clients);
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
        $clients = $this->_getCol('name', new CwClient());
        $projects = $this->_getCol('project');
        $types = $this->_getCol('type');

        $this->assign('clients', $clients);
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


    public function newincome($id)
    {
        if ($this->request->isAjax()) {
            $post = $this->request->post();
            $rule = [];

            // 获得源数据 
            $row = $this->model->find($id);
            if( !$row ){
                $this->error('save faild, record not found');
            }

            // 写入收入明细的数据
            $data = array(
                'fee' => $post['fee'],
                'service_start' => $row['service_start'],
                'service_end' => $row['service_end'],
                'type' => $row['type'],
                'client_name' => $row['client_name'],
                'account' => $post['account'],
                'project' => $row['project'],
                'income_date' => $post['income_date'],
                'remark' => '',
                'handler' => $post['handler'],
                'from_receivable' => $row['id'],
                'add_time' => date('Y-m-d H:i:s'),
            );
            $m = new CwIncome();
            $m->save($data);

            // 更新应收的数据
            $received = $row['received_fee'] + $post['fee'];
            $unreceived = $row['fee'] - $received;
            $row->save(array(
                'received_fee' => $received,
                'unreceive_fee' => $unreceived,
            ));

            $this->success('保存成功');
        }

        // get options
        $accounts = $this->_getCol('account', new CwIncome());
        $handlers = $this->_getCol('handler', new CwIncome());

        $this->assign('accounts', $accounts);
        $this->assign('handlers', $handlers);
        return $this->fetch();
    }
}