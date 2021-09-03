<?php

namespace app\admin\controller;

use app\common\controller\Admin;

class Pic extends Admin
{
    public function down()
    {
        $type = input('type/s', '');
        $id = input('id/d', '');
        $page = input('page/d', 1);
        $fail = input('fail/s', '');
        $mode = list_search(F('_data/modellist'), 'id=' . $id);//模型中获取表名
        config('upload_http', 1);
        $img = model('Img');
        $rs = model($mode[0]['name']);
        $domain = "";
        if ('fail' == trim($fail)) {
            $rs->execute('update ' . config('database.prefix') . $mode[0]['name'] . ' set ' . $type . '=REPLACE(' . $type . ',"httpf://", "http://")');
            $rs->execute('update ' . config('database.prefix') . $mode[0]['name'] . ' set ' . $type . '=REPLACE(' . $type . ',"httpsf://", "https://")');
            $rs->execute('update ' . config('database.prefix') . $mode[0]['name'] . ' set ' . $type . '=REPLACE(' . $type . ',"f//", "//")');
        }
        $upload_graph_domain = config('upload_graph_domain');
        if ($upload_graph_domain[0]) {
            foreach ($upload_graph_domain as $key => $value) {
                $domain[$key] = '%' . $value . '%';
            }
            $list = $rs->where('Left(' . $type . ',7)="http://" OR Left(' . $type . ',8)="https://" OR Left(' . $type . ',2)="//"')->where($type, 'not like', $domain, 'OR')->order($mode[0]['name'] . '_addtime desc')->paginate(config('upload_http_down'), false);
        } else {
            $count = $rs->where('Left(' . $type . ',7)="http://" OR Left(' . $type . ',8)="https://" OR Left(' . $type . ',2)="//"')->count($mode[0]['name'] . '_id');
            $list = $rs->where('Left(' . $type . ',7)="http://" OR Left(' . $type . ',8)="https://" OR Left(' . $type . ',2)="//"')->order($mode[0]['name'] . '_addtime desc')->paginate(config('upload_http_down'), false);
        }
        $pages['model_name'] = $mode[0]['title'];
        $pages['recordcount'] = $list->total();
        $pages['pagecount'] = '';
        $pages['pageindex'] = '';
        $pages['pagesize'] = config('upload_http_down');
        $pages['type'] = $type;
        $pages['types'] = $type . "字段图片下载";
        $pages['id'] = $id;
        $this->assign($pages);
        echo $this->fetch('index');
        $data = $list->all();
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                @$imgnew = $img->down_load($value[$type], $mode[0]['name']);
                if ($value[$type] == $imgnew) {
                    if (substr($value[$type], 0, 2) == '//') {
                        $rs->where($mode[0]['name'] . '_id', $value[$mode[0]['name'] . '_id'])->setField($type, str_replace("//", "f//", $value[$type]));
                    } else {
                        //print_r($value[$mode[0]['name'].'_id']);
                        //db($mode[0]['name'])->where($mode[0]['name'].'_id',$value[$mode[0]['name'].'_id'])->setField($type,str_replace(array("http://","https://"),array("httpf://","httpsf://"),$value[$type]));
                        $rs->where($mode[0]['name'] . '_id', $value[$mode[0]['name'] . '_id'])->setField($type, str_replace(array("http://", "https://"), array("httpf://", "httpsf://"), $value[$type]));
                    }
                    $msg = '<tr><td> 第<font color=red>' . ($key + 1) . '</font>张图片 <a href=\"' . $value[$type] . '\" target=\"_blank\">' . $value[$type] . '</a> <font color=red>下载失败!</font></td></tr>';
                } else {
                    $rs->where($mode[0]['name'] . '_id', $value[$mode[0]['name'] . '_id'])->setField($type, $imgnew);
                    $msg = '<tr><td> 第<font color=red>' . ($key + 1) . '</font>张图片 <a href=\"' . $value[$type] . '\" target=\"_blank\">' . $value[$type] . '</a> <span class=\"text-success\">下载成功！</span></td></tr>';
                }
                $this->show_msg($msg);
            }
            $this->show_msg("<tr><td>请稍等一会，正在释放服务器资源...</td></tr>");
            echo '<meta http-equiv="refresh" content=' . config('player_collect_time') . ';url=' . url('admin/pic/down', array('id' => $id, 'type' => $type)) . '>';
        } else {
            $count = $rs->where('Left(' . $type . ',8)="httpf://" OR Left(' . $type . ',9)="httpsf://" OR Left(' . $type . ',3)="f//"')->count($mode[0]['name'] . '_id');
            if ($count) {
                $this->show_msg("<tr><td>共有" . $count . "张远程图片保存失败,如果需要重新下载,请点击<a href=\"" . url('admin/pic/down', array('fail' => 'fail', 'id' => $id, 'type' => $type)) . "\">[这里]</a></td></tr>");
                exit;
            } else {
                $this->show_msg("<tr><td>恭喜您,没有可下载的图片了！</td></tr>");
                exit;
            }
            $this->show_msg("<tr><td>没有需要下载的图片！</td></tr>");
            exit;
        }
    }

    public function actor($vid = "")
    {
        config('upload_http', 1);
        $img = model('Img');
        $rs = model('Role');
        $id = $vid;
        $fail = input('fail/s', '');
        if ('fail' == trim($fail)) {
            $rs->execute('update ' . config('database.prefix') . 'role set role_pic=REPLACE("role_pic","httpf://", "http://")');
            $rs->execute('update ' . config('database.prefix') . 'role set role_pic=REPLACE("role_pic","httpsf://", "https://")');
            $rs->execute('update ' . config('database.prefix') . 'role set role_pic=REPLACE("role_pic","f//", "//")');
        }
        $upload_graph_domain = config('upload_graph_domain');
        if ($upload_graph_domain[0]) {
            foreach ($upload_graph_domain as $key => $value) {
                $domain[$key] = '%' . $value . '%';
            }
            $list = $rs->where('Left(role_pic,7)="http://" OR Left(role_pic,8)="https://" OR Left(role_pic,2)="//"')->where('role_vid', $vid)->where('role_pic', 'not like', $domain, 'AND')->order('role_id desc')->select();
        } else {
            $list = $rs->where('Left(role_pic,7)="http://" OR Left(role_pic,8)="https://" OR Left(role_pic,2)="//"')->where('role_vid', $vid)->order('role_id desc')->select();
        }
        $page['model_name'] = "角色";
        $page['recordcount'] = count($list);
        $page['types'] = "缩略图下载";
        $page['pagesize'] = $page['recordcount'];
        $this->assign($page);
        echo $this->fetch('index');
        if ($list) {
            foreach ($list as $key => $value) {
                @$imgnew = $img->down_load($value['role_pic'], 'role');
                if ($value['role_pic'] == $imgnew) {
                    if (substr($value[$type], 0, 2) == '//') {
                        $rs->where($mode[0]['name'] . '_id', $value[$mode[0]['name'] . '_id'])->setField($type, str_replace("//", "f//", $value[$type]));
                    } else {
                        //print_r($value[$mode[0]['name'].'_id']);
                        //db($mode[0]['name'])->where($mode[0]['name'].'_id',$value[$mode[0]['name'].'_id'])->setField($type,str_replace(array("http://","https://"),array("httpf://","httpsf://"),$value[$type]));
                        $rs->where($mode[0]['name'] . '_id', $value[$mode[0]['name'] . '_id'])->setField($type, str_replace(array("http://", "https://"), array("httpf://", "httpsf://"), $value[$type]));
                    }
                    $msg = '<tr><td> 第<font color=red>' . ($key + 1) . '</font>张图片 <a href=\"' . $value['role_pic'] . '\" target=\"_blank\">' . $value['role_pic'] . '</a> <font color=red>下载失败!</font></td></tr>';
                } else {
                    $rs->where('role_id = ' . $value['role_id'])->setField('role_pic', $imgnew);
                    $msg = '<tr><td> 第<font color=red>' . ($key + 1) . '</font>张图片 <a href=\"' . $value['role_pic'] . '\" target=\"_blank\">角色：' . $value['role_name'] . '</a> <span class=\"text-success\">图片下载成功！</span></td></tr>';
                }
                $this->show_msg($msg);
                if (end(array_keys($list)) == $key) {
                    echo '<meta http-equiv="refresh" content="0;url=' . url('admin/role/actor', array('vid' => $vid)) . '>';
                    echo $this->show_msg("<tr><td>远程图片下载完成");
                    exit;

                }

            }
        } else {
            echo $this->show_msg('<tr><td>没有远程图片需要下载</td></tr>');
            exit;
        }
    }

    public function content_id()
    {
        $id = input('id/s', '');
        $sid = input('sid/d', '');
        $type = input('type/s', 'content');
        config('upload_http', 1);
        $img = model('Img');
        $mode = list_search(F('_data/modellist'), 'id=' . $sid);//模型中获取表名
        $rs = db($mode[0]['name']);
        $where[$mode[0]['name'] . '_id'] = array('in', $id);
        $list = db($mode[0]['name'])->where($where)->select();
        $page['model_name'] = $mode[0]['title'];
        $page['recordcount'] = count($list);
        $page['pagecount'] = $page['recordcount'];
        $page['types'] = "内容图片下载";
        $page['pagesize'] = $page['recordcount'];
        $page['type'] = $type;
        $this->assign($page);
        echo $this->fetch('index');
        foreach ($list as $key => $value) {
            $content = xianyu_content_images(trim($value[$mode[0]['name'] . '_content']), $mode[0]['name']);
            if ($content) {
                $rs->where($mode[0]['name'] . '_id', $value[$mode[0]['name'] . '_id'])->setField($mode[0]['name'] . '_content', $content);
                $msg = '<tr><td> <font color=red>' . ($key + 1) . '</font> [' . $mode[0]['title'] . ']:ID' . $value[$mode[0]['name'] . '_id'] . '：' . $value[$mode[0]['name'] . '_name'] . '<span class=\"text-success\">内容图片下载成功！</span></tr></td>';
            } else {
                $msg = '<tr><td> <font color=red>' . ($key + 1) . '</font> [' . $mode[0]['title'] . ']:ID' . $value[$mode[0]['name'] . '_id'] . '：' . $value[$mode[0]['name'] . '_name'] . '<font color=red>内容图片下载失败!</font></tr></td>';
            }
            $this->show_msg($msg);
        }
    }

    public function content()
    {
        $id = input('id/d', '');
        $page = input('page/d', 1);
        $type = input('type/s', 'content');
        $mode = list_search(F('_data/modellist'), 'id=' . $id);//模型中获取表名
        config('upload_http', 1);
        $img = model('Img');
        $rs = model($mode[0]['name']);
        $list = $rs->whereor($mode[0]['name'] . '_content', ['like', '%src=http://%'], ['like', "%src='http://%"], ['like', '%src="http://%'], ['like', '%src=https://%'], ['like', "%src='https://%"], ['like', '%src="https://%'], ['like', '%src=//%'], ['like', "%src='//%"], ['like', '%src="//%'])->order($mode[0]['name'] . '_addtime desc')->paginate(10, false, ['page' => $page]);
        $pages['model_name'] = $mode[0]['title'];
        $pages['recordcount'] = $list->total();
        $pages['pagecount'] = $list->lastPage();
        $pages['pageindex'] = $list->currentPage();
        $pages['pagesize'] = 10;
        $pages['types'] = $type . "内容图片下载";
        $pages['type'] = $type;
        $pages['id'] = $id;
        $this->assign($pages);
        $this->assign('jumpurl', F('_down/' . $id . '-' . $type));
        //断点生成(写入缓存)
        F('_down/' . $id . '-' . $type, admin_url('admin/pic/content', ['type' => $type, 'id' => $id, 'page' => $page, 'fail' => $fail]));
        echo $this->fetch('index');
        $data = $list->all();
        if ($data) {
            foreach ($data as $key => $value) {
                $content = xianyu_content_images(trim($value[$mode[0]['name'] . '_content']), $mode[0]['name']);
                if ($content) {
                    $rs->where($mode[0]['name'] . '_id', $value[$mode[0]['name'] . '_id'])->setField($mode[0]['name'] . '_content', $content);
                    $msg = '<tr><td> <font color=red>' . ($key + 1) . '</font> [' . $mode[0]['title'] . ']:ID' . $value[$mode[0]['name'] . '_id'] . '：' . $value[$mode[0]['name'] . '_name'] . '<span class=\"text-success\">内容图片下载成功！</span></tr></td>';
                } else {
                    $msg = '<tr><td> <font color=red>' . ($key + 1) . '</font> [' . $mode[0]['title'] . ']:ID' . $value[$mode[0]['name'] . '_id'] . '：' . $value[$mode[0]['name'] . '_name'] . '<font color=red>内容图片下载失败!</font></tr></td>';
                }
                $this->show_msg($msg);
            }
            if ($list->currentPage() < $list->lastPage()) {
                $this->show_msg("<tr><td>请稍等一会，正在释放服务器资源...</td></tr>");
                echo '<meta http-equiv="refresh" content=' . config('player_collect_time') . ';url=' . url('admin/pic/content', array('id' => $id, 'type' => $type, 'page' => $page + 1)) . '>';
            } else {
                echo $this->show_msg("<tr><td>恭喜您,本次下载完成！</td></tr>");
                F('_down/' . $id . '-' . $type, NULL);
                exit;
            }
        } else {
            F('_down/' . $id . '-' . $type, NULL);
            echo $this->show_msg("<tr><td>没有数据，无须下载图片！</td></tr>");
            exit;
        }
    }

    function show_msg($msg, $name = "showmsg")
    {
        echo "<script type=\"text/javascript\">" . $name . "(\"{$msg}\")</script>";
        ob_flush(); //此句不能少
        flush();
        sleep(0);
    }
}