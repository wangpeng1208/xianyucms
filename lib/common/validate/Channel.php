<?php

namespace app\common\validate;
class Channel extends Base
{
    protected $rule = array(
        'list_name' => 'require',
    );

    protected $message = array(
        'list_name.require' => '栏目标题不能为空！',
    );

    protected $scene = array(
        'add' => 'list_name',
        'edit' => 'list_name'
    );
}