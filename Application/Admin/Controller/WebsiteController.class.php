<?php
/**
 * Created by PhpStorm.
 * User: PJL
 * Date: 2017/8/16
 * Time: 11:56
 *
 * 平台管理控制器
 */

namespace Admin\Controller;

class WebsiteController extends BackendController
{
    protected $gameType; //游戏分类
    protected $departmentId = null; //部门ID
    public function __construct()
    {
        parent::__construct();
        $this->gameType = array(
            '网络游戏',
            '角色扮演',
            '卡牌游戏',
            '策略战争',
            '单机游戏',
        );
        $this->departmentId = session('admin.partment');
    }

    /**
     * 官网设置
     */
    public function home()
    {
        if (IS_POST) {
            $data                                       = I();
            $start                                      = $data['start'] ? $data['start'] : 0;
            $pageSize                                   = $data['limit'] ? $data['limit'] : 30;
            $this->departmentId && $map['departmentId'] = $this->departmentId;
            $data['abbr'] && $map['abbr']               = $data['abbr'];
            $data['id'] && $map['id']               = $data['id'];
            $count                                      = D("Website")->commonCount("home", $map);
            $res                                        = D("Website")->commonQuery("home", $map, $start, $pageSize);
            foreach ($res as $key => $val) {
                $agent                  = D("Admin")->commonQuery("agent", array("agent" => $val["agent"]), 0, 1, "agentName", "lg_");
                $res[$key]['name']      = '<a href="https://game.cmgcwl.cn/' . $val['abbr'] . '/" target="_blank">' . $val['name'] . '</a>';
                $res[$key]['agentName'] = $agent["agentName"] . "（" . $val["agent"] . "）";
                $res[$key]['create']    = date('Y-m-d H:i:s', $val['createTime']);
                $res[$key]['opt']       = createBtn('<a href="javascript:;" onclick="homeEdit(' . $val['id'] . ',this)">编辑</a> | <a href="' . U('Web/Index/', array('abbr' => $val['abbr'])) . '" target="_blank" onmouseover="showQR(\'' . 'https://game.cmgcwl.cn/' . $val['abbr'] . '/wap' . '\',this)" onmouseout="hideQR()">预览</a>');
            }
            $arr = array('rows' => $res, 'results' => $count);
            exit(json_encode($arr));
        } else {
            $this->display();
        }
    }

    /**
     * 添加官网
     */
    public function homeAdd()
    {
        if (IS_POST) {
            $data = I();
            if (!$data["name"] || !$data["abbr"]) {
                $this->error("名称或缩写未填写！");
            }
            if (D("Website")->commonQuery("home", array("abbr" => $data["abbr"]), 0, 1, "name")) {
                $this->error("名称缩写已经存在！");
            }

            if (file_exists(APP_PATH . 'Admin/View/Web/' . $data['abbr']) || file_exists(APP_PATH . 'Admin/View/Wap/' . $data['abbr'])) {
                $this->error("名称缩写已经存在（模板页面）！");
            }

            if (file_exists('./Website/Game/'.$data['abbr'])) {
                $this->error("名称缩写已经存在（静态页面）！");
            }

            if (D("Website")->commonQuery("home", array("agent" => $data["agent"]), 0, 1, "agent")) {
                $this->error("该母包已经有官网！");
            }
            $data["createTime"] = $data["updateTime"] = time();
            if ($data["agent"]) {
                $agent           = D("Admin")->commonQuery("agent", array("agent" => $data["agent"]), 0, 1, "agentName,game_id", "lg_");
                $data["game_id"] = $agent["game_id"];
            }
            $res = D("Website")->commonAdd("home", $data);
            if ($res) {
                $files = file_upload($res, false, "website/qrcode/{$data['game_id']}/{$data['abbr']}");
                if ($files) {
                    foreach ($files as $key => $value) {
                        $value['key'] == 'iosImg' && $insert['iosImg']         = '/Uploads/' . $value['savepath'] . $value['savename'];
                        $value['key'] == 'androidImg' && $insert['androidImg'] = '/Uploads/' . $value['savepath'] . $value['savename'];
                        $value['key'] == 'gameLogo' && $insert['gameLogo']     = '/Uploads/' . $value['savepath'] . $value['savename'];
                        $value['key'] == 'concern' && $insert['concern']       = '/Uploads/' . $value['savepath'] . $value['savename'];
                        $value['key'] == 'bgCenter' && $insert['bgCenter']     = '/Uploads/' . $value['savepath'] . $value['savename'];
                        $value['key'] == 'bgSide' && $insert['bgSide']         = '/Uploads/' . $value['savepath'] . $value['savename'];
                        $value['key'] == 'phoneBg' && $insert['phoneBg']       = '/Uploads/' . $value['savepath'] . $value['savename'];

                    }
                }
                D("Website")->commonExecute("home", array("id" => $res), $insert);
                bgLog(4, session("admin.realname") . "添加了官网：官网名称为“{$data['name']}”，名称缩写为“{$data['abbr']}”" . ($data["agent"] ? "，渠道号为“{$data['agent']}”，游戏包为“{$agent['agentName']}”" : ""));
                //复制资源
                $this->copyWebSite($data["abbr"], $data['module']);
                $this->success("操作成功");
            } else {
                $this->error("操作失败");
            }
        } else {
            $this->assign('gameType', $this->gameType);
            $this->ajaxReturn(array("status" => 1, "_html" => $this->fetch()));
        }
    }

    /**
     * 编辑官网
     */
    public function homeEdit()
    {
        if (IS_POST) {
            $data = I();
            if (!$data["id"]) {
                $this->error("数据异常！");
            }
            if (!$data["name"]) {
                $this->error("名称或缩写未填写！");
            }
            unset($data['abbr']);
            $data["updateTime"] = time();
            if ($data["agent"]) {
                $agent           = D("Admin")->commonQuery("agent", array("agent" => $data["agent"]), 0, 1, "agentName,game_id", "lg_");
                $data["game_id"] = $agent["game_id"];
            }
            $home = D("Website")->commonQuery("home", array("id" => $data["id"]));

            $files = file_upload($data['id'], false, "website/qrcode/{$data['game_id']}/{$home['abbr']}");
            if ($files) {
                foreach ($files as $key => $value) {
                    $value['key'] == 'iosImg' && $data['iosImg']         = '/Uploads/' . $value['savepath'] . $value['savename'];
                    $value['key'] == 'androidImg' && $data['androidImg'] = '/Uploads/' . $value['savepath'] . $value['savename'];
                    $value['key'] == 'gameLogo' && $data['gameLogo']     = '/Uploads/' . $value['savepath'] . $value['savename'];
                    $value['key'] == 'concern' && $data['concern']       = '/Uploads/' . $value['savepath'] . $value['savename'];
                    $value['key'] == 'bgCenter' && $data['bgCenter']     = '/Uploads/' . $value['savepath'] . $value['savename'];
                    $value['key'] == 'bgSide' && $data['bgSide']         = '/Uploads/' . $value['savepath'] . $value['savename'];
                    $value['key'] == 'phoneBg' && $data['phoneBg']       = '/Uploads/' . $value['savepath'] . $value['savename'];
                }
            }
            $res = D("Website")->commonExecute("home", array("id" => $data["id"]), $data);
            if ($res) {
                $str = "";
                foreach ($data as $k => $v) {
                    if ($v == $home[$k]) {
                        continue;
                    }

                    if ($k == "name") {
                        $str .= "，官网名称由“{$home[$k]}”改为“{$v}”";
                    }

                    if ($k == "abbr") {
                        $str .= "，名称缩写由“{$home[$k]}”改为“{$v}”";
                    }

                    if ($k == "agent") {
                        $str .= "，渠道号由“{$home[$k]}”改为“{$v}”";
                    }

                }
                bgLog(3, session("admin.realname") . "修改了游戏官网“{$home['name']}”：" . trim($str, "，"));
                $this->success("修改成功");
            } else {
                $this->error("修改失败");
            }
        } else {
            $id   = I("id");
            $info = D("Website")->commonQuery("home", array("id" => $id));
            $this->assign('gameType', $this->gameType);
            $this->assign("info", $info);
            $this->ajaxReturn(array("status" => 1, "_html" => $this->fetch()));
        }
    }

    /**
     * 资讯列表
     */
    public function article()
    {
        if (IS_POST) {
            $data                               = I();
            $start                              = $data['start'] ? $data['start'] : 0;
            $pageSize                           = $data['limit'] ? $data['limit'] : 30;
            $data['id'] && $map['id']           = $data['id'];
            $data['home_id'] && $map['home_id'] = $data['home_id'];
            $data['title'] && $map['title']     = array('LIKE', '%' . $data['title'] . '%');
            if ($this->departmentId) {
                $home_ids       = getDataList('home', 'id', C('DB_PREFIX_WEBSITE'), array('departmentId' => $this->departmentId), 'WEBSITE');
                $map['home_id'] = array('IN', array_keys($home_ids));
            }

            $count = D("Website")->commonCount("article", $map);
            $res   = D("Website")->commonQuery("article", $map, $start, $pageSize, "*", '', 'createTime DESC');
            foreach ($res as $key => $val) {
                $home                    = D("Website")->commonQuery("home", array("id" => $val["home_id"]), 0, 1, "name,game_id,abbr");
                $column                  = D("Website")->commonQuery("column", array("id" => $val["column_id"]), 0, 1, "columnName");
                $res[$key]['homeName']   = $home["name"];
                $res[$key]['abbr']       = $home['abbr'];
                $res[$key]['columnName'] = $column["columnName"];
                $res[$key]['release']    = date('Y-m-d H:i:s', $val['releaseTime']);
                $res[$key]['opt']        = createBtn('<a href="' . U("Website/articleEdit", array("id" => $val["id"])) . '" title="资讯编辑" class="page-action" data-type="setTitle">编辑</a> | <a href="' . U("Web/read", array('abbr' => $home['abbr'], 'preview' => 1, 'id' => $val['id'])) . '" title="预览" target="_blank" class="page-action" data-type="setTitle">预览</a> <a href="javascript:;" title="静态" onclick=statis_html("' . $home['abbr'] . '",' . $val['id'] . ') class="page-action" data-type="setTitle">静态</a> | <a href="javascript:;" title="删除" onclick=articleDelete(' . $val['id'] . ') class="page-action" data-type="setTitle">删除</a>');
            }

            $arr = array('rows' => $res, 'results' => $count);
            exit(json_encode($arr));
        } else {
            $this->display();
        }
    }

    /**
     * 添加资讯
     */
    public function articleAdd()
    {
        if (IS_POST) {
            $data = I();
            if (!$data["home_id"] || !$data["column_id"] || !$data["title"]) {
                $this->error("官网、栏目或标题未选择或未填写！");
            }

            $data["content"]     = preg_replace('/src="\/static\/admin\/js\/ueditor\/php\/upload/', 'src="https://img.chuangyunet.net/static/admin/js/ueditor/php/upload', $data['content']);
            $data["author"]      = session("admin.realname");
            $data["createTime"]  = $data["updateTime"]  = time();
            $data["releaseTime"] = $data["releaseTime"] ? strtotime($data["releaseTime"]) : time();
            $res                 = D("Website")->commonAdd("article", $data);
            if ($res) {
                $home   = D("Website")->commonQuery("home", array("id" => $data["home_id"]), 0, 1, "name");
                $column = D("Website")->commonQuery("column", array("id" => $data["column_id"]), 0, 1, "columnName");
                bgLog(4, session("admin.realname") . "添加了资讯：官网名称为“{$home['name']}”，栏目名称为“{$column['columnName']}”，资讯名称为“{$data['title']}”");
                $this->success("操作成功", U('article'));
            } else {
                $this->error("操作失败");
            }
        } else {
            $column = D('Website')->commonQuery("column", array(), 0, 999, "id,pid,columnName");

            $tree = new \Vendor\Tree\Tree();
            $str  = "<option value='\$id' \$selected>\$spacer \$columnName</option>";
            $tree->init($column);
            $select_menus = $tree->get_tree(0, $str);

            $this->assign("column", $select_menus);
            $this->display();
        }
    }

    /**
     * 编辑资讯
     */
    public function articleEdit()
    {
        if (IS_POST) {
            $data = I();
            if (!$data["id"] || !$data["column_id"] || !$data["title"]) {
                $this->error("栏目或标题未选择或未填写！");
            }
            $data["content"] = preg_replace('/src="\/static\/admin\/js\/ueditor\/php\/upload/', 'src="https://img.chuangyunet.net/static/admin/js/ueditor/php/upload', $data['content']);

            $data["updateTime"]  = time();
            $data["releaseTime"] = $data["releaseTime"] ? strtotime($data["releaseTime"]) : time();
            $article             = D("Website")->commonQuery("article", array("id" => $data["id"]));
            $res                 = D("Website")->commonExecute("article", array("id" => $data["id"]), $data);

            if ($res) {
                bgLog(3, session("admin.realname") . "修改了官网资讯“{$article['title']}”");
                $this->success("修改成功", U("Website/article"));
            } else {
                $this->error("修改失败");
            }
        } else {

            $id      = I('id', 0, 'intval');
            $article = D("Website")->commonQuery("article", array("id" => $id));
            $home    = D("Website")->commonQuery("home", array("id" => $article["home_id"]));
            $column  = D('Website')->commonQuery("column", array(), 0, 9999, "id,pid,columnName");

            $tree = new \Vendor\Tree\Tree();
            foreach ($column as $r) {
                $r['selected'] = $r['id'] == $article['column_id'] ? 'selected' : '';
                $array[]       = $r;
            }
            $str = "<option value='\$id' \$selected>\$spacer \$columnName</option>";
            $tree->init($array);
            $columns = $tree->get_tree(0, $str);

            $this->assign("home", $home);
            $this->assign("info", $article);
            $this->assign("column", $columns);
            $this->display();
        }
    }

    /**
     * 删除资讯
     */
    public function articleDelete()
    {
        $id = I('id', 0, 'intval');
        !$id && $this->error('操作失败');
        if (D('Website')->commonDelete('article', array('id' => $id))) {
            $this->ajaxReturn(array('status' => 1, 'info' => '操作成功'));
        } else {
            $this->ajaxReturn(array('status' => 0, 'info' => '操作失败'));
        }
    }

    /**
     * 栏目列表
     */
    public function columnList()
    {
        $this->display();
    }

    /**
     * 栏目添加
     */
    public function columnAdd()
    {
        if (IS_POST) {
            $data = I();
            if (!$data) {
                $this->error('请检查必填参数是否为空', U('Website/columnList'));
            }
            if ($data['pid'] == 0) {
                $success = '一级栏目添加成功';
                $error   = '一级栏目添加失败';
            } else {
                $success = '子栏目添加成功';
                $error   = '子栏目添加失败';
            }
            $data['createTime'] = time();
            if (D('Website')->commonAdd('column', $data)) {
                bgLog(4, session('admin.username') . '添加了栏目，参数：' . http_build_query($data));
                $this->success($success, U('Website/columnList'));
            } else {
                $this->error($error, U('Website/columnList'));
            }
        } else {
            $pid       = I('pid', 0, 'intval');
            $menu_list = D('Website')->commonQuery("column", array(), 0, 99999, "id,pid,columnName");

            $tree = new \Vendor\Tree\Tree();
            foreach ($menu_list as $r) {
                $r['selected'] = $r['id'] == $pid ? 'selected' : '';
                $array[]       = $r;
            }
            $str = "<option value='\$id' \$selected>\$spacer \$columnName</option>";
            $tree->init($array);
            $select_menus = $tree->get_tree(0, $str);
            $this->assign('select_menus', $select_menus);

            if (IS_AJAX) {
                $respose = $this->fetch();
                $this->ajaxReturn(array('status' => 0, '_html' => $respose));
            } else {
                $this->display();
            }
        }
    }

    /**
     * 栏目编辑
     */
    public function columnEdit()
    {
        if (IS_POST) {
            $data = I();
            if (!$data['id']) {
                $this->error('参数有误', U('Website/columnList'));
            }
            if (D('Website')->commonExecute('column', array('id' => $data['id']), $data)) {
                bgLog(3, session('admin.username') . '修改了栏目，修改的参数:' . http_build_query($data));
                $this->success('修改成功', U('Website/columnList'));
            } else {
                $this->error('修改失败', U('Website/columnList'));
            }
        } else {
            $id        = I('id', 0, 'intval');
            $menu_info = D('Website')->commonQuery('column', array('id' => $id));
            $menu_list = D('Website')->commonQuery("column", array(), 0, 99999, "id,pid,columnName");

            $tree = new \Vendor\Tree\Tree();
            foreach ($menu_list as $r) {
                $r['selected'] = $r['id'] == $menu_info['pid'] ? 'selected' : '';
                $array[]       = $r;
            }
            $str = "<option value='\$id' \$selected>\$spacer \$columnName</option>";
            $tree->init($array);
            $select_menus = $tree->get_tree(0, $str);
            $this->assign('info', $menu_info);
            $this->assign('select_menus', $select_menus);

            if (IS_AJAX) {
                $respose = $this->fetch();
                $this->ajaxReturn(array('status' => 0, '_html' => $respose));
            } else {
                $this->display();
            }
        }
    }

    /**
     * 栏目删除
     */
    public function columnDelete()
    {
        $id = I('id', 0, 'intval');
        if (!$id) {
            $this->error('参数有误');
        }
        is_int($id) && $id = array($id);
        if (D('Website')->commonDelete('column', array('id' => array('in', $id)))) {
            bgLog(2, session('admin.username') . '删除了栏目，参数id：' . http_build_query($id));
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

    /**
     * 幻灯片列表
     */
    public function slideList()
    {
        if (IS_POST) {
            $data                                   = I();
            $start                                  = $data['start'] ? $data['start'] : 0;
            $pageSize                               = $data['limit'] ? $data['limit'] : 30;
            $data['id'] && $map['id']               = $data['id'];
            $data['slideType'] && $map['slideType'] = $data['slideType'];
            $data['slideName'] && $map['slideName'] = array('LIKE', '%' . $data['slideName'] . '%');
            $map2 = [];
            $data['homeid'] && $map2['id'] = $data['homeid'];
            $this->departmentId && $map2['departmentId'] = $this->departmentId;
            if ($map2) {
                $agents       = getDataList('home', 'agent', C('DB_PREFIX_WEBSITE'), $map2, 'WEBSITE');
                $map['agent'] = array('IN', array_keys($agents));
            }

            $count     = D("Website")->commonCount("slide", $map);
            $res       = D("Website")->commonQuery("slide", $map, $start, $pageSize);
            $column    = getDataList('column', 'id', C('DB_PREFIX_WEBSITE'), array(), 'WEBSITE');
            $status    = array('<span style="color:green">正常</span>', '<span style="color:red">关闭</span>');
            $slideType = array(1 => 'PC端', '2' => 'H5端');
            foreach ($res as $key => $val) {
                $res[$key]['slideType']  = $slideType[$val['slideType']];
                $res[$key]['status']     = $status[$val['status']];
                $res[$key]['columnName'] = $column[$val['columnId']]['columnName'];
                $res[$key]['opt']        = createBtn('<a href="' . U("Website/slidePic", array('id' => $val['id'])) . '" >图片管理</a> | <a href="javascript:;" onclick="slideEdit(' . $val['id'] . ',this)">编辑</a> | <a href="javascript:;" onclick="slideDelete(' . $val['id'] . ',this)">删除</a>');
            }
            $arr = array('rows' => $res, 'results' => $count);
            exit(json_encode($arr));
        } else {
            $this->display();
        }
    }

    /**
     * 添加幻灯片
     */
    public function slideAdd()
    {
        if (IS_POST) {
            $data = I();
            if (!$data["slideName"]) {
                $this->error("幻灯片名称未填写！");
            }
            if (D("Website")->commonQuery("slide", array("slideName" => $data["slideName"]), 0, 1, "slideName")) {
                $this->error("幻灯片名称已经存在！");
            }
            if ($data['templateId'] == 7 && $data['columnId'] == 14) { //游戏H5游戏截图
                $data['slideOrder'] = 1;
            } elseif ($data['templateId'] == 8 && $data['columnId'] == 14) { //游戏H5职业介绍
                $data['slideOrder'] = 0;
            } elseif ($data['templateId'] == 4 && $data['columnId'] == 13) { //游戏web首页头部幻灯片
                $data['slideOrder'] = 0;
            } elseif ($data['templateId'] == 5 && $data['columnId'] == 13) { //游戏web首页中部职业介绍
                $data['slideOrder'] = 1;
            } elseif ($data['templateId'] == 6 && $data['columnId'] == 13) { //游戏web游戏截图
                $data['slideOrder'] = 2;
            } elseif ($data['templateId'] == 10 && $data['columnId'] == 13) { //真龙截图幻灯片
                $data['slideOrder'] = 3;
            }
            $data["createTime"] = time();
            $res                = D("Website")->commonAdd("slide", $data);
            if ($res) {
                bgLog(4, session("admin.realname") . "添加了幻灯片：幻灯片名称为“{$data['slideName']}”" . ($data["agent"] ? "，渠道号为“{$data['agent']}”" : ""));
                $this->success("操作成功");
            } else {
                $this->error("操作失败");
            }
        } else {
            $tpl    = D('Website')->commonQuery("slide_template", array(), 0, 999, "*");
            $column = D('Website')->commonQuery("column", array('_string' => "(pid = 12 or id = 12)"), 0, 999, "id,pid,columnName");

            $tree = new \Vendor\Tree\Tree();
            $str  = "<option value='\$id' \$selected>\$spacer \$columnName</option>";
            $tree->init($column);
            $select_menus = $tree->get_tree(0, $str);

            $this->assign('tpls', $tpl);
            $this->assign('columns', $select_menus);
            $this->ajaxReturn(array("status" => 1, "_html" => $this->fetch()));
        }
    }

    /**
     * 编辑幻灯片
     */
    public function slideEdit()
    {
        if (IS_POST) {
            $data = I();
            if (!$data["slideName"]) {
                $this->error("幻灯片名称未填写！");
            }
            if (D("Website")->commonQuery("slide", array("slideName" => $data["slideName"], "id" => array("NEQ", $data["id"])), 0, 1, "slideName")) {
                $this->error("幻灯片名称已经存在！");
            }

            if ($data['templateId'] == 7 && $data['columnId'] == 14) {
                $data['slideOrder'] = 1;
            } elseif ($data['templateId'] == 8 && $data['columnId'] == 14) {
                $data['slideOrder'] = 0;
            } elseif ($data['templateId'] == 4 && $data['columnId'] == 13) {
                $data['slideOrder'] = 0;
            } elseif ($data['templateId'] == 5 && $data['columnId'] == 13) {
                $data['slideOrder'] = 1;
            } elseif ($data['templateId'] == 6 && $data['columnId'] == 13) {
                $data['slideOrder'] = 2;
            } elseif ($data['templateId'] == 10 && $data['columnId'] == 13) {
                $data['slideOrder'] = 3;
            }

            $res = D("Website")->commonExecute("slide", array('id' => $data['id']), $data);
            if ($res) {
                bgLog(3, session("admin.realname") . "编辑了幻灯片：幻灯片名称为“{$data['slideName']}”" . ($data["agent"] ? "，渠道号为“{$data['agent']}”" : ""));
                $this->success("操作成功");
            } else {
                $this->error("操作失败");
            }
        } else {
            $id     = I('id');
            $slide  = D('Website')->commonQuery('slide', array('id' => $id));
            $tpl    = D('Website')->commonQuery("slide_template", array(), 0, 999, "*");
            $column = D('Website')->commonQuery("column", array('_string' => "(pid = 12 or id = 12)"), 0, 999, "id,pid,columnName");

            foreach ($column as $r) {
                $r['selected'] = $r['id'] == $slide['columnId'] ? 'selected' : '';
                $array[]       = $r;
            }

            $tree = new \Vendor\Tree\Tree();
            $str  = "<option value='\$id' \$selected>\$spacer \$columnName</option>";
            $tree->init($array);
            $select_menus = $tree->get_tree(0, $str);

            $this->assign('tpls', $tpl);
            $this->assign('columns', $select_menus);
            $this->assign('info', $slide);
            $this->ajaxReturn(array("status" => 1, "_html" => $this->fetch()));
        }
    }

    /**
     * 编辑幻灯片
     */
    public function slideDelete()
    {
        if (IS_POST) {
            $id = I('id', 0, 'intval');
            empty($id) && $this->ajaxReturn(array('status' => 0, 'info' => '参数有误'));
            if (D('Website')->commonDelete('slide', array('id' => $id))) {
                //删除幻灯片对应的图片记录
                D('Website')->commonDelete('slide_picture', array('slideId' => $id));
                $this->ajaxReturn(array('status' => 1, 'info' => '操作成功'));
            } else {
                $this->ajaxReturn(array('status' => 0, 'info' => '操作失败'));
            }
        } else {
            $this->ajaxReturn(array('status' => 0, 'info' => '操作失败'));
        }
    }

    /**
     * 幻灯片图片
     */
    public function slidePic()
    {
        if (IS_POST) {
            $data           = I();
            $map['slideId'] = I('get.id');
            $start          = $data['start'];
            $pageSize       = $data['pageSize'];
            $count          = D("Website")->commonCount("slide_picture", $map);
            $res            = D("Website")->commonQuery("slide_picture", $map, $start, $pageSize);
            $status         = array('<span style="color:green">显示</span>', '<span style="color:red">停用</span>');
            foreach ($res as $key => $val) {
                $res[$key]['status'] = $status[$val['status']];
                $res[$key]['smlPic'] = "<img src='{$val['smlPic']}' style='max-width:100px;max-height:80px' />";
                $res[$key]['bigPic'] = "<a target='_blank' href='{$val['bigPic']}'>{$val['bigPic']}</a>";
                $res[$key]['opt']    = createBtn('<a href="javascript:;" onclick="picEdit(' . $val['id'] . ',this)">编辑</a> | <a href="javascript:;" onclick="picDelete(' . $val['id'] . ',this)">删除</a>');
            }
            $arr = array('rows' => $res, 'results' => $count);
            exit(json_encode($arr));
        } else {
            $this->display();
        }
    }

    /**
     * 编辑幻灯片图片
     */
    public function picEdit()
    {
        if (IS_POST) {
            $data  = I();
            $files = file_upload($data['id'], false, 'slide');
            if (count($files) > 0 && is_array($files)) {
                count($files['smlPic']) > 0 ? $data['smlPic'] = '/Uploads/' . $files['smlPic']['savepath'] . $files['smlPic']['savename'] : '';
                count($files['bigPic']) > 0 ? $data['bigPic'] = '/Uploads/' . $files['bigPic']['savepath'] . $files['bigPic']['savename'] : '';
            }
            $res = D('Website')->commonExecute('slide_picture', array('id' => $data['id']), $data);
            if ($res) {
                $this->success('操作成功');
            } else {
                $this->error('操作失败');
            }
        } else {
            $id   = I("id");
            $info = D("Website")->commonQuery("slide_picture", array("id" => $id));
            $this->assign("info", $info);
            $this->ajaxReturn(array("status" => 1, "_html" => $this->fetch()));
        }
    }

    /**
     * 添加幻灯片图片
     */
    public function picAdd()
    {
        if (IS_POST) {
            $data = I();
            empty($data) && $this->error('参数不为空');
            $data['createTime'] = time();
            $lastId             = D('Website')->commonAdd('slide_picture', $data);
            if ($lastId) {
                $files = file_upload($lastId, false, 'slide');
                if (count($files) > 0 && is_array($files)) {
                    count($files['smlPic']) > 0 ? $pic['smlPic'] = '/Uploads/' . $files['smlPic']['savepath'] . $files['smlPic']['savename'] : '';
                    count($files['bigPic']) > 0 ? $pic['bigPic'] = '/Uploads/' . $files['bigPic']['savepath'] . $files['bigPic']['savename'] : '';
                }
                $res = D('Website')->commonExecute('slide_picture', array('id' => $lastId), $pic);
            }
            if ($res) {
                $this->success('操作成功');
            } else {
                $this->error('操作失败');
            }
        } else {
            $id   = I("id");
            $info = D("Website")->commonQuery("slide_picture", array("id" => $id));
            $this->assign("info", $info);
            $this->ajaxReturn(array("status" => 1, "_html" => $this->fetch()));
        }
    }

    /**
     * 幻灯片图片删除
     */
    public function picDelete()
    {
        $id = I('id', 0, 'intval');
        !$id && $this->error('操作失败');
        if (D('Website')->commonDelete('slide_picture', array('id' => $id))) {
            $this->success("操作成功");
        } else {
            $this->success('操作失败');
        }
    }

    /**
     * 幻灯片模板列表
     */
    public function slideTemp()
    {
        if (IS_POST) {
            $data                                         = I();
            $start                                        = $data['start'] ? $data['start'] : 0;
            $pageSize                                     = $data['limit'] ? $data['limit'] : 30;
            $data['id'] && $map['id']                     = $data['id'];
            $data['templateName'] && $map['templateName'] = array('LIKE', '%' . $data['templateName'] . '%');
            $count                                        = D("Website")->commonCount("slide_template", $map);
            $res                                          = D("Website")->commonQuery("slide_template", $map, $start, $pageSize);
            foreach ($res as $key => $val) {
                $res[$key]['opt'] = createBtn('<a href="javascript:;" onclick="slideTempEdit(' . $val['id'] . ',this)">编辑</a>');
            }
            $arr = array('rows' => $res, 'results' => $count);
            exit(json_encode($arr));
        } else {
            $this->display();
        }
    }

    /**
     * 编辑幻灯片模板
     */
    public function slideTempEdit()
    {
        if (IS_POST) {
            $data = I();
            if (!$data["templateName"]) {
                $this->error("幻灯片模板名称未填写！");
            }
            if (D("Website")->commonQuery("slide_template", array("templateName" => $data["templateName"], "id" => array("NEQ", $data["id"])), 0, 1, "templateName")) {
                $this->error("幻灯片模板名称已经存在！");
            }
            $res = D("Website")->commonExecute("slide_template", array('id' => $data['id']), $data);
            if ($res) {
                bgLog(3, session("admin.realname") . "编辑了幻灯片模板：幻灯片模板名称为“{$data['templateName']}”");
                $this->success("操作成功");
            } else {
                $this->error("操作失败");
            }
        } else {
            $id   = I('id');
            $info = D('Website')->commonQuery("slide_template", array('id' => $id));

            $this->assign('info', $info);
            $this->ajaxReturn(array("status" => 1, "_html" => $this->fetch()));
        }
    }

    /**
     * 添加幻灯片模板
     */
    public function slideTempAdd()
    {
        if (IS_POST) {
            $data = I();
            if (!$data["templateName"]) {
                $this->error("幻灯片模板名称未填写！");
            }
            if (D("Website")->commonQuery("slide_template", array("templateName" => $data["templateName"]))) {
                $this->error("幻灯片模板名称已经存在！");
            }
            $res = D("Website")->commonAdd("slide_template", $data);
            if ($res) {
                bgLog(3, session("admin.realname") . "添加了幻灯片模板：幻灯片模板名称为“{$data['templateName']}”");
                $this->success("操作成功");
            } else {
                $this->error("操作失败");
            }
        } else {
            $this->ajaxReturn(array("status" => 1, "_html" => $this->fetch()));
        }
    }

    /**
     * 游戏官网静态化
     */
    public function webStatic()
    {
        $col = array(
            'index'       => '首页',
            'yindao'      => '引导',
            'xinwen'      => '新闻',
            'libao'       => '礼包',
            'huodong'     => '活动',
            'meiti'       => '媒体',
            'gonglue'     => '攻略',
            'ziliao'      => '游戏资料',
            'zhiye'       => '职业介绍',
            'shizhuang'   => '超酷时装',
            'wuqi'        => '武器大全',
            'jietu'       => '截图',
            'yuyue'       => '预约活动页',
            'tuisong'     => '推送页',
            'gonggao'     => '公告',
            'retie'       => '热帖',
            'web_list'    => '热帖',
            'list_jietu'  => '游戏截图',
            'list_sp'     => '游戏视频',
            'gamegonglue' => '游戏攻略',
            'gamewanfa'   => '游戏玩法',
            'gamexitong'  => '游戏系统',
            'index_one'   => '第二首页',
            'video_list'  => '视频列表',
            'xiazai'      => '下载页面',
            'zhuanti'     => '专题页面',
            'zixun'       => '资讯',
            'tuijian'     => '推荐',
            'gameHub'     => '游戏中心',
            'cooperate'   => '联系合作',
            'recruit'     => '招贤纳士',

        );
        if (IS_POST) {
            $data    = I();
            $stype   = $data['stype'];
            $home_id = $data['home_id'];
            $webtype = $data['webtype'];
            empty($home_id) && $this->error('请选择官网');

            $info = D("Website")->commonQuery("home", array("id" => $home_id), 0, 1, "abbr");
            $ts   = $stype ? array($stype) : array_keys($col);
            foreach ($ts as $v) {
                if ($webtype == 1) {
                    $urls = U('Web/' . $v, array('abbr' => $info['abbr'], 'cache' => 1), true, true, true);
                } elseif ($webtype == 2) {
                    $urls = U('Web/' . $v, array('abbr' => $info['abbr'], 'cache' => 1, 'os' => 'wap'), true, true, true);
                }
                file_get_contents($urls);
            }
            $this->success('生成成功');
        } else {
            $this->assign('col', $col);
            $this->display();
        }
    }

    /**
     * 复制官网素材
     */
    public function copyWebSite($abbr, $tpl = 'ztfyl')
    {
        $webType = array('Web', 'Wap');
        foreach ($webType as $webType) {

            $tplDir = APP_PATH . 'Admin/View/' . $webType . '/' . $tpl;
            //复制模板
            if (!file_exists(APP_PATH . 'Admin/View/' . $webType . '/' . $abbr)) {
                mkdir(APP_PATH . 'Admin/View/' . $webType . '/' . $abbr, 0755, true);
            }

            $file = scanfiles($tplDir);
            if (count($file) > 0) {
                foreach ($file as $value) {
                    $filename = basename($value);
                    //复制官网模板
                    copy($value, APP_PATH . 'Admin/View/' . $webType . '/' . $abbr . '/' . $filename);
                }

                unset($GLOBALS['arrs']);
                //复制静态资源
                $staticDir = './static/' . strtolower($webType) . '/' . $tpl . '/';

                if (!file_exists('./static/' . strtolower($webType) . '/' . $abbr)) {
                    mkdir('./static/' . strtolower($webType) . '/' . $abbr, 0755, true);
                }

                if (!file_exists('./static/' . strtolower($webType) . '/' . $abbr . '/css')) {
                    mkdir('./static/' . strtolower($webType) . '/' . $abbr . '/css', 0755, true);
                }

                if (!file_exists('./static/' . strtolower($webType) . '/' . $abbr . '/images')) {
                    mkdir('./static/' . strtolower($webType) . '/' . $abbr . '/images', 0755, true);
                }

                if (!file_exists('./static/' . strtolower($webType) . '/' . $abbr . '/js')) {
                    mkdir('./static/' . strtolower($webType) . '/' . $abbr . '/js', 0755, true);
                }

                if ($tpl == 'zltz') {
                    if (!file_exists('./static/' . strtolower($webType) . '/' . $abbr . '/images/bg')) {
                        mkdir('./static/' . strtolower($webType) . '/' . $abbr . '/images/bg', 0755, true);
                    }

                    if (!file_exists('./static/' . strtolower($webType) . '/' . $abbr . '/images/military')) {
                        mkdir('./static/' . strtolower($webType) . '/' . $abbr . '/images/military', 0755, true);
                    }
                }

                if ($tpl == 'jyjh') {
                    if (!file_exists('./static/' . strtolower($webType) . '/' . $abbr . '/videos')) {
                        mkdir('./static/' . strtolower($webType) . '/' . $abbr . '/videos', 0755, true);
                    }
                }

                $dirPath = scandir($staticDir);
                foreach ($dirPath as $dir) {
                    //旧目录
                    if ($dir == '.' || $dir == '..') {
                        continue;
                    }

                    $oldFile = scanfiles($staticDir . $dir);
                    foreach ($oldFile as $oldFileName) {
                        if (strpos($oldFileName, '.html') || strpos($oldFileName, '.svn')) {
                            continue;
                        }

                        $newFileName = basename($oldFileName);
                        if ($tpl == 'zltz') {
                            copy($oldFileName, str_replace('/zltz/', '/'.$abbr.'/', $oldFileName));
                        } else {
                            //复制官网静态资源
                            copy($oldFileName, './static/' . strtolower($webType) . '/' . $abbr . '/' . $dir . '/' . $newFileName);
                        }
                    }
                    unset($GLOBALS['arrs']);
                }
            }
        }
    }
}
