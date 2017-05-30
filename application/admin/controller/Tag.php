<?php
namespace app\admin\controller;

use think\Controller;
class Tag extends Common
{
    public function index()
    {
        $this->check();
        $tag_list = db('tag')->order('tagid desc')->paginate(10);
        $page = $tag_list->render();
        $this->assign('tag_list', $tag_list);
        $this->assign('page', $page);
        return $this->fetch();
    }
    private function check()
    {
        db('tag')->where('tag', '')->delete();
        
    }
    public function delete()
    {
        $data = input('param.');
        if (!isset($data['tagid']) || empty($data['tagid'])) {
            $this->error('参数错误');
        }
        if (is_array($data['tagid'])) {
            foreach ($data['tagid'] as $v) {
                $v = intval($v);
                db('tag')->where('tagid', $v)->delete();
                db('tag_data')->where('tagid', $v)->delete();
            }
            $this->success('删除成功');
        } else {
            $tagid = intval($data['tagid']);
            if (!$tagid) {
                $this->error('非法参数');
            }
            db('tag')->where('tagid', $tagid)->delete();
            db('tag_data')->where('tagid', $tagid)->delete();
            $this->success('删除成功');
        }
        $this->success('删除成功');
    }
}