<?php
namespace app\admin\controller;

use think\Controller;
use think\Validate;
use think\Config;
use think\Db;
class Guestbook extends Common
{
    public function index()
    {
        $guestbook_list = db('guestbook')->order('id desc')->paginate(10);
        $page = $guestbook_list->render();
        $this->assign('guestbook_list', $guestbook_list);
        $this->assign('page', $page);
        return $this->fetch();
    }
    public function edit()
    {
        $id = intval($_GET['id']);
        if (!$id) {
            $this->error('非法参数');
        }
        $result = db('guestbook')->where('id', $id)->find();
        if (!$result) {
            $this->error('留言不存在');
        }
        $this->assign('result', $result);
        return $this->fetch();
    }
    public function update()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $data['replytime'] = request()->time();
            $data['status'] = 1;
            $id = intval($data['id']);
            db('guestbook')->where('id', $id)->update($data);
            $this->success('回复成功', 'guestbook/index');
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
                db('guestbook')->where('id', $v)->delete();
            }
            $this->success('删除成功');
        } else {
            $id = intval($data['id']);
            if (!$id) {
                $this->error('非法参数');
            }
            db('guestbook')->where('id', $id)->delete();
            $this->success('删除成功');
        }
        $this->success('删除成功');
    }
    public function deleteAll()
    {
        //db('guestbook')->where(1)->delete();
        $config = Config::get('database');
        $prefix = $config['prefix'];
        db('guestbook')->execute('truncate ' . $prefix . 'guestbook');
        $this->success('删除成功');
    }
}