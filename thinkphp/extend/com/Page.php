<?php
namespace com;
class Page {
    private $myde_total;          //总记录数
    private $myde_size;           //一页显示的记录数
    private $myde_page;           //当前页
    private $myde_page_count;     //总页数
    private $myde_i;              //起头页数
    private $myde_en;             //结尾页数
    private $myde_url;            //获取当前的url
	private $myde_firsturl;            //获取第一页url
    /*
     * $show_pages
     * 页面显示的格式，显示链接的页数为2*$show_pages+1。
     * 如$show_pages=2那么页面上显示就是[首页] [上页] 1 2 3 4 5 [下页] [尾页] 
     */
    private $show_pages;

    public function __construct($myde_total = 1, $myde_size = 1, $myde_page = 1, $myde_url, $myde_firsturl, $show_pages = 2) {
        $this->myde_total = $this->numeric($myde_total);
        $this->myde_size = $this->numeric($myde_size);
        $this->myde_page = $this->numeric($myde_page);
        $this->myde_page_count = ceil($this->myde_total / $this->myde_size);
        $this->myde_url = $myde_url;
		$this->myde_firsturl = $myde_firsturl;
        if ($this->myde_total < 0)
            $this->myde_total = 0;
        if ($this->myde_page < 1)
            $this->myde_page = 1;
        if ($this->myde_page_count < 1)
            $this->myde_page_count = 1;
        if ($this->myde_page > $this->myde_page_count)
            $this->myde_page = $this->myde_page_count;
        $this->limit = ($this->myde_page - 1) * $this->myde_size;
        $this->myde_i = $this->myde_page - $show_pages;
        $this->myde_en = $this->myde_page + $show_pages;
        if ($this->myde_i < 1) {
            $this->myde_en = $this->myde_en + (1 - $this->myde_i);
            $this->myde_i = 1;
        }
        if ($this->myde_en > $this->myde_page_count) {
            $this->myde_i = $this->myde_i - ($this->myde_en - $this->myde_page_count);
            $this->myde_en = $this->myde_page_count;
        }
        if ($this->myde_i < 1)
            $this->myde_i = 1;
    }

    //检测是否为数字
    private function numeric($num) {
        if (strlen($num)) {
            if (!preg_match("/^[0-9]+$/", $num)) {
                $num = 1;
            } else {
                $num = substr($num, 0, 11);
            }
        } else {
            $num = 1;
        }
        return $num;
    }

    //地址替换
    private function page_replace($page) {
        return str_replace("xianyupage", $page, $this->myde_url);
    }
    //地址替换
    private function firstpage_replace($page) {
        return str_replace("xianyupage", $page, $this->myde_firsturl);
    }
    //首页
    private function myde_home() {
        if ($this->myde_page != 1) {
            return '<li><a href="'.$this->firstpage_replace(1).'" class="prev disabled" data="p-0">首页</a></li>';
        } else {
            return '<li class="disabled"><span>首页</span></li>';
        }
    }
    //上一页
    private function myde_prev() {
		if ($this->myde_page == 2) {
            return '<li><a href="'.$this->firstpage_replace($this->myde_page- 1).'" class="prev" data="p-'.($this->myde_page - 1).'">上一页</a></li>' ;
        }
        elseif ($this->myde_page != 1) {
            return '<li><a href="'.$this->page_replace($this->myde_page- 1).'" class="prev" data="p-'.($this->myde_page - 1).'">上一页</a></li>' ;
        } else {
            return '<li class="disabled"><span>上一页</span></li>';
        }
    }
  //下一页
    private function myde_next() {
        if ($this->myde_page != $this->myde_page_count) {
            return '<li><a href="'.$this->page_replace($this->myde_page + 1).'" class="next pagegbk" data="p-'.($this->myde_page + 1).'">下一页</a></li>';
        } else {
            return '<li class="disabled"><span>下一页</span></li>';
        }
    }

    //尾页
    private function myde_last() {
        if ($this->myde_page != $this->myde_page_count) {
            return '<li><a href="'.$this->page_replace($this->myde_page_count).'" class="next pagegbk" data="p-'.$this->myde_page_count.'">尾页</a></li>';
        } else {
            return '<li class="disabled"><span>尾页</span></li>';
        }
    }

    //输出
    public function myde_write($id = 'page') {
        $str.=$this->myde_home();
        $str.=$this->myde_prev();
        if ($this->myde_i > 1) {
            $str.='<li class="hidden-xs"><span>...</span></li>';
        }
        for ($i = $this->myde_i; $i <= $this->myde_en; $i++) {
            if ($i == $this->myde_page) {
                $str.='<li class="hidden-xs active"><span>'.$i.'</span></li>';
				$str.='<li class="visible-xs active"><span class="num">'.$i.'/'.$this->myde_page_count.'</span></li>';
            } else {
				if($i == 1){
				$str.='<li class="hidden-xs"><a href="'. $this->firstpage_replace($i) .'" data="p-'.$i.'" title="第"'. $i .'"页">'.$i.'</a></li>';
			    }else{
                $str.='<li class="hidden-xs"><a href="'. $this->page_replace($i) .'" data="p-'.$i.'" title="第'. $i .'页">'.$i.'</a></li>';
				}
            }
        }
        if ($this->myde_en < $this->myde_page_count) {
            $str.='<li class="hidden-xs"><span>...</span></li>';
        }
        $str.=$this->myde_next();
        $str.=$this->myde_last();
        return $str;
    }

}

?>