<?php

namespace app\common\validate;
class Ads extends Base
{
    protected $rule = [
        ['ads_name', 'require|alphaDash|unique:ads', '广告标识不能为空|广告标识必须为数字字母下划线_及破折号-|广告标识存在'],
        ['ads_title', 'require', '广告名称不能为空'],
    ];
}