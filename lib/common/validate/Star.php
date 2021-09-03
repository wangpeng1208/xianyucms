<?php

namespace app\common\validate;
class Star extends Base
{
    protected $rule = [
        ['star_name', 'require', '明星名称不能为空'],
        ['star_cid', 'require|number|checkCid', '明星分类不能为空|明星分类必须为数字|请选择当前分类下面的子栏目'],
    ];

    protected function checkCid($value)
    {
        $tree = list_search(F('_data/listtree'), 'list_id=' . $value);
        if (!empty($tree[0]['son'])) {
            return false;
        } else {
            return true;
        }
    }
}