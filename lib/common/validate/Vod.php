<?php

namespace app\common\validate;
class Vod extends Base
{
    protected $rule = [
        ['vod_name', 'require', '视频名称不能为空'],
        ['vod_cid', 'require|checkCid', '视频分类不能为空|请选择当前视频分类下面的子栏目'],
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