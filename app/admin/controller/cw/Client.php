<?php

namespace app\admin\controller\cw;

use app\common\controller\AdminController;
use EasyAdmin\annotation\ControllerAnnotation;
use EasyAdmin\annotation\NodeAnotation;
use think\App;
use think\facade\Db;

/**
 * @ControllerAnnotation(title="cw_client")
 */
class Client extends AdminController
{

    use \app\admin\traits\Curd;

    public function __construct(App $app)
    {
        parent::__construct($app);

        $this->model = new \app\admin\model\CwClient();
        
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
            $data = [
                'code'  => 0,
                'msg'   => '',
                'count' => $count,
                'data'  => $list,
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
            $this->validate($post, $rule);

            // 检查客户名是否冲突
            $rs = $this->model->where(['name'=>$post['name']])->count();
            if( $rs ){
                $this->error('客户名称已存在');
            }

            try {
                $save = $this->model->save($post);
            } catch (\Exception $e) {
                $this->error('保存失败:'.$e->getMessage());
            }
            $save ? $this->success('保存成功') : $this->error('保存失败');
        }


        // get options
        $this->assign('states', $this->_getCol('state'));
        $this->assign('codes', $this->_getCol('code'));
        $this->assign('types', $this->_getCol('type'));
        $this->assign('tax_types', $this->_getCol('tax_type'));
        $this->assign('names', $this->_getCol('name'));
        $this->assign('srcs', $this->_getCol('src'));
        $this->assign('levels', $this->_getCol('level'));

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
            $this->validate($post, $rule);
            try {
                $save = $row->save($post);
            } catch (\Exception $e) {
                $this->error('保存失败');
            }
            $save ? $this->success('保存成功') : $this->error('保存失败');
        }
        $this->assign('data', $row);

        // get options
        $this->assign('states', $this->_getCol('state'));
        $this->assign('codes', $this->_getCol('code'));
        $this->assign('types', $this->_getCol('type'));
        $this->assign('tax_types', $this->_getCol('tax_type'));
        $this->assign('names', $this->_getCol('name'));
        $this->assign('srcs', $this->_getCol('src'));
        $this->assign('levels', $this->_getCol('level'));
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

    public function list($id)
    {
        if ($this->request->isAjax()) {
            if (input('selectFields')) {
                return $this->selectList();
            }
            $row = $this->model->find($id);
            if( !$row ){
                $this->error('record not found');
            }

            $list = Db::query("(SELECT income_date AS t,'收入' as code,client_name,fee,`type`,project FROM ea_cw_income WHERE client_name=:name ORDER BY t DESC)
            UNION
            (SELECT SUBSTR(add_time,1,10) AS t, '应收' AS CODE,client_name,fee,`type`,project FROM ea_cw_receivable WHERE client_name=:name2 ORDER BY t DESC)", ['name'=>$row['name'], 'name2'=>$row['name']]);
            /*
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
            */

            // use source sql direct
            

            $data = [
                'code'  => 0,
                'msg'   => '',
                'data'  => $list,
            ];
            return json($data);
        }

        $this->assign('id', $id);
        return $this->fetch();
    }
}