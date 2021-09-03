<?php

namespace app\common\validate;
class Role extends Base
{
    protected $rule = [
        ['role_vid', 'require|number', '视频ID不能为空|视频ID必须为数字'],
        ['role_name', 'require', '角色名称不能为空'],
        ['role_cid', 'require|number|checkCid', '角色分类不能为空|角色分类必须为数字|请选择当前角色分类下面的子栏目'],
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