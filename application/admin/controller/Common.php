<?php
namespace app\admin\controller;

use think\Controller;
use think\Db;
class Common extends Controller
{
    public $USER;

    protected function _initialize()
    {
     /*   $this->seo = db('system')->where('id', 1)->find();
        dump($this->seo);exit();*/
        error_reporting(E_ERROR | E_WARNING | E_PARSE);//没有必要的一些错误忽略
		if (!session('fivecms_admin_id')||!session('fivecms_admin_username')||request()->time() - session('fivecms_login_time') > 2 * 60 * 60) {
            session('fivecms_admin_username', null);
			session('fivecms_admin_id', null);
			session('fivecms_login_time', null);
			session('fivecms_last_time', null);
            session('fivecms_last_ip', null);
            $this->redirect(url('Login/index'));
        }
        $prefix =config('database.prefix');
        // 当前模块名
//        request()->module();
        // 当前控制器名
        $CONTROLLER_NAME=request()->controller();
        // 当前操作名

        $ACTION_NAME=request()->action();
        $current_action_name = $ACTION_NAME == 'edit' ? "index" : $ACTION_NAME;
        $current =Db::query("SELECT s.id,s.title,s.name,s.tips,s.pid,p.pid as ppid,p.title as ptitle FROM {$prefix}auth_rule s left join {$prefix}auth_rule p on p.id=s.pid where s.name='" . $CONTROLLER_NAME . '/' . $current_action_name . "'");
        $this->assign('current', $current[0]);

        $menu = db('auth_rule')->field('id,title,pid,name,icon')->where("islink=1")->order('listorder ASC')->select();
//        dump($menu);
        $menu = $this->getMenu($menu);
//       dump($menu);exit();
        $this->assign('menu', $menu);
    }
    public function logout()
    {
        session('fivecms_admin_username', null);
        session('fivecms_admin_id', null);
		session('fivecms_login_time', null);
		session('fivecms_last_time', null);
        session('fivecms_last_ip', null);
        $this->success('退出成功', 'Login/index');
    }
    public function cache()
    {
		$path=RUNTIME_PATH;
        delDirAndFile($path);
        $this->success('清除缓存成功');
    }
    /*
    *
    * 处理二级菜单
    *
    *
    */

    protected function getMenu($items, $id = 'id', $pid = 'pid', $son = 'children')
    {
        $tree = array();
        $tmpMap = array();

        foreach ($items as $item) {
            $tmpMap[$item[$id]] = $item;
        }

        foreach ($items as $item) {
            if (isset($tmpMap[$item[$pid]])) {
                $tmpMap[$item[$pid]][$son][] = &$tmpMap[$item[$id]];
            } else {
                $tree[] = &$tmpMap[$item[$id]];
            }
        }
        return $tree;
    }
    /* 审核状态
     * status参数
     * 1：不通过
     * 2：通过
     * 模型用常量获取
     */
    public function status(){
        $id = isset($_REQUEST['id'])?$_REQUEST['id']:false;
        //id无效的
        if(!$id){
            $this->error('参数错误！');
        }

        if ($_REQUEST['val']=="1"){
            //申请审核通过
            $val="2";
            $tip="已经审核通过";
        }else{
            //关闭通过
            $val="1";
            $tip="已经审核不通过";
        }
        $model=M(CONTROLLER_NAME);

        $map['id']  = $id;
        $map['aid']  = $id;
        $map['_logic']  = "or";
        $data['status']=$val;
        if($model->where($map)->save($data)){
            $this->success($tip);
        }else{
            $this->error($tip);
        }
    }

    /**
     *
     * 函数：日志记录
     * @param  string $log   日志内容。
     * @param  string $name （可选）用户名。
     *
     **/
    function addlog($log,$name=false){
        if(!$name){
            $user =session('fivecms_admin_username');
            $data['name'] = $user;
        }else{
            $data['name'] = $name;
        }
        $data['t'] = time();
        $data['ip'] = $_SERVER["REMOTE_ADDR"];
        $data['log'] = $log;
        db('log')->insertGetId($data);
    }


    /**
     *
     * 函数：获取用户信息
     * @param  int $uid      用户ID。
     * @param  string $name  数据列（如：'uid'、'uid,user'）
     *
     **/
    function member($uid,$field=false) {
        $model = M('Member');
        if($field){
            return $model ->field($field)-> where(array('uid'=>$uid)) -> find();
        }else{
            return $model -> where(array('uid'=>$uid)) -> find();
        }
    }
    public function get_keywords()
    {
        $number = input('number');
        $data = input('data');
        return $this->get_keywords_data($data, $number);
    }
    private function get_keywords_data($data, $number = 3)
    {
        $data = trim(strip_tags($data));
        if (empty($data)) {
            return '';
        }
        $http = new \org\Http();
        $data = iconv('utf-8', 'gbk', $data);
        $http->post('http://tool.phpcms.cn/api/get_keywords.php', array('siteurl' => __ROOT__, 'charset' => 'utf-8', 'data' => $data, 'number' => $number));
        if ($http->is_ok()) {
            return iconv('gbk', 'utf-8', $http->get_data());
        }
        return '';
    }
}