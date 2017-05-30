<?php
/*
 * 857773627@qq.com
 * 智慧导览
 */
namespace app\index\controller;

use think\Controller;
class Index extends Controller
{
    protected function _initialize()
    {   
	   
        $this->seo = db('system')->where('id', 1)->find();
    }
    public function index()
    {
        $seo = seo($this->seo['title'], $this->seo['keywords'], $this->seo['description']);
        $this->assign('seo', $seo);
        return $this->fetch();
    }
    public function lists()
    {
        $catid = intval(input('catid'));
        if (!$catid) {
            $this->error('参数错误');
        }
        $cate_db = db('category');
        /*栏目详情start*/
        $cate_info = $cate_db->where('catid', $catid)->find();
        if (!$cate_info) {
            $this->error('栏目不存在');
        }
        $this->assign('category', new_html_entity_decode($cate_info));
        /*栏目详情end*/
        /*子栏目start*/
        $subcate = $cate_db->where('pid', $catid)->limit(10)->select();
        /*子栏目end*/
        /*同级栏目start*/
        $samecate = $cate_db->where('pid', get_catpid($catid))->limit(10)->select();
        /*同级栏目end*/
        if (empty($subcate)) {
            $subcate = $samecate;
        }
        $this->assign('subcate', $subcate);
        $this->assign('samecate', $samecate);
        /*文章列表start*/
        $article_db = db('article');
        $article_list = $article_db->where('catid', 'in', catid_str($catid))->where('status', 0)->order('listorder desc,id desc')->paginate(10);
        $page = $article_list->render();
        $this->assign('article_list', $article_list);
        $this->assign('page', $page);
        /*文章列表end*/
        /*seo start*/
        $seo = seo($cate_info['catname'] . '-' . $this->seo['title'], $cate_info['keywords'], $cate_info['description']);
        $this->assign('seo', $seo);
        /*seo end*/
        /*模板start*/
        if ($cate_info['ispart'] == 1) {
            $template = str_replace('.html', '', $cate_info['category']);
            return $this->fetch($template);
        } else {
            $template = str_replace('.html', '', $cate_info['list']);
            return $this->fetch($template);
        }
        /*模板end*/
    }
    public function show()
    {
        $catid = intval(input('catid'));
        $id = intval(input('id'));
        if (!$catid || !$id) {
            $this->error('参数错误');
        }
        $cate_db = db('category');
        $article_db = db('article');
        /*栏目详情start*/
        $cate_info = $cate_db->where('catid', $catid)->find();
        if (empty($cate_info)) {
            $this->error('栏目不存在');
        }
        $this->assign('category', new_html_special_chars($cate_info));
        /*栏目详情end*/
        /*文章详情start*/
        $article_info = $article_db->where('catid', $catid)->where('id', $id)->where('status', 0)->find();
        if (empty($article_info)) {
            $this->error('文章不存在');
        }
        $keywords = $article_info['keywords'];
        $this->assign('keywords', stringToArray($keywords));
        $this->assign('article', new_html_entity_decode($article_info));
        /*文章详情end*/
        /*子栏目start*/
        $subcate = $cate_db->where('pid', $catid)->limit(10)->select();
        /*子栏目end*/
        /*同级栏目start*/
        $samecate = $cate_db->where('pid', get_catpid($catid))->limit(10)->select();
        /*同级栏目end*/
        if (empty($subcate)) {
            $subcate = $samecate;
        }
        $this->assign('subcate', $subcate);
        $this->assign('samecate', $samecate);
        /*点击数start*/
        $article_db->where('id', $id)->setInc('hits');
        /*点击数end*/
        /*上一篇start*/
        $previous_page = $article_db->where('catid', $catid)->where('status', 0)->where('id', 'lt', $id)->order('id desc')->limit('1')->find();
        if (empty($previous_page)) {
            $previous_page = ['title' => '第一篇', 'thumb' => __ROOT__ . '/public/images/nopic_small.gif', 'url' => 'javascript:alert(\'第一篇\');'];
        }
        $this->assign('previous_page', $previous_page);
        /*上一篇end*/
        /*下一篇start*/
        $next_page = $article_db->where('catid', $catid)->where('status', 0)->where('id', 'gt', $id)->order('id asc')->limit('1')->find();
        if (empty($next_page)) {
            $next_page = ['title' => '最后一篇', 'thumb' => __ROOT__ . '/public/images/nopic_small.gif', 'url' => 'javascript:alert(\'最后一篇\');'];
        }
        $this->assign('next_page', $next_page);
        /*下一篇end*/
        /*seo start*/
        $seo = seo($article_info['title'] . '-' . $this->seo['title'], $article_info['keywords'], $article_info['description']);
        $this->assign('seo', $seo);
        /*seo end*/
        /*模板start*/
        $template = str_replace('.html', '', $cate_info['show']);
        return $this->fetch($template);
        /*模板end*/
    }
    public function search()
    {
        $q = input('q');
        if (!$q) {
            $this->error('参数错误');
        }
        $q = FilterSearch(urldecode($q));
        $db = db('article');
        $article_list = $db->where('title', 'like', '%' . $q . '%')->where('status', 0)->order('listorder desc,id desc')->paginate(10, false, ['query' => ['q' => $q]]);
        $article_arr = [];
        foreach ($article_list as $k => $v) {
            $article_arr[$k]['title'] = str_replace($q, '<font color="#f00">' . $q . '</font>', $v['title']);
            $article_arr[$k]['url'] = $v['url'];
            $article_arr[$k]['thumb'] = $v['thumb'];
            $article_arr[$k]['description'] = $v['description'];
            $article_arr[$k]['inputtime'] = $v['inputtime'];
        }
        $page = $article_list->render();
        $this->assign('article_list', $article_arr);
        $this->assign('page', $page);
        /*文章列表end*/
        /*seo start*/
        $seo = seo($q . '搜索' . '-' . $this->seo['title'], $this->seo['keywords'], $this->seo['description']);
        $this->assign('seo', $seo);
        /*seo end*/
        return $this->fetch();
    }
    public function guestbook_list()
    {
        $guestbook_list = db('guestbook')->where('status', 1)->paginate(10);
        $page = $guestbook_list->render();
        $this->assign('guestbook_list', $guestbook_list);
        $this->assign('page', $page);
        $seo = seo('在线留言-' . $this->seo['title'], $this->seo['keywords'], $this->seo['description']);
        $this->assign('seo', $seo);
        return $this->fetch();
    }
    public function guestbook()
    {
        if (request()->isPost()) {
            $data = input('post.');
            $captcha = new \org\Captcha();
            if (!$captcha->check($data['code'])) {
                $this->error('验证码错误');
            }
            if ($data['name'] == "") {
                $this->error('姓名不能为空');
            }
            if (!preg_match('/^[1-9]*[1-9][0-9]*$/', $data['qq'])) {
                $this->error('QQ号格式不正确');
            }
            $email = $_POST['email'];
            if (!preg_match("/^[\\w\\-\\.]+@[\\w\\-\\.]+(\\.\\w+)+\$/", $data['email'])) {
                $this->error('邮箱格式不正确');
            }
            if (!preg_match('/^1([0-9]{10})$/', $data['telephone'])) {
                $this->error('手机号码格式不正确');
            }
            if ($data['title'] == "") {
                $this->error('标题不能为空');
            }
            $data['title'] = strip_tags($data['title']);
            $data['addtime'] = request()->time();
            if ($data['content'] == "") {
                $this->error('内容不能为空');
            }
            $id = db('guestbook')->insertGetId($data);
            if ($id > 0) {
                $this->success('留言成功');
            }
        } else {
            $seo = seo('在线留言-' . $this->seo['title'], $this->seo['keywords'], $this->seo['description']);
            $this->assign('seo', $seo);
            return $this->fetch();
        }
    }
    public function captcha()
    {
        $captcha = new \org\Captcha(config('captcha'));
        $captcha->entry();
    }
    public function tag()
    {
        $tag = input('get.tag');
        $tag = FilterSearch(urldecode($tag));
        $r = db('tag')->where('tag', $tag)->find();
        if (!$r) {
            $this->error('TAG不存在！', __ROOT__);
        }
        $tagid = $r['tagid'];
        $list = db('tag_data')->where('tagid', $tagid)->paginate(10);
        if (!empty($list)) {
            $article_list = [];
            foreach ($list as $k => $v) {
                $article_info = db('article')->where('id', $v['contentid'])->find();
                $article_list[$k]['title'] = str_replace($tag, '<font color="#f00">' . $tag . '</font>', $article_info['title']);
                $article_list[$k]['description'] = str_replace($tag, '<font color="#f00">' . $tag . '</font>', $article_info['description']);
                $article_list[$k]['thumb'] = $article_info['thumb'];
                $article_list[$k]['inputtime'] = $article_info['inputtime'];
                $article_list[$k]['hits'] = $article_info['hits'];
                $article_list[$k]['url'] = $article_info['url'];
            }
        }
        $page = $list->render();
        $this->assign('article_list', $article_list);
        $this->assign('page', $page);
        $seo = seo($tag . '-' . $this->seo['title'], $this->seo['keywords'], $this->seo['description']);
        $this->assign('seo', $seo);
        /*点击数start*/
        db('tag')->where('tag', $tag)->setInc('hits');
        /*点击数end*/
        return $this->fetch();
    }
    public function tag_list()
    {
        $seo = seo($this->seo['title'], $this->seo['keywords'], $this->seo['description']);
        $this->assign('seo', $seo);
        return $this->fetch();
    }
}