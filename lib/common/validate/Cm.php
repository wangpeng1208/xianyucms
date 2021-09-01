<?php
// +----------------------------------------------------------------------
// | ZanPianCMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.xianyu.com All rights reserved.
// +----------------------------------------------------------------------
// | BBS:  <http://www.feifeicms.cc>
// +----------------------------------------------------------------------
namespace app\common\validate;
class Cm extends Base{
 protected $rule = [
		['cm_vid', 'require|number', '内容ID不能为空|内容ID必须为数字'],
		['cm_sid', 'number', '类型ID不能为空|类型ID必须为数字'],	
        ['cm_pid', 'number', '一级评论ID必须位数字'],
		['cm_tid', 'number', '二级评论ID必须位数字'],
		['cm_tuid', 'number', '三级评论ID必须位数字'],
		['cm_spid', 'number', '上级评论ID必须位数字'],
		['cm_uid', 'number|different:cm_tuid', '评论会员ID必须为数字|不能回复自己的评论'],
		['cm_content', 'require|max:1000|unique:cm,cm_content^cm_vid^cm_pid', '评论内容不能为空|评论内容最长不超过1000字符|你已经发布过相同评论内容，请不要灌水哦！'],
    ];
 
}
