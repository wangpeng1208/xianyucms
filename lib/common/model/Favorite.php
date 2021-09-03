<?php

namespace app\common\model;

use think\Db;
use think\Model;

class Favorite extends Model
{
    public function isFavoriteVod($where)
    {
        $count = $this->where($where)->count();
        return $count > 0 ? true : false;
    }

    public function getFavoriteCatalogs($userid)
    {
        $list = $this->field('*,count(list_id) as count')->alias('r')->join('list b', 'b.list_id=r.favorite_cid')->where('favorite_uid', $userid)->group('list_id,list_name')->select();
        return $list;
    }

    public function getFavoriteList($userid, $pid, $page)
    {
        $where['favorite_uid'] = $userid;
        if (!empty($pid)) {
            $where['favorite_cid'] = $pid;
        }
        $list = $this->field('vod_id,vod_name,vod_letters,vod_cid,vod_pic,vod_title,vod_addtime,vod_continu,vod_gold,vod_actor,vod_year,vod_jumpurl,favorite_id,favorite_addtime,favorite_cid')->alias('r')->join('vod b', 'b.vod_id=r.favorite_vid')->where($where)->order('vod_addtime desc ')->paginate($page['limit'], false, ['page' => $page['currentpage']]);
        return $list;

    }

    public function getFavoriteDatas($userid)
    {
        //print_r($userid) ;
        $list = Db::query("SELECT vod_id,vod_name,vod_letters,vod_cid,vod_pic,vod_title,vod_addtime,vod_continu,vod_gold,vod_actor,vod_year,vod_jumpurl FROM " . config('database.prefix') . "vod  inner join ( select b.favorite_vid,count(b.favorite_vid) as fnum from " . config('database.prefix') . "favorite b where not exists(select favorite_vid from " . config('database.prefix') . "favorite c where favorite_uid = " . $userid . " and b.favorite_vid = c.favorite_vid ) group by b.favorite_vid ) a  on " . config('database.prefix') . "vod.vod_id = a.favorite_vid  ORDER BY  a.fnum desc , " . config('database.prefix') . "vod.vod_hits_day desc  LIMIT 10");
        if (empty($list)) {
            $list = Db::query("SELECT vod_id,vod_name,vod_letters,vod_cid,vod_pic,vod_title,vod_addtime,vod_continu,vod_gold,vod_actor,vod_year,vod_jumpurl FROM " . config('database.prefix') . "vod  WHERE  vod_id  NOT  IN  ( SELECT favorite_vid FROM " . config('database.prefix') . "favorite) ORDER BY " . config('database.prefix') . "vod.vod_hits desc  LIMIT 10");
        }
        return $list;
    }

}