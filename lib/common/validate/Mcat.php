<?php

namespace app\common\validate;
class Mcat extends Base
{
    protected $rule = [
        ['m_name', 'require', '类型名不能为空'],
    ];
}