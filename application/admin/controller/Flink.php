<?php
namespace app\admin\controller;

use think\Controller;
use think\Validate;
use think\Config;
use think\Db;
class Flink extends Common
{
    public function index()
    {
        $flink_list = db('flink')->order('listorder desc,id desc')->paginate(10);
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
        if (request()->isPost()) {
            $data = input('post.');
            $data = $this->data_check($data);
            $id = db('flink')->insertGetId($data);
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
        $id = intval($_GET['id']);
        if (!$id) {
            $this->error('非法参数');
        }
        $result = db('flink')->where('id', $id)->find();
        if (!$result) {
            $this->error('友情链接不存在');
        }
        $this->assign('result', $result);
        return $this->fetch();
    }
    public function update()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $data = $this->data_check($data);
            $id = intval($data['id']);
            db('flink')->where('id', $id)->update($data);
            $this->success('修改成功', 'Flink/index');
        }
    }
    public function delete()
    {
        $data = input('param.');
        if (!isset($data['id']) || empty($data['id'])) {
            $this->error('参数错误');
        }
        if (is_array($data['id'])) {
            foreach ($data['id'] as $v) {
                $v = intval($v);
                db('flink')->where('id', $v)->delete();
            }
            $this->success('删除成功');
        } else {
            $id = intval($data['id']);
            if (!$id) {
                $this->error('非法参数');
            }
            db('flink')->where('id', $id)->delete();
            $this->success('删除成功');
        }
        $this->success('删除成功');
    }
    public function deleteAll()
    {
        //db('article')->where(1)->delete();
        $config = Config::get('database');
        $prefix = $config['prefix'];
        db('flink')->execute('truncate ' . $prefix . 'flink');
        $this->success('删除成功');
    }
    public function listorder()
    {
        $data = input('post.');
		if(!$data)$this->error('参数错误');
        foreach ($data['listorder'] as $k => $v) {
            $k = intval($k);
            db('flink')->where('id', $k)->update(['listorder' => $v]);
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
        if (!preg_match($reg, $data['url'])) {
            $this->error('地址格式不正确');
        }
        return $data;
    }
}