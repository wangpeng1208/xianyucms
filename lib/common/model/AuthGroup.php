<?php

namespace app\common\model;
class AuthGroup extends Model
{

    const TYPE_ADMIN = 1;                   // 管理员用户组类型标识
    const MEMBER = 'member';
    const UCENTER_MEMBER = 'ucenter_member';
    const AUTH_GROUP_ACCESS = 'auth_group_access'; // 关系表表名
    const AUTH_EXTEND = 'auth_extend';       // 动态权限扩展信息表
    const AUTH_GROUP = 'auth_group';        // 用户组表名
    const AUTH_EXTEND_CATEGORY_TYPE = 1;              // 分类权限标识
    const AUTH_EXTEND_MODEL_TYPE = 2; //分类权限标识

    protected $type = array(
        'id' => 'integer',
    );

    public function change()
    {
        $data = input('post.');
        if ($data['id']) {
            $result = $this->isUpdate(true)->save($data, array('id' => $data['id']));
        } else {
            $result = $this->isUpdate(false)->save($data);
        }
        if (false !== $result) {
            return true;
        } else {
            return $this->getError();
        }
    }

    /**
     * 返回用户拥有管理权限的分类id列表
     *
     * @param int $uid 用户id
     * @return array
     *
     *  array(2,4,8,13)
     *
     */
    static public function getAuthModels($uid)
    {
        return self::getAuthExtend($uid, self::AUTH_EXTEND_MODEL_TYPE, 'AUTH_MODEL');
    }

    /**
     * 返回用户拥有管理权限的分类id列表
     *
     * @param int $uid 用户id
     * @return array
     *
     *  array(2,4,8,13)
     *
     */
    static public function getAuthCategories($uid)
    {
        return self::getAuthExtend($uid, self::AUTH_EXTEND_CATEGORY_TYPE, 'AUTH_CATEGORY');
    }

    /**
     * 返回用户拥有管理权限的扩展数据id列表
     *
     * @param int $uid 用户id
     * @param int $type 扩展数据标识
     * @param int $session 结果缓存标识
     * @return array
     *  array(2,4,8,13)
     */
    static public function getAuthExtend($uid, $type, $session)
    {
        if (!$type) {
            return false;
        }
        if ($session) {
            $result = session($session);
        }
        if ($uid == session('member_auth.uid') && !empty($result)) {
            return $result;
        }
        $result = db(self::AUTH_GROUP_ACCESS)->alias('g')
            ->join(config('database.prefix') . self::AUTH_EXTEND . ' c', 'g.group_id=c.group_id')
            ->where("g.uid='$uid' and c.type='$type' and !isnull(extend_id)")
            ->column('extend_id');
        if ($uid == session('member_auth.uid') && $session) {
            session($session, $result);
        }
        return $result;
    }
}