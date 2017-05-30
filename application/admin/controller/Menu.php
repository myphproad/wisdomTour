<?php
/**
*
* 版权所有：亿次元科技<ma.running@foxmail.com>
* 作    者：心要飞<857773627@qq.com>
* 日    期：2017-04-21
* 版    本：1.0.0
* 功能说明：菜单控制器。
*
**/

namespace app\admin\controller;
use think\Controller;
class Menu extends Common
{
	var $model;
	function _initialize() {
		$this->model ='auth_rule';//本控制器下的表名称
		parent::_initialize ();
	}
	public function index(){
		$model=$this->model;
		$list  = db($model)->order('listorder asc')->select();
		$listResult = $this->getMenu($list);
//		dump($listResult);
		$this->assign('list', $listResult);
		return $this->fetch();
    }

	public function del(){
		$model=$this->model;
		$data = input('param.');
		if (!isset($data['id']) || empty($data['id'])) {
			$this->error('参数错误');
		}
		if (is_array($data['id'])) {
			//数组删除
			foreach ($data['id'] as $v) {
				$v = intval($v);
				db('auth_rule')->where('id', $v)->delete();
			}
			$this->success('删除成功');
		} else {
			//单个删除
			$id = intval($data['id']);
			if (!$id) {
				$this->error('非法参数');
			}
			$result_follow=db('auth_rule')->where('pid',$id)->find();//查下属
			if($result_follow){
				$this->error('有子类，不允许删除！');
			}
			db('auth_rule')->where('id', $id)->delete();
			$this->addlog('删除菜单ID：'.$id);
			$this->success('删除成功');
		}
	}
	public function deleteAll()
	{
		$model=$this->model;
		$config = Config::get('database');
		$prefix = $config['prefix'];
		db('auth_rule')->execute('truncate ' . $prefix . 'article');
		$this->success('删除成功');
	}
	public function edit()
	{
		$model=$this->model;
		if (request()->isPost()) {
			$data = input('post.');
			if (empty($data['title'])) {
				$this->error('栏目名称必须填写');
			}
			$id = intval($data['id']);
//			dump($id);exit();
			db('auth_rule')->where('id', $id)->update($data);
			$this->success('修改成功', 'Menu/index');
		}else{
		$id = input('id');
		if (!$id) {
			$this->error('参数错误');
		}
		$list  = db('auth_rule')->order('listorder asc')->select();
		$listResult = $this->getMenu($list);
//		dump($listResult);
		$detail = db('auth_rule')->where('id', $id)->find();
		$this->assign('list', $listResult);
		$this->assign('detail', $detail);
		$this->assign('type', 'edit');
		return $this->fetch();

		}
	}
	public function add(){
		$model=$this->model;
		if (request()->isPost()) {
			$data = input('post.');
			$id=db($model)->insert($data);
			if ($id > 0) {
				$this->success('添加成功', 'Menu/index');
			} else {
				$this->error('添加失败');
			}
		}else{
			$list  = db($model)->order('listorder asc')->select();
			$listResult = $this->getMenu($list);
			/*$index_site_config=config('index_site');
			dump($index_site_config);exit();
			foreach ($str as $key => $value) {
				foreach ($value as $key1 => $value1) {
					var_dump($value1);
				}
				// var_dump($value);
			}*/



			$this->assign('list', $listResult);
			$this->assign('type', 'add');
			return $this->fetch('edit');
		}

	}
	public function status()
	{
		$model=$this->model;
		$data = input('post.');
		if (!isset($data['id']) || empty($data['id'])) {
			$this->error('参数错误');
		}
		foreach ($data['id'] as $v) {
			$v = intval($v);
			$status = db($model)->where('id', $v)->value('status');
			$status = $status ? 0 : 1;
			db($model)->where('id', $v)->update(['status' => $status]);
		}
		$this->success('更新成功');
	}
	public function update()
	{
		$model=$this->model;
		if (request()->isPost()) {
			$data = input('post.');
			if (empty($data['title'])) {
				$this->error('名称必须填写');
			}
			$catid = intval($data['id']);

			db($model)->where('catid', $catid)->update($data);
			$this->success('修改成功', 'Menu/index');
		}
	}
}