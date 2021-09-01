<?php

namespace app\common\controller;

use app\common\model\AuthGroup;
use app\common\model\AuthRule;
use think\Console;
use think\Db;
use \com\Tree;
use app\common\controller\Home;

class Admin extends Home
{
    protected $url;
    protected $request;
    protected $module;
    protected $controller;
    protected $action;

    public function _initialize()
    {
        parent::_initialize();
        //获取request信息
        $this->requestInfo();
        if (!is_login() and !in_array($this->url, array('admin/login/index', 'admin/login/logout', 'admin/login/verify')) and !in_array($this->model, array('api/timming'))) {
            return $this->redirect('admin/login/index');
        }
        if (!is_file(RUNTIME_PATH . 'conf/database.php') || !is_file(RUNTIME_PATH . 'install/install.lock')) {
            return $this->redirect('install/index/index');
        }
        if (!in_array($this->url, array('admin/login/index', 'admin/login/logout', 'admin/login/verify')) and !in_array($this->model, array('api/timming'))) {
            // 是否是超级管理员
            define('IS_ROOT', is_administrator('1'));
            if (!IS_ROOT && config('admin_allow_ip')) {
                // 检查IP地址访问
                if (!in_array(get_client_ip(), explode(',', config('admin_allow_ip')))) {
                    $this->error('403:禁止访问');
                }
            }
            // 检测系统权限
            if (!IS_ROOT) {
                $access = $this->accessControl();
                if (false === $access) {
                    $this->error('非超级管理禁止操作和访问');
                } elseif (null === $access) {
                    $dynamic = $this->checkDynamic(); //检测分类栏目有关的各项动态权限
                    if ($dynamic === null) {
                        if (!$this->checkRule($this->url . ',' . $this->model, array('in', '1,2'))) {
                            $this->error('管理组未授权访问!');
                        } else {
                            // 检测分类及内容有关的各项动态权限
                            $dynamic = $this->checkDynamic();
                            if (false === $dynamic) {
                                $this->error('管理组未授权访问!');
                            }
                        }
                    } elseif ($dynamic === false) {
                        $this->error('管理组未授权访问!');
                    }
                }
            }
        }
        // 非选项卡时重定向
        if (!$this->request->isPost() && !$this->request->isAjax() && input("ref") == 'addtabs') {
            $url = preg_replace_callback("/([\?|&]+)ref=addtabs(&?)/i", function ($matches) {
                return $matches[2] == '&' ? $matches[1] : '';
            }, $this->request->url());
            $this->redirect('admin/index/index', [], 302, ['referer' => $url]);
            exit;
        }
        $this->config_url = F('_data/url_html_config');
        $this->assign('config', admin_config());
        if (!is_file(RUNTIME_PATH . 'route.php')) {
            Console::call('optimize:route');
        }
        if (!is_file(RUNTIME_PATH . 'classmap.php')) {
            Console::call('optimize:autoload');
        }
        if (!is_file(RUNTIME_PATH . 'init.php')) {
            Console::call('optimize:config');
        }
        if (!is_dir(RUNTIME_PATH . 'schema')) {
            Console::call('optimize:schema');
        }
    }

    protected function setNav()
    {
        $rules = F('_data/rules');
        if (empty($rules)) {
            $list = db('AuthRule')->order('sort asc,id asc')->select();
            if (!empty($list)) {
                $tree = Tree::instance();
                $tree->init($list, 'id', 'pid');
                $list = $tree->getTreeList($tree->getTreeArray(0), 'title');
            }
            F('_data/rules', $list);
        }
        $nav = list_search(F('_data/rules'), 'name=' . $this->url);
        $data = $nav[0];
        if ($data) {
            $this->assign('nav_title', $data);
            $nav_list = getParents($data['id'], TRUE);
        }
        $this->assign('nav_list', $nav_list);
    }

    /**
     * 权限检测
     * @param string $rule 检测的规则
     * @param string $mode check模式
     * @return boolean
     */
    final protected function checkRule($rule, $type = AuthRule::rule_url, $mode = 'url')
    {
        static $Auth = null;
        if (!$Auth) {
            $Auth = new \com\Auth();
        }
        if (!$Auth->check($rule, session('member_auth.uid'), $type, $mode)) {
            return false;
        }
        return true;
    }

    /**
     * 检测是否是需要动态判断的权限
     * @return boolean|null
     *      返回true则表示当前访问有权限
     *      返回false则表示当前访问无权限
     *      返回null，则表示权限不明
     */
    protected function checkDynamic()
    {
        if (IS_ROOT) {
            return true; //管理员允许访问任何页面
        }
        return null; //不明,需checkRule
    }

    /**
     * action访问控制,在 **登陆成功** 后执行的第一项权限检测任务
     *
     * @return boolean|null  返回值必须使用 `===` 进行判断
     *
     *   返回 **false**, 不允许任何人访问(超管除外)
     *   返回 **true**, 允许任何管理员访问,无需执行节点权限检测
     *   返回 **null**, 需要继续执行节点权限检测决定是否允许访问
     */
    final protected function accessControl()
    {
        $allow = config('allow_visit');
        $deny = config('deny_visit');
        $check = strtolower($this->request->controller() . '/' . $this->request->action());
        if (!empty($deny) && in_array_case($check, $deny)) {
            return false; //非超管禁止访问deny中的方法
        }
        if (!empty($allow) && in_array_case($check, $allow)) {
            return true;
        }
        return null; //需要检测节点权限
    }

    protected function setMenu()
    {
        $this->assign('menus', setMenu());
    }

    protected function getContentMenu()
    {
        $model = model('Model');
        $list = array();
        $map = array(
            'status' => array('gt', 0),
            'extend' => array('gt', 0),
        );
        $list = $model->where($map)->field("name,id,title,icon,'' as 'style'")->select();

        //判断是否有模型权限
        $models = AuthGroup::getAuthModels(session('member_auth.uid'));
        foreach ($list as $key => $value) {
            if (IS_ROOT || in_array($value['id'], $models)) {
                if ('admin/content/index' == $this->url && input('model_id') == $value['id']) {
                    $value['style'] = "active";
                }
                $value['url'] = "admin/content/index?model_id=" . $value['id'];
                $value['title'] = $value['title'] . "管理";
                $value['icon'] = $value['icon'] ? $value['icon'] : 'file';
                $menu[] = $value;
            }
        }
        if (!empty($menu)) {
            $this->assign('extend_menu', array('内容管理' => $menu));
        }
    }

    /**
     * 解析数据库语句函数
     * @param string $sql sql语句   带默认前缀的
     * @param string $tablepre 自己的前缀
     * @return multitype:string 返回最终需要的sql语句
     */
    public function sqlSplit($sql, $tablepre)
    {
        if ($tablepre != "zp_") {
            $sql = str_replace("zp_", $tablepre, $sql);
        }

        $sql = preg_replace("/TYPE=(InnoDB|MyISAM|MEMORY)( DEFAULT CHARSET=[^; ]+)?/", "ENGINE=\\1 DEFAULT CHARSET=utf8", $sql);

        if ($r_tablepre != $s_tablepre) {
            $sql = str_replace($s_tablepre, $r_tablepre, $sql);
            $sql = str_replace("\r", "\n", $sql);
            $ret = array();
            $num = 0;
            $queriesarray = explode(";\n", trim($sql));
            unset($sql);
            foreach ($queriesarray as $query) {
                $ret[$num] = '';
                $queries = explode("\n", trim($query));
                $queries = array_filter($queries);
                foreach ($queries as $query) {
                    $str1 = substr($query, 0, 1);
                    if ($str1 != '#' && $str1 != '-') {
                        $ret[$num] .= $query;
                    }

                }
                $num++;
            }
        }
        return $ret;
    }

    //request信息
    protected function requestInfo()
    {
        $this->param = $this->request->param();
        defined('MODULE_NAME') or define('MODULE_NAME', $this->request->module());
        defined('CONTROLLER_NAME') or define('CONTROLLER_NAME', $this->request->controller());
        defined('ACTION_NAME') or define('ACTION_NAME', $this->request->action());
        defined('IS_POST') or define('IS_POST', $this->request->isPost());
        defined('IS_GET') or define('IS_GET', $this->request->isGet());
        $this->url = strtolower($this->request->module() . '/' . $this->request->controller() . '/' . $this->request->action());
        $this->model = strtolower($this->request->module() . '/' . $this->request->controller());
        $this->assign('request', $this->request);
        $this->assign('param', $this->param);
    }

    /**
     * 渲染配置信息
     * @param mixed $name 键名或数组
     * @param mixed $value 值
     */
    protected function assignconfig($name, $value = '')
    {
        //print_r($name) ;
        $this->view->config = array_merge($this->view->config ? $this->view->config : [], is_array($name) ? $name : [$name => $value]);
    }

    /**
     * 获取单个参数的数组形式
     */
    protected function getArrayParam($param)
    {
        if (isset($this->param['id'])) {
            return array_unique((array)$this->param[$param]);
        } else {
            return array();
        }
    }

    //生成缓存配置文件
    public function config_cache()
    {
        config_cache();
    }

    //生成热门关键词JS
    public function hotkeywords_cache()
    {
        hotkeywords_cache();
    }

    //生成广告JS
    public function ad_cache()
    {
        ad_cache();
    }

    //会员配置缓存
    public function userconfig_cache()
    {
        userconfig_cache();
    }

    //生成会员导航缓存
    public function usernav_cache()
    {
        usernav_cache();
    }

    //生成播放器缓存
    public function play_cache()
    {
        play_cache();
    }

    //生成前台分类缓存
    public function list_cache()
    {
        list_cache();
    }

}

?>