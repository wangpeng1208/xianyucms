<?php

namespace app\common\validate;
class Special extends Base
{
    protected $rule = [
        ['special_name', 'require', '专题名称不能为空'],
        ['special_cid', 'require|number|checkCid', '专题分类不能为空|专题分类必须为数字|请选择当前分类下面的子栏目'],
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