<?php

namespace app\common\validate;
class Attribute extends Base
{

    protected $rule = array(
        'name' => 'require|/^[a-zA-Z]\w{0,39}$/',
        'title' => 'require',
        'type' => 'require',
        'length' => 'requireIn:type,textarea,longtext,editor|integer',
        'remark' => 'require',
    );

    protected $message = array(
        'length.requireIn' => '字段长度必须！',
        'length.integer' => '字段必须为整形',
        'name.require' => '字段名不能为空！',
        'title.require' => '字段标题不能为空！',
        'type.require' => '类型不能为空！',
        'remark.require' => '字段备注不能为空！',
    );

    protected $scene = array(
        'add' => 'name,title,type,remark,length',
        'edit' => 'name,title,type,remark,length'
    );
}