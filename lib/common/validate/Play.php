<?php

namespace app\common\validate;
class Play extends Base
{
    protected $rule = [
        ['play_title', 'require', '播放器名称不能为空'],
        ['play_name', 'require|alphaNum|unique:play', '播放器标识不能为空|播放器标识只能为数字和字母|播放标识已存在'],
    ];
}