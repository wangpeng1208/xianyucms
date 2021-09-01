<?php


namespace app\admin\controller;

use app\common\controller\Admin;
use think\Request;
class Upload extends Admin
{
	public function _initialize()
	{
		parent::_initialize();

	}
	public function ajax()
	{
		if (config('upload_graph')) {
			return $this->graph();
		} else {
			return $this->local();
		}
	}
	public function graph()
	{
		if (config('upload_graph_type') == 'sina') {
			return $this->sina();
		}
		if (config('upload_graph_type') == 'sm') {
			return $this->sm();
		}
	}
	public function sm()
	{
		$co = new \com\Curl();
		$file = $this->request->file('file');
		$data = $this->request->Post();
		$uppath = URL_PATH . config('upload_path') . DS . 'temp';
		if (!is_dir($uppath)) {
			@mkdir($uppath, 0755, true);
		}
		if ($file) {
			$info = $file->validate(['ext' => config('upload_class')])->rule('uniqid')->move($uppath);
			if ($info) {
				$picurl = $info->getPathName();
				$data = $co->post('https://sm.ms/api/upload', $data, array('smfile' => $picurl));
				$images = json_decode($data, true);
				unset($info);
				@unlink($picurl);
				if ($images['code'] == 'success') {
					if (config('upload_graph_http')) {
						$images['data']['url'] = str_replace(array('http:', 'https:'), '', $images['data']['url']);
					}
					return json_encode(array('code' => 1, 'msg' => '上传成功', 'data' => array('url' => $images['data']['url'])));
				}
			} else {
				return json_encode(array('code' => -1, 'msg' => $file->getError()));
			}
		}
	}
	public function sina()
	{
		$cookie = config('upload_sina_cookie');
		$co = new \com\Curl();
		$file = $this->request->file('file');
		$data = $this->request->Post();
		$uppath = URL_PATH . config('upload_path') . DS . 'temp';
		if (!is_dir($uppath)) {
			@mkdir($uppath, 0755, true);
		}
		if ($file) {
			$info = $file->validate(['ext' => config('upload_class')])->rule('uniqid')->move($uppath);
			if ($info) {
				$picurl = $info->getPathName();
				$urlarray = config('http_api');
				$rand = array_rand($urlarray, 1);
				$apiurl = "http://" . $urlarray[$rand] . "/xianyucms/upload.php";
				$data = $co->post($apiurl, ['cookie' => $cookie], array('file' => $picurl));
				$images = json_decode($data, true);
				unset($info);
				@unlink($picurl);
				if ($images['code'] && $images['pic']) {
					if (config('upload_graph_http')) {
						$images['pic'] = str_replace(array('http:', 'https:'), '', $images['pic']);
					}
				}
				return json_encode(array('code' => $images['code'], 'msg' => $images['msg'], 'data' => array('url' => $images['pic'])));
			} else {
				return json_encode(array('code' => -1, 'msg' => $file->getError()));
			}
		}
	}
	public function local()
	{
		$file = $this->request->file('file');
		$data = $this->request->Post();
		$sid = !empty($data['sid']) ? trim($data['sid']) : 'vod';
		$uppath = URL_PATH . config('upload_path') . DS;
		$uppath_s = URL_PATH . config('upload_path') . '-s' . DS;
		if ($sid) {
			$uppath .= $sid . DS;
			mkdirss($uppath);
		}
		if (true !== $this->validate(['image' => $file], ['image' => 'require|image'])) {
			return json_encode(array('code' => -1, 'msg' => "未上传文件或超出服务器上传限制"));
		} else {
			$info = $file->rule(config('upload_style'))->move($uppath);
			$filepath = explode('/', trim(str_replace("\\", "/", $info->getSaveName())));
			$image = \think\Image::open($info->getRealPath());
			if (config('upload_water')) {
				$waterimg = ROOT_PATH . DS . PUBLIC_URL . DS . 'tpl' . DS . 'admin' . DS . config('upload_water_img');
				$image->water($waterimg, config('upload_water_pos'), config('upload_water_pct'))->save($info->getRealPath());
			}
			if (config('upload_thumb')) {
				if ($sid) {
					$uppath_s .= $sid . DS . $filepath[0];
					mkdirss($uppath_s);
				}
				$upload_thumb = explode('/', trim(config('upload_thumb_size')));
				$image->thumb($upload_thumb[0], $upload_thumb[1], config('upload_thumb_pos'))->save($uppath_s . '/' . $filepath[1]);
			}
			if (config('upload_ftp')) {
				$img = model('Img');
				$img->ftp_upload($sid . '/' . $filepath[0] . '/' . $filepath[1]);
			}
			$imagesurl = $sid . '/' . $filepath[0] . '/' . $filepath[1];
			return json_encode(array('code' => 1, 'msg' => '上传成功', 'data' => array('url' => $imagesurl)));
		}
	}
	public function picture()
	{
		$file = $this->request->file('file');
		if (empty($data['api'])) {
			echo '<div style="font-size:12px; height:30px; line-height:30px">';
		}
		$sid = !empty($data['sid']) ? trim($data['sid']) : 'vod';
		$fileback = !empty($data['fileback']) ? trim($data['fileback']) : 'vod_pic';
		$uppath = URL_PATH . config('upload_path') . DS;
		$uppath_s = URL_PATH . config('upload_path') . '-s' . DS;
		if ($sid) {
			$uppath .= $sid . DS;
			mkdirss($uppath);
		}
		$file = request()->file('imgFile');
		if (true !== $this->validate(['image' => $file], ['image' => 'require|image'])) {
			if (!empty($data['api'])) {
				return json_encode(array('error' => 1, 'message' => "请上传图片文件"));
			} else {
				return '请选择需要上传的图片　[<a href=' . url('admin/upload/show', array('sid' => $sid, 'fileback' => $fileback)) . '>重新上传</a>]';
			}
		} else {
			$info = $file->rule(config('upload_style'))->move($uppath);
			$filepath = explode('/', trim(str_replace("\\", "/", $info->getSaveName())));
			$image = \think\Image::open($info->getRealPath());
			if (config('upload_water')) {
				$waterimg = ROOT_PATH . 'public' . DS . 'tpl' . DS . 'admin' . DS . 'static' . DS . config('upload_water_img');
				$image->water($waterimg, config('upload_water_pos'), config('upload_water_pct'))->save($info->getRealPath());
			}
			if (config('upload_thumb')) {
				if ($sid) {
					$uppath_s .= $sid . DS . $filepath[0];
					mkdirss($uppath_s);
				}
				$upload_thumb = explode('/', trim(config('upload_thumb_size')));
				$image->thumb($upload_thumb[0], $upload_thumb[1], config('upload_thumb_pos'))->save($uppath_s . '/' . $filepath[1]);
			}
			if (config('upload_ftp')) {
				$img = model('Img');
				$img->ftp_upload($sid . '/' . $filepath[0] . '/' . $filepath[1]);
			}
			if (!empty($data['api'])) {
				$imagesurl = config('site_path') . config('upload_path') . '/' . $sid . '/' . $filepath[0] . '/' . $filepath[1];
				$title = $data['title'] ? $data['title'] : '';
				echo json_encode(array('error' => 0, 'url' => $imagesurl, 'title' => $title));
				exit;
			} else {
				echo '<script type=\'text/javascript\'>parent.document.getElementById(\'' . $fileback . "').value='" . $sid . '/' . $filepath[0] . '/' . $filepath[1] . "';</script>";
				echo '文件上传成功　　[<a href=' . url('admin/upload/show', array('sid' => $sid, 'fileback' => $fileback)) . '>重新上传</a>]';
				echo '</div>';
			}
		}
	}
}