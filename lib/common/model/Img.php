<?php

namespace app\common\model;

use think\Model;

class Img extends Model
{
    //调用接口
    public function down_load($url, $sid = 'vod', $pyname = '')
    {
        if (is_array($url)) {
            $pathArr = array();
            foreach ($url as $key => $value) {
                if (config('upload_http') && (strpos($value, '://') > 0 || substr($value, 0, 2) == '//') || config('upload_http_news') && $sid == "news" && (strpos($value, '://') > 0 || substr($value, 0, 2) == '//')) {
                    if (substr($value, 0, 2) == '//') {
                        $value = 'http:' . $value;
                    }
                    if (config('upload_graph') && config('upload_graph_control')) {
                        $pathArr[] = $this->down_graph($value, $sid);
                    } else {
                        $pathArr[] = $this->down_img($value, $sid);
                    }
                } else {
                    $pathArr[] = $value;
                }
            }
            return $pathArr;
        } else {
            if (config('upload_http') && (strpos($url, '://') > 0 || substr($url, 0, 2) == '//')) {
                if (substr($url, 0, 2) == '//') {
                    $url = 'http:' . $url;
                }
                if (config('upload_graph') && config('upload_graph_control')) {
                    return $this->down_graph($url, $sid);
                } else {
                    return $this->down_img($url, $sid, $pyname);
                }
            } else {
                return $url;
            }
        }
    }

    //远程下载到图床
    public function down_graph($url, $sid = 'vod', $pyname = '')
    {
        if (config('upload_graph_type') == 'sina') {
            return $this->down_sina($url, $sid = 'vod', $pyname = '');
        }
        if (config('upload_graph_type') == 'sm') {
            return $this->down_sm($url, $sid = 'vod', $pyname = '');
        }
    }

    //远程下载到图床
    public function down_sina($url, $sid = 'vod', $pyname = '')
    {
        $domain = graph_domain($url);
        if ($domain) {
            return $url;
        }
        $value = preg_replace("/http[^.]*[^A]+pic\.php\?url=/is", '', $url);
        $cookie = config('upload_sina_cookie');
        $co = new \com\Curl();
        $chr = strrchr($value, '.');
        if ($chr == '.') {
            $chr = $chr . 'jpg';
        }
        $filename = URL_PATH . config('upload_path') . DS . 'temp' . DS . uniqid() . $chr;
        $get_file = $co->get($value);
        if ($get_file) {
            write_file($filename, $get_file);
            $urlarray = config('http_api');
            $rand = array_rand($urlarray, 1);
            $apiurl = "http://" . $urlarray[$rand] . "/xianyucms/upload.php";
            $data = $co->post($apiurl, ['cookie' => $cookie], array('file' => $filename));
            $images = json_decode($data, true);
            @unlink($filename);
            if ($images['code'] && $images['pic']) {
                if (config('upload_graph_http')) {
                    $images['pic'] = str_replace(array('http:', 'https:'), '', $images['pic']);
                }
                return $images['pic'];
            }

        }
        return $url;
    }

    //远程下载到图床
    public function down_sina_back($url, $sid = 'vod', $pyname = '')
    {
        $value = preg_replace("/http[^.]*[^A]+pic\.php\?url=/is", '', $url);
        $data = model('img')->curl_request('http://zs.mtkan.cc/url_upload.php', ['url' => $value]);
        $images = json_decode($data, true);
        if ($images['original_pic'] && strripos($images['original_pic'], 'Array') === false) {
            if (config('upload_graph_http')) {
                $images['original_pic'] = str_replace(array('http:', 'https'), '', $images['original_pic']);
            }
            return $images['original_pic'];
        } else {
            return $url;
        }
    }

    //远程下载到图床
    public function down_sm($url, $sid = 'vod', $pyname = '')
    {
        $domain = graph_domain($url);
        if ($domain) {
            return $url;
        }
        $value = preg_replace("/http[^.]*[^A]+pic\.php\?url=/is", '', $url);
        $co = new \com\Curl();
        $chr = strrchr($value, '.');
        if ($chr == '.') {
            $chr = $chr . 'jpg';
        }
        $filename = URL_PATH . config('upload_path') . DS . 'temp' . DS . uniqid() . $chr;
        $get_file = $co->get($value);
        if ($get_file) {
            write_file($filename, $get_file);
            $data = $co->post('https://sm.ms/api/upload', ['name' => 'images'], array('smfile' => $filename));
            $images = json_decode($data, true);
            @unlink($filename);
            if ($images['code'] == 'success') {
                if (config('upload_graph_http')) {
                    $images['data']['url'] = str_replace(array('http:', 'https:'), '', $images['data']['url']);
                }
                return $images['data']['url'];
            } else {
                return $url;
            }
        }
        return $url;
    }

    //远程下载图片
    public function down_img($url, $sid = 'vod', $pyname = '')
    {
        $domain = graph_domain($url);
        if ($domain) {
            return $url;
        }
        $chr = strrchr($url, '.');
        if (!empty($pyname)) {
            $imgUrl = $pyname;
        } else {
            $imgUrl = uniqid();
        }
        if ($chr == '.') {
            $chr = $chr . 'jpg';
        }
        $imgPath = $sid . '/' . date(config('upload_style'), time()) . '/';
        $imgPath_s = './' . config('upload_path') . '-s/' . $imgPath;
        $filename = './' . config('upload_path') . '/' . $imgPath . $imgUrl . $chr;
        $get_file = xianyu_get_url($url, 5);
        if ($get_file) {
            write_file($filename, $get_file);
            //是否添加水印
            if (config('upload_water')) {
                $waterimg = ROOT_PATH . DS . PUBLIC_URL . DS . 'tpl' . DS . 'admin' . DS . config('upload_water_img');
                try {
                    $image = \think\Image::open($filename);

                    $image->water($waterimg, config('upload_water_pos'), config('upload_water_pct'))->save($filename);
                } catch (\Exception $e) {

                }
            }
            //是否生成缩略图
            if (config('upload_thumb')) {
                mkdirss($imgPath_s);
                try {
                    $image = \think\Image::open($filename);
                    $upload_thumb = explode('/', trim(config('upload_thumb_size')));
                    $image->thumb($upload_thumb[0], $upload_thumb[1], config('upload_thumb_pos'))->save($imgPath_s . $imgUrl . $chr);
                } catch (\Exception $e) {

                }
            }

            //是否上传远程
            if (config('upload_ftp')) {
                $this->ftp_upload($imgPath . $imgUrl . $chr);
            }
            return $imgPath . $imgUrl . $chr;
        } else {
            return $url;
        }
    }

    //远程ftp附件	
    public function ftp_upload($imgurl)
    {
        $ftpcon = array(
            'ftp_host' => config('upload_ftp_host'),
            'ftp_port' => config('upload_ftp_port'),
            'ftp_user' => config('upload_ftp_user'),
            'ftp_pwd' => config('upload_ftp_pass'),
            'ftp_dir' => config('upload_ftp_dir'),
        );
        $ftp = new \com\Ftp();
        $ftp->config($ftpcon);
        $ftp->connect();
        $ftpimg = $ftp->put(config('upload_path') . '/' . $imgurl, config('upload_path') . '/' . $imgurl);
        if (config('upload_thumb')) {
            $ftpimg_s = $ftp->put(config('upload_path') . '-s/' . $imgurl, config('upload_path') . '-s/' . $imgurl);
        }
        if (config('upload_ftp_del')) {
            if (isset($ftpimg_s)) {
                @unlink(config('upload_path') . '-s/' . $imgurl);
            }
            if ($ftpimg) {
                @unlink(config('upload_path') . '/' . $imgurl);
            }
        }
        $ftp->bye();
    }

    //远程ftp附件
    function curl_request($url, $post = '', $cookie = '', $returnCookie = 0)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_REFERER, "http://XXX");
        if ($post) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
        }
        if ($cookie) {
            curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        }
        curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        if (curl_errno($curl)) {
            return curl_error($curl);
        }
        curl_close($curl);
        if ($returnCookie) {
            list($header, $body) = explode("\r\n\r\n", $data, 2);
            preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
            $info['cookie'] = substr($matches[1][0], 1);
            $info['content'] = $body;
            return $info;
        } else {
            return $data;
        }
    }
}