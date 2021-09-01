<?php
namespace app\common\model;
use think\Model;
use app\common\library\Baidu;
class News extends Model{
	protected $auto = ['newsaddtime','newsgold','newsletter','newsremark','newspic','newscontent'];
	protected function setnewsaddtimeAttr($value,$data){
		if($data['checktime']) {
			return time();
		}elseif($value){
			return strtotime($value);
		}else{
			return time();	
		}
	}
	protected function setnewsgoldAttr($value){
		if($value > 10){
			$value = 10;
		}	
		return 	$value;
	}
	protected function setnewsletterAttr($value,$data){
        if (empty($value)) {
			return getletter($data['news_name']);
		}else{
			return trim($value);
		}
	}
	protected function setnewsremarkAttr($value,$data){
		if(empty($value)){
			return msubstr(trim($data['news_content']),0,100,'utf-8',false);
		}else{
			return trim($value);
		}
	}	
	//图片处理
	protected function setnewspicAttr($value,$data){
		$img = model('Img');
		if($value){
		     return $img->down_load(trim($value),'news');
		}
		else{
            if(config("news_pic")==1){
				$urlpic=xianyu_content_pic(trim($data['news_content']),1);
			}			
            elseif(config("news_pic")==2){
				$urlpic=xianyu_content_pic(trim($data['news_content']),2);
			}else{
			    $urlpic=xianyu_content_pic(trim($data['news_content']));
			}
		    if($urlpic)	{
			   return $img->down_load(trim($urlpic),'news');
			}else{
			   return "";	
			}
		}
	}
	//内容处理
	protected function setnewscontentAttr($value){
	 if(!empty($value)){
	 return xianyu_content_images(trim($value),'news');
	 }else{
	    return ""; 	 
	  }
	 }
    public function newsrel(){
        return $this->hasMany('Newsrel','newsrel_nid');
    }
    public function star(){
        return $this->hasMany('Newsrel','newsrel_nid')->where('newsrel_sid',3);
    }
    public function vod(){
        return $this->hasMany('Newsrel','newsrel_nid')->where('newsrel_sid',1);
    }	
	 public function tag(){
        return $this->hasMany('Tag','tag_id');
    }
	
}