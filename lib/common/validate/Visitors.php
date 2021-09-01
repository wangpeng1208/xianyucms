<?php
// +----------------------------------------------------------------------
// | ZanPianCMS [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.xianyu.com All rights reserved.
// +----------------------------------------------------------------------
// | BBS:  <http://www.feifeicms.cc>
// +----------------------------------------------------------------------
namespace app\common\validate;
class Visitors extends Base{
 protected $rule = [
        ['visitors_uid', 'unique:visitors,visitors_uid^visitors_userid', '已经存在来访'],
		['visitors_userid', 'unique:visitors,visitors_userid^visitors_uid', '已经存在来访']
    ];
}