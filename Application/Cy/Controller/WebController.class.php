<?php
/**
 * Created by Sublime.
 * User: XSM
 * Date: 2017/8/28
 * Time: 15:45
 *
 * 游戏官网生成控制器
 */

namespace Cy\Controller;
use \Think\controller;
class WebController extends controller
{
    protected $website = null;     //游戏官网域名地址
    protected $home    = null;     //官网信息
    protected $type    = null;     //是否批量静态文章
    protected $os      = null;     //H5官网标识
    protected $template_path = ''; //模板路径
    
    
    public function __construct(){
        parent::__construct();
        $this->type = I('type');
        $this->os   = I('os');
        if($this->type != 'batchStatic'){
            $this->abbr    = I('abbr','','trim');
            empty($this->abbr) && $this->error('官网缩写错误');
            $this->home = D('Website')->commonQuery('home',array('abbr'=>$this->abbr));

            //兼容web和H5
            if($this->os == 'wap'){
                $this->template_path = 'Wap/'.$this->abbr.'/'.strtolower(ACTION_NAME);
                $this->website = "http://fx.chuangyunet.net/{$this->abbr}/wap/";
            }else{
                $this->template_path = CONTROLLER_NAME.'/'.$this->abbr.'/'.strtolower(ACTION_NAME);
                $this->website = "http://fx.chuangyunet.net/{$this->abbr}/";
            }
        }
    }

    //官网数据
    protected function homeData()
    {
        //分配游戏和官网信息
        if($this->os == 'wap'){
            $static_url = 'https://img.chuangyunet.net'.C('TMPL_PARSE_STRING.__WAP__').'/'.$this->abbr;
            $header  = 'Wap/'.$this->abbr.'/header';
        }else{
            $static_url = 'https://img.chuangyunet.net'.C('TMPL_PARSE_STRING.__WEB__').'/'.$this->abbr;
            $header  = 'Web/'.$this->abbr.'/header';
        }
        //获取短连
        $shortLink = curl_get('http://api.t.sina.com.cn/short_url/shorten.json?source=3271760578&url_long=http://apisdk.chuangyunet.net/Api/Ajax/qrcodeLink.html?abbr='.$this->abbr);
        $this->assign('url_short',json_decode($shortLink,true)[0]['url_short']);
        $this->assign('website',$this->website);
        $this->assign('abbr',$this->abbr);
        $this->assign('static_url',$static_url);
        $this->assign('img_url','https://img.chuangyunet.net');
        $this->assign('info',$this->home);
        $this->assign('header',$this->fetch($header)); //头部公用文件
        if(I('get.cache')) $this->staticHtml();

    }

    //官网咨询文章填充
    protected function paddingData()
    {
        $table    = 'article';
        $map      = array('status'=>1,'home_id'=>$this->home['id']);
        $start    = 0;
        $pageSize = strtolower(ACTION_NAME) == 'zixun' ? 30 : 5;

        //最新
        $this->assign('zuixin',D('Website')->commonQuery('article', array_merge($map,array('column_id'=>array('IN',array(4,3,5,6)))), $start, $pageSize, 'id,column_id,home_id,title,title2,createTime,releaseTime,CONCAT("'.$this->website.'",column_id) AS link'.'', C('DB_PREFIX_WEBSITE'), 'createTime DESC'));

        //新闻
        $this->assign('xinwen',D('Website')->commonQuery('article', array_merge($map,array('column_id'=>4)), $start, $pageSize, 'id,column_id,home_id,title,title2,createTime,releaseTime,CONCAT("'.$this->website.'",column_id) AS link'.'', C('DB_PREFIX_WEBSITE'), 'createTime DESC'));
        
        //公告
        $this->assign('gonggao',D('Website')->commonQuery('article', array_merge($map,array('column_id'=>3)), $start, $pageSize, 'id,column_id,home_id,title,title2,createTime,releaseTime,CONCAT("'.$this->website.'",column_id) AS link'.'', C('DB_PREFIX_WEBSITE'), 'createTime DESC'));
        
        //活动
        $this->assign('huodong',D('Website')->commonQuery('article', array_merge($map,array('column_id'=>5)), $start, $pageSize, 'id,column_id,home_id,title,title2,createTime,releaseTime,CONCAT("'.$this->website.'",column_id) AS link'.'', C('DB_PREFIX_WEBSITE'), 'createTime DESC'));
        
        //攻略
        $this->assign('gonglve',D('Website')->commonQuery('article', array_merge($map,array('column_id'=>6)), $start, $pageSize, 'id,column_id,home_id,title,title2,createTime,releaseTime,CONCAT("'.$this->website.'",column_id) AS link'.'', C('DB_PREFIX_WEBSITE'), 'createTime DESC'));
        
        //资料
        $this->assign('ziliao',D('Website')->commonQuery('article', array_merge($map,array('column_id'=>7)), $start, $pageSize, 'id,column_id,home_id,title,title2,createTime,releaseTime,CONCAT("'.$this->website.'",column_id) AS link'.'', C('DB_PREFIX_WEBSITE'), 'createTime DESC'));
        
        //常见问题
        $this->assign('cjwt',D('Website')->commonQuery('article', array_merge($map,array('column_id'=>8)), $start, $pageSize, 'id,column_id,home_id,title,title2,createTime,releaseTime,CONCAT("'.$this->website.'",column_id) AS link'.'', C('DB_PREFIX_WEBSITE'), 'createTime DESC'));
        
        //媒体
        $this->assign('meiti',D('Website')->commonQuery('article', array_merge($map,array('column_id'=>9)), $start, $pageSize, 'id,column_id,home_id,title,title2,createTime,releaseTime,CONCAT("'.$this->website.'",column_id) AS link'.'', C('DB_PREFIX_WEBSITE'), 'createTime DESC'));

        //官网数据
        $this->homeData();

    }

    /**
     * 生成静态页
     */

    protected function staticHtml(){
        $action = strtolower(ACTION_NAME);
        $os = I('get.os');
        if($os == 'wap'){
            $base_path = './Website/Game/'.$this->home['abbr'].'/wap';
        }else{
            $base_path = './Website/Game/'.$this->home['abbr'];
        }
        $filename = 'index.html';
        if($action == 'read'){
            $id = I('get.id', '0', 'intval');
            $read = $this->get('read');
            $column_id = $read['column_id'];
            $base_path .= '/'.$column_id.'/';
            $filename  = $id.'.html';
        }elseif($action == 'index'){
            $base_path .= '/';
        }else{
            $base_path .= '/'.$action.'/';
        }

        if($action == 'read' || $action == 'index'){//同时生成缓存
            $urls = I('server.HTTP_HOST').__SELF__; //lgame.com/Admin/Web/read.html?abbr=qyj&id=2&cache=1
            if($action == 'read'){
                curl_get(str_replace('read', 'index', $urls));

                $urls .= $urls.'&os=wap';
                curl_get($urls);
                curl_get(str_replace('read', 'index', $urls));
            }
            
        }
        !is_dir(dirname($base_path)) && @mkdir(dirname($base_path), 0777, true);
        $this->buildHtml($filename, $base_path, $this->template_path);
        
    }


    /*==========================================================================模板展示=============================================================================*/

    //首页
    public function index()
    {
        $this->paddingData();
        $this->display($this->template_path);
    }

    //咨询列表页
    public function zixun()
    {
        $this->paddingData();
        $this->display($this->template_path);
    }

    //下载列表页
    public function xiazai()
    {
        $this->paddingData();
        $this->display($this->template_path);
    }

    //咨询详情页
    public function read()
    {
        $preview = I('get.preview','0', 'intval');
        $map     = array('id'=>I('get.id', '0', 'intval'));
        !$preview && $map['status'] = 1;
        if($this->type == 'batchStatic'){
            //http://lgame.com/Admin/Web/read.html?abbr=qyj&id=3&cache=1
            $ids = I('id');
            foreach ($ids as $key => $value) {
                $id_list = explode('_', $value);
                curl_get("http://{$_SERVER['HTTP_HOST']}/Admin/Web/read.html?abbr={$id_list[1]}&id={$id_list[0]}&cache=1");
            }
            $this->ajaxReturn(array('status'=>1,'info'=>'批量静态化成功！'));
        }
        $read = D('Website')->commonQuery('article',array($map));
        
        if($read){
            $next_last = D('Website')->getArticle($this->home['id'],$read['column_id'],$read['id'],$this->website);
            $this->assign('next_last', $next_last);
        }

        $column_name = getDataList('column','id',C('DB_PREFIX_WEBSITE'),array(),'WEBSITE')[$read['column_id']]['columnName'];
        $this->assign('read', $read);
        $this->assign('column_name', $column_name);
        $this->paddingData();
        if(I('get.cache') && IS_AJAX){
            $this->ajaxReturn(array('status'=>1,'info'=>'静态化成功！'));
        }else{
            $this->display($this->template_path);
        }
    }

}
