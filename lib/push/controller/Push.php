<?php

namespace app\push\controller;

use GatewayWorker\Lib\Gateway;
use Workerman\MySQL\Connection;

class Push
{
    /*
     * @var array 消息内容
     * */
    protected $message_data = [
        'type' => '',
        'msg' => '',
    ];
    /*
     * @var string 消息类型
     * */
    protected $message_type = '';
    /*
     * @var string $client_id
     * */
    protected $client_id = '';
    /*
     * @var int 当前登陆用户
     * */
    protected $uid = null;
    protected $vodRoom = null;
    protected $db = null;
    /*
     * @var null 本类实例化结果
     * */
    protected static $instance = null;

    protected function __construct($message_data = [])
    {
    }

    /*
     * 实例化本类
     * */
    public static function instance()
    {
        if (is_null(self::$instance)) self::$instance = new static();
        return self::$instance;
    }

    /*
     * 检测参数并返回
     * @param array || string $keyValue 需要提取的键值
     * @param null || bool $value
     * @return array;
     * */
    protected function checkValue($keyValue = null, $value = null)
    {
        if (is_string($keyValue))
            $this->message_data = $this->message_data[$keyValue] ?? (is_null($value) ? '' : $value);
        if (is_array($keyValue))
            $this->message_data = array_merge($keyValue, $this->message_data);
        if (is_bool($value) && $value === true && is_array($this->message_data) && is_array($keyValue)) {
            $newData = [];
            foreach ($keyValue as $key => $item) {
                $newData [] = $this->message_data[$key];
            }
            return $newData;
        }
        return $this->message_data;
    }

    /*
     * 开始设置回调
     * @param string $typeFnName 回调函数名
     * @param string $client_id
     * @param array $message_data
     *
     * */
    public function start($typeFnName, $client_id, $message_data)
    {
        $this->message_type = $typeFnName;
        $this->message_data = $message_data;
        $this->client_id = $client_id;
        if (method_exists($this, $typeFnName))
            call_user_func([$this, $typeFnName]);
        else
            throw new \Exception('缺少回调方法');
    }

    /*
     * 心跳检测
     *
     * */
    protected function ping()
    {
        return;
    }



    protected function init()
    {
        $token = $this->message_data['msg']['token'];
        if (empty($token)) {
            // token不存在时 关闭ws  ? 是否游客也可以看到在线人数 则给游客加一个 9999
            $this->uid = '99999' . rand(11111, 99999);
            Gateway::bindUid($this->client_id, $this->uid);
            $_SESSION['user'] = [
                'userid' => $this->uid,
                'nickname' => '游客' . $this->uid
            ];
            Gateway::bindUid($this->client_id, $_SESSION['user']['userid']);
        } else {
            require_once './lib/push/mysql/Connection.php';
            $dataBase = require './runtime/conf/database.php';
            $this->db = new Connection($dataBase['hostname'], $dataBase['hostport'], $dataBase['username'], $dataBase['password'], $dataBase['database']);
            $userToken = $this->db->select('user_id')->from('zanpiancms_user_token')->where("token='$token'")->row();
            $this->uid = $userToken['user_id'];
            $userInfo = $this->db->select('*')->from('zanpiancms_user')->where("userid='$this->uid'")->row();
            $_SESSION['user'] = $userInfo;
            Gateway::bindUid($this->client_id, $_SESSION['user']['userid']);
        }

    }

    /*
     * 自动进入视频聊天房
     */
    protected function vodRoomJoin()
    {
        $this->checkLogin();
        // 不是播放页直接拒绝消息回执
        if ($this->message_data["msg"]["model"] !== 'home' && $this->message_data["msg"]["controller"] !== 'vod' && $this->message_data["msg"]["action"] !== 'play') {
            Gateway::closeClient($this->client_id);
        }
        //  视频公共房间标识 vodRoom_ 视频ID
        $vodRoom = 'vodRoom_' . $this->message_data["msg"]["videoId"];
        $this->vodRoom = $vodRoom;
        // 加入公共房间
        Gateway::joinGroup($this->client_id, $vodRoom);
        // 所有在线id
        $onLineNumber = Gateway::getUidCountByGroup($vodRoom);
        // 通知自己进入了 标明自己当前状态
        Gateway::sendToUid($this->uid, json_encode([
            'type' => 'notice',
            'nickname' => $_SESSION['user']['nickname'],
            'msgContent' => '在线',
            'status' => 1,
        ]));
        // vodRoomJoinTip 在线人数和在线列表
        Gateway::sendToGroup($vodRoom, json_encode([
            'type' => 'vodRoomJoinTip',
            'onLineNumber' => $onLineNumber,
            'msgContent' => $_SESSION['user']['nickname'] . '进入房间',
            'onLineUser' => Gateway::getClientSessionsByGroup($vodRoom)
        ]));
    }

    // 视频公共房间聊天
    protected function vodRoomChat()
    {
        $this->checkLogin();
        $data = [
            'type' => 'vodRoomChat',
            'uid' => $this->uid,
            'nickname' => $_SESSION['user']['nickname'],
            'avatar' => $_SESSION['user']['avatar'],
            'time' => time(),
            'msgContent' => $this->message_data['msg']['msgContent']
        ];

        Gateway::sendToGroup($this->vodRoom, json_encode($data));
    }

    /*
     * checkLogin
     */
    protected function checkLogin()
    {
        if (!isset($_SESSION['user']['userid'])) {
            Gateway::sendToUid($this->uid, json_encode([
                'type' => 'notice',
                'msgContent' => '验证信息不通过，被拒绝'
            ]));
            Gateway::closeClient($this->client_id);
            return false;
        }
        return true;
    }

    /*
     * 消息撤回
     * @param string $client_id
     * @param array $message_data
     * */
    protected function recall()
    {
        list($id, $room) = $this->checkValue(['id' => 0, 'room' => ''], true);
        if (!$id)
            throw new \Exception('缺少撤回消息的id');
        if (!$room)
            throw new \Exception('缺少房间号');
        if (LiveBarrage::del($id)) {
            Gateway::sendToGroup($room, json_encode([
                'type' => 'recall',
                'id' => $id
            ]), Gateway::getClientIdByUid($this->uid));
        }
    }


}