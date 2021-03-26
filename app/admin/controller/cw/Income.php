<?php

namespace app\admin\controller\cw;

use app\common\controller\AdminController;
use EasyAdmin\annotation\ControllerAnnotation;
use EasyAdmin\annotation\NodeAnotation;
use think\App;
use app\admin\model\CwClient;
use app\admin\model\CwReceivable;
use think\facade\Db;

/**
 * @ControllerAnnotation(title="cw_income")
 */
class Income extends AdminController
{

    use \app\admin\traits\Curd;

    protected $sort = [
        'id' => 'desc',
    ];

    public function __construct(App $app)
    {
        parent::__construct($app);

        $this->model = new \app\admin\model\CwIncome();
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
        $clients = $this->_getCol('client_name');
        $accounts = $this->_getCol('account');
        $projects = $this->_getCol('project');

        $this->assign('clients', $clients);
        $this->assign('accounts', $accounts);
        $this->assign('projects', $projects);
        return $this->fetch();
    }

    /**
     * @NodeAnotation(title="按客户汇总")
     */
    public function byclient()
    {
        $tbname = 'ea_cw_income';
        if ($this->request->isAjax()) {
            if (input('selectFields')) {
                return $this->selectList();
            }

            // where: income_date
            list($page, $limit, $where) = $this->buildTableParames();
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
        $tbname = 'ea_cw_income';
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
        $accounts = $this->_getCol('account');
        $projects = $this->_getCol('project');
        $types = $this->_getCol('type');
        $handlers = $this->_getCol('handler');

        $this->assign('clients', $clients);
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
            list($start, $end) = explode(' - ', $post['service_time']);
            $post['service_start'] = $start;
            $post['service_end'] = $end;
            unset($post['service_time']);


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
        $accounts = $this->_getCol('account');
        $projects = $this->_getCol('project');
        $types = $this->_getCol('type');
        $handlers = $this->_getCol('handler');

        $this->assign('clients', $clients);
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
            if( $data['from_receivable'] ){
                $m = new CwReceivable();
                $r = $m->find($data['from_receivable']);
                if( $r ){
                    // reset
                    $r->save(array(
                        'received_fee' => $r['received_fee'] - $data['fee'],
                        'unreceive_fee' => $r['unreceive_fee'] + $data['fee'],
                    ));
                }
            }

            $save = $row->delete();
        } catch (\Exception $e) {
            $this->error('删除失败'.$e->getMessage());
        }
        $save ? $this->success('删除成功') : $this->error('删除失败');
    }


    public function received($id){
        if ($this->request->isAjax()) {
            if (input('selectFields')) {
                return $this->selectList();
            }

            list($page, $limit, $where) = $this->buildTableParames();
            $where[] = ['from_receivable', '=', $id];
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
                if( $row['from_receivable'] ){
                    $m = new \app\admin\model\CwReceivable();
                    $r = $m->find($row['from_receivable']);
                    if( $r ){
                        $received_fee = $r['received_fee'] + $post['value'] - $row['fee'];
                        $unreceive_fee = $r['fee'] - $received_fee;
                        $r->save([
                            'received_fee' => $received_fee,
                            'unreceive_fee' => $unreceive_fee,
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
}