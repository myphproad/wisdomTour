<?php
namespace app\Common\taglib;

use think\template\TagLib;
class Article extends Taglib
{
    // 标签定义
    protected $tags = [
	'category' => ['attr' => 'pid,num,order,name', 'close' => 1], 
	'arclist' => ['attr' => 'cateid,num,order,name', 'close' => 1], 
	'taglist' => ['attr' => 'num,order,name', 'close' => 1], 
	'flist' => ['attr' => 'num,order,name', 'close' => 1]
	];
	
    public function tagCategory($tag, $content)
    {
        $pid = isset($tag['pid']) ? $tag['pid'] : 0;
        $num = $tag['num'];
		$order = isset($tag['order']) ? $tag['order'] : 'catid desc';
        $parseStr = $parseStr = '<?php ';
        $parseStr .= '$__CATEGORY__ = db(\'category\')->where(\'pid\',' . $pid . ')->where(\'ishidden\',0)->order("' . $order . '")->limit(' . $num . ')->select();';
        $parseStr .= '?>{volist name="__CATEGORY__" id="' . $tag['name'] . '"}';
        $parseStr .= $content;
        $parseStr .= '{/volist}';
        //解析模板
        $this->tpl->parse($parseStr);
        return $parseStr;
    }
    public function tagArclist($tag, $content)
    {
        $num = $tag['num'];
        $order = isset($tag['order']) ? $tag['order'] : 'id desc';
        $where = 'status=0';
        if (isset($tag['catid']) && intval($tag['catid'])) {
            $where .= ' and catid in (' . catid_str($tag['catid']) . ')';
        }
        if (isset($tag['thumb']) && intval($tag['thumb'])) {
            $where .= " and thumb != ''";
        }
        $parseStr = $parseStr = '<?php ';
        $parseStr .= '$__LIST__ = db(\'article\')->where("' . $where . '")->order("' . $order . '")->limit(' . $num . ')->select();';
        $parseStr .= '?>{volist name="__LIST__" id="' . $tag['name'] . '"}';
        $parseStr .= $content;
        $parseStr .= '{/volist}';
        //解析模板
        $this->tpl->parse($parseStr);
        return $parseStr;
    }
    public function tagTaglist($tag, $content)
    {
        $num = $tag['num'];
		$order = isset($tag['order']) ? $tag['order'] : 'tagid desc';
        $parseStr = $parseStr = '<?php ';
        $parseStr .= '$__LIST__ = db(\'tag\')->order("' . $order . '")->limit(' . $num . ')->select();';
        $parseStr .= '?>{volist name="__LIST__" id="' . $tag['name'] . '"}';
        $parseStr .= $content;
        $parseStr .= '{/volist}';
        //解析模板
        $this->tpl->parse($parseStr);
        return $parseStr;
    }
    public function tagFlist($tag, $content)
    {
        $num = $tag['num'];
		$order = isset($tag['order']) ? $tag['order'] : 'id desc';
		if (isset($tag['logo']) && intval($tag['logo'])) {
            $where = "logo != ''";
        }else{
			$where = "logo  = ''";
			
			}
        $parseStr = $parseStr = '<?php ';
        $parseStr .= '$__LIST__ = db(\'flink\')->where("' . $where . '")->order("' . $order . '")->limit(' . $num . ')->select();';
        $parseStr .= '?>{volist name="__LIST__" id="' . $tag['name'] . '"}';
        $parseStr .= $content;
        $parseStr .= '{/volist}';
        //解析模板
        $this->tpl->parse($parseStr);
        return $parseStr;
    }
}