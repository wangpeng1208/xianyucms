<?php

namespace app\common\validate;
class Gb extends Base
{
    protected $rule = [
        ['gb_content', 'require|unique:gb,gb_content^gb_cid^gb_uid^gb_title', '留言内容不能为空|你已发布过相同留言内容，请不要灌水哦！'],
        ['gb_cid', 'require|checkcookie', '留言类型必须选择|您已经留言过了，请休息一会，喝杯咖啡！'],
    ];

    protected function checkcookie($value)
    {
        $cachename = 'gb-' . ip2long(get_client_ip()) . '-' . intval($value);
        $gbcache = cache($cachename);

        if (!empty($gbcache)) {
            return false;
        } else {
            return true;
        }

    }
}