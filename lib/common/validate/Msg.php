<?php

namespace app\common\validate;
class Msg extends Base
{
    protected $rule = [
        ['msg_uid', 'require|number|unique:msg,msg_uid^msg_userid', '内容ID不能为空|内容ID必须为数字|不能给自己发送私信'],
        ['msg_userid', 'require|number', '内容ID不能为空|内容ID必须为数字'],
        ['msg_content', 'require|unique:msg,msg_content^msg_title^msg_uid^msg_userid', '私信内容不能为空|你已发布过相同私信内容，请不要灌水哦！'],
        ['msg_title', 'require', '私信内容不能为空'],
    ];

}