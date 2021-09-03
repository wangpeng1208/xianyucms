<?php

namespace app\common\validate;
class Slide extends Base
{
    protected $rule = array(
        'slide_name' => 'require',
        'slide_vid' => 'number',
    );
    protected $message = array(
        'slide_name.require' => '幻灯片名称不能为空！',
        'slide_vid.number' => '视频ID必须为数字',
    );
}