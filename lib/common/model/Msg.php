<?php

namespace app\common\model;

use think\Model;

class Msg extends Model
{
    protected function setmsgcontentAttr($value)
    {
        $userconfig = F('_data/userconfig_cache');
        if ($userconfig['user_replace']) {
            $array = explode('|', $userconfig['user_replace']);
            $content = remove_xss(trim(str_replace($array, '***', nb(nr(strip_tags($value))))));
        } else {
            $content = remove_xss(strip_tags($value));
        }
        return $content;
    }

    public function getmsgs($where, $page)
    {
        $list = db('msg')
            ->alias('m')
            ->join('user u', 'u.userid=m.msg_userid', 'LEFT')
            ->where($where)->order('msg_addtime desc ')
            ->paginate($page['limit'], false, ['page' => $page['currentpage']]);
        return $list;
    }

}