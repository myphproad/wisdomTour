<?php
/**
 *
 * 版权所有：亿次元科技<ma.running@foxmail.com>
 * 作    者：心要飞<857773627@qq.com>
 * 日    期：2017-04-21
 * 版    本：1.0.0
 * 功能说明：首页位置样式控制器
 *
 **/
namespace app\admin\controller;

use think\Controller;
use think\Validate;
use think\Config;
use think\Db;
class IndexSite extends Common
{
    var $model;
    function _initialize() {
        $this->model ='index_site';//本控制器下的表名称
        parent::_initialize ();
    }
    public function index()
    {
        $model=$this->model;
        $flink_list = db($model)->order('listorder desc,id desc')->paginate(10);
        $page = $flink_list->render();
        $this->assign('flink_list', $flink_list);
        $this->assign('page', $page);
        return $this->fetch();
    }
    public function add()
    {
        return $this->fetch();
    }
    public function insert()
    {
        $model=$this->model;
        if (request()->isPost()) {
            $data = input('post.');
            $data = $this->data_check($data);
            $id = db($model)->insertGetId($data);
			if(!$id)return FALSE;
            if (isset($data['dosubmit'])) {
                $this->success('添加成功', 'Flink/index');
            } else {
                $this->success('添加成功');
            }
        }
    }
    public function edit()
    {
        $model=$this->model;
        $id = intval($_GET['id']);
        if (!$id) {
            $this->error('非法参数');
        }
        $result = db($model)->where('id', $id)->find();
        if (!$result) {
            $this->error('友情链接不存在');
        }
        $this->assign('result', $result);
        return $this->fetch();
    }
    public function update()
    {
        $model=$this->model;
        if (request()->isPost()) {
            $data = input('post.');
            $data = $this->data_check($data);
            $id = intval($data['id']);
            db($model)->where('id', $id)->update($data);
            $this->success('修改成功', 'Flink/index');
        }
    }
    public function delete()
    {
        $model=$this->model;
        $data = input('param.');
        if (!isset($data['id']) || empty($data['id'])) {
            $this->error('参数错误');
        }
        if (is_array($data['id'])) {
            foreach ($data['id'] as $v) {
                $v = intval($v);
                db($model)->where('id', $v)->delete();
            }
            $this->success('删除成功');
        } else {
            $id = intval($data['id']);
            if (!$id) {
                $this->error('非法参数');
            }
            db($model)->where('id', $id)->delete();
            $this->success('删除成功');
        }
        $this->success('删除成功');
    }
    public function deleteAll()
    {
        //db('article')->where(1)->delete();
        $model=$this->model;
        $config = Config::get('database');
        $prefix = $config['prefix'];
        db($model)->execute('truncate ' . $prefix . 'flink');
        $this->success('删除成功');
    }
    public function listorder()
    {
        $model=$this->model;
        $data = input('post.');
		if(!$data)$this->error('参数错误');
        foreach ($data['listorder'] as $k => $v) {
            $k = intval($k);
            db($model)->where('id', $k)->update(['listorder' => $v]);
        }
        $this->success('更新成功');
    }
    private function data_check($data)
    {
        $rule = ['title' => 'require|max:100'];
        $msg = ['title.require' => '标题必须填写', 'title.max' => '标题最多不能超过100个字符'];
        $validate = new Validate($rule, $msg);
        if (!$validate->check(['title' => $data['title']])) {
            $this->error($validate->getError());
        }
        $data['title'] = safe_replace($data['title']);
        $reg = '/\\b((?#protocol)https?|ftp):\\/\\/((?#domain)[-A-Z0-9.]+)((?#file)\\/[-A-Z0-9+&@#\\/%=~_|!:,.;]*)?((?#parameters)\\?[A-Z0-9+&@#\\/%=~_|!:,.;]*)?/i';
       /* if (!preg_match($reg, $data['url'])) {
            $this->error('地址格式不正确');
        }*/
        return $data;
    }
}