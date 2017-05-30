<?php
namespace app\admin\controller;

use think\Controller;
use think\Validate;
use think\Config;
use think\Db;
class Carousel extends Common
{
    var $model;
    function _initialize() {
        $this->model ='Carousel';//本控制器下的表名称
        parent::_initialize ();
    }
    public function index()
    {
        $model=$this->model;
        $q = input('q');
        if (!empty($q)) {
            $map['title'] = ['like', '%' . strip_tags(trim($q)) . '%'];
        }
        if (empty($map)) {
            $map = 1;
        }
        $article_list = db($model)->where($map)->order('listorder desc,id desc')->paginate(10, false, ['query' => ['q' => $q]]);
        $page = $article_list->render();
        $this->assign('q', $q);
        $this->assign('article_list', $article_list);
//        dump($article_list);exit();

        $this->assign('page', $page);
        return $this->fetch();
    }
    public function add()
    {
        $model=$this->model;
        $catid = input('catid');
        $list = db($model)->order('listorder desc')->select();
        $this->assign('list', $list);
        $this->assign('catid', intval($catid));
        return $this->fetch();
    }
    public function insert()
    {
        $model=$this->model;
        if (request()->isPost()) {
            $data = input('post.');
            $data = $this->data_check($data);
            $data['create_time'] = request()->time();
			$id = db($model)->insertGetId($data);
			if(!$id)return FALSE;
			$url = __ROOT__ . '/index.php/index/show.html?catid=' . $data['catid'] . '&id=' . $id;
			db($model)->where('id', $id)->update(['url' => $url]);
            if (isset($data['dosubmit'])) {
                $this->success('添加成功', 'Article/index');
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
            $this->error('文章不存在');
        }
        $this->assign('result', $result);
        return $this->fetch();
    }
    public function update()
    {
        $model=$this->model;
        if (request()->isPost()) {
            $data = input('post.');
            $data = $this->data_check($data);//双重验证
//            dump($data);exit();
            $data['create_time'] = request()->time();
            $id = intval($data['id']);
            db($model)->where('id', $id)->update($data);
            $this->success('修改成功', url('index'));
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
        $model=$this->model;
        $config = Config::get('database');
        $prefix = $config['prefix'];
        db($model)->execute('truncate ' . $prefix . 'article');
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
    public function status()
    {
        $model=$this->model;
        $data = input('post.');
        if (!isset($data['id']) || empty($data['id'])) {
            $this->error('参数错误');
        }
        foreach ($data['id'] as $v) {
            $v = intval($v);
            $status = db('article')->where('id', $v)->value('status');
            $status = $status ? 0 : 1;
            db($model)->where('id', $v)->update(['status' => $status]);
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
        //自动提取摘要
        if ($data['description'] == '' && isset($data['content'])) {
            $description_length = 200;
            $data['description'] = str_cut(str_replace(["'", "\r\n", "\t", '&ldquo;', '&rdquo;', '&nbsp;'], '', strip_tags(stripslashes($data['content']))), $description_length);
            $data['description'] = addslashes($data['description']);
        }
        //自动提取缩略图
        if ($data['thumb'] == '' && isset($data['content'])) {
            $auto_thumb_no = 0;
            if (preg_match_all("/(src)=([\"|']?)([^ \"'>]+\\.(gif|jpg|jpeg|bmp|png))\\2/i", stripslashes($data['content']), $matches)) {
                $data['thumb'] = $matches[3][$auto_thumb_no];
            }
        }
        $data['description'] = str_replace(['/', '\\', '#', '.', "'"], ' ', $data['description']);
        return $data;
    }
}